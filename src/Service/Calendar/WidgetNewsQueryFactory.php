<?php

namespace App\Service\Calendar;

use App\Entity\DfxBox;
use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

final class WidgetNewsQueryFactory
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CalendarPublicationQueryHelper $calendarPublicationQueryHelper,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly NewsDateWindowQueryApplier $newsDateWindowQueryApplier,
    ) {
    }

    public function buildNewsboxQuery(DfxKonf $konf, DfxBox $box, Request $request): QueryBuilder
    {
        $calendarScope = $this->calendarScopeResolver->resolveReadScope($konf);
        $query = $this->em->getRepository(DfxNews::class)
            ->createQueryBuilder('n')
            ->select(['n'])
            ->where('n.newsTyp = :newsTyp')
            ->setParameter('newsTyp', 'beitrag');

        if ($calendarScope->restrictsResults()) {
            $query->andWhere('n.datefix IN (:kids)')
                ->setParameter('kids', $calendarScope->ids());
        }

        $this->calendarPublicationQueryHelper->applyPublishedVisibility($query, 'n', $konf);
        $this->newsDateWindowQueryApplier->apply($query, 'n', null, null);

        $rubrik = $this->normalizeString($request->query->all('form')['rubrik'] ?? null);
        if ($rubrik !== null) {
            $query->andWhere('n.rubrik LIKE :rubrik')
                ->setParameter('rubrik', $this->jsonArrayContainsPattern($rubrik));
        }

        $limit = $box->getBoxItems() > 0 ? $box->getBoxItems() : 5;

        return $query
            ->orderBy('n.datumInput', 'DESC')
            ->addOrderBy('n.id', 'DESC')
            ->setMaxResults($limit);
    }

    private function normalizeString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function jsonArrayContainsPattern(string $value): string
    {
        return '%"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"%';
    }
}
