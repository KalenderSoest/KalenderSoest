<?php

namespace App\Service\Analytics;

use App\Entity\DfxKonf;
use App\Entity\DfxLogs;
use App\Entity\DfxLogsJahr;
use App\Entity\DfxLogsMonat;
use App\Entity\DfxLogsTag;
use App\Entity\DfxNfxCounter;
use App\Entity\DfxReminder;
use App\Entity\DfxTermine;
use App\Service\Calendar\SharedMediaDeletionService;
use App\Service\Messaging\MailDeliveryService;
use App\Service\Presentation\HtmlResponseService;
use App\Service\Support\ParameterBagService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

final class NightlyMaintenanceService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailDeliveryService $mailDeliveryService,
        private readonly SharedMediaDeletionService $sharedMediaDeletionService,
        private readonly HtmlResponseService $htmlResponseService,
        private readonly ParameterBagService $parameterBagService,
    ) {
    }

    public function nightrun(bool $echo = false): Response|true
    {
        $cfgAbsMail = (string) $this->parameterBagService->get('dfx_mail');
        $gestern = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
        $heute = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $monat_heute = date('m', $heute);
        $monat_gestern = date('m', $gestern);
        $jahr_heute = date('Y', $heute);
        $jahr_gestern = date('Y', $gestern);

        $arAccounts = [];
        $entitiesM = null;
        $entitiesJ = null;
        $arAll = [];
        $msg = null;
        $sum = 0;
        $sumApi = 0;

        $accounts = $this->em->getRepository(DfxNfxCounter::class)
            ->createQueryBuilder('c')
            ->select(['c'])
            ->where('c.datefix > 0')
            ->andWhere('DATE_DIFF(CURRENT_DATE(), c.dfxLastLog) < 366')
            ->orderBy('c.id')
            ->getQuery()
            ->getResult();

        $i = 0;
        $batchSize = 20;
        foreach ($accounts as $account) {
            $konf = $account->getDatefix();
            $kid = $konf->getId();
            $hits = $account->getDfxDay() ?? 0;
            $hitsApi = $account->getDfxApiDay() ?? 0;

            $logdate = new DateTime('@' . $gestern);
            $logs = new DfxLogsTag();
            $logs->setDatefix($konf);
            $logs->setDatum($logdate);
            $logs->setHits($hits);
            $logs->setHitsApi($hitsApi);
            $this->em->persist($logs);

            $archiveDeleteCounts = $this->archiveExpiredTermineForKonf($konf);

            $arAccounts[$kid]['cImg'] = $archiveDeleteCounts['cImg'];
            $arAccounts[$kid]['cPdf'] = $archiveDeleteCounts['cPdf'];
            $arAccounts[$kid]['cMedia'] = $archiveDeleteCounts['cMedia'];
            $arAccounts[$kid]['cDel'] = $archiveDeleteCounts['cDel'];
            $arAccounts[$kid]['cHits'] = $account->getDfxDay();
            $arAccounts[$kid]['cHitsApi'] = $account->getDfxApiDay();

            if ($hits > 0) {
                $account->setDfxDay(0);
                $sum += $hits;
            }

            if ($hitsApi > 0) {
                $account->setDfxApiDay(0);
                $sumApi += $hitsApi;
            }

            $this->em->persist($account);
            $i++;
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $arAll['sum'] = $sum;
        $arAll['sumApi'] = $sumApi;

        $query = $this->em->createQuery('DELETE App\Entity\DfxLogs l WHERE DATE_DIFF(CURRENT_DATE(), l.zeit) > 30');
        $arAll['logs'] = $query->getResult();

        if ($monat_gestern != $monat_heute) {
            $query = $this->em->createQuery('SELECT l, SUM(l.hits) as summe, SUM(l.hitsApi) as summeApi
            FROM ' . DfxLogsTag::class . ' l
            WHERE MONTH(l.datum) = :monat AND YEAR(l.datum) = :jahr
            GROUP BY l.datefix
            ORDER BY l.datefix');
            $query->setParameters(['monat' => $monat_gestern, 'jahr' => $jahr_gestern]);
            $entitiesM = $query->getResult();
            $i = 0;
            foreach ($entitiesM as $entity) {
                $logs = new DfxLogsMonat();
                $logs->setDatefix($entity[0]->getDatefix());
                $logs->setHits($entity['summe']);
                $logs->setHitsApi($entity['summeApi']);
                $logs->setMonat($monat_gestern);
                $logs->setJahr($jahr_gestern);
                $this->em->persist($logs);
                $i++;
                if (($i % $batchSize) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
            }

            $this->em->flush();
            $query = $this->em->createQuery('DELETE App\Entity\DfxLogsTag l WHERE DATE_DIFF(CURRENT_DATE(), l.datum) > 90');
            $arAll['logsM'] = $query->getResult();
            $arAll['monat'] = $monat_gestern;
        }

        if ($jahr_gestern != $jahr_heute) {
            $query = $this->em->createQuery('SELECT l, SUM(l.hits) as summe, SUM(l.hitsApi) as summeApi
                FROM ' . DfxLogsMonat::class . ' l
                WHERE l.jahr = :jahr
                GROUP BY l.datefix
                ORDER BY l.datefix');
            $query->setParameters(['jahr' => $jahr_gestern]);
            $entitiesJ = $query->getResult();
            $i = 0;
            foreach ($entitiesJ as $entity) {
                $logs = new DfxLogsJahr();
                $logs->setDatefix($entity[0]->getDatefix());
                $logs->setHits($entity['summe']);
                $logs->setHitsApi($entity['summeApi']);
                $logs->setJahr($jahr_gestern);
                $this->em->persist($logs);
                $i++;
                if (($i % $batchSize) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
            }

            $this->em->flush();
            $query = $this->em->createQuery('DELETE App\Entity\DfxLogsMonat l WHERE l.jahr = :jahr');
            $query->setParameters(['jahr' => $jahr_gestern - 2]);
            $arAll['logsJ'] = $query->getResult();
            $arAll['jahr'] = $jahr_gestern;
        }

        $entities = $this->em->getRepository(DfxReminder::class)
            ->createQueryBuilder('r')
            ->select(['r'])
            ->where('r.datum <= CURRENT_DATE()')
            ->getQuery()
            ->getResult();

        $i = 0;
        $erinnerungen = null;
        foreach ($entities as $entity) {
            $termin = $entity->getTermin();
            if (isset($termin)) {
                $konf = $termin->getDatefix();
                $kid = $konf->getId();
                $options = ['termin' => $termin, 'konf' => $konf];
                $mailOk = $this->mailDeliveryService->sendTemplate('remind.html.twig', (string) $kid, $options, $entity->getEmail(), 'Erinnerung an "' . $termin->getTitel() . '"', $termin->getMail(), $cfgAbsMail);
                $erinnerungen .= 'Mailer: ' . ($mailOk ? '1' : '0') . ' / Erinnerungsmail für Termin ' . $termin->getTitel() . ' an ' . $entity->getEmail() . '<br>';
                $this->em->remove($entity);
            }

            $i++;
            if (($i % $batchSize) === 0) {
                $this->em->flush();
            }
        }

        if (isset($entity)) {
            $this->em->flush();
        }

        $erinnerungen .= $erinnerungen . '<br><br><h3>Löschbericht Dateien</h3>' . $msg;
        $options = ['accounts' => $accounts, 'kalender' => $arAccounts, 'accountsM' => $entitiesM, 'accountsJ' => $entitiesJ, 'erinnerungen' => $erinnerungen, 'all' => $arAll];
        $kid = (string) $this->parameterBagService->get('metaId');
        $this->mailDeliveryService->sendTemplate('nightrun.html.twig', $kid, $options, $cfgAbsMail, 'Datefix-Tagesstatistik ' . date('d.m.Y'), $cfgAbsMail, $cfgAbsMail);

        if ($echo) {
            return $this->htmlResponseService->render('Emails/nightrun.html.twig', $options);
        }

        return true;
    }

    /**
     * @return array{cImg:int,cPdf:int,cMedia:int,cDel:int}
     */
    private function archiveExpiredTermineForKonf(DfxKonf $konf): array
    {
        $query = $this->em->createQuery(
            'SELECT t FROM App\Entity\DfxTermine t WHERE t.datefix = :kid AND DATE_DIFF(CURRENT_DATE(), t.datum) > :archivtage AND t.archiv != 1'
        );
        $query->setParameters([
            'kid' => $konf->getId(),
            'archivtage' => $konf->getArchivTage(),
        ]);

        $counts = ['cImg' => 0, 'cPdf' => 0, 'cMedia' => 0, 'cDel' => 0];

        foreach ($query->getResult() as $termin) {
            $deletedFiles = $this->sharedMediaDeletionService->deleteTerminFiles($termin);
            $counts['cImg'] += $deletedFiles['cImg'];
            $counts['cPdf'] += $deletedFiles['cPdf'];
            $counts['cMedia'] += $deletedFiles['cMedia'];
            $this->em->remove($termin);
            $this->em->flush();
            $counts['cDel']++;
        }

        return $counts;
    }

}
