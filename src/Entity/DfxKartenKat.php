<?php

namespace App\Entity;

/**
 * DfxKartenKat
 */
class DfxKartenKat
{
    /**
     * @var string
     */
    private $katName;

    /**
     * @var integer
     */
    private $katPlaetze;

    private ?int $katPlaetzeFrei = null;

    /**
     * @var string
     */
    private $katPreis0;

    /**
     * @var string
     */
    private $katPreis1;

    /**
     * @var string
     */
    private $katPreis2;

    /**
     * @var string
     */
    private $katText0;

    /**
     * @var string
     */
    private $katText1;

    /**
     * @var string
     */
    private $katText2;

    /**
     * @var string
     */
    private $katRgb;

    /**
     * @var integer
     */
    private $id;

    private ?DfxKonf $datefix = null;

    private ?DfxLocation $location = null;

    public function setKatName(?string $katName): static
    {
        $this->katName = $katName;

        return $this;
    }

    public function getKatName(): ?string
    {
        return $this->katName;
    }

    public function setKatPlaetze(?int $katPlaetze): static
    {
        $this->katPlaetze = $katPlaetze;

        return $this;
    }

    public function getKatPlaetze(): ?int
    {
        return $this->katPlaetze;
    }

    public function setKatPreis0(?string $katPreis0): static
    {
        $this->katPreis0 = $katPreis0;

        return $this;
    }

    public function getKatPreis0(): ?string
    {
        return $this->katPreis0;
    }

    public function setKatPreis1(?string $katPreis1): static
    {
        $this->katPreis1 = $katPreis1;

        return $this;
    }

    public function getKatPreis1(): ?string
    {
        return $this->katPreis1;
    }

    public function setKatPreis2(?string $katPreis2): static
    {
        $this->katPreis2 = $katPreis2;

        return $this;
    }

    public function getKatPreis2(): ?string
    {
        return $this->katPreis2;
    }

    public function setKatText0(?string $katText0): static
    {
        $this->katText0 = $katText0;

        return $this;
    }

    public function getKatText0(): ?string
    {
        return $this->katText0;
    }

    public function setKatText1(?string $katText1): static
    {
        $this->katText1 = $katText1;

        return $this;
    }

    public function getKatText1(): ?string
    {
        return $this->katText1;
    }

    public function setKatText2(?string $katText2): static
    {
        $this->katText2 = $katText2;

        return $this;
    }

    public function getKatText2(): ?string
    {
        return $this->katText2;
    }

    public function setKatRgb(?string $katRgb): static
    {
        $this->katRgb = $katRgb;

        return $this;
    }

    public function getKatRgb(): ?string
    {
        return $this->katRgb;
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

    public function setLocation(?DfxLocation $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): ?DfxLocation
    {
        return $this->location;
    }

    public function getKatPlaetzeFrei(): ?int
    {
        return $this->katPlaetzeFrei;
    }

    public function setKatPlaetzeFrei(?int $katPlaetzeFrei): static
    {
        $this->katPlaetzeFrei = $katPlaetzeFrei;

        return $this;
    }
}
