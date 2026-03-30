<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


/**
 * DfxDozenten
 */
class DfxDozenten
{
    /**
     * @var string
     */
    private ?string $vorname = null;

    /**
     * @var string
     */
    private ?string $name = null;

    /**
     * @var string
     */
    private ?string $strasse = null;

    /**
     * @var string
     */
    private ?string $nat = null;

    /**
     * @var string
     */
    private ?string $plz = null;

    /**
     * @var string
     */
    private ?string $ort = null;

    /**
     * @var string
     */
    private ?string $lg = null;

    /**
     * @var string
     */
    private ?string $bg = null;

    /**
     * @var string
     */
    private ?string $telefon = null;

    /**
     * @var string
     */
    private ?string $fax = null;

    /**
     * @var string
     */
    private ?string $email = null;

    /**
     * @var string
     */
    private ?string $www = null;

    /**
     * @var string
     */
    private ?string $ansprech = null;

    /**
     * @var string
     */
    private ?string $imgDoz = null;

    /**
     * @var string
     */
    private ?string $imgDozPos = null;

     private ?DateTime $updatedAt = null;

    /**
     * @var string
     */
    private ?string $info = null;

    /**
     * @var string
     */
    private ?string $zusatz = null;

    /**
     * @var integer
     */
    private int $id;

    private ?DfxVeranstalter $veranstalter = null;

    private ?DfxNfxUser $user = null;

    private ?DfxLocation $location = null;

    private ?DfxKonf $datefix = null;

    public function setVorname(?string $vorname): static
    {
        $this->vorname = $vorname;

        return $this;
    }

    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setStrasse(?string $strasse): static
    {
        $this->strasse = $strasse;

        return $this;
    }

    public function getStrasse(): ?string
    {
        return $this->strasse;
    }

    public function setNat(?string $nat): static
    {
        $this->nat = $nat;

        return $this;
    }

    public function getNat(): ?string
    {
        return $this->nat;
    }

    public function setPlz(?string $plz): static
    {
        $this->plz = $plz;

        return $this;
    }

    public function getPlz(): ?string
    {
        return $this->plz;
    }

    public function setOrt(?string $ort): static
    {
        $this->ort = $ort;

        return $this;
    }

    public function getOrt(): ?string
    {
        return $this->ort;
    }

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

    public function setTelefon(?string $telefon): static
    {
        $this->telefon = $telefon;

        return $this;
    }

    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setFax(?string $fax): static
    {
        $this->fax = $fax;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setWww(?string $www): static
    {
        $this->www = $www;

        return $this;
    }

    public function getWww(): ?string
    {
        return $this->www;
    }

    public function setAnsprech(?string $ansprech): static
    {
        $this->ansprech = $ansprech;

        return $this;
    }

    public function getAnsprech(): ?string
    {
        return $this->ansprech;
    }

    public function setImgDoz(?string $imgDoz): static
    {
        $this->imgDoz = $imgDoz;

        return $this;
    }

    public function getImgDoz(): ?string
    {
        return $this->imgDoz;
    }

    public function setImgDozPos(?string $imgDozPos): static
    {
        $this->imgDozPos = $imgDozPos;

        return $this;
    }

    public function getImgDozPos(): ?string
    {
        return $this->imgDozPos;
    }

    public function setInfo(?string $info): static
    {
        $this->info = $info;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setZusatz(?string $zusatz): static
    {
        $this->zusatz = $zusatz;

        return $this;
    }

    public function getZusatz(): ?string
    {
        return $this->zusatz;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setVeranstalter(?DfxVeranstalter $veranstalter): static
    {
        $this->veranstalter = $veranstalter;

        return $this;
    }

    public function getVeranstalter(): ?DfxVeranstalter
    {
        return $this->veranstalter;
    }

    public function setUser(?DfxNfxUser $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?DfxNfxUser
    {
        return $this->user;
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
     * @var DateTime
     */
    private DateTime $datumInput;

    /**
     * @var DateTime
     */
    private DateTime $datumModified;

    public function setDatumInput(?DateTime $datumInput): static
    {
        $this->datumInput = $datumInput;

        return $this;
    }

    public function getDatumInput(): ?DateTime
    {
        return $this->datumInput;
    }

    public function setDatumModified(?DateTime $datumModified): static
    {
        $this->datumModified = $datumModified;

        return $this;
    }

    public function getDatumModified(): ?DateTime
    {
        return $this->datumModified;
    }

    private ArrayCollection|array $termine;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->termine = new ArrayCollection();
    }

    public function addTermine(DfxTermine $termine): static
    {
        if (!$this->termine->contains($termine)) {
            $this->termine->add($termine);
            $termine->addDozenten($this);
        }

        return $this;
    }

    public function removeTermine(DfxTermine $termine): static
    {
        if ($this->termine->removeElement($termine)) {
            $termine->removeDozenten($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, DfxTermine>
     */
    public function getTermine(): Collection
    {
        return $this->termine;
    }
    
     
     
}
