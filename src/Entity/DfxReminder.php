<?php

namespace App\Entity;

use DateTime;

/**
 * DfxReminder
 */
class DfxReminder
{
    /**
     * @var DateTime
     */
    private DateTime $datum;

    /**
     * @var string
     */
    private ?string $email = null;

    /**
     * @var string
     */
    private ?string $code = null;

    /**
     * @var integer
     */
    private int $id;

    private ?DfxTermine $termin = null;

    private ?DfxKonf $datefix = null;

    public function setDatum(DateTime $datum): static
    {
        $this->datum = $datum;

        return $this;
    }

    public function getDatum(): ?DateTime
    {
        return $this->datum;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getId(): ?int
    {
        return $this->id;
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
    private bool $datenschutz;

    public function setDatenschutz(?bool $datenschutz): static
    {
        $this->datenschutz = $datenschutz;

        return $this;
    }

    /**
     * Get datenschutz
     *
     * @return boolean 
     */
    public function getDatenschutz(): bool
    {
        return $this->datenschutz;
    }

    public function isDatenschutz(): ?bool
    {
        return $this->datenschutz;
    }
}
