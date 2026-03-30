<?php

namespace App\Controller;

use App\Service\FileUploadService;
use App\Service\Styling\OwnCssBuilderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;


use App\Entity\DfxKonf;
use App\Form\DfxKonfType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\CurrentContext;

/**
 * DfxKonf controller.
 *
 */
#[IsGranted('ROLE_ADMIN')]
class DfxKonfController extends AbstractController
{


    public function __construct(private readonly FileUploadService $fileUploadService, private readonly OwnCssBuilderService $ownCssBuilderService, private readonly EntityManagerInterface $em, private readonly CurrentContext $currentContext)
    {
        }

	
	
    /**
     * Edit one DfxKonf entity.
     *
     */
    #[Template("DfxKonf/edit.html.twig")]
    #[Route(path: '/admin/dfxkonf/edit/{kid}', name: 'admin_dfxkonf', defaults: ['kid' => 1], methods: ['GET'])]
    public function edit($kid): array
    {
        if($this->isGranted('ROLE_SUPER_ADMIN')){
            // Nur Superadmin darf auf anderen Kalender zugreifen
            $entity = $this->em->getRepository(DfxKonf::class)->find($kid);
        }else if($this->isGranted('ROLE_ADMIN')){
            // Admin darf auf eigenen Kalender zugreifen
            $kid = $this->currentContext->getUser() -> getDatefix()->getId();
            $entity = $this->em->getRepository(DfxKonf::class)->find($kid);
        }else {
            throw $this->createNotFoundException('Illegaler Zugriff Account-Konfiguration');
        }
        
    	if ($entity === null) {
        	throw $this->createNotFoundException('Keinen Datefix-Account gefunden.');
        }
        
        $editForm = $this->createEditForm($entity);
        return [
        		'entity'      => $entity,
        		'edit_form'   => $editForm->createView(),
        		
        ];
    }
    
    /**
     * Creates a form to edit a DfxKonf entity.
     *
     * @param DfxKonf $entity The entity
     *
     * @return FormInterface The form
     */
    private function createEditForm(DfxKonf $entity): FormInterface
    {
    	$form = $this->createForm( DfxKonfType::class, $entity, [
    			'action' => $this->generateUrl('dfxkonf_update', ['kid' => $entity->getId()]),
    			'method' => 'POST',
    			'em' => $this->em,
    	]);
    
    	 $form->add('submit', SubmitType::class, ['label' => 'Änderungen speichern', 'attr' => ['class' =>'btn btn-primary']])
        ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' =>'btn btn-primary']]);
    
    	return $form;
    }

    /**
     * Edits an existing DfxKonf entity.
     *
    */
    #[Template("DfxKonf/edit.html.twig")]
    #[Route(path: '/admin/dfxkonf/update/{kid}', name: 'dfxkonf_update', methods: ['POST'])]
    public function update(Request $request, int $kid): RedirectResponse|array
    {
    	$entity = $this->em->getRepository(DfxKonf::class)->find($kid);
    
    	if ($entity === null) {
    		throw $this->createNotFoundException('Unable to find DfxKonf entity.');
    	}
    
    	
    	$editForm = $this->createEditForm($entity);
    	$editForm->handleRequest($request);
    
    	if ($editForm->isSubmitted() && $editForm->isValid()) {
            $group = $this->loadFirstToGroupKonf($entity);

            if($group instanceof DfxKonf && $entity->getInheritGroup() == 1){
                $entity->setRubriken($group->getRubriken());
                
            }else if($entity->getInheritMeta() == 1 ){
                $meta = $this->em->getRepository(DfxKonf::class)->find(1);
                $entity->setRubriken($meta->getRubriken());
            }else{
                $entity->setRubriken(array_diff($entity->getRubriken(), array_filter($entity->getRubriken(), 'is_null')));
            }
            
            if($group instanceof DfxKonf && $entity->getInheritZielgruppenGroup() == 1){
                $entity->setZielgruppen($group->getZielgruppen());
            }else if($entity->getInheritZielgruppenMeta() == 1 ){
                $meta = $this->em->getRepository(DfxKonf::class)->find(1);
                $entity->setZielgruppen($meta->getZielgruppen());
            }else{
                $entity->setZielgruppen(array_diff($entity->getZielgruppen(), array_filter($entity->getZielgruppen(), 'is_null')));
            }
            
            if($entity->getDfxCss()!=null || $entity->getDfxFarbeEigen()!=null || $entity->getDfxFarbeRaster()!=null || $entity->getDfxFarbeRasterEigen()!=null || $entity->getDfxFontType()!=null || $entity->getDfxFontSize()!=null || $entity->getDfxFontColor()!=null ){
                $cssmsg = $this->ownCssBuilderService->writeForKonf($entity);
            if($entity->getDfxFarbeEigen()!=null)
                $entity->setDfxFarbe(null);
            
            if($entity->getDfxFarbeRasterEigen()!=null)
                $entity->setDfxFarbeRaster(null);
            }elseif ($entity->getDfxFarbe()==null){
                $entity->setDfxFarbe('#818181');
            }
            
            $file = $request->files->get('datefix_backendbundle_dfxkonf')['imageFile'];
            if($file !== null) {
                $entity->setImgLogo($this->fileUploadService->upload($file, $kid));
            }
            
            $file2 = $request->files->get('datefix_backendbundle_dfxkonf')['imageFile2'];
            if($file2 !== null) {
                $entity->setImgBanner($this->fileUploadService->upload($file2, $kid));
            }


            $this->em->flush();
            return $this->redirectToRoute('dfxkonf_show', ['kid' =>$kid]);
    	}
    
    	return [
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
    			
    	];
    }

    /**
     * Finds and displays a DfxKonf entity.
     *
     */
    #[Template("DfxKonf/show.html.twig")]
    #[Route(path: '/admin/dfxkonf/show/{kid}', name: 'dfxkonf_show', methods: ['GET'])]
    public function show( int $kid): array
    {
        $entity = $this->em->getRepository(DfxKonf::class)->find($kid);
        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find DfxKonf entity.'.$kid);
        }
        
        return [
            'entity'=> $entity,
        ];
    }

    private function loadFirstToGroupKonf(DfxKonf $entity): ?DfxKonf
    {
        $groupIds = $entity->getToGroup();
        if ($groupIds === []) {
            return null;
        }

        $groupId = (int) $groupIds[0];
        if ($groupId < 1) {
            return null;
        }

        $group = $this->em->getRepository(DfxKonf::class)->find($groupId);

        return $group instanceof DfxKonf ? $group : null;
    }

}
