<?php

namespace App\Service\Presentation;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Service\Support\ParameterBagService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Twig\Environment;

final class PdfResponseService
{
    public function __construct(
        private readonly ParameterBagService $parameterBagService,
        private readonly Environment $twig,
    ) {
    }

    public function render(string $html, ?string $filename = null): Response
    {
        try {
            $options = $this->parameterBagService->get('dompdf');
            if (!is_array($options)) {
                $options = [];
            }

            $projectDir = (string) $this->parameterBagService->get('kernel.project_dir');
            $orientation = $options['orientation'] ?? 'portrait';
            $format = $options['format'] ?? 'A4';
            $encoding = $options['encoding'] ?? 'UTF-8';
            $defaultFont = $options['default_font'] ?? null;
            $margins = $options['margins'] ?? [10, 10, 10, 10];
            $chroot = $options['chroot'] ?? [$projectDir];
            $isRemoteEnabled = (bool) ($options['remote_enabled'] ?? true);

            $dompdfOptions = new Options();
            $dompdfOptions->setIsRemoteEnabled($isRemoteEnabled);
            $dompdfOptions->setIsHtml5ParserEnabled(true);
            $dompdfOptions->setChroot($chroot);

            if (is_string($defaultFont) && $defaultFont !== '') {
                $dompdfOptions->setDefaultFont($defaultFont);
            }

            $dompdf = new Dompdf($dompdfOptions);
            $dompdf->setPaper($format, $this->normalizeOrientation($orientation));
            $dompdf->loadHtml($this->applyPageMargins($html, $margins), $encoding);
            $dompdf->render();

            $safeFilename = $filename ?? 'document.pdf';
            $pdfContent = $dompdf->output();

            return new Response(
                $pdfContent,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $safeFilename . '"',
                ]
            );
        } catch (Throwable $e) {
            $content = $this->twig->render('pdf/error.html.twig', [
                'error' => $e->getMessage(),
            ]);

            return new Response($content, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function normalizeOrientation(string $orientation): string
    {
        return strtoupper($orientation) === 'L' ? 'landscape' : 'portrait';
    }

    private function applyPageMargins(string $html, mixed $margins): string
    {
        if (!is_array($margins) || count($margins) !== 4) {
            return $html;
        }

        [$top, $right, $bottom, $left] = array_map(
            static fn (mixed $margin): int => max(0, (int) $margin),
            array_values($margins)
        );

        $pageStyle = sprintf(
            '<style>@page { margin: %dmm %dmm %dmm %dmm; }</style>',
            $top,
            $right,
            $bottom,
            $left
        );

        return $pageStyle . $html;
    }
}
