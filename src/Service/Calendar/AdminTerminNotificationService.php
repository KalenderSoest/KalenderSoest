<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxNfxUser;
use App\Entity\DfxTermine;
use App\Service\Messaging\MailDeliveryService;
use Doctrine\ORM\EntityManagerInterface;

final class AdminTerminNotificationService
{
    public function __construct(
        private readonly MailDeliveryService $mailDeliveryService,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function notifyWrite(
        string $action,
        DfxTermine $termin,
        DfxKonf $konf,
        DfxNfxUser $user,
        bool $canPublish,
        bool $canMeta,
        bool $canGroup,
    ): void {
        $kid = $konf->getId();
        $options = ['termin' => $termin, 'konf' => $konf, 'user' => $user];
        $replyTo = $user->getEmail();

        $subjects = match ($action) {
            'create' => [
                'user' => 'Ihr Veranstaltungseintrag',
                'admin' => 'Neuer Veranstaltungseintrag',
                'meta' => 'Neuer Veranstaltungseintrag ohne Meta-Status',
                'group' => 'Neuer Veranstaltungseintrag ohne Group-Status',
            ],
            'update' => [
                'user' => 'Ihr geänderter Veranstaltungseintrag',
                'admin' => 'Geänderter Veranstaltungseintrag',
                'meta' => 'Geänderter Veranstaltungseintrag ohne Meta-Status',
                'group' => 'Geänderter Veranstaltungseintrag ohne Group-Status',
            ],
            'copy' => [
                'user' => 'Ihr kopierter Veranstaltungseintrag',
                'admin' => 'Kopierter Veranstaltungseintrag',
                'meta' => 'Kopierter Veranstaltungseintrag ohne Meta-Status',
                'group' => 'Kopierter Veranstaltungseintrag ohne Group-Status',
            ],
            default => null,
        };

        if ($subjects === null) {
            return;
        }

        if (!$canPublish) {
            $this->mailDeliveryService->sendTemplate('user_termin_user.html.twig', $kid, $options, $user->getEmail(), $subjects['user']);
            $this->mailDeliveryService->sendTemplate('user_termin_admin.html.twig', $kid, $options, $konf->getUser()->getEmail(), $subjects['admin'], $replyTo);
        }

        if (!$konf->getPubMetaAll() && !$canMeta && $termin->getPubMeta() == 0) {
            $userMeta = $this->em->getRepository(DfxNfxUser::class)->find(100);
            if ($userMeta instanceof DfxNfxUser) {
                $this->mailDeliveryService->sendTemplate('user_termin_meta_admin.html.twig', $kid, $options, $userMeta->getEmail(), $subjects['meta'], $replyTo);
            }
        }

        if (!$konf->getPubGroupAll() && !$canGroup && $termin->getPubGroup() == 0 && count($konf->getToGroup()) > 0) {
            foreach ($konf->getToGroup() as $dfxId) {
                $datefix = $this->em->getRepository(DfxKonf::class)->find($dfxId);
                if (!$datefix instanceof DfxKonf) {
                    continue;
                }

                $this->mailDeliveryService->sendTemplate('user_termin_group_admin.html.twig', $kid, $options, $datefix->getUser()->getEmail(), $subjects['group'], $replyTo);
            }
        }
    }
}
