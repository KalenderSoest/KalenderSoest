<?php

namespace App\Service\Analytics;

use App\Entity\DfxKonf;
use App\Entity\DfxLogs;
use App\Entity\DfxNews;
use App\Entity\DfxNfxCounter;
use App\Entity\DfxTermine;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

final class UsageTrackingService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NightlyMaintenanceService $nightlyMaintenanceService,
    ) {
    }

    public function track(DfxKonf $konf, ?DfxTermine $termin = null, ?DfxNews $artikel = null): void
    {
        $kid = $konf->getId();
        $now = new DateTime(date('Y-m-d H:i:s'));
        $counter = $this->em->getRepository(DfxNfxCounter::class)->findOneBy(['datefix' => $kid]);
        if ($counter === null) {
            return;
        }

        $counter->setDfxDay($counter->getDfxDay() + 1);
        $counter->setDfxSum($counter->getDfxSum() + 1);

        if ($termin !== null) {
            $termin->setCounter($termin->getCounter() + 1);
        } elseif ($artikel !== null) {
            $artikel->setCounter($artikel->getCounter() + 1);
        }

        $query = $this->em->getRepository(DfxNfxCounter::class)->createQueryBuilder('c');
        $query->select('DAY(MAX(c.dfxLastLog)) AS lastLogDay');
        $lastlog = $query->getQuery()->getSingleResult();

        $counter->setDfxLastLog($now);
        $log = new DfxLogs();
        $log->setDatefix($konf);
        if (isset($_SERVER['SERVER_ADDR'])) {
            $log->setIp($_SERVER['SERVER_ADDR']);
        }

        $log->setHost($_SERVER['SERVER_NAME']);
        $log->setAgent(substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255));
        $log->setZeit($now);

        $this->em->persist($counter);
        $this->em->persist($log);
        $this->em->flush();

        if (($lastlog['lastLogDay'] ?? null) != date('d')) {
            $this->nightlyMaintenanceService->nightrun();
        }
    }

    public function getMenu(DfxKonf $konf): array
    {
        return $this->em->getRepository(DfxNews::class)
            ->createQueryBuilder('n')
            ->select(['n.kurztitel', 'n.id'])
            ->where('n.menueeintrag = 1')
            ->andWhere('n.datefix = :kid')
            ->andWhere('n.pub = true')
            ->orderBy('n.reihenfolge')
            ->setParameter('kid', $konf->getId())
            ->getQuery()
            ->getResult();
    }
}
