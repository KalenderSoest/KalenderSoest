<?php

namespace App\Controller;

use App\Service\Presentation\TemplatePathResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DfxRegion;
use App\Form\DfxRegionType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\CurrentContext;

/**
 * DfxRegion controller.
 */
#[IsGranted('ROLE_ADMIN')]
class DfxRegionController extends AbstractController
{


    public function __construct(private readonly TemplatePathResolver $templatePathResolver, private readonly EntityManagerInterface $em, private readonly CurrentContext $currentContext)
    {
        }

    /**
     * Lists all DfxRegion entities.
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    #[Route(path: '/admin/region/', name: 'admin_region', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
    	$user = $this->currentContext->getUser();
        $konf = $user -> getDatefix();
    	$kid = $user -> getDatefix() -> getId();
    	$strWhere='r.datefix = :kid';
    	$arParams = ['kid' => $kid];
    	    	
        $entities = $this->em->getRepository(DfxRegion::class);
        
        $query = $entities->createQueryBuilder('r')
        ->where($strWhere)
         ->setParameter('kid',$kid)
        ->orderBy('r.region', 'ASC')
        ->getQuery();
         
           

        $pagination = $paginator->paginate(
        		$query,
        		$request->query->getInt('dfxp', 1)/*page number*/,
        		20/*limit per page*/
        );
        $tpl = $this->templatePathResolver->resolve('DfxRegion','index.html.twig', $konf);
        return $this->render($tpl, ['pagination' => $pagination]);
        
        
    }


    /**
     * Creates a new DfxRegion entity.
     *

     * @param Request $request
     * @return array|RedirectResponse
     */
    #[Template("DfxRegion/new.html.twig")]
    #[Route(path: '/admin/region/', name: 'region_create')]
    public function create(Request $request): RedirectResponse|Response
    {
    	
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();

        $entity = new DfxRegion();
        $entity -> setDatefix($konf);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $konf->setFeldRegion(1);
            $this->em->persist($konf);
            $this->em->persist($entity);
            $this->em->flush();
            return $this->redirectToRoute('admin_region');
        }
              
        $form   = $this->createCreateForm($entity);
        $tpl = $this->templatePathResolver->resolve('DfxRegion','new.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity, 'form'   => $form]);


    }

    /**
     * Creates a form to create a DfxRegion entity.
     *
     * @param DfxRegion $entity The entity
     *
     * @return FormInterface The form
     */
    private function createCreateForm(DfxRegion $entity): FormInterface
    {
    	
        $form = $this->createForm( DfxRegionType::class, $entity, [
            'action' => $this->generateUrl('region_create'),
            'method' => 'POST',
        ]);

       $form->add('submit', SubmitType::class, ['label' => 'Datensatz speichern', 'attr' => ['class' =>'btn btn-primary']])
        ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' =>'btn btn-primary']]);
       
       return $form;
   
    }

    /**
     * Displays a form to create a new DfxRegion entity.
     *

     */
    #[Template("DfxRegion/new.html.twig")]
    #[Route(path: '/admin/region/new', name: 'region_new', methods: ['GET'])]
    public function new(): Response
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();

        $entity = new DfxRegion();
        $entity -> setDatefix($konf);
        $form   = $this->createCreateForm($entity);

        $tpl = $this->templatePathResolver->resolve('DfxRegion','new.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity, 'form'   => $form]);

        
    }


    /**
     * Displays a form to edit an existing DfxRegion entity.
     *
     * @param int $id
     * @return Response
     */
    #[Route(path: '/admin/region/{id}/edit', name: 'region_edit', methods: ['GET'])]
    public function edit(int $id): Response
    {
    	$user = $this->currentContext->getUser();
        $konf = $user -> getDatefix();
    	$kid = $user -> getDatefix() -> getId();

        $entity = $this->em->getRepository(DfxRegion::class)->find($id);

        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxRegion entity.');
        }

        if($kid != $entity -> getDatefix() -> getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        $form   = $this->createEditForm($entity);

        $tpl = $this->templatePathResolver->resolve('DfxRegion','edit.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity, 'form'   => $form]);
    }

    /**
    * Creates a form to edit a DfxRegion entity.
    *
    * @param DfxRegion $entity The entity
    *
    * @return FormInterface The form
    */
    private function createEditForm(DfxRegion $entity): FormInterface
    {
        $form = $this->createForm( DfxRegionType::class, $entity, [
            'action' => $this->generateUrl('region_update', ['id' => $entity->getId()]),
            'method' => 'PUT',
			
        ]);

       $form->add('submit', SubmitType::class, ['label' => 'Änderungen speichern', 'attr' => ['class' =>'btn btn-primary']])
        ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' =>'btn btn-primary']]);
       return $form;
    }

    /**
     * Edits an existing DfxRegion entity.
     *

     * @param Request $request
     * @param $id
     * @return array|RedirectResponse
     */
    #[Template("DfxRegion/edit.html.twig")]
    #[Route(path: '/admin/region/{id}', name: 'region_update', methods: ['PUT'])]
    public function update(Request $request, $id): RedirectResponse|Response
    {
    	$user = $this->currentContext->getUser();
        $konf = $user -> getDatefix();
    	$kid = $user -> getDatefix() -> getId();

        $entity = $this->em->getRepository(DfxRegion::class)->find($id);
        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxRegion entity.');
        }

        if($kid != $entity ->getDatefix()-> getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        $form = $this->createEditForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            return $this->redirectToRoute('admin_region');
        }

        $tpl = $this->templatePathResolver->resolve('DfxRegion','edit.html.twig', $konf);
        return $this->render($tpl, ['entity' => $entity, 'form'   => $form]);
    }

    /**
     * Deletes a DfxRegion entity.
     *
     * @param $id
     * @return RedirectResponse
     */
    #[Route(path: '/admin/region/{id}/delete', name: 'region_delete', methods: ['GET'])]
    public function delete($id): RedirectResponse
    {
       $user = $this->currentContext->getUser();
       $kid = $user -> getDatefix() -> getId();
       $entity = $this->em->getRepository(DfxRegion::class)->find($id);

       if ($entity === null) {
           throw $this->createNotFoundException('Unable to find DfxRegion entity.');
       }

       if($kid != $entity ->getDatefix()->getId()){
           throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
       }

       $this->em->remove($entity);
       $this->em->flush();
       return $this->redirectToRoute('admin_region');
    }
}
