<?php

namespace App\Entity;

/**
 * DfxOrte
 */
class DfxOrte
{
    /**
     * @var string
     */
    private ?string $plz = null;

    /**
     * @var string
     */
    private ?string $ort = null;

    /**
     * @var integer
     */
    private int $id;

    private ?DfxKonf $datefix = null;

    private ?DfxRegion $region = null;

    public function setPlz(?string $plz): static
    {
        $this->plz = $plz;

        return $this;
    }

    public function getPlz(): ?string
    {
        return $this->plz;
    }

    public function setOrt(string $ort): static
    {
        $this->ort = $ort;

        return $this;
    }

    public function getOrt(): ?string
    {
        return $this->ort;
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

    public function setRegion(?DfxRegion $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getRegion(): ?DfxRegion
    {
        return $this->region;
    }

    /**
     * @var float
     */
    private ?float $lg = null;

    /**
     * @var float
     */
    private ?float $bg = null;

    public function setLg(?float $lg): static
    {
        $this->lg = $lg;

        return $this;
    }

    public function getLg(): ?float
    {
        return $this->lg;
    }

    public function setBg(?float $bg): static
    {
        $this->bg = $bg;

        return $this;
    }

    public function getBg(): ?float
    {
        return $this->bg;
    }
}
