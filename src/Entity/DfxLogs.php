<?php

namespace App\Entity;

use DateTime;

/**
 * DfxLogs
 */
class DfxLogs
{
    /**
     * @var string
     */
    private ?string $ip = null;

    /**
     * @var string
     */
    private ?string $host = null;

    /**
     * @var string
     */
    private ?string $agent = null;

    /**
     * @var DateTime
     */
    private DateTime $zeit;

    /**
     * @var integer
     */
    private int $id;

    private ?DfxKonf $datefix = null;

    public function setIp(?string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setHost(?string $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setAgent(?string $agent): static
    {
        $this->agent = $agent;

        return $this;
    }

    public function getAgent(): ?string
    {
        return $this->agent;
    }

    public function setZeit(?DateTime $zeit): static
    {
        $this->zeit = $zeit;

        return $this;
    }

    public function getZeit(): ?DateTime
    {
        return $this->zeit;
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
