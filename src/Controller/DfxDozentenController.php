<?php

namespace App\Controller;

use App\Security\CurrentContext;
use App\Service\Calendar\AdminDozentenFormFactory;
use App\Service\Calendar\DozentenJsonBuilder;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DfxDozenten;
use Knp\Component\Pager\PaginatorInterface;

class DfxDozentenController extends AbstractController
{
    public function __construct(
        private readonly AdminDozentenFormFactory $adminDozentenFormFactory,
        private readonly DozentenJsonBuilder $dozentenJsonBuilder,
        private readonly EntityManagerInterface $em,
        private readonly CurrentContext $currentContext,
    ) {
    }

    #[Route(path: '/admin/dozenten/', name: 'admin_dozenten', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
    	$user = $this->currentContext->getUser();
    	$kid = $user -> getDatefix() -> getId();
    	$strWhere='v.datefix = :kid';
    	$arParams = ['kid' => $kid];
    	    	
    	if (false === $this->isGranted('ROLE_DFX_ALL')) {
    		$uid = $user->getId();
    		$strWhere .=' AND v.user = :uid';
    		$arParams['uid'] = $uid;
    	}
    	
        /** @var EntityRepository $entities */
        $entities = $this->em->getRepository(DfxDozenten::class);

        $queryBuilder = $entities->createQueryBuilder('v')
            ->where($strWhere);
        foreach ($arParams as $name => $value) {
            $queryBuilder->setParameter($name, $value);
        }
        $query = $queryBuilder
            ->orderBy('v.name', 'ASC')
            ->getQuery();
         
           

        $pagination = $paginator->paginate(
        		$query,
        		$request->query->getInt('dfxp', 1)/*page number*/,
        		20/*limit per page*/
        );
               
        return $this->render('DfxDozenten/index.html.twig', ['pagination' => $pagination]);
        
        
    }

    #[Route(path: '/admin/dozenten/json/{id}', name: 'dozent_json', methods: ['GET'])]
    public function jsonAction(int $id): JsonResponse
    {
        $entity = $this->em->getRepository(DfxDozenten::class)->find($id);
        if (!$entity instanceof DfxDozenten) {
            throw $this->createNotFoundException('Unable to find DfxDozenten entity.');
        }

        return new JsonResponse($this->dozentenJsonBuilder->build($entity));
    }


    #[Template("DfxDozenten/new.html.twig")]
    #[Route(path: '/admin/dozenten/', name: 'dozent_create')]
    public function create(Request $request): RedirectResponse|array
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();

    	
        $entity = new DfxDozenten();
        $entity->setDatefix($konf);
        $now = new DateTime(date("Y-m-d H:i:s"));
        $entity->setDatumInput($now);
        $form = $this->adminDozentenFormFactory->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity->setUser($user);
            $this->em->persist($entity);
            $this->em->flush();

            return $this->redirectToRoute('admin_dozenten');
        }

        return [
            'entity' => $entity,
        	'konf' => $konf,
            'form'   => $form->createView(),
        ];
    }

    #[Template("DfxDozenten/new.html.twig")]
    #[Route(path: '/admin/dozenten/new', name: 'dozent_new', methods: ['GET'])]
    public function new(): array
    {
        $user = $this->currentContext->getUser();
        $konf = $user -> getDatefix();
        $entity = new DfxDozenten();
        $entity -> setDatefix($konf);

        $form   = $this->adminDozentenFormFactory->createCreateForm($entity);
        
        return [
        		'entity' => $entity,
        		'konf' => $konf,
        		'form'   => $form->createView(),
        ];

       
    }

    #[Template("DfxDozenten/edit.html.twig")]
    #[Route(path: '/admin/dozenten/{id}/edit', name: 'dozent_edit', methods: ['GET'])]
    public function edit($id): array
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();
    	$kid = $konf -> getId();

        $entity = $this->em->getRepository(DfxDozenten::class)->find($id);


        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxDozenten entity.');
        }

        if($kid != $entity -> getDatefix() -> getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        if (false === $this->isGranted('ROLE_DFX_ALL') && $user != $entity ->getUser()) {
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Users.');
        }
        $form   = $this->adminDozentenFormFactory->createEditForm($entity);

        return [
        		'entity' => $entity,
        		'konf' => $konf,
        		'form'   => $form->createView(),
        ];

        
        
    }

    #[Template("DfxDozenten/edit.html.twig")]
    #[Route(path: '/admin/dozenten/{id}', name: 'dozent_update', methods: ['PUT'])]
    public function update(Request $request, $id): RedirectResponse|array
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();

        $entity = $this->em->getRepository(DfxDozenten::class)->find($id);

        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxDozenten entity.');
        }

        if($konf != $entity ->getDatefix()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        if (false === $this->isGranted('ROLE_DFX_ALL') && $user != $entity ->getUser()) {
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Users.');
        }

        $now = new DateTime(date("Y-m-d H:i:s"));
        $entity->setDatumModified($now);

        $editForm = $this->adminDozentenFormFactory->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('admin_dozenten');
        }

         return [
         		'entity' => $entity,
         		'konf' => $konf,
         		'form'   => $editForm->createView(),
         ];
        
    }

    #[Route(path: '/admin/dozenten/{id}/delete', name: 'dozent_delete', methods: ['GET'])]
    public function delete($id): RedirectResponse
    {
    	$user = $this->currentContext->getUser();
    	$kid = $user -> getDatefix() -> getId();

        $entity = $this->em->getRepository(DfxDozenten::class)->find($id);

        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxDozenten entity.');
        }

        if($kid != $entity ->getDatefix()->getId()){
          	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        if (false === $this->isGranted('ROLE_DFX_ALL') && $user != $entity ->getUser()) {
            throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Users.');
        }

        $this->em->remove($entity);
        $this->em->flush();
        return $this->redirectToRoute('admin_dozenten');
    }
}
