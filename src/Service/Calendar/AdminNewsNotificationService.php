<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use App\Entity\DfxNfxUser;
use App\Service\Messaging\MailDeliveryService;
use Doctrine\ORM\EntityManagerInterface;

final class AdminNewsNotificationService
{
    public function __construct(
        private readonly MailDeliveryService $mailDeliveryService,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function notifyWrite(
        string $action,
        DfxNews $newsItem,
        DfxKonf $konf,
        DfxNfxUser $user,
        bool $canPublish,
        bool $canMeta,
        bool $canGroup,
    ): void {
        $kid = $konf->getId();
        $options = ['newsItem' => $newsItem, 'konf' => $konf, 'user' => $user];
        $replyTo = $user->getEmail();

        $subjects = match ($action) {
            'create' => [
                'user' => 'Ihr Artikeleintrag',
                'admin' => 'Neuer Artikeleintrag',
                'meta' => 'Neuer Artikeleintrag ohne Meta-Status',
                'group' => 'Neuer Artikeleintrag ohne Group-Status',
            ],
            'update' => [
                'user' => 'Ihr geänderter Artikeleintrag',
                'admin' => 'Geänderter Artikeleintrag',
                'meta' => 'Geänderter Artikeleintrag ohne Meta-Status',
                'group' => 'Geänderter Artikeleintrag ohne Group-Status',
            ],
            default => null,
        };

        if ($subjects === null) {
            return;
        }

        if (!$canPublish) {
            $this->mailDeliveryService->sendTemplate('user_newsItem_user.html.twig', $kid, $options, $user->getEmail(), $subjects['user']);
            $this->mailDeliveryService->sendTemplate('user_newsItem_admin.html.twig', $kid, $options, $konf->getUser()->getEmail(), $subjects['admin'], $replyTo);
        }

        if (!$konf->getPubMetaAll() && !$canMeta && $newsItem->getPubMeta() == 0) {
            $userMeta = $this->em->getRepository(DfxNfxUser::class)->find(100);
            if ($userMeta instanceof DfxNfxUser) {
                $this->mailDeliveryService->sendTemplate('user_newsItem_meta_admin.html.twig', $kid, $options, $userMeta->getEmail(), $subjects['meta'], $replyTo);
            }
        }

        if (!$konf->getPubGroupAll() && !$canGroup && $newsItem->getPubGroup() == 0 && count($konf->getToGroup()) > 0) {
            foreach ($konf->getToGroup() as $dfxId) {
                $datefix = $this->em->getRepository(DfxKonf::class)->find($dfxId);
                if (!$datefix instanceof DfxKonf) {
                    continue;
                }

                $this->mailDeliveryService->sendTemplate('user_newsItem_group_admin.html.twig', $kid, $options, $datefix->getUser()->getEmail(), $subjects['group'], $replyTo);
            }
        }
    }
}
