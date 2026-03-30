<?php

namespace App\Controller;

use App\Entity\DfxNfxUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\DfxNfxUserType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\CurrentContext;
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly CurrentContext $currentContext, private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * Lists all DfxNfxUser entities.
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    #[Route(path: '/admin/user/', name: 'admin_user', methods: ['GET', 'POST'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->currentContext->getUser();
        $konf = $user -> getDatefix();
    	$kid = $konf -> getId();
    	$entities = $this->em->getRepository(DfxNfxUser::class);
        if($konf->getIsMeta() == 1 && $this->isGranted('ROLE_SUPER_ADMIN')){
            $query = $entities->createQueryBuilder('u')
                ->where('u.roles LIKE :role1 OR u.roles LIKE :role2 OR u.roles LIKE :role3 OR u.roles LIKE :role4 OR u.datefix = :kid')
                ->setParameter('role1', $this->jsonArrayContainsPattern('ROLE_ADMIN'))
                ->setParameter('role2', $this->jsonArrayContainsPattern('ROLE_DFX_META'))
                ->setParameter('role3', $this->jsonArrayContainsPattern('ROLE_DFX_GROUP'))
                ->setParameter('role4', $this->jsonArrayContainsPattern('ROLE_SUPER_ADMIN'))
                ->setParameter('kid',$kid);
        }else{
            $query = $entities->createQueryBuilder('u')
                ->where('u.datefix = :kid')
                ->setParameter('kid',$kid);
        }

    	$formSuche = $this->createFilterForm();
        $formSuche->handleRequest($request);
        if ($formSuche->isSubmitted() && $formSuche->isValid()) {
            $suche = $formSuche->getData();
            if(isset($suche['uid'])){
                $query ->andWhere('u.id = :uid')
                    ->setParameter('uid',$suche['uid']);

            }

            if(isset($suche['name'])){
                $query ->andWhere('u.nameLang LIKE :name')
                    ->setParameter('name',$suche['name']);
            }

            if(isset($suche['email'])){
                $query ->andWhere('u.email LIKE :email')
                ->setParameter('email',$suche['email']);
            }
        }

        $query->orderBy('u.nameLang', 'ASC')
        ->getQuery();
        $pagination = $paginator->paginate(
        		$query,
        		$request->query->getInt('dfxp', 1)/*page number*/,
        		20/*limit per page*/
        );
        return $this->render('User/index.html.twig', ['pagination' => $pagination, 'form_suche' => $formSuche->createView()]);

    }

    private function createFilterForm(): FormInterface
    {
        $filter = [];
        return $this->createFormBuilder($filter, ['method' => 'POST', 'action' => $this->generateUrl('admin_user')])
            ->add('uid', TextType::class, ['label' => 'Usernummer', 'required' => false, 'attr' => []])
            ->add('name', TextType::class, ['label' => 'Name', 'required' => false, 'attr' => []])
            ->add('email', TextType::class, ['label' => 'Email', 'required' => false, 'attr' => []])
            ->add('submit', SubmitType::class, ['label' => 'Suchen', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();
    }


    /**
     * Creates a new DfxNfxUser entity.
     *

     * @param Request $request
     * @return array|RedirectResponse
     */
    #[Template("User/new.html.twig")]
    #[Route(path: '/admin/user/create', name: 'user_create', methods: ['POST'])]
    public function create(Request $request): RedirectResponse|array
    {
    	$newUser = new DfxNfxUser();
    	$user = $this->currentContext->getUser();
    	// $kid = $user -> getDatefix() -> getId();
    	$kalender = $user -> getDatefix();
    	$kunde = $user -> getKunde();

    	$form = $this->createCreateForm($newUser);
    	$form->handleRequest($request);
    	// $formdata = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
        	$newUser -> setUsername('123_'.$newUser -> getEmail());
        	$newUser -> setDatefix($kalender);
        	$newUser -> setKunde($kunde);
        	$newUser -> setRoles($this->mergeRoles($newUser->getRoles(), $form->get('rolesM')->getData(), $form->get('rolesG')->getData()));
            $newUser -> setPassword($this->passwordHasher->hashPassword($newUser, $form->get('password')->getData()));

        	$this->em->persist($newUser);
        	$this->em->flush();

            $newUser -> setUsername($newUser -> getId());
            $this->em->persist($newUser);
            $this->em->flush();


            return $this->redirectToRoute('admin_user');

        }

        return [
            'entity' => $newUser,
            'form'   => $form->createView(),
        ];
    }

    /**
     * Creates a form to create a DfxNfxUser entity.
     *
     * @param DfxNfxUser $newUser
     * @return FormInterface The form
     */
    private function createCreateForm(DfxNfxUser $newUser): FormInterface
    {
        $form = $this->createForm( DfxNfxUserType::class, $newUser, [
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
            'password_required' => true,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Datensatz speichern', 'attr' => ['class' =>'btn btn-primary']])
        ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' =>'btn btn-primary']]);

       return $form;
    }

    /**
  * Displays a form to create a new DfxNfxUser entity.
  */
 #[Template("User/new.html.twig")]
 #[Route(path: '/admin/user/new', name: 'user_new', methods: ['GET'])]
 public function new(): array
    {

    	$adminuser = $this->currentContext->getUser();
    	$kalender = $adminuser -> getDatefix();

    	$kunde = $adminuser -> getKunde();

    	$newUser = new DfxNfxUser();
    	$newUser -> setDatefix($kalender);
    	$newUser -> setKunde($kunde);

    	$form   = $this->createCreateForm($newUser);

    	return [
    			'entity' => $newUser,
    			'form'   => $form->createView(),
    	];
    }


    /**
     * Displays a form to edit an existing DfxNfxUser entity.
     *
     * @param $id
     * @return array
     */
    #[Template("User/edit.html.twig")]
    #[Route(path: '/admin/user/{id}/edit', name: 'user_edit', methods: ['GET'])]
    public function edit(#[MapEntity(id: 'id')] DfxNfxUser $entity): array
    {
    	$user = $this->currentContext->getUser();
    	$kunde = $user -> getKunde();

        if($kunde !== $entity ->getKunde() && false === $this->isGranted('ROLE_SUPER_ADMIN')){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Kunden.');
        }

        $arRoles = $entity->getRoles();
        if(in_array('ROLE_DFX_META', $arRoles)){
        	$entity->setRolesM(['ROLE_DFX_META']);
        }

        if(in_array('ROLE_DFX_GROUP', $arRoles)){
        	$entity->setRolesG(['ROLE_DFX_GROUP']);
        }

        $editForm = $this -> createEditForm($entity);


        return [
            'entity'      => $entity,
            'form'   => $editForm->createView(),

        ];
    }

    /**
    * Creates a form to edit a DfxNfxUser entity.
    *
    * @param DfxNfxUser $entity The entity
    *
    * @return FormInterface The form
    */
    private function createEditForm(DfxNfxUser $entity): FormInterface
    {
        $form = $this->createForm( DfxNfxUserType::class, $entity, [
            'action' => $this->generateUrl('user_update', ['id' => $entity->getId()]),
            'method' => 'POST',
        	'rolesM' => $entity->getRolesM(),
        	'rolesG' => $entity->getRolesG(),
            'password_required' => false,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Änderungen speichern', 'attr' => ['class' =>'btn btn-primary']])
        ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' =>'btn btn-primary']]);

       return $form;

    }

    /**
     * Edits an existing DfxNfxUser entity.
     *
     * @param Request $request
     * @param $id
     * @return array|RedirectResponse
     */
    #[Template("User/edit.html.twig")]
    #[Route(path: '/admin/user/{id}/update', name: 'user_update', methods: ['POST'])]
    public function update(Request $request, #[MapEntity(id: 'id')] DfxNfxUser $entity): RedirectResponse|array
    {
    	$user = $this->currentContext->getUser();
    	$kunde = $user -> getKunde();

        if($kunde !== $entity ->getKunde() && false === $this->isGranted('ROLE_SUPER_ADMIN')){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Kunden.');
        }

        $arRoles = $entity->getRoles();
        if(in_array('ROLE_DFX_META', $arRoles)){
        	$entity->setRolesM(['ROLE_DFX_META']);
        }

        if(in_array('ROLE_DFX_GROUP', $arRoles)){
        	$entity->setRolesG(['ROLE_DFX_GROUP']);
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entity->setRoles($this->mergeRoles($entity->getRoles(), $editForm->get('rolesM')->getData(), $editForm->get('rolesG')->getData()));
            if ($entity->getId() == 100) {
                $entity->setRoles(['ROLE_SUPER_ADMIN']);
            }

            if (!empty($editForm->get('password')->getData())){
                $entity->setPassword($this->passwordHasher->hashPassword($entity, $editForm->get('password')->getData()));
            }

            $this->em->flush();
            return $this->redirectToRoute('admin_user');
        }

        return [
            'entity'      => $entity,
            'form'   => $editForm->createView(),

        ];
    }

    /**
     * Deletes a DfxNfxUser entity.
     *
     * @param $id
     * @return RedirectResponse
     */
    #[Route(path: '/admin/user/{id}/delete', name: 'user_delete', methods: ['GET'])]
    public function delete(#[MapEntity(id: 'id')] DfxNfxUser $entity): RedirectResponse
    {
    	if($entity->getId() == 100){
    		throw $this->createNotFoundException('Der User 100 darf nicht gelöscht werden.');
    	}

        $user = $this->currentContext->getUser();
        $kunde = $user -> getKunde();

        if($kunde !== $entity ->getKunde() && false === $this->isGranted('ROLE_SUPER_ADMIN')){
        	throw $this->createNotFoundException('Unerlaubter Zugriff auf Datensatz eines anderen Kunden.');
        }

        if($user->getDatefix()->getUser()->getId() == $user->getId()){
            throw $this->createNotFoundException('Der Accountbesitzer darf nicht gelöscht werden.');
        }

        if($user->getId() == 100){
            throw $this->createNotFoundException('Der User 100 darf nicht gelöscht werden.');
        }

        $this->em->remove($entity);
        $this->em->flush();

        return $this->redirectToRoute('admin_user');
    }


    /**
     * setMeta in a User entity.
     *
     * @param $id
     * @return Response
     */
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route(path: '/admin/user/{id}/setmeta', name: 'user_setmeta', methods: ['GET'])]
    public function setMeta(#[MapEntity(id: 'id')] DfxNfxUser $entity): Response
    {

    	$entity -> setRoles($this->mergeRoles($entity->getRoles(), ['ROLE_DFX_META']));
    	$this->em->persist($entity);
    	$this->em->flush();

    	return new Response('ok');
    }

    /**
     * setMeta in a User entity.
     *
     * @param $id
     * @return Response
     */
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route(path: '/admin/user/{id}/unsetmeta', name: 'user_unsetmeta', methods: ['GET'])]
    public function unsetMeta(#[MapEntity(id: 'id')] DfxNfxUser $entity): Response
    {
    	$entity -> setRoles(['ROLE_ADMIN']);
    	$this->em->persist($entity);
    	$this->em->flush();

    	return new Response('ok');
    }

    /**
     * setGroup in a User entity.
     *
     * @param $id
     * @return Response
     */
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route(path: '/admin/user/{id}/setgroup', name: 'user_setgroup', methods: ['GET'])]
    public function setGroup(#[MapEntity(id: 'id')] DfxNfxUser $entity): Response
    {

    	$entity -> setRoles($this->mergeRoles($entity->getRoles(), ['ROLE_DFX_GROUP']));
    	$this->em->persist($entity);
    	$this->em->flush();

    	return new Response('ok');
    }

    /**
     * setGroup in a User entity.
     *
     * @param $id
     * @return Response
     */
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route(path: '/admin/user/{id}/unsetgroup', name: 'user_unsetgroup', methods: ['GET'])]
    public function unsetGroup(#[MapEntity(id: 'id')] DfxNfxUser $entity): Response
    {
    	$entity->setRoles(['ROLE_ADMIN']);
    	$this->em->persist($entity);
    	$this->em->flush();

    	return new Response('ok');
    }

    private function jsonArrayContainsPattern(string $value): string
    {
        return '%"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"%';
    }

    private function mergeRoles(array ...$roleSets): array
    {
        $roles = [];

        foreach ($roleSets as $roleSet) {
            foreach ($roleSet as $role) {
                $role = trim((string) $role);
                if ($role === '') {
                    continue;
                }

                $roles[$role] = $role;
            }
        }

        return array_values($roles);
    }
 }
