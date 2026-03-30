<?php

namespace App\Controller;

use App\Entity\DfxKonf;
use App\Service\Support\ConsoleCommandService;
use App\Service\Analytics\NightlyMaintenanceService;
use App\Service\Styling\OwnCssBuilderService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use ScssPhp\ScssPhp\Compiler as ScssCompiler;
use ScssPhp\ScssPhp\OutputStyle;
use ScssPhp\ScssPhp\Exception\SassException;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * DfxSuperAdmin controller.
 */
#[IsGranted('ROLE_SUPER_ADMIN')]
class SuperAdminController extends AbstractController
{

    public function __construct(private readonly ConsoleCommandService $consoleCommandService, private readonly OwnCssBuilderService $ownCssBuilderService, private readonly NightlyMaintenanceService $nightlyMaintenanceService, private readonly EntityManagerInterface $em)
    {

    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    #[Template("SuperAdmin/index.html.twig")]
    #[Route(path: '/admin/superadmin/', name: 'superadmin', methods: ['GET', 'POST'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
    	$repository = $this->em->getRepository(DfxKonf::class);
    	$query = $repository->createQueryBuilder('d')
    	->select(['d']);
    	$arParams=[];
        $formSuche = $this->createFilterForm();
        $formSuche->handleRequest($request);
        if ($formSuche->isSubmitted() && $formSuche->isValid()) {
            $suche = $formSuche->getData();
            if(isset($suche['uid'])){
                $query ->andWhere('d.user = :uid');
                $arParams['uid'] = $suche['uid'];
            }

            if(isset($suche['kid'])){
                $query ->andWhere('d.id = :kid');
                $arParams['kid'] = $suche['kid'].'%';
            }

            if(isset($suche['email'])){
                $query
                    ->leftJoin('d.user','u')
                    ->andWhere('u.email LIKE :email');
                $arParams['email'] = '%'.$suche['email'].'%';
            }
        }

        $query->setParameters(new ArrayCollection($arParams))
            ->orderBy('d.id', 'ASC')
            ->getQuery();
        $pagination = $paginator->paginate(
    			$query,
    			$request->query->getInt('dfxp', 1),20
    	);
    	return $this->render('SuperAdmin/index.html.twig', ['pagination' => $pagination, 'form_suche' => $formSuche->createView()]);
    }

    private function createFilterForm(): FormInterface
    {
        $filter = [];
        return $this->createFormBuilder($filter, ['method' => 'POST', 'action' => $this->generateUrl('superadmin')])
            ->add('kid', TextType::class, ['label' => 'Kalender Nr', 'required' => false, 'attr' => []])
            ->add('uid', TextType::class, ['label' => 'ID Adminuser', 'required' => false, 'attr' => []])
            ->add('email', TextType::class, ['label' => 'Email Adminuser', 'required' => false, 'attr' => []])
            ->add('submit', SubmitType::class, ['label' => 'Suchen', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();
    }


    /**
     * Deletes a DfxKonf entity.
     *
     * @param DfxKonf $entity
     * @return RedirectResponse
     */
    #[Route(path: '/admin/superadmin/delete/{kid}', name: 'superadmin_dfxdelete', methods: ['GET'])]
    public function delete(#[MapEntity(id: 'kid')] DfxKonf $entity): RedirectResponse
    {
        if ($entity->getId() == 1) {
            throw $this->createNotFoundException('Der Meta-Kalender darf nicht gelöscht werden');
        }
        $this->em->remove($entity);
        $this->em->flush();
        return $this->redirectToRoute('superadmin');
    }

    /**
     * Set DfxMeta.
     *
     * @param DfxKonf $entity
     * @return RedirectResponse
     */
    #[Route(path: '/admin/superadmin/meta/{kid}/set', name: 'dfxmeta_set', methods: ['GET'])]
    public function setMeta(#[MapEntity(id: 'kid')] DfxKonf $entity): RedirectResponse
    {
        $user = $entity->getUser();
        if ($user->getId() != 100) {
            $user->setRoles($this->mergeRoles($user->getRoles(), ['ROLE_SUPER_ADMIN']));
        }
        $entity->setIsMeta(true);
        $this->em->persist($entity);
        $this->em->persist($user);
        $this->em->flush();
        return $this->redirectToRoute('superadmin');
    }

    /**
     * Unset DfxMeta.
     *
     * @param DfxKonf $entity
     * @return RedirectResponse
     */
    #[Route(path: '/admin/superadmin/meta/{kid}/unset', name: 'dfxmeta_unset', methods: ['GET'])]
    public function unsetMeta(#[MapEntity(id: 'kid')] DfxKonf $entity): RedirectResponse
    {
        $user = $entity->getUser();
        if ($user->getId() != 100) {
            $user->setRoles(['ROLE_ADMIN']);
        }
        $entity->setIsMeta(false);
        $this->em->persist($entity);
        $this->em->persist($user);
        $this->em->flush();
        return $this->redirectToRoute('superadmin');
    }

    /**
     * Set DfxGroup.
     *
     * @param DfxKonf $entity
     * @return RedirectResponse
     */
    #[Route(path: '/admin/superadmin/group/{kid}/set', name: 'dfxgroup_set', methods: ['GET'])]
    public function setGroup(#[MapEntity(id: 'kid')] DfxKonf $entity): RedirectResponse
    {
        $user = $entity->getUser();
        if ($user->getId() != 100) {
            $user->setRoles($this->mergeRoles($user->getRoles(), ['ROLE_DFX_GROUP']));
        }
        $entity->setIsGroup(true);
        $this->em->persist($entity);
        $this->em->persist($user);
        $this->em->flush();
        return $this->redirectToRoute('superadmin');
    }

    /**
     * Unset DfxGroup.
     *
     * @param DfxKonf $entity
     * @return RedirectResponse
     */
    #[Route(path: '/admin/superadmin/group/{kid}/unset', name: 'dfxgroup_unset', methods: ['GET'])]
    public function unsetGroup(#[MapEntity(id: 'kid')] DfxKonf $entity): RedirectResponse
    {
        $user = $entity->getUser();
        if ($user->getId() != 100) {
            $user->setRoles(['ROLE_ADMIN']);
        }
        $entity->setIsGroup(false);
        $this->em->persist($entity);
        $this->em->persist($user);
        $this->em->flush();
        return $this->redirectToRoute('superadmin');
    }




    /**
     *
     * @throws Exception|SassException
     */
    #[Template("SuperAdmin/generatecss.html.twig")]
    #[Route(path: '/admin/superadmin/writecss', name: 'superadmin_writecss', methods: ['GET'])]
    public function writecss(): array{
        $cfgSkins =["#6c757d"=>"gra","#FF9900"=>"org","#F05513"=>"hro","#c63d4e"=>"dro","#996600"=>"brn","#007bff"=>"hbl","#4182C2"=>"dbl","#84b231"=>"hgr","#839636"=>"olv"];
        $msg = '<h3>Schreibe SCSS-Dateien mit Variablen für Farbe und Ecken</h3>';
        $scssPath = $this->getParameter('kernel.project_dir') . '/web/scss/';
        $cssPath = $this->getParameter('kernel.project_dir') . '/web/css/';
        $skel = file_get_contents($scssPath . 'datefix_skel.scss');
        $compiler = new ScssCompiler();
        $compiler->setImportPaths([$scssPath, $scssPath . 'scss_org/'] );
        $compiler->setOutputStyle(OutputStyle::COMPRESSED);
        $css = $compiler->compileFile($scssPath . 'scss_org/bootstrap.scss')->getCss();
        file_put_contents($cssPath . 'bootstrap.css', $css);
        foreach ($cfgSkins AS $farbe => $kuerzel) {
            $arVal = [$farbe, '3px', '4px', '2px'];
            $arVar = [
                '#dfx-color#',
                '#dfx-border-radius#',
                '#dfx-border-radius-large#',
                '#dfx-border-radius-small#',
            ];
            $skelR = str_replace($arVar, $arVal, $skel);
            file_put_contents($scssPath . 'datefix.' . $kuerzel . '-r.scss', $skelR);
            $msg .= 'Farbe'.$farbe.' / Datei '.$scssPath . 'datefix.'.$kuerzel.'-r.scss geschrieben<br />';

            $arVal = [$farbe,'0px','0px','0px'];
            $skelK = str_replace($arVar, $arVal, $skel);
            file_put_contents($scssPath . 'datefix.' . $kuerzel . '-k.scss', $skelK);
            $msg .= 'Farbe'.$farbe.' / Datei '.$scssPath . 'datefix.'.$kuerzel.'-k.scss geschrieben<br />';


        }

        foreach ($cfgSkins AS $kuerzel){
            try {
                $cssR = $compiler->compileFile($scssPath . 'datefix.' . $kuerzel . '-r.scss')->getCss();
                $cssK = $compiler->compileFile($scssPath . 'datefix.' . $kuerzel . '-k.scss')->getCss();
                file_put_contents($cssPath . 'datefix.' . $kuerzel . '-r.css', $cssR);
                $msg .= 'Datei bootstrap.' . $kuerzel . '-r.css geschrieben<br />';
                file_put_contents($cssPath . 'datefix.' . $kuerzel . '-k.css', $cssK);
                $msg .= 'Datei bootstrap.' . $kuerzel . '-k.css geschrieben<br />';
            } catch (SassException $e) {
                // Fehler-Handling
                $msg .= 'Fehler beim Schreiben der CSS-Datei für Farbschema: ' . $kuerzel.'. SCSS compile error: ' . $e->getMessage();
                error_log('Fehler beim Schreiben der CSS-Datei für Farbschema: ' . $kuerzel.'. SCSS compile error: ' . $e->getMessage());
            }
        }
        return [
            'msg' => $msg
        ];

            /*



            $msg .= 'Farbe'.$farbe.' / Datei '. $this->getParameter('kernel.project_dir') . '/web/scss/datefix.'.$kuerzel.'-r.scss geschrieben<br />';

            $skel= implode("",(@file($this->getParameter('kernel.project_dir') . "/web/scss/datefix_skel.scss")));
            $arVal = [$farbe,'1rem','0px','0px','0px'];
            $scss=fopen($this->getParameter('kernel.project_dir') . '/web/scss/datefix.'.$kuerzel.'-k.scss','w');
            $skel = str_replace($arVar,$arVal,$skel);
            fwrite($scss,$skel);
            fclose($scss);


            */

   }


   #[Template("SuperAdmin/generateowncss.html.twig")]
   #[Route(path: '/admin/superadmin/writeowncss', name: 'superadmin_writeowncss')]
   public function ownCss(): array{
   	    $msg = '<h3>Schreibe CSS-Dateien für Accounts mit eigenen CSS-Dateien </h3>';
   		$repository = $this->em->getRepository(DfxKonf::class);
   		$query = $repository->createQueryBuilder('k')
   		->select(['k']);
   	    $query ->where('k.dfxFarbeEigen IS NOT NULL OR k.dfxFarbeRaster IS NOT NULL OR k.dfxFarbeRasterEigen IS NOT NULL OR k.dfxFontSize IS NOT NULL OR k.dfxFontColor IS NOT NULL');
   	    $entities = $query->getQuery()->getResult();
   		foreach ($entities AS $entity){
   	    	$msg .= $this->ownCssBuilderService->writeForKonf($entity);
   		}

   		return [
   				'msg' => $msg
   		];
   }


   #[Template("SuperAdmin/nightrun.html.twig")]
    #[Route(path: '/admin/superadmin/nightrun', name: 'superadmin_nightrun', methods: ['GET'])]
    public function nightrun(): array{
   		$msg = $this->nightlyMaintenanceService->nightrun(TRUE);
   		return [
   			'msg' => $msg
   		];
    }


     #[Template("SuperAdmin/update_daba.html.twig")]
     #[Route(path: "/dabaupdate", name: "superadmin_dabaupdate", methods: ["GET"])]
    public function dabaUpdateAction(): array
     {
        $msg_entities = $this->consoleCommandService->run(array('command' => 'make:entity', '--regenerate' =>'App'));
        $msg_update = $this->consoleCommandService->run(array('command' => 'make:migration',  '--no-interaction' => true));
        $msg_update .= $this->consoleCommandService->run(array('command' => 'doctrine:migrations:migrate',  '--no-interaction' => true));
        return array(
            'msg_entities' => nl2br($msg_entities), 'msg_update' => nl2br($msg_update)
        );
    }


    #[Template("SuperAdmin/clearcache.html.twig")]
    #[Route(path: '/admin/superadmin/clearcache', name: 'superadmin_clearcache')]
    public function clearcache(): array
    {
        $msg = $this->consoleCommandService->run(['command' => 'cache:clear', '--env' => 'prod']);

    	return [
    		'msg' => nl2br($msg)
    	];
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
