<?php

namespace App\Service\Calendar;

use App\Entity\DfxTermine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

final class AdminTerminSeriesMediaService
{
    private const MEDIA_CONFIG = [
        'img' => ['path' => 'images/dfx', 'referenceFields' => ['img', 'img2', 'img3', 'img4', 'img5']],
        'img2' => ['path' => 'images/dfx', 'referenceFields' => ['img', 'img2', 'img3', 'img4', 'img5']],
        'img3' => ['path' => 'images/dfx', 'referenceFields' => ['img', 'img2', 'img3', 'img4', 'img5']],
        'img4' => ['path' => 'images/dfx', 'referenceFields' => ['img', 'img2', 'img3', 'img4', 'img5']],
        'img5' => ['path' => 'images/dfx', 'referenceFields' => ['img', 'img2', 'img3', 'img4', 'img5']],
        'pdf' => ['path' => 'pdf/dfx', 'referenceFields' => ['pdf']],
        'media' => ['path' => 'media/dfx', 'referenceFields' => ['media']],
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SharedMediaDeletionService $sharedMediaDeletionService,
    ) {
    }

    /**
     * @return array{img:?string,img2:?string,img3:?string,img4:?string,img5:?string,pdf:?string,media:?string}
     */
    public function prepareUpdateSeriesMedia(
        DfxTermine $entity,
        FormInterface $form,
        string|int $kid,
        \DateTimeInterface $originalDatum,
        \DateTimeInterface $originalDatumVon,
        ?string $originalDatumSerie,
    ): array {
        $this->deleteSeriesMediaIfMarked($entity, $form, $kid);

        $entity->setDatum($originalDatum);
        $entity->setDatumVon($originalDatumVon);
        $entity->setDatumSerie($originalDatumSerie);

        return $this->captureMediaState($entity);
    }

    /**
     * @param array{img:?string,img2:?string,img3:?string,img4:?string,img5:?string,pdf:?string,media:?string} $originalMediaState
     */
    public function cleanupReplacedSeriesMedia(array $originalMediaState, DfxTermine $entity, string|int $kid): void
    {
        $currentMediaState = $this->captureMediaState($entity);

        foreach (self::MEDIA_CONFIG as $field => $config) {
            $oldFile = $originalMediaState[$field] ?? null;
            $newFile = $currentMediaState[$field] ?? null;

            if (!is_string($oldFile) || $oldFile === '' || $oldFile === $newFile) {
                continue;
            }

            $this->sharedMediaDeletionService->deleteIfUnused(
                DfxTermine::class,
                $kid,
                null,
                $oldFile,
                $config['path'],
                $config['referenceFields'],
            );
        }
    }

    /**
     * @param iterable<DfxTermine> $termine
     */
    public function applyMediaToSeriesOccurrences(iterable $termine, array $mediaState): ?DfxTermine
    {
        $lead = null;
        $index = 0;

        foreach ($termine as $termin) {
            $this->applyMediaState($termin, $mediaState);

            if ($index === 0) {
                $lead = $termin;
            }

            $this->em->persist($termin);
            $index++;
        }

        return $lead;
    }

    private function deleteSeriesMediaIfMarked(DfxTermine $entity, FormInterface $form, string|int $kid): void
    {
        $map = [
            ['deleteField' => 'imageFileDelete', 'value' => $entity->getImg(), 'path' => 'images/dfx', 'referenceFields' => ['img', 'img2', 'img3', 'img4', 'img5']],
            ['deleteField' => 'imageFileDelete2', 'value' => $entity->getImg2(), 'path' => 'images/dfx', 'referenceFields' => ['img', 'img2', 'img3', 'img4', 'img5']],
            ['deleteField' => 'imageFileDelete3', 'value' => $entity->getImg3(), 'path' => 'images/dfx', 'referenceFields' => ['img', 'img2', 'img3', 'img4', 'img5']],
            ['deleteField' => 'imageFileDelete4', 'value' => $entity->getImg4(), 'path' => 'images/dfx', 'referenceFields' => ['img', 'img2', 'img3', 'img4', 'img5']],
            ['deleteField' => 'imageFileDelete5', 'value' => $entity->getImg5(), 'path' => 'images/dfx', 'referenceFields' => ['img', 'img2', 'img3', 'img4', 'img5']],
            ['deleteField' => 'pdfFileDelete', 'value' => $entity->getPdf(), 'path' => 'pdf/dfx', 'referenceFields' => ['pdf']],
            ['deleteField' => 'mediaFileDelete', 'value' => $entity->getMedia(), 'path' => 'media/dfx', 'referenceFields' => ['media']],
        ];

        foreach ($map as $config) {
            if (!$form->has($config['deleteField']) || $form->get($config['deleteField'])->getData() !== true) {
                continue;
            }

            if (!is_string($config['value']) || $config['value'] === '') {
                continue;
            }

            $this->sharedMediaDeletionService->deleteIfUnused(
                DfxTermine::class,
                $kid,
                $entity->getId(),
                $config['value'],
                $config['path'],
                $config['referenceFields'],
            );
        }
    }

    /**
     * @return array{img:?string,img2:?string,img3:?string,img4:?string,img5:?string,pdf:?string,media:?string}
     */
    public function captureMediaState(DfxTermine $entity): array
    {
        return [
            'img' => $entity->getImg(),
            'img2' => $entity->getImg2(),
            'img3' => $entity->getImg3(),
            'img4' => $entity->getImg4(),
            'img5' => $entity->getImg5(),
            'pdf' => $entity->getPdf(),
            'media' => $entity->getMedia(),
        ];
    }

    /**
     * @param array{img:?string,img2:?string,img3:?string,img4:?string,img5:?string,pdf:?string,media:?string} $mediaState
     */
    public function applyMediaState(DfxTermine $termin, array $mediaState): void
    {
        $termin->setImg($mediaState['img']);
        $termin->setImg2($mediaState['img2']);
        $termin->setImg3($mediaState['img3']);
        $termin->setImg4($mediaState['img4']);
        $termin->setImg5($mediaState['img5']);
        $termin->setPdf($mediaState['pdf']);
        $termin->setMedia($mediaState['media']);
    }
}
