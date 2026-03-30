<?php
namespace App\Controller;
use App\Entity\DfxKonf;
use App\Form\AdminTermineFilterType;
use App\Service\Calendar\AdminAccessGuard;
use App\Service\Calendar\AdminTerminFormFactory;
use App\Service\Calendar\AdminMediaFileService;
use App\Service\Calendar\AdminTerminNotificationService;
use App\Service\Calendar\AdminPublicationWriteService;
use App\Service\Calendar\AdminTerminSeriesMediaService;
use App\Service\Calendar\AdminTermineFilterData;
use App\Service\Calendar\AdminTermineListQueryFactory;
use App\Service\Calendar\CalendarScopeResolver;
use App\Service\Calendar\SharedMediaDeletionService;
use App\Service\Calendar\TerminDuplicateChecker;
use App\Service\Calendar\TerminWriteWorkflowService;
use App\Service\Presentation\PdfResponseService;
use App\Service\Presentation\TemplatePathResolver;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DfxTermine;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\CurrentContext;

/**
 * DfxTermine controller.
 */
class DfxTermineController extends AbstractController
{


    public function __construct(private readonly TemplatePathResolver $templatePathResolver, private readonly PdfResponseService $pdfResponseService, private readonly CalendarScopeResolver $calendarScopeResolver, private readonly AdminTermineListQueryFactory $adminTermineListQueryFactory, private readonly AdminAccessGuard $adminAccessGuard, private readonly AdminPublicationWriteService $adminPublicationWriteService, private readonly AdminMediaFileService $adminMediaFileService, private readonly AdminTerminSeriesMediaService $adminTerminSeriesMediaService, private readonly AdminTerminNotificationService $adminTerminNotificationService, private readonly AdminTerminFormFactory $adminTerminFormFactory, private readonly TerminDuplicateChecker $terminDuplicateChecker, private readonly SharedMediaDeletionService $sharedMediaDeletionService, private readonly TerminWriteWorkflowService $terminWriteWorkflowService, private readonly EntityManagerInterface $em, private readonly CurrentContext $currentContext)
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
     * @param PaginatorInterface $paginatorSerie
     * @param PaginatorInterface $paginatorMeta
     * @param PaginatorInterface $paginatorGroup
     * @return Response
     */
    #[Route(path: '/admin/termine/{action}', name: 'admin_termine', defaults: ['action' => ''], methods: ['GET'])]
    public function index($action, Request $request, PaginatorInterface $paginator, PaginatorInterface $paginator2, PaginatorInterface $paginator3, PaginatorInterface $paginatorSerie, PaginatorInterface $paginatorMeta, PaginatorInterface $paginatorGroup): Response
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();

    	// Baue Terminabfrage
    	$nItems = 20;
    	$form = $this->createFilterForm($konf);
    	// Werte Formular aus
    	$form->handleRequest($request);
    	$session = $request->getSession();

        if ($action === 'reset') {
            $session->clear();
        }

        $filterData = $this->createAdminTermineFilterData($form, $session);
        $strFilter = $this->buildAdminFilterSummary($filterData);

        $calendarScope = $this->calendarScopeResolver->resolveAdminReadScope(
            $konf,
            $filterData->hideSub,
            true === $this->isGranted('ROLE_DFX_GROUP'),
        );

        $queries = $this->adminTermineListQueryFactory->build(
            $konf,
            $calendarScope,
            $filterData,
            false === $this->isGranted('ROLE_DFX_ALL') ? $user->getId() : null,
        );

    	$termine = $paginator->paginate(
    			$queries['published'],
    			$request->query->getInt('dfxp_pub', 1)/*page number*/,
    			$nItems /*limit per page*/,
                ['pageParameterName' => 'dfxp_pub']
    	);

    	$termine2 = $paginator2->paginate(
    			$queries['unpublished'],
    			$request->query->getInt('dfxp_unpub', 1)/*page number*/,
    			$nItems /*limit per page*/,
                ['pageParameterName' => 'dfxp_unpub']
    	);

    	$termineMeta = $paginatorMeta->paginate(
    			$queries['metaPending'],
    			$request->query->getInt('dfxp_meta', 1)/*page number*/,
    			$nItems /*limit per page*/,
                ['pageParameterName' => 'dfxp_meta']
    	);

    	$termineGroup = $paginatorGroup->paginate(
    			$queries['groupPending'],
    			$request->query->getInt('dfxp_group', 1)/*page number*/,
    			$nItems /*limit per page*/,
                ['pageParameterName' => 'dfxp_group']
    	);

    	$termine3 = $paginator3->paginate(
    			$queries['archived'],
    			$request->query->getInt('dfxp_arch', 1)/*page number*/,
    			$nItems /*limit per page*/,
                ['pageParameterName' => 'dfxp_arch']
    	);

    	$termineSerie = $paginatorSerie->paginate(
    			$queries['series'],
    			$request->query->getInt('dfxp_series', 1)/*page number*/,
    			$nItems /*limit per page*/,
                ['pageParameterName' => 'dfxp_series']
    	);
        $tpl = $this->templatePathResolver->resolve('DfxTermine','index.html.twig', $konf);
        return $this->render($tpl, ['filter_form' => $form, 'termine' => $termine, 'termine2' => $termine2, 'termine3' => $termine3, 'termineSerie' => $termineSerie, 'termineMeta' => $termineMeta, 'termineGroup' => $termineGroup, 'konf' => $konf, 'filter' => $strFilter]);

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
        return $this->createForm(AdminTermineFilterType::class, null, [
            'konf' => $konf,
            'options_radio' => $this->getParameter('optionsRadio'),
            'action' => $this->generateUrl('admin_termine', ['kid' => $konf->getId()]),
            'method' => 'GET',
            'attr' => ['name' => 'filter', 'id' => 'filter'],
        ]);
	}

    private function createAdminTermineFilterData(FormInterface $form, SessionInterface $session): AdminTermineFilterData
    {
        $data = $form->isSubmitted() && $form->isValid() ? (array) $form->getData() : [];

        $hideSub = $this->syncSessionBool($session, 'hideSub', $data);
        $filterPub = $this->syncSessionBool($session, 'filterPub', $data);
        $optionsRadio = $this->syncSessionNullableInt($session, 'filterOptionsRadio', $data, 'optionsRadio');

        return new AdminTermineFilterData(
            rubrik: $this->syncSessionString($session, 'rubrik', $data),
            zielgruppe: $this->syncSessionString($session, 'zielgruppe', $data),
            ort: $this->syncSessionString($session, 'ort', $data),
            nat: $this->syncSessionString($session, 'nat', $data),
            veranstalter: $this->syncSessionString($session, 'veranstalter', $data),
            lokal: $this->syncSessionString($session, 'lokal', $data),
            plz: $this->syncSessionString($session, 'plz', $data),
            suche: $this->syncSessionString($session, 'suche', $data),
            datumVon: $this->syncSessionDate($session, 'datum_von', $data),
            datumBis: $this->syncSessionDate($session, 'datum_bis', $data),
            optionsRadio: $optionsRadio,
            hideSub: $hideSub,
            filterPub: $filterPub,
        );
    }

    private function buildAdminFilterSummary(AdminTermineFilterData $filterData): ?string
    {
        $parts = [];

        if ($filterData->rubrik !== null) {
            $parts[] = 'Rubrik ' . $filterData->rubrik;
        }
        if ($filterData->zielgruppe !== null) {
            $parts[] = 'Zielgruppe ' . $filterData->zielgruppe;
        }
        if ($filterData->ort !== null) {
            $parts[] = 'Ort ' . $filterData->ort;
        }
        if ($filterData->nat !== null) {
            $parts[] = 'Land ' . $filterData->nat;
        }
        if ($filterData->veranstalter !== null) {
            $parts[] = 'Veranstalter ' . $filterData->veranstalter;
        }
        if ($filterData->lokal !== null) {
            $parts[] = 'Lokal ' . $filterData->lokal;
        }
        if ($filterData->plz !== null) {
            $parts[] = 'PLZ ' . $filterData->plz;
        }
        if ($filterData->suche !== null) {
            $parts[] = 'Suche ' . $filterData->suche;
        }
        if ($filterData->datumVon !== null) {
            $parts[] = 'Datum ab ' . $filterData->datumVon->format('d.m.Y');
        }
        if ($filterData->datumBis !== null) {
            $parts[] = 'Datum bis ' . $filterData->datumBis->format('d.m.Y');
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

    private function syncSessionDate(SessionInterface $session, string $key, array $data): ?DateTimeInterface
    {
        if (array_key_exists($key, $data)) {
            if ($data[$key] === null) {
                $session->remove($key);

                return null;
            }

            $session->set($key, $data[$key]);

            return $data[$key];
        }

        return $session->get($key);
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

    private function syncSessionNullableInt(SessionInterface $session, string $key, array $data, string $field): ?int
    {
        if (array_key_exists($field, $data)) {
            if ($data[$field] === null || $data[$field] === '') {
                $session->remove($key);

                return null;
            }

            $normalized = (int) $data[$field];
            $session->set($key, $normalized);

            return $normalized;
        }

        $stored = $session->get($key);

        return is_numeric($stored) ? (int) $stored : null;
    }

    /**
     * Creates a new DfxTermine entity.
     */
    #[Template("DfxTermine/new.html.twig")]
    #[Route(path: '/admin/termine/add/create', name: 'termine_create', methods: ['POST'])]
    public function create(Request $request): Response|RedirectResponse
    {
        $entity = new DfxTermine();
        $user = $this->currentContext->getUser();
        $konf = $user->getDatefix();
        $kid = $konf->getId();
        $entity->setUser($user);
        $entity->setDatefix($konf);
        $calendarIds = $this->calendarScopeResolver->resolveReadScope($konf)->ids();
        $form = $this->adminTerminFormFactory->createCreateForm($entity, $konf, $calendarIds);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->terminWriteWorkflowService->mergeSeriesDateInputs(
                $entity,
                (string) $form->get('datumSerie')->getData(),
                (string) $form->get('datum_s_liste')->getData(),
            );
        	$now = new DateTime(date("Y-m-d H:i:s"));
            $this->terminWriteWorkflowService->prepareCreate($entity, $now, $user->getNameLang());
        	// Verhindere Doublettenprüfung bei Serienterminen
            if (!$this->terminWriteWorkflowService->hasSeriesDates($entity)) {
                $double = $this->terminDuplicateChecker->countDuplicates($entity);
                if ($double > 0) {
                    $error = 'Dieser Termin ist in der Datenbank bereits vorhanden!';
                    $tpl = $this->templatePathResolver->resolve('DfxTermine', 'new.html.twig', $konf);
                    return $this->render($tpl, ['error' => $error, 'entity' => $entity, 'konf' => $konf, 'form' => $form]);
                }
            }

            if (false === $this->isGranted('ROLE_DFX_PUB')) {
        		$entity->setPub(false);
        	}

        	if (false === $this->isGranted('ROLE_DFX_META')) {
        		$entity->setPubMeta(false);
        	}

        	if (false === $this->isGranted('ROLE_DFX_GROUP')) {
        		$entity->setPubGroup(false);
        	}

            $this->adminMediaFileService->applyUploadedFiles($request, 'termine', $entity, $kid);

           	if($this->terminWriteWorkflowService->hasSeriesDates($entity)){
                $termin_end = $this->terminWriteWorkflowService->createSeriesFromPrototype($entity);
        	}else{
        		$termin_end = $this->terminWriteWorkflowService->persistSingle($entity);
            }

            $this->adminTerminNotificationService->notifyWrite(
                'create',
                $termin_end,
                $konf,
                $user,
                true === $this->isGranted('ROLE_DFX_PUB'),
                true === $this->isGranted('ROLE_DFX_META'),
                true === $this->isGranted('ROLE_DFX_GROUP'),
            );

           	return $this->redirectToRoute('admin_termine');
        }

        // Überprüfe auf individuelles Template
        $tpl = $this->templatePathResolver->resolve('DfxTermine','new.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form'   => $form]);

     }

   /**
    * Displays a form to create a new DfxTermine entity.
    *
    */
   #[Template("DfxTermine/new.html.twig")]
   #[Route(path: '/admin/termine/add/new', name: 'termine_new', methods: ['GET'])]
   public function new(): Response
   {
        $entity = new DfxTermine();
        $user = $this->currentContext->getUser();
        $konf = $user -> getDatefix();
        $entity->setDatefix($konf);
        $entity->setZeit(new DateTime('00:00:00'));
        $entity->setZeitBis(new DateTime('00:00:00'));

        $calendarIds = $this->calendarScopeResolver->resolveAssignmentScope($konf)->ids();
        $form   = $this->adminTerminFormFactory->createCreateForm($entity, $konf, $calendarIds);
        $tpl = $this->templatePathResolver->resolve('DfxTermine','new.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form'   => $form]);
   }


    /**
     * Displays a form to edit an existing DfxTermine entity.
     *
     * @param $id
     * @param $code
     * @return Response
     */
    #[Route(path: '/admin/termine/{id}/edit/{code}', name: 'termine_edit', defaults: ['code' => ''], methods: ['GET'])]
    public function edit($id, $code): Response
    {
    	$user = $this->currentContext->getUser();
    	$konf= $user->getDatefix();

    	if($code != null){
    		$serie = 1;
    	}else{
    		$serie = 0;
    	}

        $entity = $this->adminAccessGuard->requireEntityForGroupScope(
            (int) $id,
            DfxTermine::class,
            $user,
            'Unable to find DfxTermine entity.',
            'Unerlaubter Zugriff auf Datensatz eines anderen Accounts durch User' . $user->getId() . ' .',
        );

        if($entity->getZeit() == null)
        	$entity->setZeit(new DateTime('00:00:00'));

        if($entity->getZeitBis() == null)
        	$entity->setZeitBis(new DateTime('00:00:00'));

		$calendarIds = $this->calendarScopeResolver->resolveAssignmentScope($konf)->ids();
		$editForm = $this->adminTerminFormFactory->createEditForm($entity, $konf, $serie, $calendarIds);

		 // Überprüfe auf individuelles Template
        $tpl = $this->templatePathResolver->resolve('DfxTermine','edit.html.twig', $konf);
    	return $this->render($tpl, [
				'serie' => $serie,
				'entity' => $entity,
				'konf' => $konf,
				'form'   => $editForm

		]);

    }

    /**
     * Edits an existing DfxTermine entity.
     *

     * @param Request $request
     * @param $id
     * @param $code
     * @return RedirectResponse|Response
     * @throws Exception
     */
    #[Template("DfxTermine/edit.html.twig")]
    #[Route(path: '/admin/termine/{id}/update/{code}', name: 'termine_update', defaults: ['code' => ''], methods: ['POST'])]
    public function update(Request $request, $id, $code): RedirectResponse|Response
    {
    	$user = $this->currentContext->getUser();
        $entity = $this->adminAccessGuard->requireEntityForGroupScope((int) $id, DfxTermine::class, $user, 'Unable to find DfxTermine entity.');
        $konf = $entity->getDatefix();
        $kid = $konf->getId();

        if($code != null){
        	$serie = 1;
        }else{
        	$serie = 0;
        }

        $orgDatum = $entity->getDatum();
        $orgDatumVon = $entity->getdatumVon();
        $orgDatumSerie = $entity->getDatumSerie();

        $calendarIds = $this->calendarScopeResolver->resolveAssignmentScope($konf)->ids();

        $editForm = $this->adminTerminFormFactory->createEditForm($entity, $konf, $serie, $calendarIds);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
        	$now = new DateTime(date("Y-m-d H:i:s"));
            $this->terminWriteWorkflowService->prepareUpdate($entity, $now, $user->getNameLang());

            if($serie == 0){
                $this->adminMediaFileService->clearMarkedFiles($editForm, $entity, $kid);
                $this->adminMediaFileService->applyUploadedFiles($request, 'termine', $entity, $kid);
            	$this->terminWriteWorkflowService->persistSingle($entity);
            }elseif(!empty($code)){
            	// Serientermin
                $this->adminTerminSeriesMediaService->prepareUpdateSeriesMedia(
                    $entity,
                    $editForm,
                    $kid,
                    $orgDatum,
                    $orgDatumVon,
                    $orgDatumSerie,
                );
                $entity->setDatum($orgDatum);
                $entity->setDatumVon($orgDatumVon);
                $entity->setDatumSerie($orgDatumSerie);
                $entity = $this->terminWriteWorkflowService->updateSeriesByCode(
                    $entity,
                    fn (DfxTermine $termin, DfxTermine $source): null => $this->terminWriteWorkflowService->copyAdminSeriesFields($termin, $source, $now)
                );
            }

            $this->adminTerminNotificationService->notifyWrite(
                'update',
                $entity,
                $konf,
                $user,
                true === $this->isGranted('ROLE_DFX_PUB'),
                true === $this->isGranted('ROLE_DFX_META'),
                true === $this->isGranted('ROLE_DFX_GROUP'),
            );

            return $this->redirectToRoute('admin_termine');
        }

        // Überprüfe auf individuelles template
        $tpl = $this->templatePathResolver->resolve('DfxTermine','edit.html.twig', $konf);
        return $this->render($tpl, [
        		'serie' => $serie,
        		'entity' => $entity,
        		'konf' => $konf,
        		'form'   => $editForm

        ]);
    }

    /**
     * Displays a form to edit an existing DfxTermine entity.
     *
     * @param $id
     * @return Response
     */
    #[Route(path: '/admin/termine/{id}/copy', name: 'termine_copy', methods: ['GET'])]
    public function copy($id): Response
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();

    	$entity = $this->adminAccessGuard->requireEntityForGroupScope(
            (int) $id,
            DfxTermine::class,
            $user,
            'Unable to find DfxTermine entity.',
            'Unerlaubter Zugriff auf Datensatz eines anderen Accounts durch User' . $user->getId() . ' .',
        );

    	if($entity->getZeit() == null)
    		$entity->setZeit(new DateTime('00:00:00'));

    	if($entity->getZeitBis() == null)
    		$entity->setZeitBis(new DateTime('00:00:00'));

    	$entity->setDatumSerie(null);
    	$calendarIds = $this->calendarScopeResolver->resolveAssignmentScope($konf)->ids();

    	$editForm = $this->adminTerminFormFactory->createCopyForm($entity, $konf, (int) $id, $calendarIds);

    	 // Überprüfe auf individuelles template
        $tpl = $this->templatePathResolver->resolve('DfxTermine','copy.html.twig', $konf);
    	return $this->render($tpl,[
    			'entity' => $entity,
    			'konf' => $konf,
    			'form'   => $editForm
    	]);

    }

    /**
     * Edits an existing DfxTermine entity.
     *

     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     */
    #[Template("DfxTermine/copy.html.twig")]
    #[Route(path: '/admin/termine/{id}/savecopy', name: 'termine_save_copy', methods: ['POST'])]
    public function savecopy(Request $request, $id): Response|RedirectResponse
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();
    	$kid = $konf -> getId();

    	$entityOrg = $this->adminAccessGuard->requireEntityForGroupScope((int) $id, DfxTermine::class, $user, 'Unable to find DfxTermine entity.');

    	$entity = clone $entityOrg;
    	$entity->setId(null);
        $entity->setCode(null);

        $calendarIds = $this->calendarScopeResolver->resolveAssignmentScope($konf)->ids();

    	$form = $this->adminTerminFormFactory->createCopyForm($entity, $konf, (int) $id, $calendarIds);
    	$form->handleRequest($request);

    	if ($form->isSubmitted() && $form->isValid()) {
            $this->terminWriteWorkflowService->mergeSeriesDateInputs(
                $entity,
                (string) $form->get('datumSerie')->getData(),
                (string) $form->get('datum_s_liste')->getData(),
            );
            $now = new DateTime(date("Y-m-d H:i:s"));
            $this->terminWriteWorkflowService->prepareCreate($entity, $now, $user->getNameLang());
            if (!$this->terminWriteWorkflowService->hasSeriesDates($entity)) {
                $double = $this->terminDuplicateChecker->countDuplicates($entity);
                if($double > 0){
                    $error ='Termin ist in Datenbank bereits vorhanden';
                    $tpl = $this->templatePathResolver->resolve('DfxTermine','copy.html.twig', $konf);
                    return $this->render($tpl, ['error' => $error, 'entity' => $entity,'konf' => $konf,'form'   => $form]);
                }
            }

            $this->adminMediaFileService->applyUploadedFiles($request, 'termine', $entity, $kid);

            if($this->terminWriteWorkflowService->hasSeriesDates($entity)){
                $entity = $this->terminWriteWorkflowService->createSeriesFromPrototype($entity);
            }else{
                $entity = $this->terminWriteWorkflowService->persistSingle($entity);
            }

            $this->adminTerminNotificationService->notifyWrite(
                'copy',
                $entity,
                $konf,
                $user,
                true === $this->isGranted('ROLE_DFX_PUB'),
                true === $this->isGranted('ROLE_DFX_META'),
                true === $this->isGranted('ROLE_DFX_GROUP'),
            );

            return $this->redirectToRoute('admin_termine');
    	}

    	// Überprüfe auf individuelles template
        $tpl = $this->templatePathResolver->resolve('DfxTermine','copy.html.twig', $konf);
    	return $this->render($tpl,[
    			'entity' => $entity,
    			'konf' => $konf,
    			'form'   => $form

    	]);
   }


    /**
     * Deletes a DfxTermine entity.
     *
     * @param $id
     * @param $code
     * @return RedirectResponse
     */
    #[Route(path: '/admin/termine/{id}/delete/{code}', name: 'termine_delete', defaults: ['code' => ''], methods: ['GET'])]
    public function delete($id, $code): RedirectResponse
    {
        $user = $this->currentContext->getUser();
        if($code === null){
	        $entity = $this->adminAccessGuard->requireEntityForGroupScope((int) $id, DfxTermine::class, $user, 'Unable to find DfxTermine entity.');

            $this->sharedMediaDeletionService->deleteTerminFiles($entity);
	        $this->em->remove($entity);
	        $this->em->flush();
        }else{
        	 $this->em->createQueryBuilder()
        	 ->delete(DfxTermine::class, 't')
        	 ->where('t.code = :code')
        	 ->setParameter('code', $code)
        	 ->getQuery()->execute();
        }

        return $this->redirectToRoute('admin_termine');
   }

    /**
     * Deletes a Liets of DfxTermine entities.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    #[Route(path: '/admin/termine/delete/delete_liste', name: 'termine_delete_liste', methods: ['POST'])]
    public function deleteListe(Request $request): RedirectResponse
    {
        $user = $this->currentContext->getUser();
        $items = $request->request->get('deleteList');
        foreach ($items as $item){
            $entity = $this->adminAccessGuard->requireEntityForGroupScope(
                (int) $item,
                DfxTermine::class,
                $user,
                'Unable to find DfxTermine entity.',
                'Unerlaubter Zugriff auf Datensatz (Termin ID .' . $item . ' eines anderen Accounts.',
            );

            $this->sharedMediaDeletionService->deleteTerminFiles($entity);
            $this->em->remove($entity);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_termine');
    }

    #[Route(path: '/admin/termine/batch/action', name: 'termine_batch_action', methods: ['POST'])]
    public function batchAction(Request $request): RedirectResponse
    {
        $user = $this->currentContext->getUser();
        $konf = $user->getDatefix();
        $action = (string) $request->request->get('batchAction', '');
        $section = (string) $request->request->get('batchSection', '');
        $selectedIds = $request->request->all('selectedIds');

        if ($selectedIds === [] || $action === '') {
            return $this->redirectToReferer($request);
        }

        $selectedIds = array_values(array_unique(array_map('intval', array_filter($selectedIds, static fn ($value): bool => (int) $value > 0))));

        foreach ($selectedIds as $itemId) {
            $entity = match ($action) {
                'pubmeta' => $this->adminAccessGuard->requireEntityForMetaScope(
                    $itemId,
                    DfxTermine::class,
                    $user,
                    'Unable to find DfxTermine entity.',
                ),
                default => $this->adminAccessGuard->requireEntityForGroupScope(
                    $itemId,
                    DfxTermine::class,
                    $user,
                    'Unable to find DfxTermine entity.',
                    'Unerlaubter Zugriff auf Datensatz (Termin ID .' . $itemId . ' eines anderen Accounts.',
                ),
            };

            if ($section === 'series' && $entity->getCode() !== null && $entity->getCode() !== '') {
                $this->applyBatchActionToSeries($action, $entity, $konf);
                continue;
            }

            $this->applyBatchActionToEntity($action, $entity, $konf);
        }

        return $this->redirectToReferer($request);
    }

    private function applyBatchActionToEntity(string $action, DfxTermine $entity, DfxKonf $konf): void
    {
        switch ($action) {
            case 'pub':
                $this->adminPublicationWriteService->setPublished($entity, true, $konf);
                return;
            case 'pubmeta':
                $this->adminPublicationWriteService->setMetaPublished($entity, true);
                return;
            case 'pubgroup':
                $this->adminPublicationWriteService->setGroupPublished($entity, true);
                return;
            case 'delete':
                $this->sharedMediaDeletionService->deleteTerminFiles($entity);
                $this->em->remove($entity);
                $this->em->flush();
                return;
        }
    }

    private function applyBatchActionToSeries(string $action, DfxTermine $entity, DfxKonf $konf): void
    {
        $code = (string) $entity->getCode();

        switch ($action) {
            case 'pub':
                $this->adminPublicationWriteService->bulkPublishByCode(DfxTermine::class, $code, $konf);
                return;
            case 'pubmeta':
                $this->adminPublicationWriteService->bulkSetFieldByCode(DfxTermine::class, 'pubMeta', $code, true);
                return;
            case 'pubgroup':
                $this->adminPublicationWriteService->bulkSetFieldByCode(DfxTermine::class, 'pubGroup', $code, true);
                return;
            case 'delete':
                $this->em->createQueryBuilder()
                    ->delete(DfxTermine::class, 't')
                    ->where('t.code = :code')
                    ->setParameter('code', $code)
                    ->getQuery()
                    ->execute();
                return;
        }
    }

    private function redirectToReferer(Request $request): RedirectResponse
    {
        $referer = $request->headers->get('referer');
        if (is_string($referer) && $referer !== '') {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('admin_termine');
    }


    /**
     * Lists all DfxAnmeldungen of one Event in a PDF.
     *
     * @param int $id
     * @return Response
     */
   #[Route(path: '/admin/termine/pdf/{id}', name: 'termin_pdf', methods: ['GET'])]
   public function pdf(int $id): Response
   {
   	$termin = $this->em->getRepository(DfxTermine::class)->find($id);
   	$konf = $termin->getDatefix();
   // überprüfe auf individuelles Template
    $tpl = $this->templatePathResolver->resolve('Kalender','detail_pdf.html.twig', $konf);
   	$html = $this->render($tpl, ['termin' => $termin, 'konf' => $konf])->getContent();

    return $this->pdfResponseService->render($html, 'termin_'.$id.'.pdf' );
 }

    /**
    * Unpub a DfxTermine entity.
    *
    * @param $id
    * @param $code
    * @return RedirectResponse
    */
    #[IsGranted('ROLE_DFX_PUB')]
    #[Route(path: '/admin/termine/{id}/unpub/{code}', name: 'termine_unpub', defaults: ['code' => ''], methods: ['GET'])]
   public function unpub($id, $code): RedirectResponse
   {
   	$user = $this->currentContext->getUser();
   	if($code == null){
	   	$entity = $this->adminAccessGuard->requireEntityForGroupScope((int) $id, DfxTermine::class, $user, 'Unable to find DfxTermine entity.');
            $this->adminPublicationWriteService->setPublished($entity, false);
   	}else{
            $this->adminPublicationWriteService->bulkSetFieldByCode(DfxTermine::class, 'pub', (string) $code, false);
  	}

    return $this->redirectToRoute('admin_termine');
   }

    /**
    * Unpub a DfxTermine entity.
    *
    * @param $id
    * @param $code
    * @return RedirectResponse
    */
    #[IsGranted('ROLE_DFX_PUB')]
    #[Route(path: '/admin/termine/{id}/pub/{code}', name: 'termine_pub', defaults: ['code' => ''], methods: ['GET'])]
   public function pub($id, $code): RedirectResponse
   {
   	$user = $this->currentContext->getUser();
   	$konf= $user -> getDatefix();
   	if($code == null){
	   	$entity = $this->adminAccessGuard->requireEntityForGroupScope((int) $id, DfxTermine::class, $user, 'Unable to find DfxTermine entity.');
            $this->adminPublicationWriteService->setPublished($entity, true, $konf);
   	}else{
            $this->adminPublicationWriteService->bulkPublishByCode(DfxTermine::class, (string) $code, $konf);
   	}

   	return $this->redirectToRoute('admin_termine');
   }


    /**
    * UnpubMeta a DfxTermine entity.
    *
    * @param $id
    * @param $code
    * @return Response
    */
    #[IsGranted('ROLE_DFX_PUB')]
    #[Route(path: '/admin/termine/{id}/unpubmeta/{code}', name: 'termine_unpubmeta', defaults: ['code' => ''], methods: ['GET'])]
   public function unpubMeta($id, $code): Response
   {
   	$user = $this->currentContext->getUser();
   	if($code == null){
   		$entity = $this->adminAccessGuard->requireEntityForMetaScope((int) $id, DfxTermine::class, $user, 'Unable to find DfxTermine entity.');
            $this->adminPublicationWriteService->setMetaPublished($entity, false);
   	}else{
            $this->adminPublicationWriteService->bulkSetFieldByCode(DfxTermine::class, 'pubMeta', (string) $code, false);
   	}

   	return new Response('ok');
   }

    /**
    * pubMeta a DfxTermine entity.
    *
    * @param $id
    * @param $code
    * @return Response
    */
    #[IsGranted('ROLE_DFX_PUB')]
    #[Route(path: '/admin/termine/{id}/pubmeta/{code}', name: 'termine_pubmeta', defaults: ['code' => ''], methods: ['GET'])]
   public function pubMeta($id, $code): Response
   {
   	$user = $this->currentContext->getUser();
   	if($code == null){
   		$entity = $this->adminAccessGuard->requireEntityForMetaScope((int) $id, DfxTermine::class, $user, 'Unable to find DfxTermine entity.');
            $this->adminPublicationWriteService->setMetaPublished($entity, true);
   	}else{
            $this->adminPublicationWriteService->bulkSetFieldByCode(DfxTermine::class, 'pubMeta', (string) $code, true);
   	}

   	return new Response('ok');
   }

    /**
    * UnpubGroup a DfxTermine entity.
    *
    * @param $id
    * @param $code
    * @return Response
    */
    #[IsGranted('ROLE_DFX_PUB')]
    #[Route(path: '/admin/termine/{id}/unpubgroup/{code}', name: 'termine_unpubgroup', defaults: ['code' => ''], methods: ['GET'])]
   public function unpubGroup($id, $code): Response
   {
   	$user = $this->currentContext->getUser();
   	if($code == null){
   		$entity = $this->adminAccessGuard->requireEntityForGroupScope((int) $id, DfxTermine::class, $user, 'Unable to find DfxTermine entity.');
            $this->adminPublicationWriteService->setGroupPublished($entity, false);
   	}else{
            $this->adminPublicationWriteService->bulkSetFieldByCode(DfxTermine::class, 'pubGroup', (string) $code, false);
   	}

   	return new Response('ok');
   }

    /**
    * pubGroup a DfxTermine entity.
    *
    * @param $id
    * @param $code
    * @return Response
    */
    #[IsGranted('ROLE_DFX_PUB')]
    #[Route(path: '/admin/termine/{id}/pubgroup/{code}', name: 'termine_pubgroup', defaults: ['code' => ''], methods: ['GET'])]
   public function pubGroup($id, $code): Response
   {
   	$user = $this->currentContext->getUser();
   	if($code == null){
   		$entity = $this->adminAccessGuard->requireEntityForGroupScope((int) $id, DfxTermine::class, $user, 'Unable to find DfxTermine entity.');
            $this->adminPublicationWriteService->setGroupPublished($entity, true);
   	}else{
            $this->adminPublicationWriteService->bulkSetFieldByCode(DfxTermine::class, 'pubGroup', (string) $code, true);
   	}

   	return new Response('ok');
   }

    /**
    * Lists DfxLocation as JSON-File entities.
    *
    * @param $datum
    * @return Response
    */
   #[Route(path: '/admin/termine/json/check/{datum}', methods: ['GET'])]
   public function checkDate($datum): Response{
       $qb = $this->em->createQueryBuilder();
       $konf = $this->em->getRepository(DfxKonf::class)->find(1);
       $qb->select("DATE_FORMAT(t.datumVon, '%d.%m.%Y') AS datumVon, DATE_FORMAT(t.zeit, '%H:%i') AS zeit, t.titel, t.lokal, t.ort, t.rubrik, t.optionsRadio, t.optionsCheckboxes")
       ->from('App\Entity\DfxTermine','t')
       ->where(':datum BETWEEN t.datumVon AND t.datum')
       ->setParameter('datum', $datum);
       $arTermine = $qb->getQuery()->getResult();
       // Überprüfe auf individuelles template
       $tpl = $this->templatePathResolver->resolve('DfxTermine','termincheck.html.twig', $konf);
       return $this->render($tpl,[
           'termine' => $arTermine
       ]);
   }
}
