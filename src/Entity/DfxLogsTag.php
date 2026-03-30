<?php

namespace App\Entity;

use DateTime;

/**
 * DfxLogsTag
 */
class DfxLogsTag
{
    /**
     * @var DateTime
     */
    private DateTime $datum;

    /**
     * @var integer
     */
    private int $hits;

    /**
     * @var integer
     */
    private int $hitsApi;

    /**
     * @var integer
     */
    private int $id;

    private ?DfxKonf $datefix = null;

    public function setDatum(?DateTime $datum): static
    {
        $this->datum = $datum;

        return $this;
    }

    public function getDatum(): ?DateTime
    {
        return $this->datum;
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

    public function getId(): ?string
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
