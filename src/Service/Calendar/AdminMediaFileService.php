<?php

namespace App\Service\Calendar;

use App\Service\FileUploadService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

final class AdminMediaFileService
{
    private const IMAGE_REFERENCE_FIELDS = ['img', 'img2', 'img3', 'img4', 'img5'];

    private const FIELD_CONFIG = [
        'imageFile' => ['getter' => 'getImg', 'setter' => 'setImg', 'path' => 'images/dfx', 'deleteField' => 'imageFileDelete', 'referenceFields' => self::IMAGE_REFERENCE_FIELDS],
        'imageFile2' => ['getter' => 'getImg2', 'setter' => 'setImg2', 'path' => 'images/dfx', 'deleteField' => 'imageFileDelete2', 'referenceFields' => self::IMAGE_REFERENCE_FIELDS],
        'imageFile3' => ['getter' => 'getImg3', 'setter' => 'setImg3', 'path' => 'images/dfx', 'deleteField' => 'imageFileDelete3', 'referenceFields' => self::IMAGE_REFERENCE_FIELDS],
        'imageFile4' => ['getter' => 'getImg4', 'setter' => 'setImg4', 'path' => 'images/dfx', 'deleteField' => 'imageFileDelete4', 'referenceFields' => self::IMAGE_REFERENCE_FIELDS],
        'imageFile5' => ['getter' => 'getImg5', 'setter' => 'setImg5', 'path' => 'images/dfx', 'deleteField' => 'imageFileDelete5', 'referenceFields' => self::IMAGE_REFERENCE_FIELDS],
        'pdfFile' => ['getter' => 'getPdf', 'setter' => 'setPdf', 'path' => 'pdf/dfx', 'deleteField' => 'pdfFileDelete', 'referenceFields' => ['pdf']],
        'media' => ['getter' => 'getMedia', 'setter' => 'setMedia', 'path' => 'media/dfx', 'deleteField' => 'mediaFileDelete', 'referenceFields' => ['media']],
    ];

    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly SharedMediaDeletionService $sharedMediaDeletionService,
    ) {
    }

    public function applyUploadedFiles(Request $request, string $formName, object $entity, string|int $kid): void
    {
        $files = $request->files->get($formName);
        if (!is_array($files)) {
            return;
        }

        foreach (self::FIELD_CONFIG as $field => $config) {
            $file = $files[$field] ?? null;
            if ($file === null || !method_exists($entity, $config['setter'])) {
                continue;
            }

            $entity->{$config['setter']}($this->fileUploadService->upload($file, (string) $kid));
        }
    }

    public function clearMarkedFiles(FormInterface $form, object $entity, string|int $kid): void
    {
        foreach (self::FIELD_CONFIG as $config) {
            $deleteField = $config['deleteField'];
            if (!$form->has($deleteField) || $form->get($deleteField)->getData() !== true) {
                continue;
            }

            if (!method_exists($entity, $config['getter']) || !method_exists($entity, $config['setter'])) {
                continue;
            }

            $currentFile = $entity->{$config['getter']}();
            if (is_string($currentFile) && $currentFile !== '' && $this->canDeletePhysicalFile($entity)) {
                $this->sharedMediaDeletionService->deleteIfUnused(
                    $entity::class,
                    $kid,
                    method_exists($entity, 'getId') ? $entity->getId() : null,
                    $currentFile,
                    $config['path'],
                    $config['referenceFields'],
                );
            }

            $entity->{$config['setter']}(null);
        }
    }

    private function canDeletePhysicalFile(object $entity): bool
    {
        if (!method_exists($entity, 'getCode')) {
            return true;
        }

        return $entity->getCode() === null;
    }

}
