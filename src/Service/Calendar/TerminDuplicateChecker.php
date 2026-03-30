<?php

namespace App\Service\Calendar;

use App\Entity\DfxTermine;
use Doctrine\ORM\EntityManagerInterface;

final class TerminDuplicateChecker
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function countDuplicates(DfxTermine $termin): int
    {
        $zeit = $termin->getZeit() !== null ? $termin->getZeit()->format('H:i:s') : null;
        $datum = $termin->getDatumVon() !== null ? $termin->getDatumVon()->format('Y-m-d') : null;

        return (int) $this->em->createQueryBuilder()
            ->select('count(t.id)')
            ->from(DfxTermine::class, 't')
            ->where('t.lokal = :lokal')
            ->andWhere('t.titel = :titel')
            ->andWhere('t.datumVon = :datum')
            ->andWhere('t.zeit = :zeit')
            ->setParameter('lokal', $termin->getLokal())
            ->setParameter('titel', $termin->getTitel())
            ->setParameter('datum', $datum)
            ->setParameter('zeit', $zeit)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
