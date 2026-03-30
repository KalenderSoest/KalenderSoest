<?php

namespace App\Service\Calendar;

use App\Entity\DfxTermine;
use App\Service\FileUploadService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

final class FrontendTerminUploadService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function applyLegacyUploads(DfxTermine $entity, int $kid, bool $deleteExisting = false): void
    {
        $files = ['img' => 'images', 'pdf' => 'pdf'];
        foreach ($files as $uploadField => $directory) {
            if (!isset($_POST['termine'][$uploadField])) {
                continue;
            }

            $path = $this->projectDir() . '/web/' . $directory . '/dfx/' . $kid;
            if ($deleteExisting) {
                $getter = 'get' . ucfirst($uploadField);
                $oldFile = $entity->$getter();
                if ($oldFile !== null && is_file($path . '/' . $oldFile)) {
                    unlink($path . '/' . $oldFile);
                }
            }

            $base64 = explode(',', (string) $_POST['termine'][$uploadField]['name']);
            $decoded = base64_decode($base64[1]);
            $fileName = $_POST['termine'][$uploadField]['postname'];
            $filePath = $path . '/' . $fileName;
            $handle = fopen($filePath, 'wb');
            fwrite($handle, $decoded);
            fclose($handle);

            $setter = 'set' . ucfirst($uploadField);
            $entity->$setter($fileName);
        }
    }

    public function applyAjaxUploads(DfxTermine $entity, Request $request, int $kid): void
    {
        if (!$request->files->get('termine')) {
            return;
        }

        $files = $request->files->get('termine');
        $mapping = [
            'imageFile' => 'setImg',
            'img' => 'setImg',
            'pdfFile' => 'setPdf',
            'pdf' => 'setPdf',
            'mediaFile' => 'setMedia',
            'media' => 'setMedia',
        ];

        foreach ($mapping as $key => $setter) {
            $file = $files[$key] ?? null;
            if ($file === null) {
                continue;
            }

            $entity->$setter($this->fileUploadService->upload($file, (string) $kid));
        }
    }

    private function projectDir(): string
    {
        return (string) $this->parameterBag->get('kernel.project_dir');
    }
}
