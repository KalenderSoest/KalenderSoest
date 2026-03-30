<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * DfxVeranstalter
  */
class DfxVeranstalter
{
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
     * @var float|null
     */
    private ?float $lg = null;

    /**
     * @var float|null
     */
    private ?float $bg = null;

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
     * @var DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private DateTime $updatedAt;

    /**
     * @var string
     */
    private ?string $imgVer = null;

    /**
     * @var string
     */
    private ?string $imgVerPos = null;

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

    private ?DfxNfxUser $user = null;

    private ?DfxLocation $location = null;

    private ?DfxKonf $datefix = null;

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


    public function setImgVer(?string $imgVer): static
    {
        $this->imgVer = $imgVer;

        return $this;
    }

    public function getImgVer(): ?string
    {
        return $this->imgVer;
    }

    public function setImgVerPos(?string $imgVerPos): static
    {
        $this->imgVerPos = $imgVerPos;

        return $this;
    }

    public function getImgVerPos(): ?string
    {
        return $this->imgVerPos;
    }

    /**
     * Set info
     *
     * @param string $info
     * @return DfxVeranstalter
     */
    public function setInfo(string $info): static
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Get info
     *
     * @return string 
     */
    public function getInfo(): string
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

    /**
     * Set id
     *
     * @param integer $id
     * @return DfxVeranstalter
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

    /**
     * @var string
     */
    private ?string $mobil = null;

    /**
     * @var boolean
     */
    private bool $filter1;

    /**
     * @var boolean
     */
    private bool $filter2;

    /**
     * @var boolean
     */
    private bool $filter3;

    /**
     * @var boolean
     */
    private bool $filter4;

    /**
     * @var boolean
     */
    private bool $filter5;

    private ?bool $filter6 = null;

    private ?bool $filter7 = null;

    private ?bool $filter8 = null;

    private ?bool $filter9 = null;

    private ?bool $filter10 = null;

    /**
     * @var string
     */
    private ?string $text1 = null;

    /**
     * @var string
     */
    private ?string $text2 = null;

    /**
     * @var string
     */
    private ?string $text3 = null;

    /**
     * @var string
     */
    private ?string $text4 = null;

    /**
     * @var string
     */
    private ?string $text5 = null;

    private ?string $text6 = null;

    private ?string $text7 = null;

    private ?string $text8 = null;

    private ?string $text9 = null;

    private ?string $text10 = null;

    /**
     * @var string
     */
    private ?string $textbox1 = null;

    /**
     * @var string
     */
    private ?string $textbox2 = null;

    public function setMobil(?string $mobil): static
    {
        $this->mobil = $mobil;

        return $this;
    }

    public function getMobil(): ?string
    {
        return $this->mobil;
    }

    public function setFilter1(?bool $filter1): static
    {
        $this->filter1 = $filter1;

        return $this;
    }

    /**
     * Get filter1
     *
     * @return boolean 
     */
    public function getFilter1(): bool
    {
        return $this->filter1;
    }

    public function setFilter2(?bool $filter2): static
    {
        $this->filter2 = $filter2;

        return $this;
    }

    /**
     * Get filter2
     *
     * @return boolean 
     */
    public function getFilter2(): bool
    {
        return $this->filter2;
    }

    public function setFilter3(?bool $filter3): static
    {
        $this->filter3 = $filter3;

        return $this;
    }

    /**
     * Get filter3
     *
     * @return boolean 
     */
    public function getFilter3(): bool
    {
        return $this->filter3;
    }

    public function setFilter4(?bool $filter4): static
    {
        $this->filter4 = $filter4;

        return $this;
    }

    /**
     * Get filter4
     *
     * @return boolean 
     */
    public function getFilter4(): bool
    {
        return $this->filter4;
    }

    public function setFilter5(?bool $filter5): static
    {
        $this->filter5 = $filter5;

        return $this;
    }

    /**
     * Get filter5
     *
     * @return boolean 
     */
    public function getFilter5(): bool
    {
        return $this->filter5;
    }

    public function setText1(?string $text1): static
    {
        $this->text1 = $text1;

        return $this;
    }

    public function getText1(): ?string
    {
        return $this->text1;
    }

    public function setText2(?string $text2): static
    {
        $this->text2 = $text2;

        return $this;
    }

    public function getText2(): ?string
    {
        return $this->text2;
    }

    public function setText3(?string $text3): static
    {
        $this->text3 = $text3;

        return $this;
    }

    public function getText3(): ?string
    {
        return $this->text3;
    }

    public function setText4(?string $text4): static
    {
        $this->text4 = $text4;

        return $this;
    }

    public function getText4(): ?string
    {
        return $this->text4;
    }

    public function setText5(?string $text5): static
    {
        $this->text5 = $text5;

        return $this;
    }

    public function getText5(): ?string
    {
        return $this->text5;
    }

    public function setTextbox1(?string $textbox1): static
    {
        $this->textbox1 = $textbox1;

        return $this;
    }

    public function getTextbox1(): ?string
    {
        return $this->textbox1;
    }

    public function setTextbox2(?string $textbox2): static
    {
        $this->textbox2 = $textbox2;

        return $this;
    }

    public function getTextbox2(): ?string
    {
        return $this->textbox2;
    }

    private ?DfxRegion $region = null;

    public function setRegion(?DfxRegion $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getRegion(): ?DfxRegion
    {
        return $this->region;
    }

    private ?DfxOrte $idOrt = null;

    public function setIdOrt(?DfxOrte $idOrt): static
    {
        $this->idOrt = $idOrt;

        return $this;
    }

    public function getIdOrt(): ?DfxOrte
    {
        return $this->idOrt;
    }



    public function getFilter6(): ?bool
    {
        return $this->filter6;
    }

    public function setFilter6(?bool $filter6): static
    {
        $this->filter6 = $filter6;

        return $this;
    }

    public function getFilter7(): ?bool
    {
        return $this->filter7;
    }

    public function setFilter7(?bool $filter7): static
    {
        $this->filter7 = $filter7;

        return $this;
    }

    public function getFilter8(): ?bool
    {
        return $this->filter8;
    }

    public function setFilter8(?bool $filter8): static
    {
        $this->filter8 = $filter8;

        return $this;
    }

    public function getFilter9(): ?bool
    {
        return $this->filter9;
    }

    public function setFilter9(?bool $filter9): static
    {
        $this->filter9 = $filter9;

        return $this;
    }

    public function getFilter10(): ?bool
    {
        return $this->filter10;
    }

    public function setFilter10(?bool $filter10): static
    {
        $this->filter10 = $filter10;

        return $this;
    }

    public function getText6(): ?string
    {
        return $this->text6;
    }

    public function setText6(?string $text6): static
    {
        $this->text6 = $text6;

        return $this;
    }

    public function getText7(): ?string
    {
        return $this->text7;
    }

    public function setText7(?string $text7): static
    {
        $this->text7 = $text7;

        return $this;
    }

    public function getText8(): ?string
    {
        return $this->text8;
    }

    public function setText8(?string $text8): static
    {
        $this->text8 = $text8;

        return $this;
    }

    public function getText9(): ?string
    {
        return $this->text9;
    }

    public function setText9(?string $text9): static
    {
        $this->text9 = $text9;

        return $this;
    }

    public function getText10(): ?string
    {
        return $this->text10;
    }

    public function setText10(?string $text10): static
    {
        $this->text10 = $text10;

        return $this;
    }

    private ?string $img = null;

    private ?string $img2 = null;


    private ?string $img3 = null;

    private ?string $img4 = null;

    private ?string $img5 = null;

    /**
     * Set img
     *
     * @param string|null $img
     * @return DfxVeranstalter
     */
    public function setImg(? string $img): self
    {
        $this->img = $img;

        return $this;
    }

    /**
     * Get img2
     *
     * @return string|null
     */
    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg2(?string $img2): static
    {
        $this->img2 = $img2;

        return $this;
    }

    public function getImg2(): ?string
    {
        return $this->img2;
    }

    public function setImg3(?string $img3): static
    {
        $this->img3 = $img3;

        return $this;
    }

    public function getImg3(): ?string
    {
        return $this->img3;
    }

    public function setImg4(?string $img4): static
    {
        $this->img4 = $img4;

        return $this;
    }

    public function getImg4(): ?string
    {
        return $this->img4;
    }

    public function setImg5(?string $img5): static
    {
        $this->img5 = $img5;

        return $this;
    }

    public function getImg5(): ?string
    {
        return $this->img5;
    }

    private ?string $pdf = null;

    /**
     * Set pdf
     *
     * @param string|null $pdf
     *
     * @return DfxVeranstalter
     */
    public function setPdf(? string $pdf):self
    {
        $this->pdf = $pdf;

        return $this;
    }

    /**
     * Get pdf
     *
     * @return string|null
     */
    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    private ?string $media = null;

    /**
     * Set media
     *
     * @param string|null $media
     * @return DfxVeranstalter
     */
    public function setMedia(? string $media): self
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return string|null
     */
    public function getMedia(): ?string
    {
        return $this->media;
    }

    private ?string $imgcopyright = null;

    private ?string $imgtext = null;

    private ?bool $imgcopycheck = null;

    public function getImgtext(): ?string
    {
        return $this->imgtext;
    }

    public function setImgtext(?string $imgtext): static
    {
        $this->imgtext = $imgtext;

        return $this;
    }

    public function getImgcopyright(): ?string
    {
        return $this->imgcopyright;
    }

    public function setImgcopyright(?string $imgcopyright): static
    {
        $this->imgcopyright = $imgcopyright;

        return $this;
    }

    public function getImgcopycheck(): ?bool
    {
        return $this->imgcopycheck;
    }

    public function setImgcopycheck(?bool $imgcopycheck): static
    {
        $this->imgcopycheck = $imgcopycheck;

        return $this;
    }

    public function isImgcopycheck(): ?bool
    {
        return $this->imgcopycheck;
    }

    public function isFilter1(): ?bool
    {
        return $this->filter1;
    }

    public function isFilter2(): ?bool
    {
        return $this->filter2;
    }

    public function isFilter3(): ?bool
    {
        return $this->filter3;
    }

    public function isFilter4(): ?bool
    {
        return $this->filter4;
    }

    public function isFilter5(): ?bool
    {
        return $this->filter5;
    }

    public function isFilter6(): ?bool
    {
        return $this->filter6;
    }

    public function isFilter7(): ?bool
    {
        return $this->filter7;
    }

    public function isFilter8(): ?bool
    {
        return $this->filter8;
    }

    public function isFilter9(): ?bool
    {
        return $this->filter9;
    }

    public function isFilter10(): ?bool
    {
        return $this->filter10;
    }

    private ?string $extid = null;

    public function setExtid(?string $extid): static
    {
        $this->extid = $extid;

        return $this;
    }

    public function getExtid(): ?string
    {
        return $this->extid;
    }

    private ?string $code = null;

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }




}
