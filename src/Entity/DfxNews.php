<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * DfxNews
 */
#[ORM\Entity]
class DfxNews
{
    private ?DateTime $datumBis = null;

    private ?DateTime $datumVon = null;


    private ?array $rubrik = null;

    private ?string $titel = null;

    private ?string $kurztitel = null;

    private ?string $beschreibung = null;

    private ?string $link = null;

    private ?string $mail = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $img = null;

    /**
     * @var DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private DateTime $updatedAt;


    private ?string $newsTyp = null;

    private ?bool $pub = null;

    private ?bool $pubMeta = null;

    private ?string $code = null;

    private ?string $autor = null;

    private ?string $nl = null;

    private ?bool $nlSent = null;


    private ?bool $archiv = null;

    private ?bool $menueeintrag = null;

    private ?DateTime $input = null;

    private ?DateTime $modified = null;

    private ?int $id = null;

    private ?DfxNfxUser $user = null;


    private ?DfxKonf $datefix = null;

    public function getDatumBis(): ?DateTime
    {
        return $this->datumBis;
    }

    public function setDatumBis(?DateTime $datumBis): static
    {
        $this->datumBis = $datumBis;

        return $this;
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

    public function setKurztitel(?string $kurztitel): static
    {
        $this->kurztitel = $kurztitel;

        return $this;
    }

    public function getKurztitel(): ?string
    {
        return $this->kurztitel;
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


    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
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

    public function setImg(?string $img): static
    {
        $this->img = $img;

        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }




    public function setNewsTyp(?string $newsTyp): static
    {
        $this->newsTyp = $newsTyp;

        return $this;
    }

    public function getNewsTyp(): ?string
    {
        return $this->newsTyp;
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
     * @return DfxNews
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
     * @return bool|null
     */
    public function getNlSent(): ?bool
    {
        return $this->nlSent;
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

    public function setMenueeintrag(?bool $menueeintrag): static
    {
        $this->menueeintrag = $menueeintrag;

        return $this;
    }

    /**
     * Get menueeintrag
     *
     * @return bool|null
     */
    public function getMenueeintrag(): ?bool
    {
        return $this->menueeintrag;
    }



    /**
     * Set input
     *
     * @param DateTime|null $input
     * @return DfxNews
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
     * @return DfxNews
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
     * @return DfxNews
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

    public function setDatefix(?DfxKonf $datefix): static
    {
        $this->datefix = $datefix;

        return $this;
    }

    public function getDatefix(): ?DfxKonf
    {
        return $this->datefix;
    }

    private ?string $kurztext = null;

    private ?string $pdf = null;

    private ?int $nlNr = null;

    private ?int $reihenfolge = null;

    public function setKurztext(?string $kurztext): static
    {
        $this->kurztext = $kurztext;

        return $this;
    }

    public function getKurztext(): ?string
    {
        return $this->kurztext;
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

    public function setReihenfolge(?int $reihenfolge): static
    {
        $this->reihenfolge = $reihenfolge;

        return $this;
    }

    public function getReihenfolge(): ?int
    {
        return $this->reihenfolge;
    }


    private ?DateTime $datumInput = null;

    private ?DateTime $datumModified = null;

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


    private ?string $video = null;

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

    private ?string $pdflinktext = null;

    private ?string $imgtext = null;


    /**
     * Set linktext
     *
     * @param string|null $linktext
     *
     * @return DfxNews
     */
    public function setLinktext(? string $linktext):self
    {
        $this->linktext = $linktext;

        return $this;
    }

    /**
     * Get linktext
     *
     * @return string|null
     */
    public function getLinktext(): ?string
    {
        return $this->linktext;
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


    private ?string $subtitel = null;


    /**
     * Set subtitel
     *
     * @param string|null $subtitel
     *
     * @return DfxNews
     */
    public function setSubtitel(? string $subtitel): self
    {
        $this->subtitel = $subtitel;

        return $this;
    }

    /**
     * Get subtitel
     *
     * @return string|null
     */
    public function getSubtitel(): ?string
    {
        return $this->subtitel;
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


    /**
     * Set img5
     *
     * @param string|null $img5
     * @return DfxNews
     */
    public function setImg5(? string $img5): self
    {
    	$this->img5 = $img5;

    	return $this;
    }

    /**
     * Get img5
     *
     * @return string|null
     */
    public function getImg5(): ?string
    {
    	return $this->img5;
    }




    private ?string $media = null;

    private ?string $medialinktext = null;

    private ?string $mediatyp = null;

    /**
     * Set media
     *
     * @param string|null $media
     * @return DfxNews
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



    /**
     * Set medialinktext
     *
     * @param string|null $medialinktext
     * @return DfxNews
     */
    public function setMedialinktext(? string $medialinktext): self
    {
    	$this->medialinktext = $medialinktext;

    	return $this;
    }

    /**
     * Get medialinktext
     *
     * @return string|null
     */
    public function getMedialinktext(): ?string
    {
    	return $this->medialinktext;
    }

    /**
     * Set mediatyp
     *
     * @param string|null $mediatyp
     * @return DfxNews
     */
    public function setMediatyp(? string $mediatyp): self
    {
    	$this->mediatyp = $mediatyp;

    	return $this;
    }

    /**
     * Get mediatyp
     *
     * @return string|null
     */
    public function getMediatyp(): ?string
    {
    	return $this->mediatyp;
    }





    private ?array $zielgruppe = null;


    /**
     * Set zielgruppe
     *
     * @param array $zielgruppe
     * @return DfxNews
     */
    public function setZielgruppe(array $zielgruppe): static
    {
        $this->zielgruppe = $zielgruppe;

        return $this;
    }

    /**
     * Get zielgruppe
     *
     * @return array|null
     */
    public function getZielgruppe(): ?array
    {
        return $this->zielgruppe;
    }


    private ?bool $filter1 = null;

    private ?bool $filter2 = null;

    private ?bool $filter3 = null;

    private ?bool $filter4 = null;

    private ?bool $filter5 = null;


    private ?string $text1 = null;

    private ?string $text2 = null;

    private ?string $text3 = null;

    private ?string $text4 = null;

    private ?string $text5 = null;



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

    public function setOptionsRadio(?string $optionsRadio): self
    {
        $this->optionsRadio = $optionsRadio;

        return $this;
    }

    public function getOptionsMenue(): ?string
    {
        return $this->optionsMenue;
    }

    public function setOptionsMenue(?string $optionsMenue): self
    {
        $this->optionsMenue = $optionsMenue;

        return $this;
    }

    public function getOptionsCheckboxes(): ?array
    {
        return $this->optionsCheckboxes;
    }

    public function setOptionsCheckboxes(?array $optionsCheckboxes): self
    {
        $this->optionsCheckboxes = $optionsCheckboxes;

        return $this;
    }

    public function getOptionsMenueMulti(): ?array
    {
        return $this->optionsMenueMulti;
    }

    public function setOptionsMenueMulti(?array $optionsMenueMulti): self
    {
        $this->optionsMenueMulti = $optionsMenueMulti;

        return $this;
    }


    private ?int $counter = null;

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


    public function isMenueeintrag(): ?bool
    {
        return $this->menueeintrag;
    }



}
