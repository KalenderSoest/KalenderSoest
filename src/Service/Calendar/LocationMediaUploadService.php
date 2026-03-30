<?php

namespace App\Service\Calendar;

use App\Entity\DfxLocation;
use App\Service\FileUploadService;
use Symfony\Component\HttpFoundation\Request;

final class LocationMediaUploadService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {
    }

    public function applyUploadedFiles(Request $request, DfxLocation $entity, int|string $kid): void
    {
        $files = $request->files->get('dfx_location');
        if (!is_array($files)) {
            return;
        }

        foreach ($files as $key => $file) {
            if ($file === null) {
                continue;
            }

            switch ($key) {
                case 'imageFile':
                    $entity->setImgLoc($this->fileUploadService->upload($file, (string) $kid));
                    break;
                case 'imageFile2':
                    $entity->setImg2($this->fileUploadService->upload($file, (string) $kid));
                    break;
                case 'imageFile3':
                    $entity->setImg3($this->fileUploadService->upload($file, (string) $kid));
                    break;
                case 'imageFile4':
                    $entity->setImg4($this->fileUploadService->upload($file, (string) $kid));
                    break;
                case 'imageFile5':
                    $entity->setImg5($this->fileUploadService->upload($file, (string) $kid));
                    break;
                case 'pdfFile':
                    $entity->setPdf($this->fileUploadService->upload($file, (string) $kid));
                    break;
                case 'mediaFile':
                    $entity->setMedia($this->fileUploadService->upload($file, (string) $kid));
                    break;
            }
        }
    }
}
