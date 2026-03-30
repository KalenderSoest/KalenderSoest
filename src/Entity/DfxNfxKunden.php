<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTime;
/**
 * DfxNfxKunden
 */
class DfxNfxKunden
{
	/**
     * @var integer
     */
	protected int $id;

    /**
     * @var ?string
     */
    private ?string $kunde = null;

    /**
     * @var string
     */
    private ?string $name = null;

    /**
     * @var string
     */
    private ?string $vorname = null;

    /**
     * @var string
     */
    private ?string $strasse = null;

    /**
     * @var string
     */
    private ?string $plz = null;

    /**
     * @var string
     */
    private ?string $ort = null;

    /**
     * @var ?string
     */
    private ?string $nat = null;

    /**
     * @var ?string
     */
    private ?string $ustid = null;

    /**
     * @var string
     */
    private ?string $email = null;

    /**
     * @var string
     */
    private ?string $init = null;



    private ArrayCollection|array $datefix;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->datefix = new ArrayCollection();
    }

    public function setKunde(?string $kunde): static
    {
        $this->kunde = $kunde;

        return $this;
    }

    public function getKunde(): ?string
    {
        return $this->kunde;
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

    public function setVorname(string $vorname): static
    {
        $this->vorname = $vorname;

        return $this;
    }

    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setStrasse(string $strasse): static
    {
        $this->strasse = $strasse;

        return $this;
    }

    public function getStrasse(): ?string
    {
        return $this->strasse;
    }

    public function setPlz(string $plz): static
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

    public function setNat(string $nat): static
    {
        $this->nat = $nat;

        return $this;
    }

    public function getNat(): ?string
    {
        return $this->nat;
    }

    public function setUstid(?string $ustid): static
    {
        $this->ustid = $ustid;

        return $this;
    }

    public function getUstid(): ?string
    {
        return $this->ustid;
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

    public function setInit(string $init): static
    {
        $this->init = $init;

        return $this;
    }

    public function getInit(): ?string
    {
        return $this->init;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return DfxNfxKunden
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

    /**
     * Add datefix
     *
     * @param DfxKonf $datefix
     * @return DfxNfxKunden
     */
    public function addDatefix(DfxKonf $datefix): static
    {
        $this->datefix[] = $datefix;

        return $this;
    }

    /**
     * Remove datefix
     *
     * @param DfxKonf $datefix
     */
    public function removeDatefix(DfxKonf $datefix): void
    {
        $this->datefix->removeElement($datefix);
    }

    /**
     * Get datefix
     *
     * @return ArrayCollection|array
     */
    public function getDatefix(): ArrayCollection|array
    {
        return $this->datefix;
    }

    /**
     * @var string
     */
    private ?string $telefon = null;

    /**
     * @var DateTime
     */
    private DateTime $datum;

    /**
     * @var DateTime
     */
    private DateTime $update;

    public function setTelefon(?string $telefon): static
    {
        $this->telefon = $telefon;

        return $this;
    }

    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setDatum(DateTime $datum): static
    {
        $this->datum = $datum;

        return $this;
    }

    public function getDatum(): ?DateTime
    {
        return $this->datum;
    }

    /**
     * Set update
     *
     * @param DateTime $update
     *
     * @return DfxNfxKunden
     */
    public function setUpdate(DateTime $update): static
    {
        $this->update = $update;

        return $this;
    }

    /**
     * Get update
     *
     * @return DateTime
     */
    public function getUpdate(): DateTime
    {
        return $this->update;
    }

    /**
     * @var DateTime
     */
    private DateTime $lastupdate;

    public function setLastupdate(?DateTime $lastupdate): static
    {
        $this->lastupdate = $lastupdate;

        return $this;
    }

    public function getLastupdate(): ?DateTime
    {
        return $this->lastupdate;
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
