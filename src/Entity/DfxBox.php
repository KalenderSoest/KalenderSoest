<?php

namespace App\Entity;

/**
 * DfxBox
 */
class DfxBox
{
    /**
     * @var integer
     */
    private int $boxItems;

    /**
     * @var boolean
     */
    private bool $boxOrt;

    /**
     * @var boolean
     */
    private bool $boxDatum;

    /**
     * @var boolean
     */
    private bool $boxUhr;

    /**
     * @var boolean
     */
    private bool $boxTitel;

    private ?bool $boxSubtitel = null;

    /**
     * @var boolean
     */
    private bool $boxLead;

    /**
     * @var boolean
     */
    private bool $boxBeschreibung;

    /**
     * @var boolean
     */
    private bool $boxLokal;

    /**
     * @var boolean
     */
    private bool $boxVeranstalter;

    /**
     * @var string
     */
    private ?string $boxTerminUrl = null;

    /**
     * @var string
     */
    private ?string $boxTarget = null;

    /**
     * @var integer
     */
    private int $id;

    private ?DfxKonf $datefix = null;

    public function setBoxItems(?int $boxItems): static
    {
        $this->boxItems = $boxItems;

        return $this;
    }

    public function getBoxItems(): ?int
    {
        return $this->boxItems;
    }

    public function setBoxOrt(?bool $boxOrt): static
    {
        $this->boxOrt = $boxOrt;

        return $this;
    }

    /**
     * Get boxOrt
     *
     * @return boolean
     */
    public function getBoxOrt(): bool
    {
        return $this->boxOrt;
    }

    public function setBoxDatum(?bool $boxDatum): static
    {
        $this->boxDatum = $boxDatum;

        return $this;
    }

    /**
     * Get boxDatum
     *
     * @return boolean
     */
    public function getBoxDatum(): bool
    {
        return $this->boxDatum;
    }

    public function setBoxUhr(?bool $boxUhr): static
    {
        $this->boxUhr = $boxUhr;

        return $this;
    }

    /**
     * Get boxUhr
     *
     * @return boolean
     */
    public function getBoxUhr(): bool
    {
        return $this->boxUhr;
    }

    public function setBoxTitel(?bool $boxTitel): static
    {
        $this->boxTitel = $boxTitel;

        return $this;
    }

    /**
     * Get boxTitel
     *
     * @return boolean
     */
    public function getBoxTitel(): bool
    {
        return $this->boxTitel;
    }

    public function setBoxLead(?bool $boxLead): static
    {
        $this->boxLead = $boxLead;

        return $this;
    }

    /**
     * Get boxLead
     *
     * @return boolean
     */
    public function getBoxLead(): bool
    {
        return $this->boxLead;
    }

    public function setBoxBeschreibung(?bool $boxBeschreibung): static
    {
        $this->boxBeschreibung = $boxBeschreibung;

        return $this;
    }

    /**
     * Get boxBeschreibung
     *
     * @return boolean
     */
    public function getBoxBeschreibung(): bool
    {
        return $this->boxBeschreibung;
    }

    public function setBoxLokal(?bool $boxLokal): static
    {
        $this->boxLokal = $boxLokal;

        return $this;
    }

    /**
     * Get boxLokal
     *
     * @return boolean
     */
    public function getBoxLokal(): bool
    {
        return $this->boxLokal;
    }

    public function setBoxVeranstalter(?bool $boxVeranstalter): static
    {
        $this->boxVeranstalter = $boxVeranstalter;

        return $this;
    }

    /**
     * Get boxVeranstalter
     *
     * @return boolean
     */
    public function getBoxVeranstalter(): bool
    {
        return $this->boxVeranstalter;
    }

    public function setBoxTerminUrl(?string $boxTerminUrl): static
    {
        $this->boxTerminUrl = $boxTerminUrl;

        return $this;
    }

    public function getBoxTerminUrl(): ?string
    {
        return $this->boxTerminUrl;
    }

    public function setBoxTarget(?string $boxTarget): static
    {
        $this->boxTarget = $boxTarget;

        return $this;
    }

    public function getBoxTarget(): ?string
    {
        return $this->boxTarget;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return DfxBox
     */
    public function setId(int $id): static
    {
    	$this->id = $id;

    	return $this;
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

    /**
     * @var boolean
     */
    private bool $boxImage;

    /**
     * @var string
     */
    private ?string $boxCss = null;

    public function setBoxImage(?bool $boxImage): static
    {
        $this->boxImage = $boxImage;

        return $this;
    }

    /**
     * Get boxImage
     *
     * @return boolean 
     */
    public function getBoxImage(): bool
    {
        return $this->boxImage;
    }

    public function setBoxCss(?string $boxCss): static
    {
        $this->boxCss = $boxCss;

        return $this;
    }

    public function getBoxCss(): ?string
    {
        return $this->boxCss;
    }

    public function getBoxSubtitel(): ?bool
    {
        return $this->boxSubtitel;
    }

    public function setBoxSubtitel(?bool $boxSubtitel): static
    {
        $this->boxSubtitel = $boxSubtitel;

        return $this;
    }

    public function isBoxOrt(): ?bool
    {
        return $this->boxOrt;
    }

    public function isBoxDatum(): ?bool
    {
        return $this->boxDatum;
    }

    public function isBoxUhr(): ?bool
    {
        return $this->boxUhr;
    }

    public function isBoxTitel(): ?bool
    {
        return $this->boxTitel;
    }

    public function isBoxSubtitel(): ?bool
    {
        return $this->boxSubtitel;
    }

    public function isBoxLead(): ?bool
    {
        return $this->boxLead;
    }

    public function isBoxBeschreibung(): ?bool
    {
        return $this->boxBeschreibung;
    }

    public function isBoxLokal(): ?bool
    {
        return $this->boxLokal;
    }

    public function isBoxVeranstalter(): ?bool
    {
        return $this->boxVeranstalter;
    }

    public function isBoxImage(): ?bool
    {
        return $this->boxImage;
    }
}
