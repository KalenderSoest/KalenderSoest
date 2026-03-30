<?php

namespace App\Controller;

use App\Entity\DfxKonf;
use App\Entity\DfxNfxUser;
use App\Entity\DfxTermine;
use App\Service\Frontend\CodeChallengeService;
use App\Service\Calendar\CalendarScopeResolver;
use App\Service\Calendar\FrontendTerminFormFactory;
use App\Service\Calendar\FrontendTerminNotificationService;
use App\Service\Calendar\FrontendTerminUploadService;
use App\Service\Calendar\SharedMediaDeletionService;
use App\Service\Calendar\TerminWriteWorkflowService;
use App\Service\Presentation\HtmlResponseService;
use App\Service\Presentation\TemplatePathResolver;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DfxKalenderTermineController extends AbstractController
{
    public function __construct(
        private readonly CodeChallengeService $codeChallengeService,
        private readonly SharedMediaDeletionService $sharedMediaDeletionService,
        private readonly FrontendTerminFormFactory $frontendTerminFormFactory,
        private readonly FrontendTerminNotificationService $frontendTerminNotificationService,
        private readonly FrontendTerminUploadService $frontendTerminUploadService,
        private readonly HtmlResponseService $htmlResponseService,
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly TerminWriteWorkflowService $terminWriteWorkflowService,
        private readonly EntityManagerInterface $em,
        private readonly TokenStorageInterface $usageTrackingTokenStorage,
    ) {
    }

    #[Route(path: '/js/kalender/{kid}/new', name: 'gast_termin_new', methods: ['GET'])]
    public function newAction(int $kid): Response
    {
        $konf = $this->loadKonf($kid);
        $entity = new DfxTermine();
        $entity->setDatefix($konf);
        $entity->setZeit(new DateTime('00:00:00'));
        $entity->setZeitBis(new DateTime('00:00:00'));

        $captcha = $this->codeChallengeService->create();
        $calendarIds = $this->calendarScopeResolver->resolveReadScope($konf)->ids();
        $form = $this->frontendTerminFormFactory->createWriteForm(
            $entity,
            $captcha,
            'gast_termin_create',
            ['kid' => $kid],
            'Datensatz speichern',
            ['arKids' => $calendarIds]
        );
        $filterForm = $this->frontendTerminFormFactory->createFilterForm($konf, $this->calendarScopeResolver->resolveReadScope($konf));

        $tpl = $this->templatePathResolver->resolve('Kalender', 'gast_termin_form.html.twig', $konf);

        return $this->htmlResponseService->render($tpl, [
            'termin' => $entity,
            'konf' => $konf,
            'code' => $captcha,
            'serie' => 0,
            'form' => $form->createView(),
            'filter_form' => $filterForm->createView(),
        ]);
    }

    #[Route(path: '/js/kalender/{kid}/create', name: 'gast_termin_create', methods: ['POST'])]
    public function create(Request $request, int $kid): Response
    {
        $datefix = $this->loadKonf($kid);
        $entity = new DfxTermine();
        $entity->setDatefix($datefix);

        $user = $this->loadFrontendAuthor($datefix);
        $entity->setUser($user);

        $captcha = $this->codeChallengeService->create();
        $calendarIds = $this->calendarScopeResolver->resolveReadScope($datefix)->ids();
        $form = $this->frontendTerminFormFactory->createWriteForm(
            $entity,
            $captcha,
            'gast_termin_create',
            ['kid' => $kid],
            'Datensatz speichern',
            ['arKids' => $calendarIds]
        );
        $form->handleRequest($request);

        $error = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $this->terminWriteWorkflowService->mergeSeriesDateInputs(
                $entity,
                (string) $form->get('datumSerie')->getData(),
                (string) $form->get('datum_s_liste')->getData(),
            );
            if ($this->codeChallengeService->isValid((string) $form->get('cCode')->getData(), (string) $form->get('key')->getData())) {
                $this->terminWriteWorkflowService->prepareCreate(
                    $entity,
                    new DateTime(date('Y-m-d H:i:s')),
                    $user->getNameLang(),
                    $datefix,
                    true,
                    true,
                    true,
                );
                $this->frontendTerminUploadService->applyLegacyUploads($entity, $kid);
                $this->frontendTerminUploadService->applyAjaxUploads($entity, $request, $kid);

                if ($this->terminWriteWorkflowService->hasSeriesDates($entity)) {
                    $savedTermin = $this->terminWriteWorkflowService->createSeriesFromPrototype($entity);
                    $options = ['termin' => $savedTermin, 'konf' => $datefix];
                } else {
                    $options = ['termin' => $this->terminWriteWorkflowService->persistSingle($entity), 'konf' => $datefix];
                }

                $this->frontendTerminNotificationService->notifyWrite(
                    $options['termin'],
                    $datefix,
                    $_POST,
                    (string) $form->get('gastEmail')->getData(),
                    'Ihr Veranstaltungseintrag',
                    'Neuer Veranstaltungseintrag'
                );

                return $this->renderKalenderTemplate('gast_termin_show.html.twig', $datefix, $options);
            }

            $error .= 'Fehler cC';
        }

        $error .= $form->getErrors(true);

        return $this->renderKalenderTemplate('gast_termin_form.html.twig', $datefix, [
            'termin' => $entity,
            'konf' => $datefix,
            'code' => $captcha,
            'serie' => 0,
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    #[Route(path: '/js/kalender/{id}/edit/{init}', name: 'gast_termin_edit', methods: ['GET'])]
    public function edit(int $id, string $init): Response
    {
        $entity = $this->loadFrontendTerminByInit($id, $init, 'Unable to find DfxTermine or wrong Init-Code.');
        $datefix = $entity->getDatefix();
        $this->initializeEmptyTimes($entity);

        $captcha = $this->codeChallengeService->create();
        $form = $this->frontendTerminFormFactory->createWriteForm(
            $entity,
            $captcha,
            'gast_termin_update',
            ['id' => $entity->getId(), 'init' => $entity->getInit()],
            'Änderungen speichern'
        );

        return $this->renderKalenderTemplate('gast_termin_form_edit.html.twig', $datefix, [
            'termin' => $entity,
            'konf' => $datefix,
            'code' => $captcha,
            'serie' => $entity->getCode() !== null ? 1 : 0,
            'form' => $form->createView(),
        ]);
    }

    #[Template('Kalender/gast_termin_form.html.twig')]
    #[Route(path: '/js/kalender/{id}/update/{init}', name: 'gast_termin_update', methods: ['POST'])]
    public function update(Request $request, int $id, string $init): Response
    {
        $entity = $this->loadFrontendTerminByInit($id, $init, 'Unable to find DfxTermine oder falscher Init-Code.');
        $konf = $entity->getDatefix();
        $orgDatum = $entity->getDatum();
        $orgDatumVon = $entity->getDatumVon();
        $serie = $entity->getCode() !== null ? 1 : 0;

        $captcha = $this->codeChallengeService->create();
        $form = $this->frontendTerminFormFactory->createWriteForm(
            $entity,
            $captcha,
            'gast_termin_update',
            ['id' => $entity->getId(), 'init' => $entity->getInit()],
            'Änderungen speichern'
        );
        $form->handleRequest($request);

        $error = '';
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->codeChallengeService->isValid((string) $form->get('cCode')->getData(), (string) $form->get('key')->getData())) {
                $now = new DateTime(date('Y-m-d H:i:s'));
                $this->terminWriteWorkflowService->prepareUpdate(
                    $entity,
                    $now,
                    null,
                    $konf,
                    true,
                    true,
                );
                $this->frontendTerminUploadService->applyLegacyUploads($entity, $konf->getId(), true);
                $this->frontendTerminUploadService->applyAjaxUploads($entity, $request, $konf->getId());

                if ($entity->getCode() === null) {
                    $this->terminWriteWorkflowService->persistSingle($entity);
                } else {
                    $entity->setDatum($orgDatum);
                    $entity->setDatumVon($orgDatumVon);
                    $entity = $this->terminWriteWorkflowService->updateSeriesByCode(
                        $entity,
                        fn (DfxTermine $termin, DfxTermine $source): null => $this->terminWriteWorkflowService->copyFrontendSeriesFields($termin, $source, $now)
                    );
                }

                $this->frontendTerminNotificationService->notifyWrite(
                    $entity,
                    $konf,
                    $_POST,
                    (string) $form->get('gastEmail')->getData(),
                    'Ihr geänderter Veranstaltungseintrag',
                    'Geänderter Veranstaltungseintrag'
                );

                return $this->renderKalenderTemplate('gast_termin_show.html.twig', $konf, [
                    'termin' => $entity,
                    'konf' => $konf,
                ]);
            }

            $error .= 'Fehler cC ';
        }

        $error .= $form->getErrors(true);

        return $this->renderKalenderTemplate('gast_termin_form.html.twig', $konf, [
            'termin' => $entity,
            'konf' => $konf,
            'serie' => $serie,
            'code' => $captcha,
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    #[Route(path: '/js/kalender/{id}/delete/{init}', name: 'gast_termin_delete', methods: ['GET'])]
    public function delete(int $id, string $init): Response
    {
        $entity = $this->loadFrontendTerminByInit($id, $init, 'Unable to find DfxTermine or wrong Init-Code.');
        $konf = $entity->getDatefix();
        $code = $entity->getCode();

        if (empty($code)) {
            $this->sharedMediaDeletionService->deleteTerminFiles($entity);
            $this->em->remove($entity);
            $this->em->flush();
        } else {
            $this->sharedMediaDeletionService->deleteTerminFiles($entity);
            $this->em
                ->createQueryBuilder()
                ->delete(DfxTermine::class, 't')
                ->where('t.code = :code')
                ->setParameter('code', $code)
                ->getQuery()
                ->execute();
        }

        return $this->renderKalenderTemplate('deleted.html.twig', $konf, ['entity' => $entity, 'konf' => $konf]);
    }

    private function ensureFrontendUsageToken(DfxNfxUser $user, DfxKonf $konf): void
    {
        $user->setDatefix($konf);
        $token = new UsernamePasswordToken($user, 'main', ['IS_AUTHENTICATED_ANONYMOUSLY']);
        $this->usageTrackingTokenStorage->setToken($token);
    }

    private function loadFrontendAuthor(DfxKonf $konf): DfxNfxUser
    {
        $user = $this->em->getRepository(DfxNfxUser::class)->find(99);
        if (!$user instanceof DfxNfxUser) {
            throw $this->createNotFoundException('Unable to find frontend DfxNfxUser entity.');
        }

        $this->ensureFrontendUsageToken($user, $konf);

        return $user;
    }

    private function loadFrontendTerminByInit(int $id, string $init, string $message): DfxTermine
    {
        $entity = $this->em->getRepository(DfxTermine::class)->find($id);
        if (!$entity instanceof DfxTermine || $entity->getInit() !== $init) {
            throw $this->createNotFoundException($message);
        }

        $konf = $entity->getDatefix();
        if (!$konf instanceof DfxKonf) {
            throw $this->createNotFoundException('Unable to find DfxKonf entity.');
        }

        $user = $entity->getUser();
        if (!$user instanceof DfxNfxUser) {
            throw $this->createNotFoundException('Unable to find DfxNfxUser entity.');
        }

        $this->ensureFrontendUsageToken($user, $konf);

        return $entity;
    }

    private function initializeEmptyTimes(DfxTermine $entity): void
    {
        if ($entity->getZeit() === null) {
            $entity->setZeit(new DateTime('00:00:00'));
        }

        if ($entity->getZeitBis() === null) {
            $entity->setZeitBis(new DateTime('00:00:00'));
        }
    }

    private function loadKonf(int $kid): DfxKonf
    {
        $konf = $this->em->getRepository(DfxKonf::class)->find($kid);
        if ($konf === null) {
            throw $this->createNotFoundException('Unable to find DfxKonf entity.');
        }

        return $konf;
    }

    private function renderKalenderTemplate(string $template, DfxKonf $konf, array $parameters): Response
    {
        return $this->htmlResponseService->render(
            $this->templatePathResolver->resolve('Kalender', $template, $konf),
            $parameters
        );
    }
}
