<?php

namespace App\Service\Calendar;

use App\Entity\DfxBox;
use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

final class WidgetTerminQueryFactory
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CalendarPublicationQueryHelper $calendarPublicationQueryHelper,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly KalenderFilterQueryApplier $kalenderFilterQueryApplier,
    ) {
    }

    public function buildTerminboxQuery(DfxKonf $konf, DfxBox $box, Request $request): QueryBuilder
    {
        $calendarScope = $this->calendarScopeResolver->resolveReadScope($konf);
        $query = $this->em->getRepository(DfxTermine::class)
            ->createQueryBuilder('t')
            ->select(['t']);

        if ($calendarScope->restrictsResults()) {
            $query->where('t.datefix IN (:kids)')
                ->setParameter('kids', $calendarScope->ids());
        }

        $this->calendarPublicationQueryHelper->applyPublishedVisibility($query, 't', $konf);

        $filter = $this->buildKalenderFilterData($request);
        $this->kalenderFilterQueryApplier->applySharedFilters($query, $filter, 't');
        $this->kalenderFilterQueryApplier->applyListDateWindow($query, $filter, 't');

        $limit = $box->getBoxItems() > 0 ? $box->getBoxItems() : 5;

        return $query
            ->orderBy('t.datumVon, t.zeit')
            ->setMaxResults($limit);
    }

    private function buildKalenderFilterData(Request $request): KalenderFilterData
    {
        $form = $request->query->all('form');

        return new KalenderFilterData(
            $this->normalizeString($form['rubrik'] ?? null),
            $this->normalizeString($form['zielgruppe'] ?? null),
            [
                1 => $this->toBool($form['filter1'] ?? null),
                2 => $this->toBool($form['filter2'] ?? null),
                3 => $this->toBool($form['filter3'] ?? null),
                4 => $this->toBool($form['filter4'] ?? null),
                5 => $this->toBool($form['filter5'] ?? null),
            ],
            $this->normalizeString($form['veranstalter'] ?? null),
            $this->normalizeScalar($form['idVeranstalter'] ?? null),
            $this->normalizeString($form['lokal'] ?? null),
            $this->normalizeScalar($form['idLocation'] ?? null),
            $this->normalizeString($form['umkreis'] ?? null),
            $this->normalizeString($form['plz'] ?? null),
            $this->normalizeString($form['ort'] ?? null),
            $this->toFloat($form['bg'] ?? null),
            $this->toFloat($form['lg'] ?? null),
            $this->normalizeString($form['nat'] ?? null),
            $this->normalizeScalar($form['region'] ?? null),
            $this->normalizeString($form['suche'] ?? null),
            $this->normalizeString($form['m'] ?? null),
            $this->normalizeString($form['t'] ?? null),
            $this->toDate($form['datum_von'] ?? null),
            $this->toDate($form['datum_bis'] ?? null),
        );
    }

    private function normalizeString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeScalar(mixed $value): int|string|null
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        return ctype_digit($normalized) ? (int) $normalized : $normalized;
    }

    private function toFloat(mixed $value): ?float
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (!is_scalar($value)) {
            return false;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }

    private function toDate(mixed $value): ?\DateTimeImmutable
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $normalized);

        return $date ?: null;
    }
}
