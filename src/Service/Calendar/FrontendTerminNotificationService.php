<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use App\Service\Messaging\MailDeliveryService;

final class FrontendTerminNotificationService
{
    public function __construct(
        private readonly MailDeliveryService $mailDeliveryService,
    ) {
    }

    /**
     * @param array<string, mixed> $post
     */
    public function notifyWrite(
        DfxTermine $termin,
        DfxKonf $konf,
        array $post,
        string $submitterEmail,
        string $userSubject,
        string $adminSubject,
    ): void {
        $kid = $konf->getId();
        $options = [
            'termin' => $termin,
            'konf' => $konf,
            'post' => $post,
        ];

        $this->mailDeliveryService->sendTemplate('gast_termin_user.html.twig', $kid, $options, $submitterEmail, $userSubject);

        if ($konf->getInfoToAdmin() != 1) {
            return;
        }

        $this->mailDeliveryService->sendTemplate(
            'gast_termin_admin.html.twig',
            $kid,
            $options,
            $konf->getUser()->getEmail(),
            $adminSubject,
            $submitterEmail
        );
    }
}
