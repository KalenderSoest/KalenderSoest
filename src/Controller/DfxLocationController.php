<?php

namespace App\Controller;

use App\Service\Calendar\AdminLocationFormFactory;
use App\Service\Calendar\LocationJsonBuilder;
use App\Service\Calendar\LocationMediaUploadService;
use App\Service\Presentation\TemplatePathResolver;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DfxLocation;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\CurrentContext;

/**
 * DfxLocation controller.
 */
#[IsGranted('ROLE_USER')]
class DfxLocationController extends AbstractController
{
    public function __construct(
        private readonly AdminLocationFormFactory $adminLocationFormFactory,
        private readonly LocationJsonBuilder $locationJsonBuilder,
        private readonly LocationMediaUploadService $locationMediaUploadService,
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly EntityManagerInterface $em,
        private readonly CurrentContext $currentContext,
    ) {
    }


    #[Route(path: '/admin/locations/', name: 'admin_locations', methods: ['GET', 'POST'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
    	$user = $this->currentContext->getUser();
        $kid = $user->getDatefix()->getId();
        $entities = $this->em->getRepository(DfxLocation::class);
        $query = $entities->createQueryBuilder('l')
            ->where('l.datefix = :kid')
            ->setParameter('kid',$kid);

    	if (false === $this->isGranted('ROLE_DFX_ALL')) {
    		$uid = $user->getId();
            $query->andWhere('l.user = :uid')
                ->setParameter('uid',$uid);
        }
        $formSuche = $this->adminLocationFormFactory->createFilterForm();
        $formSuche->handleRequest($request);
        if ($formSuche->isSubmitted() && $formSuche->isValid()) {
            $suche = $formSuche->getData();
            if(isset($suche['lid'])){
                $query ->andWhere('l.id = :lid')
                    ->setParameter('lid', $suche['lid']);

            }

            if(isset($suche['name'])){
                $query ->andWhere('l.name LIKE :name')
                ->setParameter('name', '%'.$suche['name'].'%');

            }
        }

        $query
            ->orderBy('l.name', 'ASC')
            ->getQuery();

        $pagination = $paginator->paginate(
        		$query,
        		$request->query->getInt('dfxp', 1)/*page number*/,
        		20/*limit per page*/
        );
        return $this->render('DfxLocation/index.html.twig', ['pagination' => $pagination, 'form_suche' => $formSuche->createView()]);
    }


    #[Route(path: '/admin/locations/json/{id}', name: 'location_json', methods: ['GET'])]
    public function jsonAction(#[MapEntity(id: 'id')] DfxLocation $entity): JsonResponse
    {
        return new JsonResponse($this->locationJsonBuilder->build($entity));
    }


    #[Template("DfxLocation/new.html.twig")]
    #[Route(path: '/admin/locations/create', name: 'location_create', methods: ['POST'])]
    public function create(Request $request): RedirectResponse|Response
    {

    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();
    	$kid = $konf->getId();

        $entity = new DfxLocation();
        $entity -> setDatefix($konf);
        $now = new DateTime(date("Y-m-d H:i:s"));
        $entity->setDatumInput($now);
        $form = $this->adminLocationFormFactory->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity -> setUser($user);
            $this->locationMediaUploadService->applyUploadedFiles($request, $entity, $kid);

            $this->em->persist($entity);
            $this->em->flush();
            return $this->redirectToRoute('admin_locations');
        }

        $tpl = $this->templatePathResolver->resolve('DfxLocation','new.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form'   => $form]);
    }

     #[Template("DfxLocation/new.html.twig")]
    #[Route(path: '/admin/locations/new', name: 'location_new', methods: ['GET'])]
    public function new(): Response
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();

        $entity = new DfxLocation();
        $entity -> setDatefix($konf);

        $form   = $this->adminLocationFormFactory->createCreateForm($entity);
        $tpl = $this->templatePathResolver->resolve('DfxLocation','new.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form'   => $form]);

    }


    #[Route(path: '/admin/locations/{id}/edit', name: 'location_edit', methods: ['GET'])]
    public function edit(#[MapEntity(id: 'id')] DfxLocation $entity): Response
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();
    	$kid = $konf -> getId();

        if($kid != $entity -> getDatefix() -> getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        if (false === $this->isGranted('ROLE_DFX_ALL') && $user !== $entity ->getUser()) {
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Users.');
        }

        $form   = $this->adminLocationFormFactory->createEditForm($entity);
        $tpl = $this->templatePathResolver->resolve('DfxLocation','edit.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form'   => $form]);


    }

    #[Route(path: '/admin/locations/{id}', name: 'location_update', methods: ['POST'])]
    public function update(Request $request, #[MapEntity(id: 'id')] DfxLocation $entity): RedirectResponse|Response
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();
    	$kid = $konf -> getId();

        if($kid != $entity ->getDatefix()-> getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        if (false === $this->isGranted('ROLE_DFX_ALL') && $user !== $entity ->getUser()) {
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Users.');
        }

        $now = new DateTime(date("Y-m-d H:i:s"));
        $entity->setDatumModified($now);
        $form = $this->adminLocationFormFactory->createEditForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get("imageFileDelete")->getData() === true){
                $datei= $this->getParameter('kernel.project_dir').'/web/images/dfx/'.$kid.'/'.$entity->getImgLoc();
                if(is_file($datei)) {
                    unlink($datei);
                }

                $entity->setImgLoc(null);
            }

            if($form->get("imageFileDelete2")->getData() === true) {
                $datei = $this->getParameter('kernel.project_dir') . '/web/images/dfx/' . $kid . '/' . $entity->getImg2();
                if (is_file($datei)) {
                    unlink($datei);
                }

                $entity->setImg2(null);
            }

            if($form->get("imageFileDelete3")->getData() === true) {
                $datei = $this->getParameter('kernel.project_dir') . '/web/images/dfx/' . $kid . '/' . $entity->getImg3();
                if (is_file($datei)) {
                    unlink($datei);
                }

                $entity->setImg3(null);
            }

            if($form->get("imageFileDelete4")->getData() === true) {
                $datei = $this->getParameter('kernel.project_dir') . '/web/images/dfx/' . $kid . '/' . $entity->getImg4();
                if (is_file($datei)) {
                    unlink($datei);
                }

                $entity->setImg4(null);
            }

            if($form->get("imageFileDelete5")->getData() === true) {
                $datei = $this->getParameter('kernel.project_dir') . '/web/images/dfx/' . $kid . '/' . $entity->getImg5();
                if (is_file($datei)) {
                    unlink($datei);
                }

                $entity->setImg5(null);
            }

            $this->locationMediaUploadService->applyUploadedFiles($request, $entity, $kid);

            $this->em->flush();
            return $this->redirectToRoute('admin_locations');
        }

        $tpl = $this->templatePathResolver->resolve('DfxLocation','edit.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form'   => $form]);

    }

    #[Route(path: '/admin/locations/{id}/delete', name: 'location_delete', methods: ['GET'])]
    public function delete(#[MapEntity(id: 'id')] DfxLocation $entity): RedirectResponse
    {
    	$user = $this->currentContext->getUser();
    	$kid = $user -> getDatefix() -> getId();

       if($kid != $entity ->getDatefix()->getId()){
           throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
       }

       if (false === $this->isGranted('ROLE_DFX_ALL') && $user !== $entity ->getUser()) {
           throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Users.');
       }

       $image = $entity->getImgLoc();
       if($image !== null){
            $datei = $this->getParameter('kernel.project_dir').'/web/images/dfx/'.$kid.'/'.$image;
            unlink($datei);
       }

        $image = $entity->getImg2();
        if($image !== null){
            $datei = $this->getParameter('kernel.project_dir').'/web/images/dfx/'.$kid.'/'.$image;
            unlink($datei);
        }

        $image = $entity->getImg3();
        if($image !== null){
            $datei = $this->getParameter('kernel.project_dir').'/web/images/dfx/'.$kid.'/'.$image;
            unlink($datei);
        }

        $image = $entity->getImg4();
        if($image !== null){
            $datei = $this->getParameter('kernel.project_dir').'/web/images/dfx/'.$kid.'/'.$image;
            unlink($datei);
        }

        $image = $entity->getImg5();
        if($image !== null){
            $datei = $this->getParameter('kernel.project_dir').'/web/images/dfx/'.$kid.'/'.$image;
            unlink($datei);
       }

       $this->em->remove($entity);
       $this->em->flush();

       return $this->redirectToRoute('admin_locations');
    }
}
