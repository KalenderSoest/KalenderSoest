<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DfxBox;
use App\Form\DfxBoxType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\CurrentContext;

/**
 * DfxBox controller.
 */
#[IsGranted('ROLE_ADMIN')]
class DfxBoxController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $em, private readonly CurrentContext $currentContext)
    {
        }

    /**
     * Edit DfxBox Configuration.
     *
     */
    #[Template("DfxKonf/terminbox.html.twig")]
    #[Route(path: '/admin/box/', name: 'box_edit', methods: ['GET'])]
    public function edit(): array
    {
    	$kid = $this->currentContext->getUser()->getDatefix()->getId();
    	$entity = $this->em->getRepository(DfxBox::class)->findOneBy(['datefix' => $kid]);

    	if ($entity === null) {
    		throw $this->createNotFoundException('Unable to find DfxBox entity.');
    	}

    	$editForm = $this->createEditForm($entity);

    	return [
    			'entity'      => $entity,
    			'form'   => $editForm->createView(),
    	];
    }

    /**
    * Creates a form to edit a DfxBox entity.
    *
    * @param DfxBox $entity The entity
    *
    * @return FormInterface The form
    */
    private function createEditForm(DfxBox $entity): FormInterface
    {
        $form = $this->createForm( DfxBoxType::class, $entity, [
            'action' => $this->generateUrl('box_update', ['id' => $entity->getId()]),
            'method' => 'POST',
        ]);

         $form->add('submit', SubmitType::class, ['label' => 'Änderungen speichern', 'attr' => ['class' =>'btn btn-primary']])
        ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' =>'btn btn-primary']]);

        return $form;
    }

    /**
     * Edits an existing DfxBox entity.
     *
     * @param Request $request     * @return array|RedirectResponse
     */
    #[Template("DfxKonf/terminbox.html.twig")]
    #[Route(path: '/admin/box/{id}', name: 'box_update', methods: ['POST'])]
    public function update(Request $request, #[MapEntity(id: 'id')] DfxBox $entity): RedirectResponse|array
    {
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('box_edit');
        }
        if ($editForm->isSubmitted()) {
            $this->addFlash('error', 'Bitte korrigieren Sie die markierten Felder.');
        }

        return [
            'entity'      => $entity,
            'form'   => $editForm->createView(),
        ];
    }
    
}

