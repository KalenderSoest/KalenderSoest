<?php

namespace App\Service\Calendar;

use App\Entity\DfxNews;
use App\Entity\DfxTermine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class SharedMediaDeletionService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly KernelInterface $kernel,
    ) {
    }

    /**
     * @return array{cImg:int,cPdf:int,cMedia:int}
     */
    public function deleteTerminFiles(DfxTermine $termin): array
    {
        $konf = $termin->getDatefix();
        if ($konf === null) {
            return ['cImg' => 0, 'cPdf' => 0, 'cMedia' => 0];
        }

        $kid = $konf->getId();

        return [
            'cImg' => array_sum([
                $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getImg(), 'images/dfx', ['img']),
                $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getImg2(), 'images/dfx', ['img2']),
                $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getImg3(), 'images/dfx', ['img3']),
                $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getImg4(), 'images/dfx', ['img4']),
                $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getImg5(), 'images/dfx', ['img5']),
            ]),
            'cPdf' => $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getPdf(), 'pdf/dfx', ['pdf']),
            'cMedia' => $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getMedia(), 'media/dfx', ['media']),
        ];
    }

    /**
     * @return array{img:int,pdf:int,media:int}
     */
    public function deleteNewsFiles(DfxNews $newsItem): array
    {
        $konf = $newsItem->getDatefix();
        if ($konf === null) {
            return ['img' => 0, 'pdf' => 0, 'media' => 0];
        }

        $kid = $konf->getId();

        return [
            'img' => array_sum([
                $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getImg(), 'images/dfx', ['img']),
                $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getImg2(), 'images/dfx', ['img2']),
                $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getImg3(), 'images/dfx', ['img3']),
                $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getImg4(), 'images/dfx', ['img4']),
                $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getImg5(), 'images/dfx', ['img5']),
            ]),
            'pdf' => $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getPdf(), 'pdf/dfx', ['pdf']),
            'media' => $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getMedia(), 'media/dfx', ['media']),
        ];
    }

    /**
     * @param list<string> $referenceFields
     */
    public function deleteIfUnused(
        string $entityClass,
        int|string $kid,
        ?int $excludeId,
        ?string $filename,
        string $relativeDirectory,
        array $referenceFields,
    ): int {
        if ($filename === null || $filename === '') {
            return 0;
        }

        if ($this->hasOtherReference($entityClass, $kid, $excludeId, $filename, $referenceFields)) {
            return 0;
        }

        $path = $this->kernel->getProjectDir() . '/web/' . $relativeDirectory . '/' . $kid . '/' . $filename;
        if (!is_file($path)) {
            return 0;
        }

        unlink($path);

        return 1;
    }

    /**
     * @param list<string> $referenceFields
     */
    private function hasOtherReference(
        string $entityClass,
        int|string $kid,
        ?int $excludeId,
        string $filename,
        array $referenceFields,
    ): bool {
        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(e.id)')
            ->from($entityClass, 'e')
            ->join('e.datefix', 'k')
            ->where('k.id = :kid')
            ->setParameter('kid', $kid)
            ->setParameter('filename', $filename);

        if ($excludeId !== null) {
            $qb->andWhere('e.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        $orX = $qb->expr()->orX();
        foreach ($referenceFields as $field) {
            $orX->add('e.' . $field . ' = :filename');
        }
        $qb->andWhere($orX);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
