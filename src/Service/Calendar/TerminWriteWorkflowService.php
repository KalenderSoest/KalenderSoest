<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

final class TerminWriteWorkflowService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdminTerminSeriesMediaService $adminTerminSeriesMediaService,
    ) {
    }

    public function prepareCreate(
        DfxTermine $entity,
        DateTime $now,
        string $authorName,
        ?DfxKonf $konf = null,
        bool $applyPublicationDefaults = false,
        bool $convertNewlinesToBr = false,
        bool $setInit = false,
    ): void {
        $this->normalizeTimes($entity);
        $entity->setDatumInput($now);
        $entity->setAutor($authorName);

        if ($setInit) {
            $entity->setInit(substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 20));
        }

        $this->finalizeTextFields($entity, $konf, $convertNewlinesToBr);

        if ($applyPublicationDefaults && $konf !== null) {
            $this->applyPublicationDefaults($entity, $konf);
        }
    }

    public function prepareUpdate(
        DfxTermine $entity,
        DateTime $now,
        ?string $authorName = null,
        ?DfxKonf $konf = null,
        bool $applyPublicationDefaults = false,
        bool $convertNewlinesToBr = false,
    ): void {
        $this->normalizeTimes($entity);
        $entity->setDatumModified($now);

        if ($authorName !== null) {
            $entity->setAutor($authorName);
        }

        $this->finalizeTextFields($entity, $konf, $convertNewlinesToBr);

        if ($applyPublicationDefaults && $konf !== null) {
            $this->applyPublicationDefaults($entity, $konf);
        }
    }

    public function mergeSeriesDateInputs(DfxTermine $entity, ?string $pickerDates = null, ?string $listDates = null): void
    {
        $dates = [];

        foreach ([$entity->getDatumSerie(), $pickerDates, $listDates] as $source) {
            if ($source === null || trim($source) === '') {
                continue;
            }

            $parts = preg_split('/[\s,;]+/', trim($source)) ?: [];
            foreach ($parts as $part) {
                $date = trim($part);
                if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $dates[] = $date;
                }
            }
        }

        $dates = array_values(array_unique($dates));
        sort($dates);

        $entity->setDatumSerie($dates !== [] ? implode(',', $dates) : null);
    }

    public function persistSingle(DfxTermine $entity): DfxTermine
    {
        if ($entity->getDatum() === null) {
            $entity->setDatum($entity->getDatumVon());
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    public function createSeriesFromPrototype(DfxTermine $entity): DfxTermine
    {
        $dates = $this->extractSeriesDates($entity);
        rsort($dates);

        $entity->setCode(substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 20));
        $mediaState = $this->adminTerminSeriesMediaService->captureMediaState($entity);
        $batchSize = 20;
        $i = 0;
        $t = 0;
        $savedTermin = $entity;

        foreach ($dates as $date) {
            $termin = clone $entity;
            $this->adminTerminSeriesMediaService->applyMediaState($termin, $mediaState);
            $termin->setDatum(new DateTime($date));
            $termin->setDatumVon(new DateTime($date));
            $this->em->persist($termin);

            if ($t === 0) {
                $termin->setDatumSerie(implode(',', $dates));
                $savedTermin = $termin;
            } else {
                $termin->setDatumSerie(null);
            }

            $t++;
            $i++;
            if (($i % $batchSize) === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();
        $this->em->clear();

        return $savedTermin;
    }

    public function updateSeriesByCode(DfxTermine $entity, callable $copyFields): DfxTermine
    {
        $sourceId = $entity->getId();
        $this->em->persist($entity);
        $mediaState = $this->adminTerminSeriesMediaService->captureMediaState($entity);
        $this->em->flush();
        $this->em->clear();

        $source = $this->em->getRepository(DfxTermine::class)->find($sourceId);
        if (!$source instanceof DfxTermine) {
            throw new \RuntimeException('Serien-Quelle konnte nach dem Speichern nicht erneut geladen werden.');
        }

        $termine = $this->em->getRepository(DfxTermine::class)
            ->createQueryBuilder('t')
            ->select(['t'])
            ->where('t.code = :code')
            ->setParameter('code', $source->getCode())
            ->orderBy('t.datum', 'DESC')
            ->getQuery()
            ->getResult();

        $batchSize = 20;
        $i = 0;
        $terminEnd = $entity;

        foreach ($termine as $index => $termin) {
            $copyFields($termin, $source, $index);
            $this->adminTerminSeriesMediaService->applyMediaState($termin, $mediaState);

            if ($index === 0) {
                $terminEnd = $termin;
            }

            $this->em->persist($termin);
            $i++;
            if (($i % $batchSize) === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();
        $this->em->clear();

        return $terminEnd;
    }

    public function copyFrontendSeriesFields(DfxTermine $termin, DfxTermine $source, DateTime $now): void
    {
        $termin->setZeit($source->getZeit());
        $termin->setZeitBis($source->getZeitBis());
        $termin->setDatumModified($now);
        $termin->setNat($source->getNat());
        $termin->setPlz($source->getPlz());
        $termin->setOrt($source->getOrt());
        $termin->setLokal($source->getLokal());
        $termin->setLokalStrasse($source->getLokalStrasse());
        $termin->setLg($source->getLg());
        $termin->setBg($source->getBg());
        $termin->setVeranstalter($source->getVeranstalter());
        $termin->setIdVeranstalter($source->getIdVeranstalter());
        $termin->setIdLocation($source->getIdLocation());
        $termin->setEintritt($source->getEintritt());
        $termin->setRubrik($source->getRubrik());
        $termin->setZielgruppe($source->getZielgruppe());
        $termin->setTitel($source->getTitel());
        $termin->setSubtitel($source->getSubtitel());
        $termin->setLead($source->getLead());
        $termin->setBeschreibung($source->getBeschreibung());
        $termin->setLink($source->getLink());
        $termin->setLinktext($source->getLinktext());
        $termin->setTicketlink($source->getTicketlink());
        $termin->setTicketlinktext($source->getTicketlinktext());
        $termin->setImgtext($source->getImgtext());
        $termin->setPdflinktext($source->getPdflinktext());
        $termin->setMail($source->getMail());
        $termin->setMailTyp($source->getMailTyp());
    }

    public function copyAdminSeriesFields(DfxTermine $termin, DfxTermine $source, DateTime $now): void
    {
        $termin->setPub($source->getPub());
        $termin->setPubGroup($source->getPubGroup());
        $termin->setPubMeta($source->getPubMeta());
        $this->copyFrontendSeriesFields($termin, $source, $now);
        $termin->setImgcopyright($source->getImgcopyright());
        $termin->setImgcopycheck($source->getImgcopycheck());
        $termin->setImgtext2($source->getImgtext2());
        $termin->setImgcopyright2($source->getImgcopyright2());
        $termin->setImgcopycheck2($source->getImgcopycheck2());
        $termin->setMedialinktext($source->getMedialinktext());
        $termin->setOnline($source->getOnline());
        $termin->setFilter1($source->getFilter1());
        $termin->setFilter2($source->getFilter2());
        $termin->setFilter3($source->getFilter3());
        $termin->setFilter4($source->getFilter4());
        $termin->setFilter5($source->getFilter5());
        $termin->setFilter6($source->getFilter6());
        $termin->setFilter7($source->getFilter7());
        $termin->setFilter8($source->getFilter8());
        $termin->setFilter9($source->getFilter9());
        $termin->setFilter10($source->getFilter10());
        $termin->setFilter11($source->getFilter11());
        $termin->setFilter12($source->getFilter12());
        $termin->setFilter13($source->getFilter13());
        $termin->setFilter14($source->getFilter14());
        $termin->setFilter15($source->getFilter15());
        $termin->setText1($source->getText1());
        $termin->setText2($source->getText2());
        $termin->setText3($source->getText3());
        $termin->setText4($source->getText4());
        $termin->setText5($source->getText5());
        $termin->setText6($source->getText6());
        $termin->setText7($source->getText7());
        $termin->setText8($source->getText8());
        $termin->setText9($source->getText9());
        $termin->setText10($source->getText10());
        $termin->setTextbox1($source->getTextbox1());
        $termin->setTextbox2($source->getTextbox2());
    }

    public function hasSeriesDates(DfxTermine $entity): bool
    {
        return (bool) $entity->getDatumSerie();
    }

    /**
     * @return string[]
     */
    public function extractSeriesDates(DfxTermine $entity): array
    {
        if ($entity->getDatumSerie()) {
            return explode(',', (string) $entity->getDatumSerie());
        }

        return [];
    }

    private function normalizeTimes(DfxTermine $entity): void
    {
        if ($entity->getZeit() !== null) {
            $entity->setZeit($entity->getZeit()->format('H:i:s') !== '00:00:00' ? $entity->getZeit() : null);
        }

        if ($entity->getZeitBis() !== null) {
            $entity->setZeitBis($entity->getZeitBis()->format('H:i:s') !== '00:00:00' ? $entity->getZeitBis() : null);
        }
    }

    private function finalizeTextFields(DfxTermine $entity, ?DfxKonf $konf, bool $convertNewlinesToBr): void
    {
        if ($konf !== null) {
            if ($konf->getMaxLengthBeschreibung() > 0 && strlen((string) $entity->getBeschreibung()) > $konf->getMaxLengthBeschreibung()) {
                $entity->setBeschreibung(substr((string) $entity->getBeschreibung(), 0, $konf->getMaxLengthBeschreibung()));
            }
            if ($konf->getMaxLengthLead() > 0 && strlen((string) $entity->getLead()) > $konf->getMaxLengthLead()) {
                $entity->setLead(substr((string) $entity->getLead(), 0, $konf->getMaxLengthLead()));
            }
        }

        if ($convertNewlinesToBr) {
            $entity->setLead(nl2br((string) $entity->getLead()));
            $entity->setBeschreibung(nl2br((string) $entity->getBeschreibung()));
        }
    }

    private function applyPublicationDefaults(DfxTermine $entity, DfxKonf $konf): void
    {
        $entity->setPub($konf->getAllowPubAll() == 1 ? 1 : 0);
        $entity->setPubMeta($konf->getAllowPubMetaAll() == 1 ? 1 : 0);
        $entity->setPubGroup($konf->getAllowPubGroupAll() == 1 ? 1 : 0);
    }
}
