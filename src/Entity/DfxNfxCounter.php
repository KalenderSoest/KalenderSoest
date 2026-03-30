<?php

namespace App\Entity;

use DateTime;

/**
 * DfxNfxCounter
 */
class DfxNfxCounter
{
    private int $dfxDay = 0;


    private ?int $nfxDay = 0;


    private ?int $dfxSum = 0;


    private ?int $nfxSum = 0;

    private ?int $dfxApiDay = 0;

    private ?int $dfxApiSum = 0;


    /**
     * @var DateTime
     */
    private DateTime $dfxDatumStart;


    /**
     * @var DateTime
     */
    private DateTime $dfxLastLog;

    /**
     * @var DateTime
     */
    private DateTime $nfxLastLog;

    /**
     * @var boolean
     */
    private int $dfxStatus = 1;


    /**
     * @var string
     */
    private ?string $init = null;

    /**
     * @var integer
     */
    private int $id;

    private ?DfxNfxKunden $kunde = null;

    private ?DfxKonf $datefix = null;

    public function setDfxDay(?int $dfxDay): static
    {
        $this->dfxDay = $dfxDay;

        return $this;
    }

    public function getDfxDay(): ?int
    {
        return $this->dfxDay;
    }

    public function setNfxDay(?int $nfxDay): static
    {
        $this->nfxDay = $nfxDay;

        return $this;
    }

    public function getNfxDay(): ?int
    {
        return $this->nfxDay;
    }

    public function setDfxSum(?int $dfxSum): static
    {
        $this->dfxSum = $dfxSum;

        return $this;
    }

    public function getDfxSum(): ?int
    {
        return $this->dfxSum;
    }

    public function setNfxSum(?int $nfxSum): static
    {
        $this->nfxSum = $nfxSum;

        return $this;
    }

    public function getNfxSum(): ?int
    {
        return $this->nfxSum;
    }

    public function setDfxApiDay(?int $dfxApiDay): static
    {
        $this->dfxApiDay = $dfxApiDay;

        return $this;
    }

    public function getDfxApiDay(): ?int
    {
        return $this->dfxApiDay;
    }

    public function setDfxApiSum(?int $dfxApiSum): static
    {
        $this->dfxApiSum = $dfxApiSum;

        return $this;
    }

    public function getDfxApiSum(): ?int
    {
        return $this->dfxApiSum;
    }




    public function setDfxDatumStart(?DateTime $dfxDatumStart): static
    {
        $this->dfxDatumStart = $dfxDatumStart;

        return $this;
    }

    public function getDfxDatumStart(): ?DateTime
    {
        return $this->dfxDatumStart;
    }



    public function setDfxLastLog(?DateTime $dfxLastLog): static
    {
        $this->dfxLastLog = $dfxLastLog;

        return $this;
    }

    public function getDfxLastLog(): ?DateTime
    {
        return $this->dfxLastLog;
    }

    public function setNfxLastLog(?DateTime $nfxLastLog): static
    {
        $this->nfxLastLog = $nfxLastLog;

        return $this;
    }

    public function getNfxLastLog(): ?DateTime
    {
        return $this->nfxLastLog;
    }

    public function setDfxStatus(?int $dfxStatus): static
    {
        $this->dfxStatus = $dfxStatus;

        return $this;
    }

    public function getDfxStatus(): ?int
    {
        return $this->dfxStatus;
    }


    public function setInit(?string $init): static
    {
        $this->init = $init;

        return $this;
    }

    public function getInit(): ?string
    {
        return $this->init;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setKunde(?DfxNfxKunden $kunde): static
    {
        $this->kunde = $kunde;

        return $this;
    }

    public function getKunde(): ?DfxNfxKunden
    {
        return $this->kunde;
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

    private ?DfxNfxUser $user = null;

    public function setUser(?DfxNfxUser $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?DfxNfxUser
    {
        return $this->user;
    }
}
