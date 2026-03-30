<?php

namespace App\Controller;

use App\Entity\DfxTermine;
use App\Entity\DfxKonf;
use App\Entity\DfxNfxUser;
use App\Service\Calendar\AnmeldungenListQueryFactory;
use App\Service\Calendar\AnmeldungenFormFactory;
use App\Service\Calendar\AnmeldungCapacityService;
use App\Service\Calendar\AnmeldungNotificationService;
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
use App\Entity\DfxAnmeldungen;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


/**
 * DfxAnmeldungen controller.
 */
class DfxAnmeldungenController extends AbstractController
{

    public function __construct(
        private readonly CodeChallengeService $codeChallengeService,
        private readonly AnmeldungenFormFactory $anmeldungenFormFactory,
        private readonly AnmeldungenListQueryFactory $anmeldungenListQueryFactory,
        private readonly AnmeldungCapacityService $anmeldungCapacityService,
        private readonly AnmeldungNotificationService $anmeldungNotificationService,
        private readonly HtmlResponseService $htmlResponseService,
        private readonly PdfResponseService $pdfResponseService,
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    private function getCurrentUser(): DfxNfxUser
    {
        $user = $this->getUser();
        if (!$user instanceof DfxNfxUser) {
            throw $this->createAccessDeniedException('Authenticated DfxNfxUser required.');
        }

        return $user;
    }

    private function getCurrentKonf(): DfxKonf
    {
        $konf = $this->getCurrentUser()->getDatefix();
        if (!$konf instanceof DfxKonf) {
            throw $this->createAccessDeniedException('No calendar account assigned to current user.');
        }

        return $konf;
    }

    /**
     * Lists all DfxAnmeldungen entities grouped by Event.
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return array
     */
    #[IsGranted('ROLE_USER')]
    #[Template("DfxAnmeldungen/index.html.twig")]
    #[Route(path: '/anmeldungen/', name: 'admin_anmeldungen', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): array
    {
    	$user = $this->getCurrentUser();
    	$konf = $this->getCurrentKonf();
    	$kid =  $konf->getId();
        $form = $this->anmeldungenFormFactory->createFilterForm();
        // Werte Formular aus
        $form->handleRequest($request);
        $strHeader = '';
        $query = $this->anmeldungenListQueryFactory->createIndexQuery($form, $kid, $user, $this->isGranted('ROLE_DFX_ALL'), $strHeader);



        $anmeldungen = $paginator->paginate(
        		$query,
        		$request->query->getInt('dfxp', 1),
        		20
        );


		return [
                    'anmeldungen' => $anmeldungen,
                    'filter_form' => $form ->createView(),
                    'header' => $strHeader
        ];

    }

    /**
     * Creates a Filterform.
     *
     *
     * @return FormInterface The form
     */
    /**
     * Lists all DfxAnmeldungen of one Event entities.
     *
     * @param $tid
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return array
     */
    #[IsGranted('ROLE_USER')]
    #[Template("DfxAnmeldungen/liste.html.twig")]
    #[Route(path: '/anmeldungen/liste/{tid}', name: 'admin_anmeldungen_liste', methods: ['GET'])]
    public function anmeldungenListe($tid,Request $request, PaginatorInterface $paginator): array
    {
    	$konf = $this->getCurrentKonf();

    	$termin = $this->em->getRepository(DfxTermine::class)->find($tid);
    	$query = $this->anmeldungenListQueryFactory->createTerminListQuery($tid)->getQuery();


    	$anmeldungen = $paginator->paginate(
    			$query,
    			$request->query->getInt('dfxp', 1)/*page number*/,
    			20 /*limit per page*/
    	);
    	return [
    			'termin' => $termin,
    			'konf'=> $konf,
    			'anmeldungen' => $anmeldungen
    	];
    }

    /**
     * Lists all DfxAnmeldungen of one Event in a PDF.
     *
     * @param integer $tid
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route(path: '/anmeldungen/pdf/{tid}', name: 'pdf_anmeldungen_liste', methods: ['GET'])]
    public function pdfListe(int $tid): Response
    {
    	$termin = $this->em->getRepository(DfxTermine::class)->find($tid);
    	$query = $this->anmeldungenListQueryFactory->createTerminPdfQuery($tid)->getQuery();

    	$anmeldungen = $query->getResult();
    	$html = $this->render('DfxAnmeldungen/liste_pdf.html.twig', ['termin' => $termin,	'anmeldungen' => $anmeldungen])->getContent();

        return $this->pdfResponseService->render($html);

     }

    /**
     * Lists all DfxAnmeldungen of one Event in a one-line-ppdf.
     *
     * @param $kid
     * @param int $tid
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
     #[Route(path: '/anmeldungen/{kid}/pdf/{tid}', name: 'pdf_anmeldungen_liste_einzeilig', methods: ['GET'])]
     public function pdfListeEinzeilig($kid, int $tid): Response
     {


     	$termin = $this->em->getRepository(DfxTermine::class)->find($tid);

     	if($kid != $termin ->getDatefix()-> getId() && false === $this->isGranted('ROLE_DFX_GROUP')){
     		throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines fremden Accounts');
     	}


     	$query = $this->anmeldungenListQueryFactory->createTerminPdfQuery($tid)->getQuery();

     	$anmeldungen = $query->getResult();
     	$html = $this->render('DfxAnmeldungen/liste_pdf_einzeilig.html.twig', ['termin' => $termin,	'anmeldungen' => $anmeldungen])->getContent();
        return $this->pdfResponseService->render($html);

     }


    /**
     * Creates a new DfxAnmeldungen entity.
     *

     * @param Request $request
     * @param $id
     * @return Response
     */
    #[Template("DfxAnmeldungen/new.html.twig")]
    #[Route(path: '/anmeldungen/create/{id}', name: 'anmeldungen_create')]
    public function create(Request $request, $id): Response
    {
        $entity = new DfxAnmeldungen();
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
        $form = $this->anmeldungenFormFactory->createCreateForm($entity, (int) $id, $arCode);
        $form->handleRequest($request);

		$error ='';
        if ($form->isSubmitted() && $form->isValid()) {
        if(true === $this->codeChallengeService->isValid((string) $form->get('cCode')->getData(), (string) $form->get('key')->getData())){
            $capacityResponse = $this->anmeldungCapacityService->reserveForNew($termin, $entity, $konf);
            if ($capacityResponse !== null) {
                return $capacityResponse;
            }

        	// Mail Admin
        	$entity->setNotiz(wordwrap((string) $entity->getNotiz(),40));
            $this->em->persist($entity);
            $this->em->flush();
            $this->anmeldungNotificationService->notifyCreated($entity, $termin);
            return $this->htmlResponseService->render('DfxAnmeldungen/show.html.twig', ['entity' => $entity, 'konf' => $konf]);
       }else{
       	    $error .= 'Fehler cC';
       }
       }

       $error .= $form->getErrors(true);
       return $this->htmlResponseService->render('DfxAnmeldungen/new.html.twig',['termin' => $termin, 'konf' => $konf, 'code' => $arCode, 'form' => $form->createView(), 'error' => $error]);

    }

    /**
     * Creates a form to create a DfxAnmeldungen entity.
     *
     * @param DfxAnmeldungen $entity The entity
     *
     * @param $id
     * @param array $arCode
     * @return FormInterface The form
     */
    /**
     * Displays a form to create a new DfxAnmeldungen entity.
     *

     * @param $id
     * @return Response
     */
    #[Template("DfxAnmeldungen/new.html.twig")]
    #[Route(path: '/anmeldungen/new/{id}', name: 'anmeldungen_new')]
    public function new($id): Response
    {
        $entity = new DfxAnmeldungen();
        $termin = $this->em->getRepository(DfxTermine::class)->find($id);
        if ($termin === null) {
            throw $this->createNotFoundException('Unable to find DfxTermin entity.');
        }

        $konf = $termin->getDatefix();

        if(true === $termin->getPlaetzeGesamt() && $termin->getPlaetzeAktuell() <= 0){
        	return $this->htmlResponseService->render('DfxAnmeldungen/belegt.html.twig', [ 'termin' => $termin, 'konf' => $konf]);
        }

        $arCode = $this->codeChallengeService->create();
        $entity->setTermin($termin);
        $form   = $this->anmeldungenFormFactory->createCreateForm($entity, (int) $id, $arCode);
        $tpl = $this->templatePathResolver->resolve('DfxAnmeldungen','new.html.twig', $konf);
        return $this->htmlResponseService->render($tpl, [ 'termin' => $termin, 'entity' => $entity, 'code' => $arCode, 'konf' => $konf, 'form' => $form->createView()]);

    }

    /**
     * Finds and displays a DfxAnmeldungen entity.
     *

     * @param $kid
     * @param $id
     * @return Response
     */
    #[Template("DfxAnmeldungen/show.html.twig")]
    #[Route(path: '/anmeldungen/{kid}/show/{tid}/{id}', name: 'anmeldungen_show')]
    public function show($kid, $id): Response
    {
        $konf = $this->getCurrentKonf();

        $entity = $this->em->getRepository(DfxAnmeldungen::class)->find($id);

        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxAnmeldungen entity.');
        }

        if($kid != $entity->getDatefix()->getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        return $this->htmlResponseService->render('DfxAnmeldungen/show.html.twig',[ 'entity' => $entity, 'konf' => $konf]);

    }

    /**
     * Displays a form to edit an existing DfxAnmeldungen entity.
     *
     * @param $id
     * @return array
     */
    #[IsGranted('ROLE_USER')]
    #[Template("DfxAnmeldungen/edit.html.twig")]
    #[Route(path: '/anmeldungen/{id}/edit', name: 'anmeldungen_edit', methods: ['GET'])]
    public function edit($id): array
    {
        $kid = $this->getCurrentKonf()->getId();
        $entity = $this->em->getRepository(DfxAnmeldungen::class)->find($id);

        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxAnmeldungen entity.');
        }

        if($kid != $entity -> getDatefix() -> getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        $editForm = $this->anmeldungenFormFactory->createEditForm($entity);
        return [
            'termin'   => $entity->getTermin(),
            'form'   => $editForm->createView(),
        ];
    }

    /**
     * Edits an existing DfxAnmeldungen entity.
     *

     * @param Request $request
     * @param $id
     * @return array|RedirectResponse|Response
     */
    #[IsGranted('ROLE_USER')]
    #[Template("DfxAnmeldungen/edit.html.twig")]
    #[Route(path: '/anmeldungen/{id}/update', name: 'anmeldungen_update', methods: ['PUT'])]
    public function update(Request $request, $id): Response|RedirectResponse|array
    {
        $konf = $this->getCurrentKonf();
        $kid = $konf->getId();
        $entity = $this->em->getRepository(DfxAnmeldungen::class)->find($id);

        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxAnmeldungen entity.');
        }

        if($kid != $entity -> getDatefix() -> getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        $termin =  $entity->getTermin();
        $currentCount = $entity->getAnzahl() ?? 0;
        $editForm = $this->anmeldungenFormFactory->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $capacityResponse = $this->anmeldungCapacityService->reserveForUpdate($termin, $entity, $konf, $currentCount);
            if ($capacityResponse !== null) {
                return $capacityResponse;
            }

        	$entity->setNotiz(wordwrap($entity->getNotiz(),40));
        	$this->em->flush();

            return $this->redirectToRoute('admin_anmeldungen_liste', ['tid' => $entity->getTermin()->getId()]);
        }

        return [
            'termin'      => $entity->getTermin(),
            'form'   => $editForm->createView(),

        ];
    }

    /**
     * Deletes a DfxAnmeldungen entity.
     *
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    #[IsGranted('ROLE_USER')]
    #[Route(path: '/anmeldungen/{id}/delete', name: 'anmeldungen_delete', methods: ['GET'])]
    public function delete(Request $request, $id): RedirectResponse
    {
            $kid = $this->getCurrentKonf()->getId();
            $entity = $this->em->getRepository(DfxAnmeldungen::class)->find($id);
            if ($entity === null) {
                throw $this->createNotFoundException('Unable to find DfxAnmeldungen entity.');
            }

            if($kid != $entity -> getDatefix() -> getId()){
            	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
            }

            // Hole aktuelle Kartenzahl
            $termin =  $entity->getTermin();

            $this->anmeldungCapacityService->release($termin, $entity);

            $this->em->remove($entity);
            $this->em->flush();
       return $this->redirectToRoute('admin_anmeldungen_liste', ['tid' => $entity->getTermin()->getId()]);
    }

    /**
     * Deletes a DfxAnmeldungen entity.
     *
     * @param $code
     * @return RedirectResponse
     */
    #[Route(path: '/anmeldungen/delete/{code}', name: 'anmeldungen_code_delete', methods: ['GET'])]
    public function deleteCode($code): RedirectResponse
    {
        $entity = $this->em->getRepository(DfxAnmeldungen::class)->findOneBy(['code' => $code]);
        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxAnmeldungen entity.');
        }

        $anmeldung = clone($entity);
        // Hole aktuelle Kartenzahl
        $termin =  $entity->getTermin();
        $this->anmeldungCapacityService->release($termin, $entity);

        $datefix = $termin->getDatefix();
        $this->em->remove($entity);
        $this->em->flush();
        $this->anmeldungNotificationService->notifyDeleted($anmeldung, $termin);
        return $this->redirect($datefix->getFrontendUrl());
    }
}
