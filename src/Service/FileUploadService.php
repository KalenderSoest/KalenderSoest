<?php

namespace App\Service;

use App\Service\Support\ParameterBagService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class FileUploadService
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly ParameterBagService $parameterBagService,
    ) {
    }

    public function upload(UploadedFile $file, string $kid, ?string $oldfile = null): string
    {
        $subdir = $this->resolveSubdir($file);
        $targetDir = rtrim((string) $this->parameterBagService->get('kernel.project_dir'), '/') . '/web/' . $subdir . '/dfx/' . $kid;

        $originalFilename = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        if ($oldfile !== null && is_file($targetDir . '/' . $oldfile)) {
            unlink($targetDir . '/' . $oldfile);
        }

        $file->move($targetDir, $fileName);

        return $fileName;
    }

    private function resolveSubdir(UploadedFile $file): string
    {
        return match ($file->getMimeType()) {
            'image/jpeg', 'image/png', 'image/gif' => 'images',
            'application/pdf' => 'pdf',
            default => 'media',
        };
    }
}
