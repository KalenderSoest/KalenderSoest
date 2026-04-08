<?php

namespace App\Service\Frontend;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use App\Entity\DfxTermine;
use Doctrine\ORM\EntityManagerInterface;
use finfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class FrontendBridgeService
{
    private const ALLOWED_SUBREQUEST_PREFIXES = [
        '/js/kalender/',
        '/js/news/',
        '/karten/',
        '/anmeldungen/',
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HttpKernelInterface $httpKernel,
        private readonly FrontendContentRenderer $frontendContentRenderer,
    ) {
    }

    public function renderContent(DfxKonf $konf, Request $request): array
    {
        $method = $request->isMethod('POST') ? 'POST' : 'GET';
        $payload = $method === 'POST' ? $request->request->all() : $request->query->all();
        if (!isset($payload['dfxpath']) && $request->query->has('dfxpath')) {
            $payload['dfxpath'] = $request->query->get('dfxpath');
        }
        $uploadResult = $this->prepareUploads($request);

        if ($uploadResult['error'] !== null) {
            return [
                'content' => $uploadResult['error'],
                'termin' => null,
                'artikel' => null,
            ];
        }

        if ($uploadResult['payload'] !== []) {
            $payload['termine'] = array_merge($payload['termine'] ?? [], $uploadResult['payload']);
        }

        if (!isset($payload['dfxpath'])) {
            if (isset($payload['dfxid'])) {
                return $this->frontendContentRenderer->renderCalendarDetail($konf, $request, (int) $payload['dfxid']);
            }

            if (isset($payload['nfxid'])) {
                return $this->frontendContentRenderer->renderNewsDetail($konf, $request, (int) $payload['nfxid']);
            }

            if (isset($payload['nfx'])) {
                return $this->frontendContentRenderer->renderNewsList($konf, $request);
            }

            return $this->frontendContentRenderer->renderCalendarList($konf, $request);
        }

        $target = $this->resolveTarget($konf, $payload);
        if ($target['error'] !== null) {
            return [
                'content' => $target['error'],
                'termin' => null,
                'artikel' => null,
            ];
        }

        $content = $this->fetchContent($target['path'], $method, $request, $payload, $uploadResult['files']);

        return [
            'content' => $content,
            'termin' => $target['termin'],
            'artikel' => $target['artikel'],
        ];
    }

    private function prepareUploads(Request $request): array
    {
        $files = $request->files->get('termine');
        if (!is_array($files) || $files === []) {
            return ['payload' => [], 'files' => [], 'error' => null];
        }

        $payload = [];
        $preparedFiles = [];
        $error = '';
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        foreach ($files as $key => $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            $mimeType = $finfo->file($file->getPathname()) ?: $file->getMimeType();
            $field = 'img';

            if ($key === 'pdfFile') {
                $field = 'pdf';
                if ($mimeType !== 'application/pdf') {
                    $error .= 'Fehler beim Dateiupload: Falsches Dateiformat (' . $file->getClientMimeType() . ') für Pdf bei Datei ' . $file->getClientOriginalName() . '.<br>';
                }
            } elseif ($key === 'mediaFile') {
                $field = 'media';
                if ($mimeType !== 'video/mp4') {
                    $error .= 'Fehler beim Dateiupload: Falsches Dateiformat (' . $file->getClientMimeType() . ') für Media-Datei bei Datei ' . $file->getClientOriginalName() . '.<br>';
                }
            } elseif (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'], true)) {
                $error .= 'Fehler beim Dateiupload: Falsches Dateiformat (' . $file->getClientMimeType() . ') für Bilder oder Grafiken bei Datei ' . $file->getClientOriginalName() . '.<br>';
            }

            if ($error === '') {
                $payload[$field] = $file->getClientOriginalName();
                $preparedFiles[$field] = $file;
            }
        }

        return ['payload' => $payload, 'files' => $preparedFiles, 'error' => $error !== '' ? $error : null];
    }

    private function resolveTarget(DfxKonf $konf, array $payload): array
    {
        $kid = $konf->getId();

        if (isset($payload['dfxpath'])) {
            $path = $this->normalizeSubRequestPath((string) $payload['dfxpath']);

            if ($path === null) {
                return ['path' => null, 'termin' => null, 'artikel' => null, 'error' => 'Ungültiger Frontend-Pfad.'];
            }

            return ['path' => $path, 'termin' => null, 'artikel' => null, 'error' => null];
        }

        if (isset($payload['dfxid'])) {
            return [
                'path' => '/js/kalender/' . $kid . '/detail/' . $payload['dfxid'],
                'termin' => $this->em->getRepository(DfxTermine::class)->find($payload['dfxid']),
                'artikel' => null,
                'error' => null,
            ];
        }

        if (isset($payload['nfx'])) {
            return ['path' => '/js/news/' . $kid, 'termin' => null, 'artikel' => null, 'error' => null];
        }

        if (isset($payload['nfxid'])) {
            return [
                'path' => '/js/news/' . $kid . '/detail/' . $payload['nfxid'],
                'termin' => null,
                'artikel' => $this->em->getRepository(DfxNews::class)->find($payload['nfxid']),
                'error' => null,
            ];
        }

        return ['path' => '/js/kalender/' . $kid, 'termin' => null, 'artikel' => null, 'error' => null];
    }

    private function fetchContent(string $path, string $method, Request $request, array $payload, array $files): string
    {
        $query = $method === 'GET'
            ? array_merge(['cb' => 'all'], $request->query->all())
            : ['cb' => 'all'];

        $post = $method === 'POST' ? $payload : [];
        if ($method === 'POST') {
            unset($post['dfxpath']);
        }

        $server = $request->server->all();
        $server['HTTP_X_FRONTEND_BRIDGE'] = '1';

        $subRequest = Request::create(
            $path,
            $method,
            $method === 'GET' ? $query : $post,
            $request->cookies->all(),
            $this->nestFilesUnderTermine($files),
            $server
        );

        if ($method === 'POST') {
            $subRequest->query->set('cb', 'all');
        }

        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $content = $response->getContent();

        return is_string($content) ? $content : 'Fehler';
    }

    private function nestFilesUnderTermine(array $files): array
    {
        return $files === [] ? [] : ['termine' => $files];
    }

    private function normalizeSubRequestPath(string $rawPath): ?string
    {
        $rawPath = trim($rawPath);
        if ($rawPath === '') {
            return null;
        }

        $parts = parse_url($rawPath);
        if ($parts === false) {
            return null;
        }

        foreach (['scheme', 'host', 'user', 'pass', 'port', 'fragment'] as $forbiddenPart) {
            if (array_key_exists($forbiddenPart, $parts)) {
                return null;
            }
        }

        $path = (string) ($parts['path'] ?? '');
        if ($path === '') {
            return null;
        }

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        foreach (self::ALLOWED_SUBREQUEST_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';

                return $path . $query;
            }
        }

        return null;
    }
}
