<?php

namespace App\Controller;

use App\Security\CurrentContext;

use App\Entity\DfxNfxCounter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Attribute\Route;

// use Assetic\AssetWriter;
/**
 * Admin controller.
 */
class AdminController extends AbstractController
{


    public function __construct( private readonly EntityManagerInterface $em, private readonly CurrentContext $currentContext)
    {
        }

    
    #[Route(path: '/admin/', name: 'admin', methods: ['GET'])]
    #[Template('Admin/index.html.twig')]
    public function index(): array
    {

    	$user = $this->currentContext->getUser();
    	$kalender = $user->getDatefix();
    	$counter = $this->em->getRepository(DfxNfxCounter::class)->findOneBy(['datefix' => $kalender]);
    	$kunde = $user->getKunde();
    	return [
    			'user'      => $user,
    			'counter' => $counter,
    			'kunde' => $kunde,
    			'kalender' => $kalender,
    	];
    }

    #[Template("Admin/dfxeinbau.html.twig")]
    #[Route(path: '/admin/dfxeinbau', name: 'dfx_einbau', methods: ['GET'])]
    public function einbau(): array
    {
    	$konf = $this->currentContext->getUser()->getDatefix();
    	return ['datefix' => $konf ];
    }

    #[Template("Admin/dfxhilfe.html.twig")]
    #[Route(path: '/admin/dfxhilfe', name: 'dfx_hilfe', methods: ['GET'])]
    public function hilfe(): array
    {
    	$konf = $this->currentContext->getUser()->getDatefix();
    	return ['datefix' => $konf ];
    }
}
