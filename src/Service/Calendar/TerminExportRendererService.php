<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxLocation;
use App\Entity\DfxTermine;
use App\Entity\DfxVeranstalter;
use App\Service\Presentation\TemplatePathResolver;
use App\Service\Support\ParameterBagService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class TerminExportRendererService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Environment $twig,
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly ParameterBagService $parameterBagService,
    ) {
    }

    /**
     * @param list<DfxTermine> $entities
     */
    public function render(DfxKonf $konf, array $entities, string $exportTyp, bool $stripTags, string $header): Response|array
    {
        return match ($exportTyp) {
            'newsletter' => $this->renderNewsletter($konf, $entities),
            'xml' => $this->renderXml($konf, $entities),
            default => $this->renderExcel($konf, $entities, $exportTyp, $stripTags, $header),
        };
    }

    /**
     * @param list<DfxTermine> $entities
     * @return array{msg: string}
     */
    private function renderNewsletter(DfxKonf $konf, array $entities): array
    {
        $tpl = $this->templatePathResolver->resolve('Kalender', 'newsletter.html.twig', $konf);
        $html = $this->twig->render($tpl, ['termine' => $entities, 'konf' => $konf]);

        return [
            'msg' => $html . '<br><br><textarea class="form-control" rows="50">' . trim(htmlentities($html)) . '</textarea>',
        ];
    }

    /**
     * @param list<DfxTermine> $entities
     */
    private function renderXml(DfxKonf $konf, array $entities): Response
    {
        $tpl = $this->templatePathResolver->resolve('Export', 'standard.xml.twig', $konf);
        $xml = $this->twig->render($tpl, ['termine' => $entities, 'konf' => $konf]);

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export_' . date('Y_m_d') . '.xml"');

        return $response;
    }

    /**
     * @param list<DfxTermine> $entities
     * @return array{msg: string}
     */
    private function renderExcel(DfxKonf $konf, array $entities, string $exportTyp, bool $stripTags, string $header): array
    {
        $kid = $konf->getId();
        $echo = "<h4>Datefix Datenexport Veranstaltungskalender ID " . $kid . ' ' . $header . '</h4>';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator("Datefix Veranstaltungskalender ID " . $kid)
            ->setTitle("Datenexport Veranstaltungskalender ID " . $kid . ' ' . $header)
            ->setSubject("Datenexport")
            ->setDescription("Datefix Datenexport Veranstaltungskalender ID " . $kid . ' ' . $header)
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Datefix Export");

        $spreadsheet->setActiveSheetIndex(0)
            ->setTitle('Termine')
            ->setCellValue('A1', 'datumVon')
            ->setCellValue('B1', 'datumBis')
            ->setCellValue('C1', 'zeit')
            ->setCellValue('D1', 'zeitBis')
            ->setCellValue('E1', 'titel')
            ->setCellValue('F1', 'rubrik')
            ->setCellValue('G1', 'lead')
            ->setCellValue('H1', 'beschreibung')
            ->setCellValue('I1', 'eintritt')
            ->setCellValue('J1', 'lokal')
            ->setCellValue('K1', 'nat')
            ->setCellValue('L1', 'plz')
            ->setCellValue('M1', 'ort')
            ->setCellValue('N1', 'lokalStrasse')
            ->setCellValue('O1', 'veranstalter')
            ->setCellValue('P1', 'subtitel');

        if ($exportTyp !== 'base') {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('Q1', 'link')
                ->setCellValue('R1', 'linktext')
                ->setCellValue('S1', 'mail')
                ->setCellValue('T1', 'mailTyp')
                ->setCellValue('U1', 'plaetzeGesamt')
                ->setCellValue('V1', 'plaetzeAktuell')
                ->setCellValue('W1', 'ticketlink')
                ->setCellValue('X1', 'ticketlinktext')
                ->setCellValue('Y1', 'video')
                ->setCellValue('Z1', 'breitengrad')
                ->setCellValue('AA1', 'längengrad')
                ->setCellValue('AB1', 'bild')
                ->setCellValue('AC1', 'pdf');
        }

        if ($exportTyp === 'all') {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('AD1', 'archivieren (ja = 1)')
                ->setCellValue('AE1', 'veröffentlichen (ja = 1)')
                ->setCellValue('AF1', 'idLocation')
                ->setCellValue('AG1', 'idVeranstalter')
                ->setCellValue('AH1', 'idRegion')
                ->setCellValue('AI1', 'pubGroup')
                ->setCellValue('AJ1', 'pubMeta')
                ->setCellValue('AK1', 'zielgruppe')
                ->setCellValue('AL1', 'imgcopycheck')
                ->setCellValue('AM1', 'imgcopyright')
                ->setCellValue('AN1', 'imgtext')
                ->setCellValue('AO1', 'online');
        }

        $echo .= 'Schreibe Spaltentitel<br>';
        $z = 2;
        $hasLocations = false;
        $hasVeranstalter = false;

        foreach ($entities as $entity) {
            $echo .= 'Schreibe Zeile ' . $z . ': ' . $entity->getTitel() . '<br>';
            $imgPath = $this->imagePath($entity->getDatefix()->getId());
            $beschreibung = $this->normalizeBeschreibung($entity, $stripTags);

            if ($entity->getDatumVon() !== null) {
                $spreadsheet->setActiveSheetIndex(0)->setCellValue('A' . $z, $entity->getDatumVon()->format('d.m.Y'));
                $spreadsheet->setActiveSheetIndex(0)->getStyle('A' . $z)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            }

            if ($entity->getDatum() !== null) {
                $spreadsheet->setActiveSheetIndex(0)->setCellValue('B' . $z, $entity->getDatum()->format('d.m.Y'));
                $spreadsheet->setActiveSheetIndex(0)->getStyle('B' . $z)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            }

            if ($entity->getZeit() !== null) {
                $spreadsheet->setActiveSheetIndex(0)->setCellValue('C' . $z, $entity->getZeit()->format('H:i'));
                $spreadsheet->setActiveSheetIndex(0)->getStyle('C' . $z)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_TIME3);
            }

            if ($entity->getZeitBis() !== null) {
                $spreadsheet->setActiveSheetIndex(0)->setCellValue('D' . $z, $entity->getZeitBis()->format('H:i'));
                $spreadsheet->setActiveSheetIndex(0)->getStyle('D' . $z)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_TIME3);
            }

            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('E' . $z, $entity->getTitel())
                ->setCellValue('F' . $z, implode('#', $entity->getRubrik() ?? []))
                ->setCellValue('G' . $z, $entity->getLead())
                ->setCellValue('H' . $z, $beschreibung)
                ->setCellValue('I' . $z, $entity->getEintritt())
                ->setCellValue('J' . $z, $entity->getLokal())
                ->setCellValue('K' . $z, $entity->getNat())
                ->setCellValue('L' . $z, $entity->getPlz())
                ->setCellValue('M' . $z, $entity->getOrt())
                ->setCellValue('N' . $z, $entity->getLokalStrasse())
                ->setCellValue('O' . $z, $entity->getVeranstalter())
                ->setCellValue('P' . $z, $entity->getSubtitel());

            if ($exportTyp !== 'base') {
                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('Q' . $z, $entity->getLink())
                    ->setCellValue('R' . $z, $entity->getLinktext())
                    ->setCellValue('S' . $z, $entity->getMail())
                    ->setCellValue('T' . $z, $entity->getMailTyp())
                    ->setCellValue('U' . $z, $entity->getPlaetzeGesamt())
                    ->setCellValue('V' . $z, $entity->getPlaetzeAktuell())
                    ->setCellValue('W' . $z, $entity->getTicketlink())
                    ->setCellValue('X' . $z, $entity->getTicketlinktext())
                    ->setCellValue('Y' . $z, $entity->getVideo())
                    ->setCellValue('Z' . $z, $entity->getBg())
                    ->setCellValue('AA' . $z, $entity->getLg())
                    ->setCellValue('AC' . $z, $entity->getPdf());
            }

            if ($exportTyp === 'all') {
                if ($entity->getIdLocation() !== null) {
                    $hasLocations = true;
                }
                if ($entity->getIdVeranstalter() !== null) {
                    $hasVeranstalter = true;
                }

                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('AD' . $z, $entity->getArchiv() == 1 ? 1 : 0)
                    ->setCellValue('AE' . $z, $entity->getPub() == 1 ? 1 : 0)
                    ->setCellValue('AF' . $z, $entity->getIdLocation() !== null ? $entity->getIdLocation()->getId() : '')
                    ->setCellValue('AG' . $z, $entity->getIdVeranstalter() !== null ? $entity->getIdVeranstalter()->getId() : '')
                    ->setCellValue('AH' . $z, $entity->getRegion() !== null ? $entity->getRegion()->getId() : '')
                    ->setCellValue('AI' . $z, $entity->getPubGroup() == 1 ? 1 : 0)
                    ->setCellValue('AJ' . $z, $entity->getPubMeta() == 1 ? 1 : 0)
                    ->setCellValue('AK' . $z, implode('#', $entity->getZielgruppe() ?? []))
                    ->setCellValue('AL' . $z, $entity->getImgcopycheck() == 1 ? 1 : 0)
                    ->setCellValue('AM' . $z, $entity->getImgcopyright())
                    ->setCellValue('AN' . $z, $entity->getImgtext())
                    ->setCellValue('AO' . $z, $entity->getOnline() == 1 ? 1 : 0);
            }

            if ($exportTyp !== 'base' && is_file($imgPath . $entity->getImg())) {
                $this->attachImage($spreadsheet, 'AB', $z, $imgPath . $entity->getImg(), $entity->getImg(), 'Bild zu ' . $entity->getTitel());
                $echo .= 'Lese Bild ' . $imgPath . $entity->getImg() . '<br>';
            }

            $z++;
        }

        if ($hasLocations) {
            $echo .= $this->addLocationSheet($spreadsheet, $konf);
        }
        if ($hasVeranstalter) {
            $echo .= $this->addVeranstalterSheet($spreadsheet, $konf, $hasLocations ? 2 : 1);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);

        try {
            $file = 'veranstaltungen_' . date('Y_m_d') . '_' . str_replace(' ', '_', $header) . '.xlsx';
            $exportDir = $this->projectDir() . '/web/exports/' . $kid;
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0777, true);
            }
            $writer->save($exportDir . '/' . $file);
            $echo .= '<br>Excel-Datei <a href="/exports/' . $kid . '/' . $file . '">' . $file . '</a> erfolgreich erzeugt';
        } catch (Exception $e) {
            $echo .= '<br>Fehler bei Erzeugung Excel-Datei: ' . $e->getMessage();
        }

        return ['msg' => $echo];
    }

    private function normalizeBeschreibung(DfxTermine $entity, bool $stripTags): ?string
    {
        if (!$stripTags) {
            return $entity->getBeschreibung();
        }

        $beschreibung = str_replace(["<br />", "</p>"], [chr(10), chr(10) . chr(10)], $entity->getBeschreibung());
        $beschreibung = strip_tags($beschreibung);

        return html_entity_decode($beschreibung);
    }

    private function addLocationSheet(Spreadsheet $spreadsheet, DfxKonf $konf): string
    {
        $echo = 'Locations gefunden<br>';
        $sheet = $spreadsheet->createSheet();
        $spreadsheet->setActiveSheetIndex($sheet->getParent()->getIndex($sheet));
        $spreadsheet->getActiveSheet()->setTitle('Locations');
        $entities = $this->em->getRepository(DfxLocation::class)->findBy(['datefix' => $konf->getId()]);

        $spreadsheet->setActiveSheetIndex($sheet->getParent()->getIndex($sheet))
            ->setCellValue('A1', 'id')
            ->setCellValue('B1', 'name')
            ->setCellValue('C1', 'strasse')
            ->setCellValue('D1', 'nat')
            ->setCellValue('E1', 'plz')
            ->setCellValue('F1', 'ort')
            ->setCellValue('G1', 'lg')
            ->setCellValue('H1', 'bg')
            ->setCellValue('I1', 'telefon')
            ->setCellValue('J1', 'fax')
            ->setCellValue('K1', 'www')
            ->setCellValue('L1', 'ansprech')
            ->setCellValue('M1', 'email')
            ->setCellValue('N1', 'zusatz')
            ->setCellValue('O1', 'imgLoc')
            ->setCellValue('P1', 'idVeranstalter');

        $z = 2;
        foreach ($entities as $entity) {
            $imgPath = $this->imagePath($entity->getDatefix()->getId());
            $spreadsheet->setActiveSheetIndex($sheet->getParent()->getIndex($sheet))
                ->setCellValue('A' . $z, $entity->getId())
                ->setCellValue('B' . $z, $entity->getName())
                ->setCellValue('C' . $z, $entity->getStrasse())
                ->setCellValue('D' . $z, $entity->getNat())
                ->setCellValue('E' . $z, $entity->getPlz())
                ->setCellValue('F' . $z, $entity->getOrt())
                ->setCellValue('G' . $z, $entity->getLg())
                ->setCellValue('H' . $z, $entity->getBg())
                ->setCellValue('I' . $z, $entity->getTelefon())
                ->setCellValue('J' . $z, $entity->getFax())
                ->setCellValue('K' . $z, $entity->getWww())
                ->setCellValue('L' . $z, $entity->getansprech())
                ->setCellValue('M' . $z, $entity->getemail())
                ->setCellValue('N' . $z, $entity->getZusatz())
                ->setCellValue('P' . $z, $entity->getVeranstalter()?->getId());

            $echo .= 'Schreibe Location ' . $z . ' : ' . $entity->getName() . '<br>';
            if (is_file($imgPath . $entity->getImgLoc())) {
                $this->attachImage($spreadsheet, 'O', $z, $imgPath . $entity->getImgLoc(), $entity->getImgLoc(), 'Bild zu ' . $entity->getName());
                $echo .= 'Lese Bild ' . $imgPath . $entity->getImgLoc() . '<br>';
            }

            $z++;
        }

        return $echo;
    }

    private function addVeranstalterSheet(Spreadsheet $spreadsheet, DfxKonf $konf, int $sheetIndex): string
    {
        $echo = 'Veranstalter gefunden<br>';
        $sheet = $spreadsheet->createSheet();
        $spreadsheet->setActiveSheetIndex($sheetIndex);
        $spreadsheet->getActiveSheet()->setTitle('Veranstalter');
        $entities = $this->em->getRepository(DfxVeranstalter::class)->findBy(['datefix' => $konf->getId()]);

        $spreadsheet->setActiveSheetIndex($sheetIndex)
            ->setCellValue('A1', 'id')
            ->setCellValue('B1', 'name')
            ->setCellValue('C1', 'strasse')
            ->setCellValue('D1', 'nat')
            ->setCellValue('E1', 'plz')
            ->setCellValue('F1', 'ort')
            ->setCellValue('G1', 'lg')
            ->setCellValue('H1', 'bg')
            ->setCellValue('I1', 'telefon')
            ->setCellValue('J1', 'fax')
            ->setCellValue('K1', 'www')
            ->setCellValue('L1', 'ansprech')
            ->setCellValue('M1', 'email')
            ->setCellValue('N1', 'zusatz')
            ->setCellValue('O1', 'imgVer')
            ->setCellValue('P1', 'idLocation');

        $z = 2;
        foreach ($entities as $entity) {
            $imgPath = $this->imagePath($entity->getDatefix()->getId());
            $spreadsheet->setActiveSheetIndex($sheetIndex)
                ->setCellValue('A' . $z, $entity->getId())
                ->setCellValue('B' . $z, $entity->getName())
                ->setCellValue('C' . $z, $entity->getStrasse())
                ->setCellValue('D' . $z, $entity->getNat())
                ->setCellValue('E' . $z, $entity->getPlz())
                ->setCellValue('F' . $z, $entity->getOrt())
                ->setCellValue('G' . $z, $entity->getLg())
                ->setCellValue('H' . $z, $entity->getBg())
                ->setCellValue('I' . $z, $entity->getTelefon())
                ->setCellValue('J' . $z, $entity->getFax())
                ->setCellValue('K' . $z, $entity->getWww())
                ->setCellValue('L' . $z, $entity->getAnsprech())
                ->setCellValue('M' . $z, $entity->getEmail())
                ->setCellValue('N' . $z, $entity->getZusatz())
                ->setCellValue('P' . $z, $entity->getLocation()?->getId());

            $echo .= 'Schreibe Veranstalter ' . $z . ': ' . $entity->getName() . '<br>';
            if (is_file($imgPath . $entity->getImgVer())) {
                $this->attachImage($spreadsheet, 'O', $z, $imgPath . $entity->getImgVer(), $entity->getImgVer(), 'Bild zu ' . $entity->getName());
                $echo .= 'Lese Bild ' . $imgPath . $entity->getImgVer() . '<br>';
            }

            $z++;
        }

        return $echo;
    }

    private function attachImage(Spreadsheet $spreadsheet, string $column, int $row, string $path, string $name, string $description): void
    {
        $activeSheet = $spreadsheet->getActiveSheet();
        $drawing = new Drawing();
        $drawing->setDescription($description);
        $drawing->setName($name);
        $drawing->setPath($path);
        $drawing->setHeight(60);
        $drawing->setOffsetY(10);
        $drawing->setOffsetX(10);
        $drawing->setCoordinates($column . $row);
        $drawing->setWorksheet($activeSheet);

        $activeSheet->getRowDimension($row)->setRowHeight(60);
        $activeSheet->getColumnDimension($column)->setWidth(16);
    }

    private function imagePath(int $kid): string
    {
        return $this->projectDir() . '/web/images/dfx/' . $kid . '/';
    }

    private function projectDir(): string
    {
        return (string) $this->parameterBagService->get('kernel.project_dir');
    }
}
