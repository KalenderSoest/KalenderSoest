<?php

namespace App\Service\Calendar;

use App\Entity\DfxKartenOrder;
use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use App\Service\Presentation\HtmlResponseService;
use Symfony\Component\HttpFoundation\Response;

final class KartenCapacityService
{
    public function __construct(
        private readonly HtmlResponseService $htmlResponseService,
    ) {
    }

    public function reserveForNew(DfxTermine $termin, DfxKartenOrder $order, DfxKonf $konf): ?Response
    {
        if ($termin->getPlaetzeGesamt() <= 0) {
            return null;
        }

        if ($termin->getPlaetzeAktuell() - $order->getAnzahl() < 0) {
            return $this->htmlResponseService->render('DfxKartenOrder/belegt.html.twig', ['termin' => $termin, 'konf' => $konf]);
        }

        $termin->setPlaetzeAktuell($termin->getPlaetzeAktuell() - $order->getAnzahl());

        return null;
    }

    public function reserveForUpdate(DfxTermine $termin, DfxKartenOrder $order, DfxKonf $konf, int $currentCount): ?Response
    {
        if ($termin->getPlaetzeGesamt() <= 0) {
            return null;
        }

        $availableBeforeUpdate = $termin->getPlaetzeAktuell() + $currentCount;
        if ($availableBeforeUpdate - $order->getAnzahl() < 0) {
            return $this->htmlResponseService->render('DfxKartenOrder/belegt.html.twig', ['termin' => $termin, 'konf' => $konf]);
        }

        $termin->setPlaetzeAktuell($availableBeforeUpdate - $order->getAnzahl());

        return null;
    }

    public function release(DfxTermine $termin, DfxKartenOrder $order): void
    {
        if ($termin->getPlaetzeGesamt() <= 0) {
            return;
        }

        $termin->setPlaetzeAktuell($termin->getPlaetzeAktuell() + $order->getAnzahl());
    }
}
