<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxLocation;
use App\Entity\DfxNfxUser;
use App\Entity\DfxRegion;
use App\Entity\DfxTermine;
use App\Entity\DfxVeranstalter;
use App\Service\Support\ParameterBagService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class TerminExcelImportService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ParameterBagService $parameterBagService,
    ) {
    }

    public function import(UploadedFile $file, DfxNfxUser $user, DfxKonf $konf): string
    {
        if (!file_exists($file->getPathname())) {
            return 'Keine Upload-Datei gefunden';
        }

        $kid = $konf->getId();
        $imgPath = (string) $this->parameterBagService->get('kernel.project_dir') . '/web/images/dfx/' . $kid . '/';
        $inputFileType = IOFactory::identify($file->getPathname());
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($file->getPathname());

        $report = '<h2>Importbericht vom ' . date('d.m.Y H:i:s') . '</h2><br>';
        $report .= $this->importLocations($spreadsheet, $imgPath, $user, $konf);
        $report .= $this->importVeranstalter($spreadsheet, $imgPath, $user, $konf);
        $report .= $this->importTermine($spreadsheet, $imgPath, $user, $konf);

        return $report;
    }

    private function importLocations(Spreadsheet $spreadsheet, string $imgPath, DfxNfxUser $user, DfxKonf $konf): string
    {
        if (!$spreadsheet->getSheetByName('Locations')) {
            return 'Keine Tabelle mit Locations vorhanden<br />';
        }

        $worksheet = $spreadsheet->setActiveSheetIndexByName('Locations');
        $report = '<strong>Importierte Tabelle: ' . $worksheet->getTitle() . '</strong><br>';
        $images = $this->extractSheetImages($worksheet, $imgPath, $report);

        foreach ($worksheet->getRowIterator() as $row) {
            $i = $row->getRowIndex();
            if ($i <= 1) {
                continue;
            }

            $idLoc = $worksheet->getCell('A' . $i)->getValue();
            $loc = $this->em->getRepository(DfxLocation::class)->find($idLoc);
            if ($loc !== null) {
                $report .= 'LocationID ' . $idLoc . ' bereits vorhanden, Eintrag wird übersprungen<br />';
                continue;
            }

            $entity = new DfxLocation();
            $entity->setUser($user);
            $entity->setDatefix($konf);

            if ($worksheet->getCell('P' . $i)->getValue() > 0) {
                $idVer = $worksheet->getCell('P' . $i)->getValue();
                $ver = $this->em->getRepository(DfxVeranstalter::class)->find($idVer);
                if ($ver === null) {
                    $report .= 'Hinweis - Aktuell keinen Eintrag gefunden für VeranstalterID ' . $idVer . ' gefunden<br />';
                }
                $entity->setVeranstalter($ver);
            }

            $entity->setName($worksheet->getCell('B' . $i)->getValue());
            $entity->setStrasse($worksheet->getCell('C' . $i)->getValue());
            $entity->setNat($worksheet->getCell('D' . $i)->getValue());
            $entity->setPlz($worksheet->getCell('E' . $i)->getValue());
            $entity->setOrt($worksheet->getCell('F' . $i)->getValue());
            $entity->setLg($worksheet->getCell('G' . $i)->getValue());
            $entity->setBg($worksheet->getCell('H' . $i)->getValue());
            $entity->setTelefon($worksheet->getCell('I' . $i)->getValue());
            $entity->setFax($worksheet->getCell('J' . $i)->getValue());
            $entity->setWww($worksheet->getCell('K' . $i)->getValue());
            $entity->setAnsprech($worksheet->getCell('L' . $i)->getValue());
            $entity->setEmail($worksheet->getCell('M' . $i)->getValue());
            $worksheet->getStyle('N' . $i)->getAlignment()->setWrapText(true);
            $entity->setZusatz($worksheet->getCell('N' . $i)->getCalculatedValue());
            $entity->setImgLoc($images['O' . $i] ?? null);
            $entity->setDatumInput(new DateTime(date('Y-m-d H:i:s')));
            $entity->setId($idLoc);

            $this->persistWithAssignedId($entity);
            $report .= $entity->getName() . ' mit ID ' . $entity->getId() . ' gespeichert ... ok<br>';
        }

        return $report;
    }

    private function importVeranstalter(Spreadsheet $spreadsheet, string $imgPath, DfxNfxUser $user, DfxKonf $konf): string
    {
        if (!$spreadsheet->getSheetByName('Veranstalter')) {
            return 'Keine Tabelle mit Veranstaltern vorhanden<br />';
        }

        $worksheet = $spreadsheet->setActiveSheetIndexByName('Veranstalter');
        $report = '<br><strong>Importierte Tabelle: ' . $worksheet->getTitle() . '</strong><br>';
        $images = $this->extractSheetImages($worksheet, $imgPath, $report);

        foreach ($worksheet->getRowIterator() as $row) {
            $i = $row->getRowIndex();
            if ($i <= 1) {
                continue;
            }

            $idVer = $worksheet->getCell('A' . $i)->getValue();
            $ver = $this->em->getRepository(DfxVeranstalter::class)->find($idVer);
            if ($ver !== null) {
                $report .= 'VeranstalterID ' . $idVer . ' bereits vorhanden, Eintrag wird übersprungen<br />';
                continue;
            }

            $entity = new DfxVeranstalter();
            $entity->setUser($user);
            $entity->setDatefix($konf);
            if ($worksheet->getCell('P' . $i)->getValue() > 0) {
                $idLoc = $worksheet->getCell('P' . $i)->getValue();
                $loc = $this->em->getRepository(DfxLocation::class)->find($idLoc);
                if ($loc === null) {
                    $report .= 'Hinweis - Aktuell keinen Eintrag gefunden für LocationsID ' . $idLoc . ' gefunden<br />';
                }
                $entity->setLocation($loc);
            }

            $entity->setName($worksheet->getCell('B' . $i)->getValue());
            $entity->setStrasse($worksheet->getCell('C' . $i)->getValue());
            $entity->setNat($worksheet->getCell('D' . $i)->getValue());
            $entity->setPlz($worksheet->getCell('E' . $i)->getValue());
            $entity->setOrt($worksheet->getCell('F' . $i)->getValue());
            $entity->setLg($worksheet->getCell('G' . $i)->getValue());
            $entity->setBg($worksheet->getCell('H' . $i)->getValue());
            $entity->setTelefon($worksheet->getCell('I' . $i)->getValue());
            $entity->setFax($worksheet->getCell('J' . $i)->getValue());
            $entity->setWww($worksheet->getCell('K' . $i)->getValue());
            $entity->setAnsprech($worksheet->getCell('L' . $i)->getValue());
            $entity->setEmail($worksheet->getCell('M' . $i)->getValue());
            $worksheet->getStyle('N' . $i)->getAlignment()->setWrapText(true);
            $entity->setZusatz($worksheet->getCell('N' . $i)->getCalculatedValue());
            $entity->setImgVer($images['O' . $i] ?? null);
            $entity->setDatumInput(new DateTime(date('Y-m-d H:i:s')));
            $entity->setId($idVer);

            $this->persistWithAssignedId($entity);
            $report .= $entity->getName() . ' mit ID ' . $entity->getId() . ' gespeichert ... ok<br>';
        }

        return $report;
    }

    private function importTermine(Spreadsheet $spreadsheet, string $imgPath, DfxNfxUser $user, DfxKonf $konf): string
    {
        $worksheet = $spreadsheet->setActiveSheetIndex(0);
        if ($worksheet === null) {
            return '';
        }

        $report = '<br><strong>Importierte Tabelle: ' . $worksheet->getTitle() . '</strong><br>';
        $images = $this->extractSheetImages($worksheet, $imgPath, $report);
        $batchSize = 20;
        $count = 0;

        foreach ($worksheet->getRowIterator() as $row) {
            $i = $row->getRowIndex();
            if ($i <= 1) {
                continue;
            }

            $entity = new DfxTermine();
            $entity->setUser($user);
            $entity->setDatefix($konf);
            $err = false;

            [$lokal, $lokalStrasse, $nat, $plz, $ort, $bg, $lg] = $this->resolveLocationFields($worksheet, $i, $entity, $report);
            [$veranstalter, $email] = $this->resolveVeranstalterFields($worksheet, $i, $entity, $report);
            $this->resolveRegion($worksheet, $i, $entity, $report);

            $strDatumVon = NumberFormat::toFormattedString($worksheet->getCell('A' . $i)->getCalculatedValue(), 'dd.mm.yyyy');
            $strDatum = NumberFormat::toFormattedString($worksheet->getCell('B' . $i)->getCalculatedValue(), 'dd.mm.yyyy');

            if (empty($worksheet->getCell('E' . $i)->getValue())) {
                $report .= '<strong>Fehler! Titelfeld ist leer ... Datensatz aus Zeile ' . $i . ' nicht gespeichert</strong><br>';
                continue;
            }

            if (empty($strDatumVon) && empty($strDatum)) {
                $report .= '<strong>Fehler ! ' . $worksheet->getCell('E' . $i)->getValue() . ' / ' . $strDatumVon . ' / ' . $strDatum . ' ... Datensatz aus Zeile ' . $i . ' nicht gespeichert, weil Datumsangaben fehlen</strong><br>';
                $err = true;
            } elseif (empty($strDatum)) {
                $strDatum = $strDatumVon;
            } elseif (empty($strDatumVon)) {
                $strDatumVon = $strDatum;
            }

            $entity->setDatumVon(new DateTime($strDatumVon));
            $entity->setDatum(new DateTime($strDatum));

            $strZeit = $worksheet->getCell('C' . $i)->getFormattedValue();
            $strZeitBis = $worksheet->getCell('D' . $i)->getFormattedValue();
            $entity->setZeit($strZeit !== '' ? new DateTime($strZeit) : null);
            $entity->setZeitBis($strZeitBis !== '' ? new DateTime($strZeitBis) : null);

            $entity->setTitel($worksheet->getCell('E' . $i)->getValue());
            $entity->setRubrik(array_map('trim', explode('#', (string) $worksheet->getCell('F' . $i)->getValue())));
            $worksheet->getStyle('G' . $i)->getAlignment()->setWrapText(true);
            $entity->setLead($worksheet->getCell('G' . $i)->getCalculatedValue());
            $worksheet->getStyle('H' . $i)->getAlignment()->setWrapText(true);
            $entity->setBeschreibung($worksheet->getCell('H' . $i)->getCalculatedValue());
            $entity->setEintritt($worksheet->getCell('I' . $i)->getFormattedValue());
            $entity->setLokal($lokal);
            $entity->setNat($nat);
            $entity->setPlz($plz);
            $entity->setOrt($ort);
            $entity->setLokalStrasse($lokalStrasse);
            $entity->setVeranstalter($veranstalter);
            $entity->setSubtitel($worksheet->getCell('P' . $i)->getCalculatedValue());
            $entity->setLink($worksheet->getCell('Q' . $i)->getCalculatedValue());
            $entity->setLinktext($worksheet->getCell('R' . $i)->getValue());
            $entity->setMail($email);
            $entity->setMailTyp($worksheet->getCell('T' . $i)->getValue());
            $entity->setPlaetzeGesamt((int) $worksheet->getCell('U' . $i)->getValue());
            $entity->setPlaetzeAktuell((int) $worksheet->getCell('V' . $i)->getValue());
            $entity->setTicketlink($worksheet->getCell('W' . $i)->getValue());
            $entity->setTicketlinktext($worksheet->getCell('X' . $i)->getValue());
            $entity->setVideo($worksheet->getCell('Y' . $i)->getValue());
            $entity->setBg($bg);
            $entity->setLg($lg);
            $entity->setImg($images['AB' . $i] ?? null);
            $entity->setPdf($worksheet->getCell('AC' . $i)->getValue());
            $entity->setArchiv((int) $worksheet->getCell('AD' . $i)->getValue());
            $entity->setPub((int) $worksheet->getCell('AE' . $i)->getValue());
            $entity->setPubGroup((int) $worksheet->getCell('AI' . $i)->getValue());
            $entity->setPubMeta((int) $worksheet->getCell('AJ' . $i)->getValue());
            $entity->setZielgruppe(array_map('trim', explode('#', (string) $worksheet->getCell('AK' . $i)->getValue())));
            $entity->setImgcopycheck((int) $worksheet->getCell('AL' . $i)->getValue());
            $entity->setImgcopyright($worksheet->getCell('AM' . $i)->getValue());
            $entity->setImgtext($worksheet->getCell('AN' . $i)->getValue());
            $entity->setOnline((int) $worksheet->getCell('AO' . $i)->getValue());
            $entity->setDatumInput(new DateTime(date('Y-m-d H:i:s')));
            $entity->setAutor($user->getNameLang());

            if ($err === false) {
                $report .= $entity->getTitel() . ' / ' . $entity->getDatumVon()->format('d.m.Y') . ' / ' . $strZeit . ' ... ok<br>';
                $this->em->persist($entity);
                $count++;
                if (($count % $batchSize) === 0) {
                    $this->em->flush();
                }
            }
        }

        $this->em->flush();

        return $report;
    }

    /**
     * @param-out string $report
     * @return array<string,string>
     */
    private function extractSheetImages($worksheet, string $imgPath, string &$report): array
    {
        $images = [];
        foreach ($worksheet->getDrawingCollection() as $drawing) {
            if ($drawing instanceof MemoryDrawing) {
                ob_start();
                call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                $imageContents = (string) ob_get_contents();
                ob_end_clean();
                $extension = match ($drawing->getMimeType()) {
                    MemoryDrawing::MIMETYPE_PNG => 'png',
                    MemoryDrawing::MIMETYPE_GIF => 'gif',
                    default => 'jpg',
                };
            } else {
                $zipReader = fopen($drawing->getPath(), 'r');
                $imageContents = '';
                while (!feof($zipReader)) {
                    $imageContents .= fread($zipReader, 1024);
                }
                fclose($zipReader);
                $extension = $drawing->getExtension();
            }

            $cellID = $drawing->getCoordinates();
            $imgCode = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10);
            $fileName = $cellID . '_' . $imgCode . '.' . $extension;
            $images[$cellID] = $fileName;
            file_put_contents($imgPath . $fileName, $imageContents);
            $report .= 'Bild ' . $fileName . ' gespeichert ... ok<br>';
        }

        return $images;
    }

    private function persistWithAssignedId(object $entity): void
    {
        $this->em->persist($entity);
        $metadata = $this->em->getClassMetaData($entity::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
        $this->em->flush();
    }

    /**
     * @param-out string $report
     * @return array{0:string,1:string,2:string,3:string,4:string,5:mixed,6:mixed}
     */
    private function resolveLocationFields($worksheet, int $row, DfxTermine $entity, string &$report): array
    {
        if ($worksheet->getCell('AF' . $row)->getValue() > 0) {
            $idLoc = $worksheet->getCell('AF' . $row)->getValue();
            $loc = $this->em->getRepository(DfxLocation::class)->find($idLoc);
            if ($loc === null) {
                $report .= 'Keinen Account gefunden für LocationID ' . $idLoc . '<br />';
                return [
                    (string) $worksheet->getCell('J' . $row)->getValue(),
                    (string) $worksheet->getCell('N' . $row)->getValue(),
                    (string) $worksheet->getCell('K' . $row)->getValue(),
                    (string) $worksheet->getCell('L' . $row)->getValue(),
                    (string) $worksheet->getCell('M' . $row)->getValue(),
                    $worksheet->getCell('Z' . $row)->getValue(),
                    $worksheet->getCell('AA' . $row)->getValue(),
                ];
            }

            $entity->setIdLocation($loc);
            return [
                $loc->getName() ?: (string) $worksheet->getCell('J' . $row)->getValue(),
                $loc->getStrasse() ?: (string) $worksheet->getCell('N' . $row)->getValue(),
                $loc->getNat() ?: (string) $worksheet->getCell('K' . $row)->getValue(),
                $loc->getPlz() ?: (string) $worksheet->getCell('L' . $row)->getValue(),
                $loc->getOrt() ?: (string) $worksheet->getCell('M' . $row)->getValue(),
                $loc->getBg() ?: $worksheet->getCell('Z' . $row)->getValue(),
                $loc->getLg() ?: $worksheet->getCell('AA' . $row)->getValue(),
            ];
        }

        return [
            (string) $worksheet->getCell('J' . $row)->getValue(),
            (string) $worksheet->getCell('N' . $row)->getValue(),
            (string) $worksheet->getCell('K' . $row)->getValue(),
            (string) $worksheet->getCell('L' . $row)->getValue(),
            (string) $worksheet->getCell('M' . $row)->getValue(),
            $worksheet->getCell('Z' . $row)->getValue(),
            $worksheet->getCell('AA' . $row)->getValue(),
        ];
    }

    /**
     * @param-out string $report
     * @return array{0:string,1:string}
     */
    private function resolveVeranstalterFields($worksheet, int $row, DfxTermine $entity, string &$report): array
    {
        if ($worksheet->getCell('AG' . $row)->getValue() > 0) {
            $idVer = $worksheet->getCell('AG' . $row)->getValue();
            $ver = $this->em->getRepository(DfxVeranstalter::class)->find($idVer);
            if ($ver === null) {
                $report .= 'Keinen Eintrag gefunden für VeranstalterID ' . $idVer . '<br />';
                return [
                    (string) $worksheet->getCell('O' . $row)->getValue(),
                    (string) $worksheet->getCell('S' . $row)->getValue(),
                ];
            }

            $entity->setIdVeranstalter($ver);
            return [$ver->getName(), $ver->getEmail()];
        }

        return [
            (string) $worksheet->getCell('O' . $row)->getValue(),
            (string) $worksheet->getCell('S' . $row)->getValue(),
        ];
    }

    private function resolveRegion($worksheet, int $row, DfxTermine $entity, string &$report): void
    {
        if ($worksheet->getCell('AH' . $row)->getValue() <= 0) {
            return;
        }

        $idRegion = $worksheet->getCell('AH' . $row)->getValue();
        $reg = $this->em->getRepository(DfxRegion::class)->find($idRegion);
        if ($reg === null) {
            $report .= 'Keinen Eintrag gefunden für RegionID ' . $idRegion . '<br />';
            return;
        }

        $entity->setRegion($reg);
    }
}
