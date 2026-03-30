<?php

namespace App\Service\Calendar;

use App\Entity\DfxAnmeldungen;
use App\Entity\DfxTermine;
use App\Service\Messaging\MailDeliveryService;

final class AnmeldungNotificationService
{
    public function __construct(
        private readonly MailDeliveryService $mailDeliveryService,
    ) {
    }

    public function notifyCreated(DfxAnmeldungen $anmeldung, DfxTermine $termin): void
    {
        $konf = $termin->getDatefix();
        if ($konf === null) {
            return;
        }

        $kid = $konf->getId();
        $options = ['anmeldung' => $anmeldung, 'termin' => $termin, 'konf' => $konf];
        $subject = 'Anmeldung für "' . $termin->getTitel() . '"';

        $this->mailDeliveryService->sendTemplate(
            'anmeldung_admin.html.twig',
            $kid,
            $options,
            $termin->getMail(),
            $subject,
            $anmeldung->getEmail()
        );

        $this->mailDeliveryService->sendTemplate(
            'anmeldung_kunde.html.twig',
            $kid,
            $options,
            $anmeldung->getEmail(),
            'Ihre Anmeldung für "' . $termin->getTitel() . '"',
            $termin->getMail()
        );
    }

    public function notifyDeleted(DfxAnmeldungen $anmeldung, DfxTermine $termin): void
    {
        $konf = $termin->getDatefix();
        if ($konf === null) {
            return;
        }

        $kid = $konf->getId();
        $options = ['anmeldung' => $anmeldung, 'termin' => $termin];
        $subject = 'Anmeldung gelöscht';

        $this->mailDeliveryService->sendTemplate(
            'anmeldung_delete_user.html.twig',
            $kid,
            $options,
            $anmeldung->getEmail(),
            $subject
        );

        $this->mailDeliveryService->sendTemplate(
            'anmeldung_delete_admin.html.twig',
            $kid,
            $options,
            $termin->getMail(),
            $subject,
            $anmeldung->getEmail()
        );
    }
}
