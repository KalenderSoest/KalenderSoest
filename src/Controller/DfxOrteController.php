<?php

namespace App\Controller;

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
use App\Entity\DfxOrte;
use App\Form\DfxOrteType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\CurrentContext;

/**
 * DfxOrte controller.
 */
#[IsGranted('ROLE_ADMIN')]
class DfxOrteController extends AbstractController
{


    public function __construct(private readonly EntityManagerInterface $em, private readonly CurrentContext $currentContext)
    {
        }

    /**
     * Lists all DfxOrte entities.
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    #[Route(path: '/admin/orte/', name: 'admin_orte', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
    	$user = $this->currentContext->getUser();
    	$kid = $user -> getDatefix() -> getId();
    	$strWhere='o.datefix = :kid';

        $entities = $this->em->getRepository(DfxOrte::class);

        $query = $entities->createQueryBuilder('o')
            ->where($strWhere)
            ->setParameter('kid', $kid)
        ->orderBy('o.ort', 'ASC')
        ->getQuery();



        $pagination = $paginator->paginate(
        		$query,
        		$request->query->getInt('dfxp', 1)/*page number*/,
        		20/*limit per page*/
        );
        return $this->render('DfxOrte/index.html.twig', ['pagination' => $pagination]);


    }


    /**
     * Creates a new DfxOrte entity.
     *

     * @param Request $request
     * @return array|RedirectResponse
     */
    #[Template("DfxOrte/new.html.twig")]
    #[Route(path: '/admin/orte/', name: 'orte_create', methods: ['POST'])]
    public function create(Request $request): RedirectResponse|array
    {

    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();

        $entity = new DfxOrte();
        $entity -> setDatefix($konf);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            return $this->redirectToRoute('admin_orte');
        }

        $form   = $this->createCreateForm($entity);
        return [
        		'entity' => $entity,
        		'konf' => $konf,
        		'form'   => $form->createView(),
        ];
    }

    /**
     * Creates a form to create a DfxOrte entity.
     *
     * @param DfxOrte $entity The entity
     *
     * @return FormInterface The form
     */
    private function createCreateForm(DfxOrte $entity): FormInterface
    {

        $form = $this->createForm( DfxOrteType::class, $entity, [
            'action' => $this->generateUrl('orte_create'),
            'method' => 'POST',
        	'konf' => $entity->getDatefix()
        ]);

       $form->add('submit', SubmitType::class, ['label' => 'Datensatz speichern', 'attr' => ['class' =>'btn btn-primary']])
        ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' =>'btn btn-primary']]);

       return $form;

    }

    /**
     * Displays a form to create a new DfxOrte entity.
     */
    #[Template("DfxOrte/new.html.twig")]
    #[Route(path: '/admin/orte/new', name: 'orte_new', methods: ['GET'])]
    public function new(): array
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();

        $entity = new DfxOrte();
        $entity -> setDatefix($konf);

        $form   = $this->createCreateForm($entity);

        return [
        		'entity' => $entity,
        		'konf' => $konf,
        		'form'   => $form->createView(),
        ];


    }


    /**
     * Displays a form to edit an existing DfxOrte entity.
     *
     * @param $id
     * @return array
     */
    #[Template("DfxOrte/edit.html.twig")]
    #[Route(path: '/admin/orte/{id}/edit', name: 'orte_edit', methods: ['GET'])]
    public function edit($id): array
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();
    	$kid = $konf -> getId();

        $entity = $this->em->getRepository(DfxOrte::class)->find($id);

        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxOrte entity.');
        }

        if($kid != $entity -> getDatefix() -> getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        $form   = $this->createEditForm($entity);

        return [
        		'entity' => $entity,
        		'konf' => $konf,
        		'form'   => $form->createView(),
        ];

    }

    /**
    * Creates a form to edit a DfxOrte entity.
    *
    * @param DfxOrte $entity The entity
    *
    * @return FormInterface The form
    */
    private function createEditForm(DfxOrte $entity): FormInterface
    {
        $form = $this->createForm( DfxOrteType::class, $entity, [
            'action' => $this->generateUrl('orte_update', ['id' => $entity->getId()]),
            'method' => 'POST',
			'konf' => $entity->getDatefix()
        ]);

       $form->add('submit', SubmitType::class, ['label' => 'Änderungen speichern', 'attr' => ['class' =>'btn btn-primary']])
        ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' =>'btn btn-primary']]);


        return $form;
    }

    /**
     * Edits an existing DfxOrte entity.
     *

     * @param Request $request
     * @param $id
     * @return array|RedirectResponse
     */
    #[Template("DfxOrte/edit.html.twig")]
    #[Route(path: '/admin/orte/{id}', name: 'orte_update', methods: ['PUT', 'POST'])]
    public function update(Request $request, $id): RedirectResponse|array
    {
    	$user = $this->currentContext->getUser();
    	$konf = $user -> getDatefix();
    	$kid = $konf -> getId();

        $entity = $this->em->getRepository(DfxOrte::class)->find($id);
        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxOrte entity.');
        }

        if($kid != $entity ->getDatefix()-> getId()){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        $form = $this->createEditForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            return $this->redirectToRoute('admin_orte');
        }
        return [
        		'entity' => $entity,
        		'konf' => $konf,
        		'form'   => $form->createView(),
        ];
    }

    /**
     * Deletes a DfxOrte entity.
     *
     * @param $id
     * @return RedirectResponse
     */
    #[Route(path: '/admin/orte/{id}/delete', name: 'orte_delete', methods: ['GET'])]
    public function delete($id): RedirectResponse
    {
       $user = $this->currentContext->getUser();
       $kid = $user -> getDatefix() -> getId();
       $entity = $this->em->getRepository(DfxOrte::class)->find($id);

       if ($entity === null) {
           throw $this->createNotFoundException('Unable to find DfxOrte entity.');
       }

       if($kid != $entity ->getDatefix()->getId()){
           throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
       }

       $this->em->remove($entity);
       $this->em->flush();
       return $this->redirectToRoute('admin_orte');
    }
}
