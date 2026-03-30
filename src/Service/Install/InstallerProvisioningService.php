<?php

namespace App\Service\Install;

use App\Entity\DfxBox;
use App\Entity\DfxKonf;
use App\Entity\DfxNfxCounter;
use App\Entity\DfxNfxKunden;
use App\Entity\DfxNfxUser;
use App\Service\Messaging\MailDeliveryService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InstallerProvisioningService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailDeliveryService $mailDeliveryService,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire('%datefix_url%')]
        private readonly string $datefixUrl,
        #[Autowire('%dfx_mail%')]
        private readonly string $dfxMail,
    ) {
    }

    public function createKunde(DfxNfxKunden $kunde): void
    {
        $this->initializeKunde($kunde);
        $kunde->setId(1);

        $webUser = $this->em->getRepository(DfxNfxUser::class)->find(99);
        if ($webUser !== null) {
            $webUser->setKunde($kunde);
            $this->em->persist($webUser);
        }

        $this->em->persist($kunde);
        $metadata = $this->em->getClassMetadata($kunde::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $this->em->flush();
    }

    public function createRegisteredAccount(DfxNfxKunden $kunde, string $plainPassword): DfxNfxUser
    {
        $this->initializeKunde($kunde);
        $this->em->persist($kunde);

        $user = new DfxNfxUser();
        return $this->provisionAccount(
            $kunde,
            $user,
            $plainPassword,
            ['ROLE_ADMIN'],
            30,
            false,
            false,
            true,
            false
        );
    }

    public function createAccount(DfxNfxUser $user, FormInterface $form): DfxNfxUser
    {
        $kunde = $this->em->getRepository(DfxNfxKunden::class)->find(1);
        if ($kunde === null) {
            throw new \RuntimeException('Keine Kundendaten gefunden. Anmeldung abgebrochen');
        }

        $user = $this->provisionAccount(
            $kunde,
            $user,
            (string) $form->get('password')->getData(),
            ['ROLE_SUPER_ADMIN'],
            366,
            true,
            true,
            false,
            true
        );

        return $user;
    }

    private function provisionAccount(
        DfxNfxKunden $kunde,
        DfxNfxUser $user,
        string $plainPassword,
        array $roles,
        int $archivTage,
        bool $isMeta,
        bool $pubMetaAll,
        bool $includeApiCounters,
        bool $fixedIds,
    ): DfxNfxUser {
        $counter = new DfxNfxCounter();
        $konf = $this->createKonf($user, $kunde, $archivTage, $isMeta, $pubMetaAll);
        $box = $this->createBox($konf);

        if ($fixedIds) {
            $user->setId(100);
        }

        $user->setRoles($roles);
        $user->setKunde($kunde);
        $user->setDatefix($konf);
        $user->setUsername($kunde->getEmail() . '_' . substr((string) time(), -5));
        $user->setEmail($kunde->getEmail());
        $user->setNameLang($kunde->getVorname() . ' ' . $kunde->getName());
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $user->setInit(md5($user->getEmail() . substr((string) time(), -5)));

        $now = new DateTime(date('Y-m-d H:i:s'));
        $counter->setDatefix($konf);
        $counter->setKunde($kunde);
        $counter->setUser($user);
        $counter->setDfxLastLog($now);
        $counter->setDfxDatumStart($now);
        $counter->setDfxStatus(1);
        $counter->setDfxDay(0);
        $counter->setDfxSum(0);
        if ($includeApiCounters) {
            $counter->setDfxApiDay(0);
            $counter->setDfxApiSum(0);
        }

        $this->em->persist($konf);
        $this->em->persist($counter);
        $this->em->persist($box);
        $this->em->persist($user);

        if ($fixedIds) {
            $this->em->getClassMetadata($user::class)->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
            $this->em->getClassMetadata($konf::class)->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        }

        $this->em->flush();

        $user->setUsername((string) $user->getId());
        $konf->setFrontendUrl($this->datefixUrl . '/kalender/' . $konf->getId());
        $this->em->persist($user);
        $this->em->persist($konf);
        $this->em->flush();

        $this->ensureFrontendDirectories((int) $konf->getId());
        $this->sendAccountMails($kunde, $konf);

        return $user;
    }

    private function createKonf(DfxNfxUser $user, DfxNfxKunden $kunde, int $archivTage, bool $isMeta, bool $pubMetaAll): DfxKonf
    {
        $konf = new DfxKonf();
        if ($isMeta) {
            $konf->setId(1);
        }
        $konf->setUser($user);
        $konf->setTitel($kunde->getKunde());
        $konf->setLengthTeaser(1000);
        $konf->setItemsListe(10);
        $konf->setImgWidth(600);
        $konf->setImgHeight(450);
        $konf->setImgPrevWidth(140);
        $konf->setImgPrevHeight(105);
        $konf->setDfxTpl('linie');
        $konf->setDfxTplVersion('dreizeilig');
        $konf->setDfxTplDetail(3);
        $konf->setNavPos('right');
        $konf->setNavWidth(3);
        $konf->setArchivTage($archivTage);
        $konf->setSprache('de');
        $konf->setDfxFarbe('#c63d4e');
        $konf->setDfxFarbeRaster('#818181');
        $konf->setTrennzeichen('|');
        $konf->setFeldTicketlink(1);
        $konf->setRubriken([]);
        $konf->setZielgruppen([]);
        $konf->setBgNav('inherit');
        $konf->setBgDatefix('inherit');
        $konf->setIsMeta($isMeta ? 1 : 0);
        $konf->setPubMetaAll($pubMetaAll ? 1 : 0);
        $konf->setInitZoom(13);

        return $konf;
    }

    private function createBox(DfxKonf $konf): DfxBox
    {
        $box = new DfxBox();
        $box->setDatefix($konf);
        $box->setBoxCss('#dfx-terminbox{
            }
            .dfx-terminbox-item{
                margin-top: 1em;
            }
            .dfx-terminbox-datum{
            }
            .dfx-terminbox-zeit{
            }
            .dfx-terminbox-titel{
                font-weight: bold;
            }
            .dfx-terminbox-subtitel{
            }
            .dfx-terminbox-text{
            }
            .dfx-terminbox-ort{
            }
            .dfx-terminbox-lokal{
            }
            .dfx-terminbox-lead{
            }
            .dfx-terminbox-image{
            }
            .dfx-terminbox-mehr{
            }
            .dfx-terminbox-alle{
                margin-top: 1em;
                margin-bottom: 1em;
            }');
        $box->setBoxItems(5);
        $box->setBoxTitel(1);
        $box->setBoxDatum(1);
        $box->setBoxUhr(1);
        $box->setBoxLokal(1);
        $box->setBoxOrt(1);

        return $box;
    }

    private function ensureFrontendDirectories(int $kid): void
    {
        foreach (['images', 'pdf', 'media'] as $directory) {
            $path = $this->projectDir . '/web/' . $directory . '/dfx/' . $kid;
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }

    private function sendAccountMails(DfxNfxKunden $kunde, DfxKonf $konf): void
    {
        $kid = $konf->getId();
        $options = ['kunde' => $kunde, 'konf' => $konf];
        $this->mailDeliveryService->sendTemplate('add_user_kunde.html.twig', $kid, $options, $kunde->getEmail(), 'Ihre Anmeldung bei Datefix');
        $this->mailDeliveryService->sendTemplate('add_user_admin.html.twig', $kid, $options, $this->dfxMail, 'Datefix Anmeldung ' . $kunde->getKunde());
    }

    private function initializeKunde(DfxNfxKunden $kunde): void
    {
        $kunde->setDatum(new DateTime(date('Y-m-d H:i:s')));
        $kunde->setInit(md5($kunde->getName() . substr((string) time(), -5)));
    }
}
