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
    private const CSS_BATCH_LIMIT = 10;

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
    public function writecss(Request $request): array{
        $cfgSkins =["#6c757d"=>"gra","#FF9900"=>"org","#F05513"=>"hro","#c63d4e"=>"dro","#996600"=>"brn","#007bff"=>"hbl","#4182C2"=>"dbl","#84b231"=>"hgr","#839636"=>"olv"];
        $offset = max(0, $request->query->getInt('offset', 0));
        $limit = self::CSS_BATCH_LIMIT;
        $msg = '<h3>Schreibe SCSS-Dateien mit Variablen für Farbe und Ecken</h3>';
        $scssPath = $this->getParameter('kernel.project_dir') . '/web/scss/';
        $cssPath = $this->getParameter('kernel.project_dir') . '/web/css/';
        $skel = file_get_contents($scssPath . 'datefix_skel.scss');
        $compiler = new ScssCompiler();
        $compiler->setImportPaths([$scssPath, $scssPath . 'scss_org/'] );
        $compiler->setOutputStyle(OutputStyle::COMPRESSED);
        $arVar = [
            '#dfx-color#',
            '#dfx-border-radius#',
            '#dfx-border-radius-large#',
            '#dfx-border-radius-small#',
        ];
        $tasks = [
            [
                'source' => $scssPath . 'scss_org/bootstrap.scss',
                'target' => $cssPath . 'bootstrap.css',
                'label' => 'bootstrap.css',
            ],
        ];

        foreach ($cfgSkins AS $farbe => $kuerzel) {
            $tasks[] = [
                'farbe' => $farbe,
                'kuerzel' => $kuerzel,
                'radius' => ['3px', '4px', '2px'],
                'scss' => $scssPath . 'datefix.' . $kuerzel . '-r.scss',
                'source' => $scssPath . 'datefix.' . $kuerzel . '-r.scss',
                'target' => $cssPath . 'datefix.' . $kuerzel . '-r.css',
                'label' => 'datefix.' . $kuerzel . '-r.css',
            ];
            $tasks[] = [
                'farbe' => $farbe,
                'kuerzel' => $kuerzel,
                'radius' => ['0px', '0px', '0px'],
                'scss' => $scssPath . 'datefix.' . $kuerzel . '-k.scss',
                'source' => $scssPath . 'datefix.' . $kuerzel . '-k.scss',
                'target' => $cssPath . 'datefix.' . $kuerzel . '-k.css',
                'label' => 'datefix.' . $kuerzel . '-k.css',
            ];
        }

        $total = count($tasks);
        $batch = array_slice($tasks, $offset, $limit);
        if ($batch === []) {
            $msg .= 'Keine CSS-Dateien in diesem Block.<br />';
        } else {
            $msg .= sprintf('Block %d bis %d von %d CSS-Dateien.<br />', $offset + 1, min($offset + count($batch), $total), $total);
        }

        foreach ($batch as $task) {
            try {
                if (isset($task['scss'])) {
                    $skelContent = str_replace($arVar, array_merge([$task['farbe']], $task['radius']), $skel);
                    file_put_contents($task['scss'], $skelContent);
                    $msg .= 'Farbe'.$task['farbe'].' / Datei '.$task['scss'].' geschrieben<br />';
                }

                $css = $compiler->compileFile($task['source'])->getCss();
                file_put_contents($task['target'], $css);
                $msg .= 'Datei ' . $task['label'] . ' geschrieben<br />';
            } catch (SassException $e) {
                // Fehler-Handling
                $msg .= 'Fehler beim Schreiben der CSS-Datei ' . $task['label'] . '. SCSS compile error: ' . $e->getMessage();
                error_log('Fehler beim Schreiben der CSS-Datei ' . $task['label'] . '. SCSS compile error: ' . $e->getMessage());
            }
        }

        $nextOffset = $offset + count($batch);
        if ($nextOffset < $total) {
            $msg .= '<p><a href="' . $this->generateUrl('superadmin_writecss', ['offset' => $nextOffset]) . '">Nächsten Block schreiben</a></p>';
        } else {
            $msg .= '<p>Alle CSS-Dateien wurden geschrieben.</p>';
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
   public function ownCss(Request $request): array{
   	    $msg = '<h3>Schreibe CSS-Dateien für Accounts mit eigenen CSS-Dateien </h3>';
        $offset = max(0, $request->query->getInt('offset', 0));
        $limit = self::CSS_BATCH_LIMIT;
   		$repository = $this->em->getRepository(DfxKonf::class);
   		$query = $repository->createQueryBuilder('k')
   		->select(['k']);
   	    $query ->where('k.dfxFarbeEigen IS NOT NULL OR k.dfxFarbeRaster IS NOT NULL OR k.dfxFarbeRasterEigen IS NOT NULL OR k.dfxFontSize IS NOT NULL OR k.dfxFontColor IS NOT NULL');
        $total = (int) (clone $query)
            ->select('COUNT(k.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $query
            ->orderBy('k.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $entities = $query->getQuery()->getResult();

        if ($entities === []) {
            $msg .= 'Keine Accounts in diesem Block.<br />';
        } else {
            $msg .= sprintf('Block %d bis %d von %d Accounts.<br />', $offset + 1, min($offset + count($entities), $total), $total);
        }
   		foreach ($entities AS $entity){
   	    	$msg .= $this->ownCssBuilderService->writeForKonf($entity);
   		}

        $nextOffset = $offset + count($entities);
        if ($nextOffset < $total) {
            $msg .= '<p><a href="' . $this->generateUrl('superadmin_writeowncss', ['offset' => $nextOffset]) . '">Nächsten Block schreiben</a></p>';
        } else {
            $msg .= '<p>Alle Account-CSS-Dateien wurden geschrieben.</p>';
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
