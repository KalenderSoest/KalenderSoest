<?php

namespace App\Service\Calendar;

use App\Entity\DfxKartenOrder;
use App\Entity\DfxNfxUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;

final class KartenOrderListQueryFactory
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function createIndexQuery(FormInterface $form, int $kid, DfxNfxUser $user, bool $allowAll, string &$header): QueryBuilder
    {
        $repository = $this->em->getRepository(DfxKartenOrder::class);
        $query = $repository->createQueryBuilder('a')
            ->select('a')
            ->leftJoin('a.termin', 't')
            ->where('a.datefix = :kid')
            ->setParameter('kid', $kid);

        $header = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $suche = $form->get('suche')->getData();
            if (!empty($suche)) {
                $header .= 'mit Suchwort(en)  "' . $suche . '" ';
                $this->applySearch($query, (string) $suche);
            }

            $datumVon = $form->get('datum_von')->getData();
            $datumBis = $form->get('datum_bis')->getData();
            $this->applyDateFilter($query, $datumVon, $datumBis, $header);
        } else {
            $header .= 'ab heute';
            $query->andWhere('t.datum >= CURRENT_DATE()');
        }

        if (!$allowAll) {
            $query
                ->andWhere('t.user = :uid')
                ->setParameter('uid', $user->getId());
        }

        return $query
            ->groupBy('a.termin, t.datumVon')
            ->orderBy('t.datumVon');
    }

    public function createTerminListQuery(int $kid, int $tid): QueryBuilder
    {
        return $this->em->getRepository(DfxKartenOrder::class)->createQueryBuilder('a')
            ->select(['a'])
            ->where('a.datefix = :kid')
            ->andWhere('a.termin = :tid')
            ->orderBy('a.datum')
            ->setParameters(['kid' => $kid, 'tid' => $tid]);
    }

    public function createTerminPdfQuery(int $kid, int $tid): QueryBuilder
    {
        return $this->em->getRepository(DfxKartenOrder::class)->createQueryBuilder('a')
            ->select(['a'])
            ->where('a.datefix = :kid')
            ->andWhere('a.termin = :tid')
            ->orderBy('a.nachname')
            ->setParameters(['kid' => $kid, 'tid' => $tid]);
    }

    private function applySearch(QueryBuilder $query, string $suche): void
    {
        $suchworte = explode(' ', $suche);
        $counter = 1;
        foreach ($suchworte as $suchwort) {
            if ($suchwort === '') {
                continue;
            }

            $query
                ->andWhere("CONCAT(COALESCE(t.titel,'_'),COALESCE(t.beschreibung,'_'),COALESCE(t.ort,'_'),COALESCE(t.lokal,'_'),COALESCE(t.veranstalter,'_')) LIKE :suchwort{$counter}")
                ->setParameter('suchwort' . $counter, '%' . $suchwort . '%');
            $counter++;
        }
    }

    private function applyDateFilter(QueryBuilder $query, mixed $datumVon, mixed $datumBis, string &$header): void
    {
        if ($datumVon == $datumBis && !empty($datumVon) && !empty($datumBis)) {
            $header .= 'am ' . $datumVon->format('d.m.Y');
            $query
                ->andWhere('(:tag BETWEEN t.datumVon AND t.datum)')
                ->setParameter('tag', $datumVon->format('Y-m-d'));
            return;
        }

        if (!empty($datumVon) && empty($datumBis)) {
            $header .= 'ab ' . $datumVon->format('d.m.Y');
            $query
                ->andWhere('t.datum >= :tag_von')
                ->setParameter('tag_von', $datumVon->format('Y-m-d'));
            return;
        }

        if (empty($datumVon) && !empty($datumBis)) {
            $header .= 'bis ' . $datumBis->format('d.m.Y');
            $query
                ->andWhere('t.datumVon <= :tag_bis')
                ->setParameter('tag_bis', $datumBis->format('Y-m-d'));
            return;
        }

        if ($datumVon != $datumBis && !empty($datumVon) && !empty($datumBis)) {
            $header .= 'von ' . $datumVon->format('d.m.Y') . ' bis ' . $datumBis->format('d.m.Y');
            $query
                ->andWhere('(t.datumVon = t.datum AND t.datum BETWEEN :tag_von AND :tag_bis) OR (t.datumVon != t.datum AND (:tag_von BETWEEN t.datumVon AND t.datum OR :tag_bis BETWEEN t.datumVon AND t.datum OR (t.datumVon BETWEEN :tag_von AND :tag_bis AND t.datum BETWEEN :tag_von AND :tag_bis)))')
                ->setParameter('tag_von', $datumVon->format('Y-m-d'))
                ->setParameter('tag_bis', $datumBis->format('Y-m-d'));
            return;
        }

        $header .= 'ab heute';
        $query->andWhere('t.datum >= CURRENT_DATE()');
    }
}
