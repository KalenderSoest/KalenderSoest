<?php

namespace App\Service\Frontend;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use App\Entity\DfxTermine;
use CURLFile;
use Doctrine\ORM\EntityManagerInterface;
use finfo;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FrontendBridgeService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        #[Autowire('%datefix_url%')]
        private readonly string $datefixUrl,
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

        $target = $this->resolveTarget($konf, $payload);
        $bridgeUrl = $this->buildBridgeUrl($target['path'], $method, $request);
        $content = $this->fetchContent($bridgeUrl, $method, $payload);

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
            return ['payload' => [], 'error' => null];
        }

        $payload = [];
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
                $prefixedName = substr(md5((string) time()), 0, 10) . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $payload[$field] = new CURLFile($file->getPathname(), $mimeType ?: 'application/octet-stream', $prefixedName);
            }
        }

        return ['payload' => $payload, 'error' => $error !== '' ? $error : null];
    }

    private function resolveTarget(DfxKonf $konf, array $payload): array
    {
        $kid = $konf->getId();

        if (isset($payload['dfxpath'])) {
            return ['path' => (string) $payload['dfxpath'], 'termin' => null, 'artikel' => null];
        }

        if (isset($payload['dfxid'])) {
            return [
                'path' => '/js/kalender/' . $kid . '/detail/' . $payload['dfxid'],
                'termin' => $this->em->getRepository(DfxTermine::class)->find($payload['dfxid']),
                'artikel' => null,
            ];
        }

        if (isset($payload['nfx'])) {
            return ['path' => '/js/news/' . $kid, 'termin' => null, 'artikel' => null];
        }

        if (isset($payload['nfxid'])) {
            return [
                'path' => '/js/news/' . $kid . '/detail/' . $payload['nfxid'],
                'termin' => null,
                'artikel' => $this->em->getRepository(DfxNews::class)->find($payload['nfxid']),
            ];
        }

        return ['path' => '/js/kalender/' . $kid, 'termin' => null, 'artikel' => null];
    }

    private function buildBridgeUrl(string $path, string $method, Request $request): string
    {
        $query = $method === 'GET'
            ? 'cb=all' . (($request->getQueryString() ?? '') !== '' ? '&' . $request->getQueryString() : '')
            : 'cb=all';

        return $this->datefixUrl . $path . '?' . $query;
    }

    private function fetchContent(string $bridgeUrl, string $method, array $payload): string
    {
        $handle = curl_init($bridgeUrl);
        if ($handle === false) {
            return 'Fehler';
        }

        if ($method === 'POST') {
            unset($payload['dfxpath']);
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt(
                $handle,
                CURLOPT_POSTFIELDS,
                $this->containsCurlFile($payload) ? $this->flattenMultipartPayload($payload) : http_build_query($payload)
            );
        }

        curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handle, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_HTTPHEADER, ['User-Agent: Datefix', 'Connection: Close']);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, 1);

        $content = curl_exec($handle);
        curl_close($handle);

        return is_string($content) ? $content : 'Fehler';
    }

    private function containsCurlFile(array $payload): bool
    {
        $hasFile = false;
        array_walk_recursive($payload, static function (mixed $value) use (&$hasFile): void {
            if ($value instanceof CURLFile) {
                $hasFile = true;
            }
        });

        return $hasFile;
    }

    /**
     * @return array<string, mixed>
     */
    private function flattenMultipartPayload(array $payload, string $prefix = ''): array
    {
        $flat = [];

        foreach ($payload as $key => $value) {
            $field = $prefix === '' ? (string) $key : $prefix . '[' . $key . ']';

            if (is_array($value)) {
                $flat += $this->flattenMultipartPayload($value, $field);
                continue;
            }

            $flat[$field] = $value;
        }

        return $flat;
    }
}
