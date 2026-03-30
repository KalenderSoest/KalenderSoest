<?php

namespace App\Entity;

/**
 * DfxRegion
 */
class DfxRegion
{
    /**
     * @var string
     */
    private ?string $region = null;

    /**
     * @var integer
     */
    private int $id;

    private ?DfxKonf $datefix = null;

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return DfxRegion
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
     * @var integer
     */
    private int $regionId;


    /**
     * Set regionId
     *
     * @param integer $regionId
     *
     * @return DfxRegion
     */
    public function setRegionId(int $regionId): static
    {
        $this->regionId = $regionId;

        return $this;
    }

    /**
     * Get regionId
     *
     * @return integer
     */
    public function getRegionId(): int
    {
        return $this->regionId;
    }
}
