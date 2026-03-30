<?php

namespace App\Controller;

use App\Entity\DfxNfxUser;
use App\Service\Install\InstallFormFactory;
use App\Service\Install\InstallerProvisioningService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DfxNfxKunden;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\CurrentContext;

// cuse App\Form\Model\Registration;
/**
 * User controller.
 */
#[IsGranted('ROLE_ADMIN')]
class RegisterController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CurrentContext $currentContext,
        private readonly InstallFormFactory $installFormFactory,
        private readonly InstallerProvisioningService $installerProvisioningService,
    )
    {
    }


	/**
     * Displays a form to create a new Kunden entity.
     *

     */
    #[Template('Admin/register.html.twig')]
    #[Route(path: 'admin/register/', name: 'register', methods: ['GET'])]
    public function index(): Response
    {
        $entity = new DfxNfxKunden();
        $form = $this->installFormFactory->createAdminRegisterForm($entity);

        return $this->render(
            'Admin/register.html.twig',
            ['form' => $form]
        );
    }

    /**
     * Creates a new User entity.
     *

     * @param Request $request
     * @return array|Response
     * @throws Exception
     */
    #[Template("Admin/register.html.twig")]
    #[Route(path: 'admin/register/account', name: 'account_create', methods: ['POST'])]
    public function createAccount(Request $request): Response|array
    {
        $newKunde = new DfxNfxKunden();
        $form = $this->installFormFactory->createAdminRegisterForm($newKunde);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->installerProvisioningService->createRegisteredAccount(
                $newKunde,
                (string) $form->get('password')->getData()
            );

            return $this->render(
                'Admin/register3.html.twig',
                ['user' => $user]
            );
        }

        return [
            'entity' => $newKunde,
            'form'   => $form->createView(),
        ];
    }
    /**
     * Displays a form to edit an existing DfxNfxKunden entity.
     *
     * @return array
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Template("Admin/kunde_edit.html.twig")]
    #[Route(path: 'admin/register/{id}/edit', name: 'kunde_edit', methods: ['GET'])]
    public function edit(): array
    {
    	$user = $this->currentContext->getUser();
    	$entity = $user->getKunde();
    	$form   = $this->installFormFactory->createAdminEditForm($entity);

    	return [
    			'entity' => $entity,
    			'form'   => $form->createView(),

    	];

    }

    /**
     * Edits an existing DfxNfxKunden entity.
     *
     * @param Request $request
     * @return array|RedirectResponse
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Template("Admin/kunde_edit.html.twig")]
    #[Route(path: 'admin/register/{id}', name: 'kunde_update', methods: ['PUT'])]
    public function update(Request $request): RedirectResponse|array
    {
    	$user = $this->currentContext->getUser();
    	$entity = $user->getKunde();
    	$form = $this->installFormFactory->createAdminEditForm($entity);
    	$form->handleRequest($request);

    	if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            return $this->redirectToRoute('admin');
    	}

      	return [
            'entity' => $entity,
            'form'   => $form->createView(),

    	];
    }


}
