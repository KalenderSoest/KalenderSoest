<?php

namespace App\Controller;

use App\Security\CurrentContext;
use App\Service\Calendar\AdminVeranstalterFormFactory;
use App\Service\Calendar\VeranstalterJsonBuilder;
use App\Service\Calendar\VeranstalterMediaUploadService;
use App\Service\Presentation\TemplatePathResolver;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DfxVeranstalter;
use Knp\Component\Pager\PaginatorInterface;

class DfxVeranstalterController extends AbstractController
{
    public function __construct(
        private readonly AdminVeranstalterFormFactory $adminVeranstalterFormFactory,
        private readonly VeranstalterJsonBuilder $veranstalterJsonBuilder,
        private readonly VeranstalterMediaUploadService $veranstalterMediaUploadService,
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly EntityManagerInterface $em,
        private readonly CurrentContext $currentContext,
    ) {
    }

    #[Route(path: '/admin/veranstalter/', name: 'admin_veranstalter', methods: ['GET', 'POST'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();
    	$kid = $user -> getDatefix() -> getId();
    	$strWhere='v.datefix = :kid';
    	$arParams = ['kid' => $kid];

    	if (false === $this->isGranted('ROLE_DFX_ALL')) {
    		$uid = $user->getId();
    		$strWhere .=' AND v.user = :uid';
    		$arParams['uid'] = $uid;
    	}

        $query = $this->em->createQueryBuilder()
            ->select('v')
            ->from(DfxVeranstalter::class, 'v')
            ->where($strWhere);

        $formSuche = $this->adminVeranstalterFormFactory->createFilterForm();
        $formSuche->handleRequest($request);
        if ($formSuche->isSubmitted() && $formSuche->isValid()) {
            $suche = $formSuche->getData();
            if(isset($suche['vid'])){
                $query ->andWhere('v.id = :vid');
                $arParams['vid'] = $suche['vid'];
            }

            if(isset($suche['name'])){
                $query ->andWhere('v.name LIKE :name');
                $arParams['name'] = '%'.$suche['name'].'%';
            }
        }

        $query
            ->setParameters(new ArrayCollection(array_map(
                static fn($value, $key) => new Parameter($key, $value),
                $arParams,
                array_keys($arParams)
            )))
            ->orderBy('v.name', 'ASC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('dfxp', 1)/*page number*/,
            20/*limit per page*/
        );
        $tpl = $this->templatePathResolver->resolve('DfxVeranstalter','index.html.twig', $konf);
        return $this->render($tpl, ['pagination' => $pagination, 'form_suche' => $formSuche->createView()]);
    }

    #[Route(path: '/admin/veranstalter/json/{id}', name: 'veranstalter_json', methods: ['GET'])]
    public function jsonAction(#[MapEntity(id: 'id')] DfxVeranstalter $entity): JsonResponse
    {
        return new JsonResponse($this->veranstalterJsonBuilder->build($entity));
    }


    #[Template("DfxVeranstalter/new.html.twig")]
    #[Route(path: '/admin/veranstalter/create', name: 'veranstalter_create', methods: ['POST'])]
    public function create(Request $request): RedirectResponse|Response
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();
        $kid = $konf->getId();
        $entity = new DfxVeranstalter();
        $entity->setDatefix($konf);
        $now = new DateTime(date("Y-m-d H:i:s"));
        $entity->setDatumInput($now);
        $form = $this->adminVeranstalterFormFactory->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity->setUser($user);
            $this->veranstalterMediaUploadService->applyUploadedFiles($request, $entity, $kid);

            $this->em->persist($entity);
            $this->em->flush();
            return $this->redirectToRoute('admin_veranstalter');
        }

        $tpl = $this->templatePathResolver->resolve('DfxVeranstalter','new.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form'   => $form]);
    }

    #[Template("DfxVeranstalter/new.html.twig")]
    #[Route(path: '/admin/veranstalter/new', name: 'veranstalter_new', methods: ['GET'])]
    public function new(): Response
    {
        $user = $this->currentContext->getUser();
        $konf = $user -> getDatefix();

        $entity = new DfxVeranstalter();
        $entity -> setDatefix($konf);

        $form   = $this->adminVeranstalterFormFactory->createCreateForm($entity);
        $tpl = $this->templatePathResolver->resolve('DfxVeranstalter','new.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form'   => $form]);


    }

    #[Template("DfxVeranstalter/edit.html.twig")]
    #[Route(path: '/admin/veranstalter/{id}/edit', name: 'veranstalter_edit', methods: ['GET'])]
    public function edit(#[MapEntity(id: 'id')] DfxVeranstalter $entity): Response
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

        $form   = $this->adminVeranstalterFormFactory->createEditForm($entity);
        $tpl = $this->templatePathResolver->resolve('DfxVeranstalter','edit.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form'   => $form]);
    }

    #[Template("DfxVeranstalter/edit.html.twig")]
    #[Route(path: '/admin/veranstalter/{id}', name: 'veranstalter_update', methods: ['POST'])]
    public function update(Request $request, #[MapEntity(id: 'id')] DfxVeranstalter $entity): RedirectResponse|Response
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();
    	$kid = $konf->getId();
        if($konf !== $entity ->getDatefix()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        if (false === $this->isGranted('ROLE_DFX_ALL') && $user !== $entity ->getUser()) {
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Users.');
        }

        $now = new DateTime(date("Y-m-d H:i:s"));
        $entity->setDatumModified($now);

        $form = $this->adminVeranstalterFormFactory->createEditForm($entity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get("imageFileDelete")->getData() === true){
                $datei= $this->getParameter('kernel.project_dir').'/web/images/dfx/'.$kid.'/'.$entity->getImgVer();
                if(is_file($datei)) {
                    unlink($datei);
                }

                $entity->setImgVer(null);
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

            $this->veranstalterMediaUploadService->applyUploadedFiles($request, $entity, $kid);

            $this->em->persist($entity);
            $this->em->flush();

            return $this->redirectToRoute('admin_veranstalter');
        }


        $tpl = $this->templatePathResolver->resolve('DfxVeranstalter','new.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity,'konf' => $konf,'form'   => $form]);

    }

    #[Route(path: '/admin/veranstalter/{id}/delete', name: 'veranstalter_delete', methods: ['GET'])]
    public function delete(#[MapEntity(id: 'id')] DfxVeranstalter $entity): RedirectResponse
    {
    	$user = $this->currentContext->getUser();
    	$kid = $user -> getDatefix() -> getId();
        if($kid != $entity ->getDatefix()->getId()){
          	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        if (false === $this->isGranted('ROLE_DFX_ALL') && $user !== $entity ->getUser()) {
            throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Users.');
        }

        $image = $entity->getImgVer();
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
        return $this->redirectToRoute('admin_veranstalter');
    }
}
