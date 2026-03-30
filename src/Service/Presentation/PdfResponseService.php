<?php

namespace App\Service\Presentation;

use App\Service\Support\ParameterBagService;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Component\HttpFoundation\Response;
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
            $options = $this->parameterBagService->get('html2pdf');
            if (!is_array($options)) {
                $options = [];
            }

            $orientation = $options['orientation'] ?? 'P';
            $format = $options['format'] ?? 'A4';
            $lang = $options['lang'] ?? 'de';
            $unicode = $options['unicode'] ?? true;
            $encoding = $options['encoding'] ?? 'UTF-8';
            $margins = $options['margins'] ?? [10, 10, 10, 10];
            $defaultFont = $options['default_font'] ?? null;
            $displayMode = $options['display_mode'] ?? 'fullpage';

            $html2pdf = new Html2Pdf($orientation, $format, $lang, $unicode, $encoding, $margins);
            if ($defaultFont) {
                $html2pdf->setDefaultFont($defaultFont);
            }

            $html2pdf->pdf->SetDisplayMode($displayMode);
            $html2pdf->writeHTML($html);
            $safeFilename = $filename ?? 'document.pdf';
            $pdfContent = $html2pdf->output($safeFilename, 'S');

            return new Response(
                $pdfContent,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $safeFilename . '"',
                ]
            );
        } catch (Html2PdfException $e) {
            $content = $this->twig->render('pdf/error.html.twig', [
                'error' => $e->getMessage(),
            ]);

            return new Response($content, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
