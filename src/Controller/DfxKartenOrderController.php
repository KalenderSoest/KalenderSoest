<?php

namespace App\Controller;

use App\Entity\DfxTermine;
use App\Service\Calendar\KartenCapacityService;
use App\Service\Calendar\KartenOrderListQueryFactory;
use App\Service\Calendar\KartenOrderFormFactory;
use App\Service\Calendar\KartenOrderNotificationService;
use App\Service\Frontend\CodeChallengeService;
use App\Service\Presentation\HtmlResponseService;
use App\Service\Presentation\PdfResponseService;
use App\Service\Presentation\TemplatePathResolver;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DfxKartenOrder;
use App\Security\CurrentContext;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * DfxKartenOrder controller.
 */
class DfxKartenOrderController extends AbstractController
{


    public function __construct(
        private readonly CodeChallengeService $codeChallengeService,
        private readonly KartenOrderFormFactory $kartenOrderFormFactory,
        private readonly KartenOrderListQueryFactory $kartenOrderListQueryFactory,
        private readonly KartenCapacityService $kartenCapacityService,
        private readonly KartenOrderNotificationService $kartenOrderNotificationService,
        private readonly HtmlResponseService $htmlResponseService,
        private readonly PdfResponseService $pdfResponseService,
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly EntityManagerInterface $em,
        private readonly CurrentContext $currentContext,
    )
    {
        }

    /**
     * Lists all DfxKartenOrder entities grouped by Event.
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return array
     */
    #[IsGranted('ROLE_USER')]
    #[Template("DfxKartenOrder/index.html.twig")]
    #[Route(path: '/karten/', name: 'admin_karten', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): array
    {
    	$user = $this->currentContext->getUser();
    	$kid =  $user -> getDatefix()->getId();
        $form = $this->kartenOrderFormFactory->createFilterForm();
        // Werte Formular aus
        $form->handleRequest($request);
        $strHeader = '';
        $query = $this->kartenOrderListQueryFactory->createIndexQuery($form, $kid, $user, $this->isGranted('ROLE_DFX_ALL'), $strHeader);
        
		

        $karten = $paginator->paginate(
        		$query,
        		$request->query->getInt('dfxp', 1),
        		20
        );
       
		return [
            'karten' => $karten,
			'filter_form' => $form ->createView(),
			'header' => $strHeader
        ];
        
    }


    /**
     * Lists all DfxKartenOrder of one Event entities.
     *
     * @param int $tid
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return array
     */
    #[IsGranted('ROLE_USER')]
    #[Template("DfxKartenOrder/liste.html.twig")]
    #[Route(path: '/karten/liste/{tid}', name: 'admin_karten_liste', methods: ['GET'])]
    public function kartenListe(int $tid, Request $request, PaginatorInterface $paginator): array
    {
    	$konf = $this->currentContext->getUser() -> getDatefix();
    	$kid = $konf->getId();
    	 
    	$termin = $this->em->getRepository(DfxTermine::class)->find($tid);
    	$query = $this->kartenOrderListQueryFactory->createTerminListQuery($kid, $tid)->getQuery();
    

    	$karten = $paginator->paginate(
    			$query,
    			$request->query->getInt('dfxp', 1)/*page number*/,
    			20 /*limit per page*/
    	);
    	return [
    			'termin' => $termin,
    			'konf' => $konf,
    			'karten' => $karten
    	];
    }

    /**
     * Lists all DfxKartenOrder of one Event entities.
     *
     * @param int $tid
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route(path: '/karten/pdf/{tid}', name: 'pdf_reservierungen_liste', methods: ['GET'])]
    public function pdfListe(int $tid): Response
    {
    	$kid = $this->currentContext->getUser() -> getDatefix()->getId();
    
    	$termin = $this->em->getRepository(DfxTermine::class)->find($tid);
    	$query = $this->kartenOrderListQueryFactory->createTerminPdfQuery($kid, $tid)->getQuery();
    	 
    	$reservierungen = $query->getResult();
    	$html = $this->render('DfxKartenOrder/liste_pdf.html.twig', ['termin' => $termin,	'reservierungen' => $reservierungen])->getContent();

        return $this->pdfResponseService->render($html);
    }

    /**
     * Lists all DfxKartenOrder of one Event entities.
     *
     * @param int $kid
     * @param int $tid
     */
    #[IsGranted('ROLE_USER')]
    #[Route(path: '/karten/{kid}/pdf/{tid}', name: 'pdf_reservierungen_liste_einzeilig', methods: ['GET'])]
    public function pdfListeEinzeilig(int $kid, int $tid): Response
    {
    
    	 
    	$termin = $this->em->getRepository(DfxTermine::class)->find($tid);
    
    	if($kid != $termin ->getDatefix()-> getId() && false === $this->isGranted('ROLE_DFX_GROUP')){
    		throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines fremden Accounts');
    	}
    
    
    	$query = $this->kartenOrderListQueryFactory->createTerminPdfQuery($kid, $tid)->getQuery();
    	 
    	$reservierungen = $query->getResult();
    	$html = $this->render('DfxKartenOrder/liste_pdf_einzeilig.html.twig', ['termin' => $termin,	'reservierungen' => $reservierungen])->getContent();
        return $this->pdfResponseService->render($html);

    }


    /**
     * Creates a new DfxKartenOrder entity.
     *

     * @param Request $request
     * @param $id
     * @return Response
     */
    #[Template("DfxKartenOrder/new.html.twig")]
    #[Route(path: '/karten/create/{id}', name: 'karten_create', methods: ['GET'])]
    public function create(Request $request, $id): Response
    {
        $entity = new DfxKartenOrder();
        $termin = $this->em->getRepository(DfxTermine::class)->find($id);
        if ($termin === null) {
        	throw $this->createNotFoundException('Unable to find DfxTermin entity.');
        }

        $konf = $termin->getDatefix();
        $kid = $konf->getId();
        $entity->setTermin($termin);
        $entity->setDatefix($konf);
        $entity->setCode(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 20));

        $now = new DateTime(date("Y-m-d H:i:s"));
        $entity->setDatum($now);
        $arCode = $this->codeChallengeService->create();
        $form = $this->kartenOrderFormFactory->createCreateForm($entity, (int) $id, $arCode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $capacityResponse = $this->kartenCapacityService->reserveForNew($termin, $entity, $konf);
            if ($capacityResponse !== null) {
                return $capacityResponse;
            }

        	$entity->setNotiz(wordwrap($entity->getNotiz(),40));
            $this->em->persist($entity);
            $this->em->flush();
            $this->kartenOrderNotificationService->notifyCreated($entity, $termin);
            return $this->htmlResponseService->render('DfxKartenOrder/show.html.twig', ['entity' => $entity, 'konf' => $konf]);

        }

        $error = (string) $form->getErrors(true);
        return $this->htmlResponseService->render('DfxKartenOrder/new.html.twig', [ 'entity' => $entity, 'termin' => $termin, 'konf' => $konf, 'code' => $arCode, 'form' => $form->createView(), 'error' => $error]);
		
        
    }
    
    /**
     * Displays a form to create a new DfxKartenOrder entity.
     *
     * @param $id
     * @return Response
     */
    #[Route(path: '/karten/new/{id}', name: 'karten_new', methods: ['GET'])]
    public function new($id): Response
    {
        $entity = new DfxKartenOrder();
        $termin = $this->em->getRepository(DfxTermine::class)->find($id);
        if ($termin === null) {
            throw $this->createNotFoundException('Unable to find DfxTermin entity.');
        }

        $konf = $termin->getDatefix();

         if(true === $termin->getPlaetzeGesamt() && $termin->getPlaetzeAktuell() <= 0){
        	return $this->htmlResponseService->render('DfxKartenOrder/belegt.html.twig',  [ 'termin' => $termin, 'konf' => $konf]);
        }

        $arCode = $this->codeChallengeService->create();
        $form   = $this->kartenOrderFormFactory->createCreateForm($entity, $id, $arCode);
        $tpl = $this->templatePathResolver->resolve('DfxKartenOrder','new.html.twig', $konf);
        return $this->htmlResponseService->render($tpl, [ 'termin' => $termin, 'konf' => $konf, 'entity' => $entity, 'code' => $arCode, 'form' => $form->createView()]);
    }

    /**
     * Finds and displays a DfxKartenOrder entity.
     *
     * @param $id
     * @return Response
     */
    #[Route(path: '/karten/{kid}/show/{tid}/{id}', name: 'karten_show', methods: ['GET'])]
    public function show($id): Response
    {
        $entity = $this->em->getRepository(DfxKartenOrder::class)->find($id);
        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxKartenOrder entity.');
        }
        $konf = $entity->getDatefix();

        return $this->htmlResponseService->render('DfxKartenOrder/show.html.twig',[ 'entity' => $entity, 'konf' => $konf] );

    }

    /**
     * Displays a form to edit an existing DfxKartenOrder entity.
     *
     * @param $id
     * @return array
     */
    #[IsGranted('ROLE_USER')]
    #[Template('DfxKartenOrder/show.html.twig')]
    #[Route(path: '/karten/{id}/edit', name: 'karten_edit', methods: ['GET'])]
    public function edit($id): array
    {
        $kid = $this->currentContext->getUser() -> getDatefix()->getId();
        $entity = $this->em->getRepository(DfxKartenOrder::class)->find($id);

        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxKartenOrder entity.');
        }

        if($kid != $entity -> getDatefix() -> getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        $form = $this->kartenOrderFormFactory->createEditForm($entity);


        return [
            'termin'   => $entity->getTermin(),
            'form'   => $form->createView(),

        ];
    }

    /**
     * Edits an existing DfxKartenOrder entity.
     *
     * @param Request $request
     * @param $id
     * @return array|RedirectResponse|Response
     */
    #[IsGranted('ROLE_USER')]
    #[IsGranted('ROLE_USER')]
    #[Template("DfxKartenOrder/edit.html.twig")]
    #[Route(path: '/karten/{id}/update', name: 'karten_update', methods: ['PUT'])]
    public function update(Request $request, $id): Response|RedirectResponse|array
    {
        $konf= $this->currentContext->getUser() -> getDatefix();
        $kid = $konf->getId();
        $entity = $this->em->getRepository(DfxKartenOrder::class)->find($id);

        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxKartenOrder entity.');
        }

        if($kid != $entity -> getDatefix() -> getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

		$termin =  $entity->getTermin();
        $currentCount = $entity->getAnzahl() ?? 0;
        $editForm = $this->kartenOrderFormFactory->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $capacityResponse = $this->kartenCapacityService->reserveForUpdate($termin, $entity, $konf, $currentCount);
            if ($capacityResponse !== null) {
                return $capacityResponse;
            }

        	$entity->setNotiz(wordwrap($entity->getNotiz(),40));
        	$this->em->flush();

            return $this->redirectToRoute('admin_karten_liste', ['tid' => $entity->getTermin()->getId()]);
        }

        return [
            'entity'      => $entity,
            'form'   => $editForm->createView(),

        ];
    }

    /**
     * Deletes a DfxKartenOrder entity.
     *
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    #[IsGranted('ROLE_USER')]
    #[Route(path: '/karten/{id}/delete', name: 'karten_delete', methods: ['GET'])]
    public function delete(Request $request, $id): RedirectResponse
    {
        $kid = $this->currentContext->getUser() -> getDatefix()->getId();
        $entity = $this->em->getRepository(DfxKartenOrder::class)->find($id);
            if ($entity === null) {
                throw $this->createNotFoundException('Unable to find DfxKartenOrder entity.');
            }

            if($kid != $entity -> getDatefix() -> getId()){
            	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
            }

            // Hole aktuelle Kartenzahl
            $termin =  $entity->getTermin();

            $this->kartenCapacityService->release($termin, $entity);

            $this->em->remove($entity);
            $this->em->flush();
       return $this->redirectToRoute('admin_karten_liste', ['tid' => $entity->getTermin()->getId()]);
    }

   
}
