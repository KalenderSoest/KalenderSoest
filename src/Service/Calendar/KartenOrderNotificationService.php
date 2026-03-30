<?php

namespace App\Service\Calendar;

use App\Entity\DfxKartenOrder;
use App\Entity\DfxTermine;
use App\Service\Messaging\MailDeliveryService;

final class KartenOrderNotificationService
{
    public function __construct(
        private readonly MailDeliveryService $mailDeliveryService,
    ) {
    }

    public function notifyCreated(DfxKartenOrder $order, DfxTermine $termin): void
    {
        $konf = $termin->getDatefix();
        if ($konf === null) {
            return;
        }

        $kid = $konf->getId();
        $options = ['karten' => $order, 'termin' => $termin, 'konf' => $konf];

        $this->mailDeliveryService->sendTemplate(
            'karten_admin.html.twig',
            $kid,
            $options,
            $termin->getMail(),
            'Kartenreservierung für "' . $termin->getTitel() . '"',
            $order->getEmail()
        );

        $this->mailDeliveryService->sendTemplate(
            'karten_kunde.html.twig',
            $kid,
            $options,
            $order->getEmail(),
            'Ihre Kartenreservierung für "' . $termin->getTitel() . '"',
            $termin->getMail()
        );
    }
}
