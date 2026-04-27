<?php

namespace App\Service\Calendar;

use App\Entity\DfxNews;
use App\Entity\DfxTermine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class SharedMediaDeletionService
{
    private const IMAGE_REFERENCE_FIELDS = ['img', 'img2', 'img3', 'img4', 'img5'];

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
                $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getImg(), 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
                $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getImg2(), 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
                $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getImg3(), 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
                $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getImg4(), 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
                $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getImg5(), 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
            ]),
            'cPdf' => $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getPdf(), 'pdf/dfx', ['pdf']),
            'cMedia' => $this->deleteIfUnused(DfxTermine::class, $kid, $termin->getId(), $termin->getMedia(), 'media/dfx', ['media']),
        ];
    }

    /**
     * @return array{cImg:int,cPdf:int,cMedia:int}
     */
    public function deleteTerminFilesByCode(string $code, int|string $kid): array
    {
        $termine = $this->em->getRepository(DfxTermine::class)
            ->createQueryBuilder('t')
            ->select(['t'])
            ->where('t.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getResult();

        return [
            'cImg' => $this->deleteUniqueFilesAfterBulkDelete(DfxTermine::class, $kid, $termine, ['img', 'img2', 'img3', 'img4', 'img5'], 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
            'cPdf' => $this->deleteUniqueFilesAfterBulkDelete(DfxTermine::class, $kid, $termine, ['pdf'], 'pdf/dfx', ['pdf']),
            'cMedia' => $this->deleteUniqueFilesAfterBulkDelete(DfxTermine::class, $kid, $termine, ['media'], 'media/dfx', ['media']),
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
                $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getImg(), 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
                $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getImg2(), 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
                $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getImg3(), 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
                $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getImg4(), 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
                $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getImg5(), 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
            ]),
            'pdf' => $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getPdf(), 'pdf/dfx', ['pdf']),
            'media' => $this->deleteIfUnused(DfxNews::class, $kid, $newsItem->getId(), $newsItem->getMedia(), 'media/dfx', ['media']),
        ];
    }

    /**
     * @return array{img:int,pdf:int,media:int}
     */
    public function deleteNewsFilesByCode(string $code, int|string $kid): array
    {
        $newsItems = $this->em->getRepository(DfxNews::class)
            ->createQueryBuilder('n')
            ->select(['n'])
            ->where('n.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getResult();

        return [
            'img' => $this->deleteUniqueFilesAfterBulkDelete(DfxNews::class, $kid, $newsItems, ['img', 'img2', 'img3', 'img4', 'img5'], 'images/dfx', self::IMAGE_REFERENCE_FIELDS),
            'pdf' => $this->deleteUniqueFilesAfterBulkDelete(DfxNews::class, $kid, $newsItems, ['pdf'], 'pdf/dfx', ['pdf']),
            'media' => $this->deleteUniqueFilesAfterBulkDelete(DfxNews::class, $kid, $newsItems, ['media'], 'media/dfx', ['media']),
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

    /**
     * @param iterable<object> $entities
     * @param list<string> $fields
     * @param list<string> $referenceFields
     */
    private function deleteUniqueFilesAfterBulkDelete(
        string $entityClass,
        int|string $kid,
        iterable $entities,
        array $fields,
        string $relativeDirectory,
        array $referenceFields,
    ): int {
        $deleted = 0;

        foreach ($this->collectUniqueFiles($entities, $fields) as $filename) {
            $deleted += $this->deleteIfUnused($entityClass, $kid, null, $filename, $relativeDirectory, $referenceFields);
        }

        return $deleted;
    }

    /**
     * @param iterable<object> $entities
     * @param list<string> $fields
     * @return list<string>
     */
    private function collectUniqueFiles(iterable $entities, array $fields): array
    {
        $files = [];

        foreach ($entities as $entity) {
            foreach ($fields as $field) {
                $getter = 'get' . ucfirst($field);
                if (!method_exists($entity, $getter)) {
                    continue;
                }

                $filename = $entity->{$getter}();
                if (!is_string($filename) || $filename === '') {
                    continue;
                }

                $files[$filename] = true;
            }
        }

        return array_keys($files);
    }
}
