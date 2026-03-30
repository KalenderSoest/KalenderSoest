<?php

namespace App\Controller;

use App\Service\Install\InstallationStepRunner;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;


/**
 * DfxSuperAdmin controller.
 */
class UpdateController extends AbstractController
{

    public function __construct(
        private readonly InstallationStepRunner $installationStepRunner,
    )
    {

    }

	#[Template("DfxFrontend/update.html.twig")]
    #[Route(path: '/update/', name: 'update', methods: ['GET'])]
    public function index(): array
	{
        return [];
	}
	
	
	
    #[Template("SuperAdmin/update_daba.html.twig")]
    #[Route(path: '/update/daba', name: 'update_daba', methods: ['GET'])]
    public function daba(): array
    {
        return $this->installationStepRunner->run('update_schema')['data'];
    }
    
    #[Template("SuperAdmin/clearcache.html.twig")]
    #[Route(path: '/update/clearcache', name: 'update_clearcache', methods: ['GET'])]
    public function clearcache(): array
    {
    	return $this->installationStepRunner->run('clear_prod_cache')['data'];
    }

    #[Template("SuperAdmin/update_daba.html.twig")]
    #[Route(path: '/update/termine-media', name: 'update_termine_media', methods: ['GET'])]
    public function termineMedia(): array
    {
        return $this->installationStepRunner->run('legacy_termin_media')['data'];
    }

    #[Template("SuperAdmin/update_daba.html.twig")]
    #[Route(path: '/update/togroup', name: 'update_togroup', methods: ['GET'])]
    public function toGroup(): array
    {
        return $this->installationStepRunner->run('migrate_konf_to_group')['data'];
    }

    #[Template("SuperAdmin/update_daba.html.twig")]
    #[Route(path: '/update/password-audit', name: 'update_password_audit', methods: ['GET'])]
    public function passwordAudit(): array
    {
        return $this->installationStepRunner->run('audit_legacy_passwords')['data'];
    }
}
