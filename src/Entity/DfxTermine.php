<?php

namespace App\Entity;

use DateTimeInterface;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * DfxTermine
 */
#[ORM\Entity]
class DfxTermine
{
    private ?DateTime $datum = null;

    private ?DateTime $datumVon = null;

    private ?DateTime $zeit = null;

    private ?DateTime $zeitBis = null;

    private ?string $nat = null;

    private ?string $plz = null;

    private ?string $ort = null;

    private ?string $lokal = null;

    private ?string $lokalStrasse = null;

    private ?float $lg = null;

    private ?float $bg = null;

    private ?string $veranstalter = null;

    private ?string $eintritt = null;

    private ?array $rubrik = null;

    private ?string $titel = null;

    private ?string $beschreibung = null;

    private ?bool $status = null;

    private ?bool $datenschutz = null;

    private ?string $link = null;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $img = null;

    /**
     * @var DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private DateTime $updatedAt;


    private ?string $imgPos = null;

    private ?string $mail = null;

    private ?string $mailTyp = null;

    private ?bool $pub = null;

    private ?bool $pubMeta = null;

    private ?string $code = null;

    private ?string $autor = null;

    private ?string $nl = null;

    private ?bool $nlSent = false;

    private ?string $serie = null;

    private ?int $plaetzeGesamt = null;

    private ?int $plaetzeAktuell = null;

    private ?int $kartenTpl = null;

    private ?array $kartenKat = null;

    private ?bool $archiv = null;

    private ?bool $online = null;

    private ?DateTime $input = null;

    private ?DateTime $modified = null;

    private ?int $id = null;

    private ?DfxNfxUser $user = null;

    private ?DfxLocation $idLocation = null;

    private ?DfxVeranstalter $idVeranstalter = null;

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

    public function setDatumVon(?DateTime $datumVon): static
    {
        $this->datumVon = $datumVon;

        return $this;
    }

    public function getDatumVon(): ?DateTime
    {
        return $this->datumVon;
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

    public function setZeitBis(?DateTime $zeitBis): static
    {
        $this->zeitBis = $zeitBis;

        return $this;
    }

    public function getZeitBis(): ?DateTime
    {
        return $this->zeitBis;
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

    public function setLokal(?string $lokal): static
    {
        $this->lokal = $lokal;

        return $this;
    }

    public function getLokal(): ?string
    {
        return $this->lokal;
    }

    public function setLokalStrasse(?string $lokalStrasse): static
    {
        $this->lokalStrasse = $lokalStrasse;

        return $this;
    }

    public function getLokalStrasse(): ?string
    {
        return $this->lokalStrasse;
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

    public function setVeranstalter(?string $veranstalter): static
    {
        $this->veranstalter = $veranstalter;

        return $this;
    }

    public function getVeranstalter(): ?string
    {
        return $this->veranstalter;
    }

    public function setEintritt(?string $eintritt): static
    {
        $this->eintritt = $eintritt;

        return $this;
    }

    public function getEintritt(): ?string
    {
        return $this->eintritt;
    }

    public function setRubrik(?array $rubrik): static
    {
        $this->rubrik = $rubrik;

        return $this;
    }

    public function getRubrik(): ?array
    {
        return $this->rubrik;
    }

    public function setTitel(string $titel): static
    {
        $this->titel = $titel;

        return $this;
    }

    public function getTitel(): ?string
    {
        return $this->titel;
    }

    public function setBeschreibung(?string $beschreibung): static
    {
        $this->beschreibung = $beschreibung;

        return $this;
    }

    public function getBeschreibung(): ?string
    {
        return $this->beschreibung;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setImg(?string $img): static
    {
        $this->img = $img;

        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }


    /**
     * Set imgPos
     *
     * @param string|null $imgPos
     * @return DfxTermine
     */
    public function setImgPos(? string $imgPos):self
    {
        $this->imgPos = $imgPos;

        return $this;
    }

    /**
     * Get imgPos
     *
     * @return string|null
     */
    public function getImgPos(): ?string
    {
        return $this->imgPos;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMailTyp(?string $mailTyp): static
    {
        $this->mailTyp = $mailTyp;

        return $this;
    }

    public function getMailTyp(): ?string
    {
        return $this->mailTyp;
    }

    public function setPub(?bool $pub): static
    {
        $this->pub = $pub;

        return $this;
    }

    /**
     * Get pub
     *
     * @return bool|null
     */
    public function getPub(): ?bool
    {
        return $this->pub;
    }

    public function setPubMeta(?bool $pubMeta): static
    {
        $this->pubMeta = $pubMeta;

        return $this;
    }

    /**
     * Get pubMeta
     *
     * @return bool|null
     */
    public function getPubMeta(): ?bool
    {
        return $this->pubMeta;
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

    public function setAutor(?string $autor): static
    {
        $this->autor = $autor;

        return $this;
    }

    public function getAutor(): ?string
    {
        return $this->autor;
    }

    /**
     * Set nl
     *
     * @param string $nl
     * @return DfxTermine
     */
    public function setNl(string $nl): static
    {
        $this->nl = $nl;

        return $this;
    }

    /**
     * Get nl
     *
     * @return string|null
     */
    public function getNl(): ?string
    {
        return $this->nl;
    }

    public function setNlSent(?bool $nlSent): static
    {
        $this->nlSent = $nlSent;

        return $this;
    }

    /**
     * Get nlSent
     *
     * @return string|null
     */
    public function getNlSent(): ?bool
    {
        return $this->nlSent;
    }

    public function setSerie(?string $serie): static
    {
        $this->serie = $serie;

        return $this;
    }

    public function getSerie(): ?string
    {
        return $this->serie;
    }

    public function setPlaetzeGesamt(?int $plaetzeGesamt): static
    {
        $this->plaetzeGesamt = $plaetzeGesamt;

        return $this;
    }

    public function getPlaetzeGesamt(): ?int
    {
        return $this->plaetzeGesamt;
    }

    public function setPlaetzeAktuell(?int $plaetzeAktuell): static
    {
        $this->plaetzeAktuell = $plaetzeAktuell;

        return $this;
    }

    public function getPlaetzeAktuell(): ?int
    {
        return $this->plaetzeAktuell;
    }

    public function setKartenTpl(?int $kartenTpl): static
    {
        $this->kartenTpl = $kartenTpl;

        return $this;
    }

    public function getKartenTpl(): ?int
    {
        return $this->kartenTpl;
    }

    public function setKartenKat(?array $kartenKat): static
    {
        $this->kartenKat = $kartenKat;

        return $this;
    }

    public function getKartenKat(): ?array
    {
        return $this->kartenKat;
    }

    public function setArchiv(?bool $archiv): static
    {
        $this->archiv = $archiv;

        return $this;
    }

    /**
     * Get archiv
     *
     * @return bool|null
     */
    public function getArchiv(): ?bool
    {
        return $this->archiv;
    }

    /**
     * Set input
     *
     * @param DateTime|null $input
     * @return DfxTermine
     */
    public function setInput(? DateTime $input): self
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Get input
     *
     * @return DateTime|null
     */
    public function getInput(): ?DateTime
    {
        return $this->input;
    }

    /**
     * Set modified
     *
     * @param DateTime $modified
     * @return DfxTermine
     */
    public function setModified(DateTime $modified): static
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return DateTime|null
     */
    public function getModified(): ?DateTime
    {
        return $this->modified;
    }

    /**
     * Set id
     *
     * @param integer|null $id
     * @return DfxTermine
     */
    public function setId(? int $id):self
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

    public function setIdLocation(?DfxLocation $idLocation): static
    {
        $this->idLocation = $idLocation;

        return $this;
    }

    public function getIdLocation(): ?DfxLocation
    {
        return $this->idLocation;
    }

    public function setIdVeranstalter(?DfxVeranstalter $idVeranstalter): static
    {
        $this->idVeranstalter = $idVeranstalter;

        return $this;
    }

    public function getIdVeranstalter(): ?DfxVeranstalter
    {
        return $this->idVeranstalter;
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

    private ?string $lead = null;

    private ?string $pdf = null;

    private ?int $nlNr = null;

    public function setLead(?string $lead): static
    {
        $this->lead = $lead;

        return $this;
    }

    public function getLead(): ?string
    {
        return $this->lead;
    }

    public function setPdf(?string $pdf): static
    {
        $this->pdf = $pdf;

        return $this;
    }

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function setNlNr(?int $nlNr): static
    {
        $this->nlNr = $nlNr;

        return $this;
    }

    public function getNlNr(): ?int
    {
        return $this->nlNr;
    }

    private ?string $imgOld = null;

    private ?DateTime $datumInput = null;

    private ?DateTime $datumModified = null;


    /**
     * Set imgOld
     *
     * @param string $imgOld
     *
     * @return DfxTermine
     */
    public function setImgOld(string $imgOld): static
    {
        $this->imgOld = $imgOld;

        return $this;
    }

    /**
     * Get imgOld
     *
     * @return string|null
     */
    public function getImgOld(): ?string
    {
        return $this->imgOld;
    }

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

    private ?bool $pubGroup = null;

    public function setPubGroup(?bool $pubGroup): static
    {
        $this->pubGroup = $pubGroup;

        return $this;
    }

    /**
     * Get pubGroup
     *
     * @return bool|null
     */
    public function getPubGroup(): ?bool
    {
        return $this->pubGroup;
    }

    private ?string $ticketlink = null;

    private ?string $video = null;

    public function setTicketlink(?string $ticketlink): static
    {
        $this->ticketlink = $ticketlink;

        return $this;
    }

    public function getTicketlink(): ?string
    {
        return $this->ticketlink;
    }

    public function setVideo(?string $video): static
    {
        $this->video = $video;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    private ?string $linktext = null;

    private ?string $ticketlinktext = null;

    private ?string $pdflinktext = null;

    private ?string $imgtext = null;

    private ?string $konferenzlinktext = null;

    private ?string $konferenzlink = null;

    public function setLinktext(?string $linktext): static
    {
        $this->linktext = $linktext;

        return $this;
    }

    public function getLinktext(): ?string
    {
        return $this->linktext;
    }

    public function setTicketlinktext(?string $ticketlinktext): static
    {
        $this->ticketlinktext = $ticketlinktext;

        return $this;
    }

    public function getTicketlinktext(): ?string
    {
        return $this->ticketlinktext;
    }

    public function setPdflinktext(?string $pdflinktext): static
    {
        $this->pdflinktext = $pdflinktext;

        return $this;
    }

    public function getPdflinktext(): ?string
    {
        return $this->pdflinktext;
    }

    public function setImgtext(?string $imgtext): static
    {
        $this->imgtext = $imgtext;

        return $this;
    }

    public function getImgtext(): ?string
    {
        return $this->imgtext;
    }

    private ?string $init = null;

    public function setInit(?string $init): static
    {
        $this->init = $init;

        return $this;
    }

    public function getInit(): ?string
    {
        return $this->init;
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
    private ?string $subtitel = null;

    public function setSubtitel(?string $subtitel): static
    {
        $this->subtitel = $subtitel;

        return $this;
    }

    public function getSubtitel(): ?string
    {
        return $this->subtitel;
    }

    private ?string $datumSerie = null;

    public function setDatumSerie(?string $datumSerie): static
    {
        $this->datumSerie = $datumSerie;

        return $this;
    }

    public function getDatumSerie(): ?string
    {
        return $this->datumSerie;
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

    private ?string $img2 = null;

    private ?string $img3 = null;

    private ?string $img4 = null;

    private ?string $img5 = null;

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

    private ?string $media = null;

    private ?string $medialinktext = null;

    private ?string $mediatyp = null;

    public function setMedia(?string $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedialinktext(?string $medialinktext): static
    {
        $this->medialinktext = $medialinktext;

        return $this;
    }

    public function getMedialinktext(): ?string
    {
        return $this->medialinktext;
    }

    public function setMediatyp(?string $mediatyp): static
    {
        $this->mediatyp = $mediatyp;

        return $this;
    }

    public function getMediatyp(): ?string
    {
        return $this->mediatyp;
    }





    private ?array $zielgruppe = null;

    public function setZielgruppe(?array $zielgruppe): static
    {
        $this->zielgruppe = $zielgruppe;

        return $this;
    }

    public function getZielgruppe(): ?array
    {
        return $this->zielgruppe;
    }


    private ?bool $filter1 = null;

    private ?bool $filter2 = null;

    private ?bool $filter3 = null;

    private ?bool $filter4 = null;

    private ?bool $filter5 = null;

    private ?bool $filter11 = null;

    private ?bool $filter12 = null;

    private ?bool $filter13 = null;

    private ?bool $filter14 = null;

    private ?bool $filter15 = null;

    private ?string $text1 = null;

    private ?string $text2 = null;

    private ?string $text3 = null;

    private ?string $text4 = null;

    private ?string $text5 = null;

    private ?string $text6 = null;

    private ?string $text7 = null;

    private ?string $text8 = null;

    private ?string $text9 = null;

    private ?string $text10 = null;


    private ?string $textbox1 = null;

    private ?string $textbox2 = null;

    public function setFilter1(?bool $filter1): static
    {
        $this->filter1 = $filter1;

        return $this;
    }

    /**
     * Get filter1
     *
     * @return bool|null
     */
    public function getFilter1(): ?bool
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
     * @return bool|null
     */
    public function getFilter2(): ?bool
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
     * @return bool|null
     */
    public function getFilter3(): ?bool
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
     * @return bool|null
     */
    public function getFilter4(): ?bool
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
     * @return bool|null
     */
    public function getFilter5(): ?bool
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

    private ?DateTime $anmeldeschluss = null;

    public function setAnmeldeschluss(?DateTime $anmeldeschluss): static
    {
        $this->anmeldeschluss = $anmeldeschluss;

        return $this;
    }

    public function getAnmeldeschluss(): ?DateTime
    {
        return $this->anmeldeschluss;
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

    private ?string $imgcopyright = null;

    private ?string $imgtext2 = null;

    private ?string $imgcopyright2 = null;

    public function setImgcopyright(?string $imgcopyright): static
    {
        $this->imgcopyright = $imgcopyright;

        return $this;
    }

    public function getImgcopyright(): ?string
    {
        return $this->imgcopyright;
    }

    public function setImgtext2(?string $imgtext2): static
    {
        $this->imgtext2 = $imgtext2;

        return $this;
    }

    public function getImgtext2(): ?string
    {
        return $this->imgtext2;
    }

    public function setImgcopyright2(?string $imgcopyright2): static
    {
        $this->imgcopyright2 = $imgcopyright2;

        return $this;
    }

    public function getImgcopyright2(): ?string
    {
        return $this->imgcopyright2;
    }


    private ?string $exportflag = null;

    private ?bool $filter6 = null;

    private ?bool $filter7 = null;

    private ?bool $filter8 = null;

    private ?bool $filter9 = null;

    private ?bool $filter10 = null;

    public function setExportflag(?string $exportflag): static
    {
        $this->exportflag = $exportflag;

        return $this;
    }

    public function getExportflag(): ?string
    {
        return $this->exportflag;
    }

    public function setFilter6(?bool $filter6): static
    {
        $this->filter6 = $filter6;

        return $this;
    }

    /**
     * Get filter6
     *
     * @return bool|null
     */
    public function getFilter6(): ?bool
    {
        return $this->filter6;
    }

    public function setFilter7(?bool $filter7): static
    {
        $this->filter7 = $filter7;

        return $this;
    }

    /**
     * Get filter7
     *
     * @return bool|null
     */
    public function getFilter7(): ?bool
    {
        return $this->filter7;
    }

    public function setFilter8(?bool $filter8): static
    {
        $this->filter8 = $filter8;

        return $this;
    }

    /**
     * Get filter8
     *
     * @return bool|null
     */
    public function getFilter8(): ?bool
    {
        return $this->filter8;
    }

    public function setFilter9(?bool $filter9): static
    {
        $this->filter9 = $filter9;

        return $this;
    }

    /**
     * Get filter9
     *
     * @return bool|null
     */
    public function getFilter9(): ?bool
    {
        return $this->filter9;
    }

    public function setFilter10(?bool $filter10): static
    {
        $this->filter10 = $filter10;

        return $this;
    }

    /**
     * Get filter10
     *
     * @return bool|null
     */
    public function getFilter10(): ?bool
    {
        return $this->filter10;
    }

    private ?bool $imgcopycheck = null;

    /**
     * @var boolean
     */
    private bool $imgcopycheck2;

    public function setImgcopycheck(?bool $imgcopycheck): static
    {
        $this->imgcopycheck = $imgcopycheck;

        return $this;
    }

    /**
     * Get imgcopycheck
     *
     * @return bool|null
     */
    public function getImgcopycheck(): ?bool
    {
        return $this->imgcopycheck;
    }

    public function setImgcopycheck2(?bool $imgcopycheck2): static
    {
        $this->imgcopycheck2 = $imgcopycheck2;

        return $this;
    }

    /**
     * Get imgcopycheck2
     *
     * @return boolean 
     */
    public function getImgcopycheck2(): bool
    {
        return $this->imgcopycheck2;
    }

    public function getFilter11(): ?bool
    {
        return $this->filter11;
    }

    public function setFilter11(?bool $filter11): static
    {
        $this->filter11 = $filter11;

        return $this;
    }

    public function getFilter12(): ?bool
    {
        return $this->filter12;
    }

    public function setFilter12(?bool $filter12): static
    {
        $this->filter12 = $filter12;

        return $this;
    }

    public function getFilter13(): ?bool
    {
        return $this->filter13;
    }

    public function setFilter13(?bool $filter13): static
    {
        $this->filter13 = $filter13;

        return $this;
    }

    public function getFilter14(): ?bool
    {
        return $this->filter14;
    }

    public function setFilter14(?bool $filter14): static
    {
        $this->filter14 = $filter14;

        return $this;
    }

    public function getFilter15(): ?bool
    {
        return $this->filter15;
    }

    public function setFilter15(?bool $filter15): static
    {
        $this->filter15 = $filter15;

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

    public function getDatenschutz(): ?bool
    {
        return $this->datenschutz;
    }

    public function setDatenschutz(?bool $datenschutz): static
    {
        $this->datenschutz = $datenschutz;

        return $this;
    }

    public function getOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(?bool $online): static
    {
        $this->online = $online;

        return $this;
    }

    private ?string $optionsRadio = null;

    private ?string $optionsMenue = null;

    private ?array $optionsCheckboxes = null;

    /**
     * @var array|null;
     */
    private ?array $optionsMenueMulti = null;

    public function getOptionsRadio(): ?string
    {
        return $this->optionsRadio;
    }

    public function setOptionsRadio(?string $optionsRadio): static
    {
        $this->optionsRadio = $optionsRadio;

        return $this;
    }

    public function getOptionsMenue(): ?string
    {
        return $this->optionsMenue;
    }

    public function setOptionsMenue(?string $optionsMenue): static
    {
        $this->optionsMenue = $optionsMenue;

        return $this;
    }

    public function getOptionsCheckboxes(): ?array
    {
        return $this->optionsCheckboxes;
    }

    public function setOptionsCheckboxes(?array $optionsCheckboxes): static
    {
        $this->optionsCheckboxes = $optionsCheckboxes;

        return $this;
    }

    public function getOptionsMenueMulti(): ?array
    {
        return $this->optionsMenueMulti;
    }

    public function setOptionsMenueMulti(?array $optionsMenueMulti): static
    {
        $this->optionsMenueMulti = $optionsMenueMulti;

        return $this;
    }

    private ?DateTimeInterface $datum3 = null;

    public function getDatum3(): ?DateTimeInterface
    {
        return $this->datum3;
    }

    public function setDatum3(?DateTimeInterface $datum3): static
    {
        $this->datum3 = $datum3;

        return $this;
    }

    private ?int $counter = null;

    public function getKonferenzlink(): ?string
    {
        return $this->konferenzlink;
    }

    public function setKonferenzlink(?string $konferenzlink): static
    {
        $this->konferenzlink = $konferenzlink;

        return $this;
    }

    public function getKonferenzlinktext(): ?string
    {
        return $this->konferenzlinktext;
    }

    public function setKonferenzlinktext(?string $konferenzlinktext): static
    {
        $this->konferenzlinktext = $konferenzlinktext;

        return $this;
    }

    public function getCounter(): ?int
    {
        return $this->counter;
    }

    public function setCounter(?int $counter): static
    {
        $this->counter = $counter;

        return $this;
    }

    public function isImgcopycheck(): ?bool
    {
        return $this->imgcopycheck;
    }

    public function isImgcopycheck2(): ?bool
    {
        return $this->imgcopycheck2;
    }

    public function isPub(): ?bool
    {
        return $this->pub;
    }

    public function isPubMeta(): ?bool
    {
        return $this->pubMeta;
    }

    public function isPubGroup(): ?bool
    {
        return $this->pubGroup;
    }

    public function isOnline(): ?bool
    {
        return $this->online;
    }

    public function isNlSent(): ?bool
    {
        return $this->nlSent;
    }

    public function isArchiv(): ?bool
    {
        return $this->archiv;
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

    public function isFilter11(): ?bool
    {
        return $this->filter11;
    }

    public function isFilter12(): ?bool
    {
        return $this->filter12;
    }

    public function isFilter13(): ?bool
    {
        return $this->filter13;
    }

    public function isFilter14(): ?bool
    {
        return $this->filter14;
    }

    public function isFilter15(): ?bool
    {
        return $this->filter15;
    }

    public function isDatenschutz(): ?bool
    {
        return $this->datenschutz;
    }

    

}
