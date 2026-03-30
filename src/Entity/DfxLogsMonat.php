<?php

namespace App\Entity;

/**
 * DfxLogsMonat
 */
class DfxLogsMonat
{
    /**
     * @var boolean
     */
    private bool $monat;

    /**
     * @var integer
     */
    private int $jahr;

    /**
     * @var integer
     */
    private int $hits;

    /**
     * @var integer
     */
    private int $hitsRss;

    /**
     * @var integer
     */
    private int $hitsApi;

    /**
     * @var integer
     */
    private int $id;

    private ?DfxKonf $datefix = null;

    public function setMonat(?int $monat): static
    {
        $this->monat = $monat;

        return $this;
    }

    public function getMonat(): ?int
    {
        return $this->monat;
    }

    public function setJahr(?int $jahr): static
    {
        $this->jahr = $jahr;

        return $this;
    }

    public function getJahr(): ?int
    {
        return $this->jahr;
    }

    public function setHits(?int $hits): static
    {
        $this->hits = $hits;

        return $this;
    }

    public function getHits(): ?int
    {
        return $this->hits;
    }

    public function setHitsApi(?int $hitsApi): static
    {
        $this->hitsApi = $hitsApi;

        return $this;
    }

    public function getHitsApi(): ?int
    {
        return $this->hitsApi;
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
}
