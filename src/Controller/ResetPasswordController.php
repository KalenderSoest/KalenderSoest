<?php

namespace App\Controller;

use App\Entity\DfxNfxUser;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\DfxNfxUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Display & process form to request a password reset.
     * @throws TransportExceptionInterface
     */
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer): Response
    {
        $postedForm = $request->request->all('reset_password_request_form');
        $requireUsername = (($postedForm['require_username'] ?? '0') === '1');
        $form = $this->createForm(ResetPasswordRequestFormType::class, null, [
            'require_username' => $requireUsername,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();
            /** @var string|null $username */
            $username = $form->has('username') ? $form->get('username')->getData() : null;

            return $this->processSendingPasswordResetEmail($email, $username, $mailer);
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form,
            'requireUsername' => $requireUsername,
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();

        if (null === $token) {
            throw $this->createNotFoundException('Es wurde kein gueltiger Passwort-Reset-Token in der URL oder Sitzung gefunden.');
        }

        try {
            /** @var DfxNfxUser $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encode(hash) the plain password, and set it.
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $this->entityManager->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function processSendingPasswordResetEmail(string $emailFormData, ?string $usernameFormData, MailerInterface $mailer): Response
    {
        /** @var DfxNfxUserRepository $repository */
        $repository = $this->entityManager->getRepository(DfxNfxUser::class);
        $emailMatchCount = $repository->countByEmail($emailFormData);

        if ($emailMatchCount > 1 && empty($usernameFormData)) {
            $this->addFlash('reset_password_error', 'Diese E-Mail-Adresse ist mehreren Benutzerkonten zugeordnet. Bitte zusätzlich die Usernummer angeben, mit der Sie sich anmelden.');

            return $this->render('reset_password/request.html.twig', [
                'requestForm' => $this->createForm(ResetPasswordRequestFormType::class, [
                    'email' => $emailFormData,
                ], [
                    'require_username' => true,
                ])->createView(),
                'requireUsername' => true,
            ]);
        }

        $user = null;

        if ($emailMatchCount === 1) {
            $user = $repository->findOneBy([
                'email' => $emailFormData,
            ]);
        } elseif ($emailMatchCount > 1 && !empty($usernameFormData)) {
            $user = $repository->findOneByEmailAndUsername($emailFormData, $usernameFormData);

            if (!$user) {
                $this->addFlash('reset_password_error', 'Die Kombination aus E-Mail-Adresse und Usernummer konnte nicht zugeordnet werden.');

                return $this->render('reset_password/request.html.twig', [
                    'requestForm' => $this->createForm(ResetPasswordRequestFormType::class, [
                        'email' => $emailFormData,
                    ], [
                        'require_username' => true,
                    ])->createView(),
                    'requireUsername' => true,
                ]);
            }
        }

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
            //     $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            // ));

            return $this->redirectToRoute('app_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('info@datefix.de', 'Datefix Passwortservice'))
            ->to((string) $user->getEmail())
            ->subject('Datefix: Passwort zuruecksetzen')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
