<?php

namespace App\Service\Styling;

use App\Entity\DfxKonf;
use App\Service\Support\ParameterBagService;
use ScssPhp\ScssPhp\Compiler as ScssCompiler;
use ScssPhp\ScssPhp\Exception\SassException;

final class OwnCssBuilderService
{
    public function __construct(
        private readonly ParameterBagService $parameterBagService,
    ) {
    }

    public function writeForKonf(DfxKonf $entity): string
    {
        $kid = $entity->getId();
        $projectDir = (string) $this->parameterBagService->get('kernel.project_dir');

        $farbe = $entity->getDfxFarbeEigen()
            ?? $entity->getDfxFarbe()
            ?? '#666666';

        $farbeRaster = $entity->getDfxFarbeRasterEigen()
            ?? $entity->getDfxFarbeRaster()
            ?? '#818181';

        $dfxCss = $entity->getDfxCss() ?? '';
        $fontType = $entity->getDfxFontType() ?? 'inherit';
        $fontSize = $entity->getDfxFontSize() ?? '1rem';
        $fontColor = $entity->getDfxFontColor() ?? '#333';

        $skelPath = $projectDir . '/web/scss/datefix_own_skel.scss';
        if (!is_readable($skelPath)) {
            return 'Fehler: Scss-Skelettdatei nicht lesbar: ' . $skelPath;
        }

        $skel = implode('', @file($skelPath));

        $arVar = [
            '#dfx-color#',
            '#dfx-grau#',
            '#dfx-css#',
            '#dfx-font-type#',
            '#dfx-font-size#',
            '#dfx-font-color#',
            '#dfx-border-radius#',
            '#dfx-border-radius-large#',
            '#dfx-border-radius-small#',
        ];

        if ($entity->getDfxRadius() === '1') {
            $arVal = [$farbe, $farbeRaster, $dfxCss, $fontType, $fontSize, $fontColor, '3px', '4px', '2px'];
        } else {
            $arVal = [$farbe, $farbeRaster, $dfxCss, $fontType, $fontSize, $fontColor, '0px', '0px', '0px'];
        }

        $ownScssDir = $projectDir . '/web/scss/own';
        $ownCssDir = $projectDir . '/web/css/own';

        if (!is_dir($ownScssDir) && !@mkdir($ownScssDir, 0775, true) && !is_dir($ownScssDir)) {
            return 'Fehler: Verzeichnis fuer Scss-Dateien konnte nicht erstellt werden: ' . $ownScssDir;
        }
        if (!is_dir($ownCssDir) && !@mkdir($ownCssDir, 0775, true) && !is_dir($ownCssDir)) {
            return 'Fehler: Verzeichnis fuer CSS-Dateien konnte nicht erstellt werden: ' . $ownCssDir;
        }

        $scssFilePath = $ownScssDir . '/' . $kid . '.scss';
        $skel = str_replace($arVar, $arVal, $skel);

        if (false === @file_put_contents($scssFilePath, $skel)) {
            return 'Fehler beim Schreiben der Scss-Datei: ' . $scssFilePath;
        }

        $msg = 'Datei ' . $scssFilePath . ' geschrieben<br />';

        $compiler = new ScssCompiler();
        $cssFilePath = $ownCssDir . '/' . $kid . '.css';
        try {
            $scss = file_get_contents($scssFilePath);
            $compiler->setImportPaths($projectDir . '/web/scss/');
            $css = $compiler->compileString((string) $scss)->getCss();
            file_put_contents($cssFilePath, $css);
            $msg .= 'Datei ' . $cssFilePath . ' geschrieben<br />';
        } catch (SassException $e) {
            $msg .= 'Fehler beim Schreiben der CSS-Datei: ' . $cssFilePath . '. SCSS compile error: ' . $e->getMessage();
            error_log('Fehler beim Schreiben der CSS-Datei: ' . $cssFilePath . '. SCSS compile error: ' . $e->getMessage());
        }

        return $msg;
    }
}
