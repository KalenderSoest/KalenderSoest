<?php
namespace App\Controller;

use App\Entity\DfxNfxKunden;
use App\Entity\DfxNfxUser;
use App\Service\Install\InstallFormFactory;
use App\Service\Install\InstallerBootstrapService;
use App\Service\Install\InstallationStepRunner;
use App\Service\Install\InstallationStateService;
use App\Service\Install\InstallationPlanService;
use App\Service\Install\InstallerProvisioningService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


/**
 * DfxInstall controller.
 */
class DfxInstallController extends AbstractController
{
    public function __construct(
        private readonly InstallerBootstrapService $installerBootstrapService,
        private readonly InstallationStateService $installationStateService,
        private readonly InstallationPlanService $installationPlanService,
        private readonly InstallFormFactory $installFormFactory,
        private readonly InstallerProvisioningService $installerProvisioningService,
        private readonly InstallationStepRunner $installationStepRunner,
    ) {
    }

    #[Template('DfxFrontend/install_status.html.twig')]
    #[Route(path: '/installer/status', name: 'dfx_install_status', methods: ['GET'])]
    public function status(): array
    {
        $state = $this->installationStateService->detect();
        $plan = $this->installationPlanService->build($state);
        $checks = $state['database']['checks'] ?? [];
        $hasRequiredMigrationActions = (bool) ($state['database']['schema_update_pending'] ?? false)
            || (bool) ($checks['array_json']['needed'] ?? false)
            || (bool) ($checks['to_group']['needed'] ?? false)
            || (bool) ($checks['legacy_media']['needed'] ?? false)
            || (($state['migrations']['latest_pending'] ?? null) !== null);

        return [
            'state' => $state,
            'plan' => $plan,
            'has_required_migration_actions' => $hasRequiredMigrationActions,
        ];
    }

    /**
     * @return response
     */
    #[Template("DfxFrontend/install3.html.twig")]
    #[Route(path: '/installer/step3', name: 'dfx_install3', methods: ['GET'])]
    public function install3(): response
    {
        $result = $this->installationStepRunner->run('schema_diff');
        return $this->render($result['template'], $result['data']);

    }

    /**
     * @return Response
     */
    #[Template("DfxFrontend/install3.html.twig")]
    #[Route(path: '/installer/step3_2', name: 'dfx_install3_2', methods: ['GET'])]
    public function install3_2_(): Response
    {
        $result = $this->installationStepRunner->run('schema_migrate');
        return $this->render($result['template'], $result['data']);
    }

    /**
     * @return array|RedirectResponse
     */
    #[Template("DfxFrontend/install4.html.twig")]
    #[Route(path: '/installer/step4', name: 'dfx_install4', methods: ['GET'])]
    public function install4(): RedirectResponse|array
    {
    	$result = $this->installerBootstrapService->ensureAnonymousWebUser();

    	if($result['success']){
    		return $this->redirectToRoute('dfx_install5');
    	}else{
    		$msg = '<div class="alert alert-danger">' . $result['message'] . ' (ID ' . ($result['user_id'] ?? 'unbekannt') . ')</div>';
    		return ['msg' => nl2br($msg)];
    	}
    	
    }
    
    /**
     * Displays a form to create a new Kunden entity.
     *
     */
    #[Template("DfxFrontend/install.html.twig")]
    #[Route(path: '/installer/step5', name: 'dfx_install5', methods: ['GET'])]
    public function install5(): Response
    {
        $form = $this->installFormFactory->createKundenForm(new DfxNfxKunden());

        return $this->render('DfxFrontend/install.html.twig', ['form' => $form]);
    
    }

    /**
     * Creates a new Kunden entity.
     *

     * @param Request $request
     * @return array|Response
     * @throws Exception
     */
    #[Template("DfxFrontend/install.html.twig")]
    #[Route(path: '/installer/step6', name: 'inst_kunden_create', methods: ['POST'])]
    public function createKunden(Request $request): Response|array
    {
    	$newKunde = new DfxNfxKunden();
    	$form = $this->installFormFactory->createKundenForm($newKunde);
    	$form->handleRequest($request);
    	if ($form->isSubmitted() && $form->isValid()) {
            $this->installerProvisioningService->createKunde($newKunde);
            $user = new DfxNfxUser();
            $accountForm = $this->installFormFactory->createAccountForm($user);

            return $this->render('DfxFrontend/install5.html.twig', ['form' => $accountForm->createView()]);

    	}

    	return [
    			'entity' => $newKunde,
                'error' => (string) $form->getErrors(true),
    			'form'   => $form->createView(),
    	];
    
    	 
    }


    /**
     * Displays a form to create a new Kunden entity.
     *
     * @param DfxNfxUser $user
     * @return FormInterface
     */
    #[Route(path: '/installer/', name: 'inst_account_new', methods: ['GET'])]
    public function accountNew(): Response
    {
        return $this->render('DfxFrontend/install5.html.twig', [
            'form' => $this->installFormFactory->createAccountForm(new DfxNfxUser())->createView(),
        ]);
    }

    /**
     * Creates a new User entity.
     *

     * @param Request $request
     * @return array|Response
     * @throws Exception
     */
    #[Template("DfxFrontend/install5.html.twig")]
    #[Route(path: '/installer/account', name: 'inst_account_create', methods: ['POST'])]
    public function createAccount(Request $request): Response|array
    {
    	$user = new DfxNfxUser();
    	$form = $this->installFormFactory->createAccountForm($user);
    	$form->handleRequest($request);
    	 
    	if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->installerProvisioningService->createAccount($user, $form);

            return $this->render('DfxFrontend/install6.html.twig', ['user' => $user]);
    	}
    
    	return [
    			'entity' => $user,
    			'form'   => $form->createView(),
    	];
    
    
    }


    /**
     * Creates a form to create a Kunden entity.
     *
     * @param DfxNfxKunden $entity The entity
     *
     * @return FormInterface The form
     */
    /**
     * Imports data from further Version.
     *
     */
    #[Template("DfxFrontend/install10.html.twig")]
    #[Route(path: '/installer/import', name: 'install_import', methods: ['GET'])]
    public function import(): array
    {
    	$form = $this->installFormFactory->createImportForm();
    	return [
            'form'   => $form->createView(),
        ];
    }
       

    #[Template("SuperAdmin/update_daba.html.twig")]
    #[Route(path: '/installer/daba', name: 'install_update_daba', methods: ['GET'])]
    public function daba(): RedirectResponse
    {
        return $this->redirectToRoute('dfx_install_run_step', ['step' => 'generate_update_migration']);
    }
    
    #[Template("SuperAdmin/clearcache.html.twig")]
    #[Route(path: '/installer/clearcache', name: 'install_update_clearcache', methods: ['GET'])]
    public function clearcache(): array
    {
    	return $this->installationStepRunner->run('clear_prod_cache')['data'];
    }

    #[Route(path: '/installer/run/{step}', name: 'dfx_install_run_step', methods: ['GET'])]
    public function runStep(string $step): Response
    {
        $result = $this->installationStepRunner->run($step);
        if (isset($result['flash_success'])) {
            $this->addFlash('success', (string) $result['flash_success']);
        }
        if (isset($result['flash_warning'])) {
            $this->addFlash('warning', (string) $result['flash_warning']);
        }
        if (isset($result['redirect_route'])) {
            return $this->redirectToRoute((string) $result['redirect_route'], $result['redirect_params'] ?? []);
        }
        return $this->render($result['template'], $result['data']);
    }
}
