<?php

namespace App\Controller;

use App\Entity\DfxKonf;
use App\Form\AdminNewsFilterType;
use App\Service\Calendar\AdminAccessGuard;
use App\Service\Calendar\AdminNewsFormFactory;
use App\Service\Calendar\AdminMediaFileService;
use App\Service\Calendar\AdminNewsFilterData;
use App\Service\Calendar\AdminNewsListQueryFactory;
use App\Service\Calendar\AdminNewsNotificationService;
use App\Service\Calendar\AdminPublicationWriteService;
use App\Service\Calendar\CalendarScopeResolver;
use App\Service\Calendar\SharedMediaDeletionService;
use App\Service\Presentation\PdfResponseService;
use App\Service\Presentation\TemplatePathResolver;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DfxNews;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\CurrentContext;

class DfxNewsController extends AbstractController
{


    public function __construct(private readonly TemplatePathResolver $templatePathResolver, private readonly PdfResponseService $pdfResponseService, private readonly CalendarScopeResolver $calendarScopeResolver, private readonly AdminNewsListQueryFactory $adminNewsListQueryFactory, private readonly AdminAccessGuard $adminAccessGuard, private readonly AdminPublicationWriteService $adminPublicationWriteService, private readonly AdminMediaFileService $adminMediaFileService, private readonly AdminNewsNotificationService $adminNewsNotificationService, private readonly AdminNewsFormFactory $adminNewsFormFactory, private readonly SharedMediaDeletionService $sharedMediaDeletionService, private readonly EntityManagerInterface $em, private readonly CurrentContext $currentContext)
    {
        }


    /**
     * Lists all DfxTermine entities.
     *
     * @param $action
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param PaginatorInterface $paginator2
     * @param PaginatorInterface $paginator3
     * @param PaginatorInterface $paginatorMeta
     * @param PaginatorInterface $paginatorGroup
     * @return Response
     */
    #[Route(path: '/admin/news/{action}', name: 'admin_news', defaults: ['action'=>''], methods: ['GET'])]
    public function index($action, Request $request, PaginatorInterface $paginator, PaginatorInterface $paginator2, PaginatorInterface $paginator3, PaginatorInterface $paginatorMeta, PaginatorInterface $paginatorGroup): Response
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();
        $strFilter = null;
        if (!$konf) {
    		$errorString = "Fehler: Es ist kein Datefix-Account mit diesem User verknüpft";
    		return new Response($errorString);
    	}

    	// Baue Newsabfrage
    	$nItems = 20;
    	$form = $this->createFilterForm($konf);
    	// Werte Formular aus
    	$form->handleRequest($request);
    	$session = $request->getSession();
        if($action === 'reset'){
            $session->clear();
        }

        $filterData = $this->createAdminNewsFilterData($form, $session);
        $strFilter = $this->buildAdminNewsFilterSummary($filterData);

        $calendarScope = $this->calendarScopeResolver->resolveAdminReadScope(
            $konf,
            $filterData->hideSub,
            true === $this->isGranted('ROLE_DFX_GROUP'),
        );

        $queries = $this->adminNewsListQueryFactory->build(
            $konf,
            $calendarScope,
            $filterData,
            false === $this->isGranted('ROLE_DFX_ALL') ? $user->getId() : null,
        );


    	$news = $paginator->paginate(
    			$queries['published'],
    			$request->query->getInt('dfxp', 1)/*page number*/,
    			$nItems /*limit per page*/
    	);

    	$news2 = $paginator2->paginate(
    			$queries['unpublished'],
    			$request->query->getInt('dfxp', 1)/*page number*/,
    			$nItems /*limit per page*/
    	);

    	$newsMeta = $paginatorMeta->paginate(
    			$queries['metaPending'],
    			$request->query->getInt('dfxp', 1)/*page number*/,
    			$nItems /*limit per page*/
    	);

    	$newsGroup = $paginatorGroup->paginate(
    			$queries['groupPending'],
    			$request->query->getInt('dfxp', 1)/*page number*/,
    			$nItems /*limit per page*/
    	);

    	$news3 = $paginator3->paginate(
    			$queries['archived'],
    			$request->query->getInt('dfxp', 1)/*page number*/,
    			$nItems /*limit per page*/
    	);



        $tpl = $this->templatePathResolver->resolve('DfxNews','index.html.twig', $konf);
        return $this->render($tpl, ['filter_form' => $form, 'news' => $news, 'news2' => $news2, 'news3' => $news3,  'newsMeta' => $newsMeta, 'newsGroup' => $newsGroup, 'konf' => $konf, 'filter' => $strFilter]);

	}


    /**
     * Creates a Filterform.
     *
     *
     * @param int $kid
     * @param array $arRubriken
     * @param array $arZielgruppen
     * @return FormInterface The form
     */
	private function createFilterForm(DfxKonf $konf): FormInterface
    {
        return $this->createForm(AdminNewsFilterType::class, null, [
            'konf' => $konf,
            'action' => $this->generateUrl('admin_news', ['kid' => $konf->getId()]),
            'method' => 'GET',
            'attr' => ['name' => 'filter', 'id' => 'filter'],
        ]);
	}

    private function createAdminNewsFilterData(FormInterface $form, SessionInterface $session): AdminNewsFilterData
    {
        $data = $form->isSubmitted() && $form->isValid() ? (array) $form->getData() : [];

        return new AdminNewsFilterData(
            rubrik: $this->syncSessionString($session, 'rubrik', $data),
            zielgruppe: $this->syncSessionString($session, 'zielgruppe', $data),
            suche: $this->syncSessionString($session, 'suche', $data),
            hideSub: $this->syncSessionBool($session, 'hideSub', $data),
            filterPub: $this->syncSessionBool($session, 'filterPub', $data),
        );
    }

    private function buildAdminNewsFilterSummary(AdminNewsFilterData $filterData): ?string
    {
        $parts = [];

        if ($filterData->rubrik !== null) {
            $parts[] = 'Rubrik ' . $filterData->rubrik;
        }
        if ($filterData->zielgruppe !== null) {
            $parts[] = 'Zielgruppe ' . $filterData->zielgruppe;
        }
        if ($filterData->suche !== null) {
            $parts[] = 'Suche ' . $filterData->suche;
        }

        if ($parts === []) {
            return null;
        }

        return '<strong>Aktive Filter: ' . implode(', ', $parts) . '</strong>';
    }

    private function syncSessionString(SessionInterface $session, string $key, array $data): ?string
    {
        if (array_key_exists($key, $data)) {
            $value = trim((string) $data[$key]);
            if ($value === '') {
                $session->remove($key);

                return null;
            }

            $session->set($key, $value);

            return $value;
        }

        $value = $session->get($key);
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return $value;
    }

    private function syncSessionBool(SessionInterface $session, string $key, array $data): bool
    {
        if (array_key_exists($key, $data)) {
            $value = (bool) $data[$key];
            $session->set($key, $value);

            return $value;
        }

        return (bool) $session->get($key, false);
    }

	    /**
     * Creates a new DfxNews entity.
     */
     #[Template("DfxNews/new.html.twig")]
    #[Route(path: '/admin/news/add/create', name: 'news_create', methods: ['POST'])]
    public function create(Request $request): RedirectResponse|Response
    {
        $entity = new DfxNews();
        $user = $this->currentContext->getUser();
        $konf = $user->getDatefix();
        $kid = $konf->getId();
        $entity->setUser($user);
        $entity->setDatefix($konf);
        $calendarIds = $this->calendarScopeResolver->resolveReadScope($konf)->ids();
        $form = $this->adminNewsFormFactory->createCreateForm($entity, $konf, $calendarIds);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new DateTime(date("Y-m-d H:i:s"));
        	$entity->setDatumInput($now);
        	$entity->setAutor($user->getNameLang());

            if (false === $this->isGranted('ROLE_DFX_PUB')) {
        		$entity->setPub(0);
        	}

        	if (false === $this->isGranted('ROLE_DFX_META')) {
        		$entity->setPubMeta(0);
        	}

        	if (false === $this->isGranted('ROLE_DFX_GROUP')) {
        		$entity->setPubGroup(0);
        	}

            if($konf->getPubMetaAll() == 1){
                $entity->setPubMeta(1);
            }

            if($konf->getPubGroupAll() == 1){
                $entity->setPubGroup(1);
            }

            $this->adminMediaFileService->applyUploadedFiles($request, 'news', $entity, $kid);


        		$newsItem_end = $entity;
        		$this->em->persist($entity);
        		$this->em->flush();



            $this->adminNewsNotificationService->notifyWrite(
                'create',
                $newsItem_end,
                $konf,
                $user,
                true === $this->isGranted('ROLE_DFX_PUB'),
                true === $this->isGranted('ROLE_DFX_META'),
                true === $this->isGranted('ROLE_DFX_GROUP'),
            );

           	return $this->redirectToRoute('admin_news');
        }

         // Überprüfe auf individuelles Template
        $tpl = $this->templatePathResolver->resolve('DfxNews','new.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form'   => $form]);

     }

   /**
    * Displays a form to create a new DfxNews entity.
    *

    */
   #[Template("DfxNews/new.html.twig")]
   #[Route(path: '/admin/news/add/artikel', name: 'artikel_new', methods: ['GET'])]
   public function new(): Response
   {
        $entity = new DfxNews();
        $user = $this->currentContext->getUser();
        $konf = $user -> getDatefix();
        $entity->setDatefix($konf);
        $calendarIds = $this->calendarScopeResolver->resolveAssignmentScope($konf)->ids();
        $form   = $this->adminNewsFormFactory->createCreateForm($entity, $konf, $calendarIds);
        $tpl = $this->templatePathResolver->resolve('DfxNews','new.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form' => $form]);
   }


    /**
     * Displays a form to edit an existing DfxNews entity.
     *
     * @Template
     * @param DfxNews $entity
     * @return Response
     */
    #[Route(path: '/admin/news/{id}/edit', name: 'news_edit', methods: ['GET'])]
    public function edit(#[MapEntity(id: 'id')] DfxNews $entity): Response
    {
    	$user = $this->currentContext->getUser();
    	$konf= $user->getDatefix();
        $entity = $this->adminAccessGuard->requireEntityForGroupScope(
            $entity->getId(),
            DfxNews::class,
            $user,
            'Unable to find DfxNews entity.',
            'Unerlaubter Zugriff auf Datensatz eines anderen Accounts durch User' . $user->getId() . ' .',
        );

        $editForm = $this->adminNewsFormFactory->createEditForm($entity, $konf);

		 // Überprüfe auf individuelles template
        $tpl = $this->templatePathResolver->resolve('DfxNews','edit.html.twig', $konf);
    	return $this->render($tpl, [
				'entity' => $entity,
				'konf' => $konf,
				'form'   => $editForm,


		]);

    }

    /**
     * Edits an existing DfxNews entity.
     *

     * @param Request $request
     * @param DfxNews $entity
     * @return RedirectResponse|Response
     */
    #[Template("DfxNews/edit.html.twig")]
    #[Route(path: '/admin/news/{id}/update', name: 'news_update', methods: ['POST'])]
    public function update(Request $request, #[MapEntity(id: 'id')] DfxNews $entity): RedirectResponse|Response
    {
    	$user = $this->currentContext->getUser();
        $entity = $this->adminAccessGuard->requireEntityForGroupScope(
            $entity->getId(),
            DfxNews::class,
            $user,
            'Unable to find DfxNews entity.',
        );
        $konf = $entity->getDatefix();
        $kid = $konf->getId();

        $editForm = $this->adminNewsFormFactory->createEditForm($entity, $konf);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
        	$now = new DateTime(date("Y-m-d H:i:s"));


        	$entity->setDatumModified($now);
        	$entity->setAutor($user->getNameLang());
            $this->adminMediaFileService->clearMarkedFiles($editForm, $entity, $kid);
            $this->adminMediaFileService->applyUploadedFiles($request, 'news', $entity, $kid);


            $this->em->flush();
            $this->adminNewsNotificationService->notifyWrite(
                'update',
                $entity,
                $konf,
                $user,
                true === $this->isGranted('ROLE_DFX_PUB'),
                true === $this->isGranted('ROLE_DFX_META'),
                true === $this->isGranted('ROLE_DFX_GROUP'),
            );

            return $this->redirectToRoute('admin_news');
        }

        // Überprüfe auf individuelles template
        $tpl = $this->templatePathResolver->resolve('DfxNews','edit.html.twig', $konf);
        return $this->render($tpl, [
        		'entity' => $entity,
        		'konf' => $konf,
        		'form'   => $editForm,

        ]);
    }

    /**
     * Deletes a DfxNews entity.
     *
     * @param DfxNews $entity
     * @param $code
     * @return RedirectResponse
     */
    #[Route(path: '/admin/news/{id}/delete/{code}', name: 'news_delete', defaults: ['code' => '}'], methods: ['GET'])]
    public function delete(#[MapEntity(id: 'id')] DfxNews $entity, $code): RedirectResponse
    {
        $user = $this->currentContext->getUser();

        if($code === null){
            $entity = $this->adminAccessGuard->requireEntityForGroupScope(
                $entity->getId(),
                DfxNews::class,
                $user,
                'Unable to find DfxNews entity.',
            );

            $this->sharedMediaDeletionService->deleteNewsFiles($entity);
	        $this->em->remove($entity);
	        $this->em->flush();
        }else{
        	 $this->em->createQueryBuilder()
        	 ->delete(DfxNews::class, 'n')
        	 ->where('n.code = :code')
        	 ->setParameter('code', $code)
        	 ->getQuery()->execute();
        }

        return $this->redirectToRoute('admin_news');
   }

    /**
     * Deletes a Liets of DfxNews entities.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    #[Route(path: '/admin/news/delete/delete_liste', name: 'news_delete_liste', methods: ['POST'])]
    public function deleteListe(Request $request): RedirectResponse
    {
        $user = $this->currentContext->getUser();
        $items = $request->request->get('deleteList');
        foreach ($items as $item){
            $entity = $this->adminAccessGuard->requireEntityForGroupScope(
                (int) $item,
                DfxNews::class,
                $user,
                'Unable to find DfxNews entity.',
                'Unerlaubter Zugriff auf Datensatz (Termin ID ' . $item . ' eines anderen Accounts.',
            );

            $this->sharedMediaDeletionService->deleteNewsFiles($entity);
            $this->em->remove($entity);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_news');
    }


    /**
     * Lists all DfxAnmeldungen of one Event in a PDF.
     *
     * @param DfxNews $newsItem
     * @return never
     */
   #[IsGranted('ROLE_ADMIN')]
   #[Route(path: '/admin/news/pdf/{id}', name: 'news_pdf', methods: ['GET'])]
   public function pdf(#[MapEntity(id: 'id')] DfxNews $newsItem): Response
   {
   	$konf = $newsItem->getDatefix();
   // überprüfe auf individuelles Template
    $tpl = $this->templatePathResolver->resolve('News','detail_pdf.html.twig', $konf);
   	$html = $this->render($tpl, ['newsItem' => $newsItem, 'konf' => $konf])->getContent();

    return $this->pdfResponseService->render($html, 'newsItem_'.$newsItem->getId().'.pdf' );
 }

    /**
     * Unpub a DfxNews entity.
     *
     * @param DfxNews $entity
     * @return RedirectResponse
     */
   #[IsGranted('ROLE_DFX_PUB')]
   #[Route(path: '/admin/news/{id}/unpub', name: 'news_unpub', methods: ['GET'])]
   public function unpub(#[MapEntity(id: 'id')] DfxNews $entity): RedirectResponse
   {
   	$user = $this->currentContext->getUser();
        $entity = $this->adminAccessGuard->requireEntityForGroupScope(
            $entity->getId(),
            DfxNews::class,
            $user,
            'Unable to find DfxNews entity.',
        );

    $this->adminPublicationWriteService->setPublished($entity, false);

       return $this->redirectToRoute('admin_news');
   }

    /**
     * Unpub a DfxNews entity.
     *
     * @param DfxNews $entity
     * @return RedirectResponse
     */
    #[IsGranted('ROLE_DFX_PUB')]
    #[Route(path: '/admin/news/{id}/pub', name: 'news_pub', methods: ['GET'])]
   public function pub(#[MapEntity(id: 'id')] DfxNews $entity): RedirectResponse
   {
   	$user = $this->currentContext->getUser();
   	$konf= $user -> getDatefix();
        $entity = $this->adminAccessGuard->requireEntityForGroupScope(
            $entity->getId(),
            DfxNews::class,
            $user,
            'Unable to find DfxNews entity.',
        );

    $this->adminPublicationWriteService->setPublished($entity, true, $konf);

   	return $this->redirectToRoute('admin_news');
   }


    /**
     * UnpubMeta a DfxNews entity.
     *
     * @param DfxNews $entity
     * @return Response
     */
    #[IsGranted('ROLE_DFX_PUB')]
    #[Route(path: '/admin/news/{id}/unpubmeta', name: 'news_unpubmeta', methods: ['GET'])]
   public function unpubMeta(#[MapEntity(id: 'id')] DfxNews $entity): Response
   {
   	$user = $this->currentContext->getUser();
        $entity = $this->adminAccessGuard->requireEntityForMetaScope(
            $entity->getId(),
            DfxNews::class,
            $user,
            'Unable to find DfxNews entity.',
        );

    $this->adminPublicationWriteService->setMetaPublished($entity, false);

   	return new Response('ok');
   }

    /**
     * pubMeta a DfxNews entity.
     *
     * @param DfxNews $entity
     * @return Response
     */
    #[IsGranted('ROLE_DFX_PUB')]
    #[Route(path: '/admin/news/{id}/pubmeta', name: 'news_pubmeta', methods: ['GET'])]
   public function pubMeta(#[MapEntity(id: 'id')] DfxNews $entity): Response
   {
   	$user = $this->currentContext->getUser();
        $entity = $this->adminAccessGuard->requireEntityForMetaScope(
            $entity->getId(),
            DfxNews::class,
            $user,
            'Unable to find DfxNews entity.',
        );

    $this->adminPublicationWriteService->setMetaPublished($entity, true);

   	return new Response('ok');
   }

    /**
     * UnpubGroup a DfxNews entity.
     *
     * @param DfxNews $entity
     * @return Response
     */
    #[IsGranted('ROLE_DFX_PUB')]
    #[Route(path: '/admin/news/{id}/unpubgroup/', name: 'news_unpubgroup', methods: ['GET'])]
   public function unpubGroup(#[MapEntity(id: 'id')] DfxNews $entity): Response
   {
   	$user = $this->currentContext->getUser();
        $entity = $this->adminAccessGuard->requireEntityForGroupScope(
            $entity->getId(),
            DfxNews::class,
            $user,
            'Unable to find DfxNews entity.',
        );

    $this->adminPublicationWriteService->setGroupPublished($entity, false);

   	return new Response('ok');
   }

    /**
     * pubGroup a DfxNews entity.
     *
     * @param DfxNews $entity
     * @return Response
     */
    #[IsGranted('ROLE_DFX_PUB')]
    #[Route(path: '/admin/news/{id}/pubgroup/', name: 'news_pubgroup', methods: ['GET'])]
   public function pubGroup(#[MapEntity(id: 'id')] DfxNews $entity): Response
   {
   	$user = $this->currentContext->getUser();
        $entity = $this->adminAccessGuard->requireEntityForGroupScope(
            $entity->getId(),
            DfxNews::class,
            $user,
            'Unable to find DfxNews entity.',
        );

    $this->adminPublicationWriteService->setGroupPublished($entity, true);
   	return new Response('ok');
   }
}
