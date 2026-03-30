<?php

namespace App\Service\Calendar;

use App\Entity\DfxAnmeldungen;
use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use App\Service\Presentation\HtmlResponseService;
use Symfony\Component\HttpFoundation\Response;

final class AnmeldungCapacityService
{
    public function __construct(
        private readonly HtmlResponseService $htmlResponseService,
    ) {
    }

    public function reserveForNew(DfxTermine $termin, DfxAnmeldungen $anmeldung, DfxKonf $konf): ?Response
    {
        if ($termin->getPlaetzeGesamt() <= 0) {
            return null;
        }

        if ($termin->getPlaetzeAktuell() - $anmeldung->getAnzahl() < 0) {
            return $this->htmlResponseService->render('DfxAnmeldungen/belegt.html.twig', ['termin' => $termin, 'konf' => $konf]);
        }

        $termin->setPlaetzeAktuell($termin->getPlaetzeAktuell() - $anmeldung->getAnzahl());

        return null;
    }

    public function reserveForUpdate(DfxTermine $termin, DfxAnmeldungen $anmeldung, DfxKonf $konf, int $currentCount): ?Response
    {
        if ($termin->getPlaetzeGesamt() <= 0) {
            return null;
        }

        $availableBeforeUpdate = $termin->getPlaetzeAktuell() + $currentCount;
        if ($availableBeforeUpdate - $anmeldung->getAnzahl() < 0) {
            return $this->htmlResponseService->render('DfxAnmeldungen/belegt.html.twig', ['termin' => $termin, 'konf' => $konf]);
        }

        $termin->setPlaetzeAktuell($availableBeforeUpdate - $anmeldung->getAnzahl());

        return null;
    }

    public function release(DfxTermine $termin, DfxAnmeldungen $anmeldung): void
    {
        if ($termin->getPlaetzeGesamt() <= 0) {
            return;
        }

        $termin->setPlaetzeAktuell($termin->getPlaetzeAktuell() + $anmeldung->getAnzahl());
    }
}
