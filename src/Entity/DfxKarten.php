<?php

namespace App\Entity;

use DateTime;

/**
 * DfxKarten
 */
class DfxKarten
{
    /**
     * @var integer
     */
    private int $anzahl;

    /**
     * @var DateTime
     */
    private DateTime $datum;

    /**
     * @var integer
     */
    private int $id;

    private ?DfxKonf $datefix = null;

    private ?DfxTermine $termin = null;

    private ?DfxKartenKat $kategorie = null;

    private ?DfxKartenOrder $order = null;

    public function setAnzahl(int $anzahl): static
    {
        $this->anzahl = $anzahl;

        return $this;
    }

    public function getAnzahl(): ?int
    {
        return $this->anzahl;
    }

    public function setDatum(DateTime $datum): static
    {
        $this->datum = $datum;

        return $this;
    }

    public function getDatum(): ?DateTime
    {
        return $this->datum;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setDatefix(?DfxKonf $datefix): static
    {
        $this->datefix = $datefix;

        return $this;
    }

    public function getDatefix(): ?DfxKonf
    {
        return $this->datefix;
    }

    public function setTermin(?DfxTermine $termin): static
    {
        $this->termin = $termin;

        return $this;
    }

    public function getTermin(): ?DfxTermine
    {
        return $this->termin;
    }

    public function setKategorie(?DfxKartenKat $kategorie): static
    {
        $this->kategorie = $kategorie;

        return $this;
    }

    public function getKategorie(): ?DfxKartenKat
    {
        return $this->kategorie;
    }

    public function setOrder(?DfxKartenOrder $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getOrder(): ?DfxKartenOrder
    {
        return $this->order;
    }
}
