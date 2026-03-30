<?php

namespace App\Entity;

/**
 * DfxKonf
 */
class DfxKonf
{
    private ?string $titel = null;

    /**
     * @var array
     */
    private ?array $rubriken = null;

    private ?string $htmlHead = null;

    private ?string $htmlFooter = null;

    private ?int $lengthTeaser = null;

    /**
     * @var integer
     */
    private ?int $itemsListe = null;

    /**
     * @var string
     */
    private ?string $dfxWidth = null;

    /**
     * @var string
     */
    private ?string $dfxAlign = null;

    /**
     * @var integer
     */
    private int $imgWidth;

    /**
     * @var integer
     */
    private int $imgHeight;

    /**
     * @var integer
     */
    private int $imgPrevWidth;

    /**
     * @var integer
     */
    private int $imgPrevHeight;

    /**
     * @var boolean
     */
    private bool $navListe = true;

    /**
     * @var boolean
     */
    private bool $navDetail = true;

    /**
     * @var string
     */
    private ?int $navWidth = 0;

    /**
     * @var string
     */
    private ?string $navPos = null;

    /**
     * @var boolean
     */
    private bool $filterRubrik = true;

    /**
     * @var boolean
     */
    private bool $filterNat = false;

    /**
     * @var boolean
     */
    private bool $filterPlz = true;

    /**
     * @var boolean
     */
    private bool $filterplzarea = true;

    /**
     * @var boolean
     */
    private bool $filterUmkreis = true;

    /**
     * @var integer
     */
    private int $filterUmkreiskm = 10;

    /**
     * @var boolean
     */
    private bool $filterOrt = true;

    /**
     * @var boolean
     */
    private bool $filterRegion = false;

    /**
     * @var boolean
     */
    private bool $filterLocation = true;

    /**
     * @var boolean
     */
    private bool $filterVeranstalter = true;

    /**
     * @var boolean
     */
    private bool $feldImg = true;

    /**
     * @var boolean
     */
    private bool $feldPdf = true;

    /**
     * @var boolean
     */
    private bool $feldNat = false;

    /**
     * @var boolean
     */
    private bool $feldPlz = true;

    /**
     * @var boolean
     */
    private bool $feldOrt = true;

    /**
     * @var boolean
     */
    private bool $feldKarten = false;

    /**
     * @var boolean
     */
    private bool $feldKategorien = false;

    /**
     * @var boolean
     */
    private bool $feldAnmeldung = false;

    /**
     * @var boolean
     */
    private bool $feldPlatzlimit = false;

    /**
     * @var boolean
     */
    private bool $feldLocation = true;

    /**
     * @var boolean
     */
    private bool $feldVeranstalter = true;

    /**
     * @var boolean
     */
    private bool $feldLink = true;

    /**
     * @var boolean
     */
    private bool $feldRegion = false;

    /**
     * @var boolean
     */
    private bool $feldOnlinetermin = false;

    /**
     * @var boolean
     */
    private bool $allowInputAll = false;

    /**
     * @var boolean
     */
    private bool $allowPubAll = false;

    /**
     * @var string
     */
    private ?string $sprache = null;

    /**
     * @var boolean
     */
    private bool $allowRemind = true;

    /**
     * @var boolean
     */
    private bool $allowMail = true;

    /**
     * @var boolean
     */
    private bool $allowExport = true;

    /**
     * @var boolean
     */
    private bool $allowPrint = true;

    /**
     * @var boolean
     */
    private bool $useIcons = true;

    /**
     * @var string
     */
    private ?string $dfxTpl = null;

    /**
     * @var string
     */
    private ?string $dfxTplVersion = null;

    /**
     * @var string
     */
    private ?string $frontendUrl = null;



    /**
	 * 
     * @var integer
     */

    private int $id;

    private ?DfxNfxUser $user = null;

    public function setTitel(?string $titel): static
    {
        $this->titel = $titel;

        return $this;
    }

    public function setRubriken(?array $rubriken): static
    {
        $this->rubriken = $rubriken;

        return $this;
    }

    public function getRubriken(): ?array
    {
        return $this->rubriken;
    }

    public function setLengthTeaser(?int $lengthTeaser): static
    {
        $this->lengthTeaser = $lengthTeaser;

        return $this;
    }

    public function getLengthTeaser(): ?int
    {
        return $this->lengthTeaser;
    }

    public function setItemsListe(?int $itemsListe): static
    {
        $this->itemsListe = $itemsListe;

        return $this;
    }

    public function getItemsListe(): ?int
    {
        return $this->itemsListe;
    }

    public function setDfxWidth(?string $dfxWidth): static
    {
        $this->dfxWidth = $dfxWidth;

        return $this;
    }

    public function getDfxWidth(): ?string
    {
        return $this->dfxWidth;
    }

    public function setDfxAlign(?string $dfxAlign): static
    {
        $this->dfxAlign = $dfxAlign;

        return $this;
    }

    public function getDfxAlign(): ?string
    {
        return $this->dfxAlign;
    }

    public function setImgWidth(?int $imgWidth): static
    {
        $this->imgWidth = $imgWidth;

        return $this;
    }

    public function getImgWidth(): ?int
    {
        return $this->imgWidth;
    }

    public function setImgHeight(?int $imgHeight): static
    {
        $this->imgHeight = $imgHeight;

        return $this;
    }

    public function getImgHeight(): ?int
    {
        return $this->imgHeight;
    }

    public function setImgPrevWidth(?int $imgPrevWidth): static
    {
        $this->imgPrevWidth = $imgPrevWidth;

        return $this;
    }

    public function getImgPrevWidth(): ?int
    {
        return $this->imgPrevWidth;
    }

    public function setImgPrevHeight(?int $imgPrevHeight): static
    {
        $this->imgPrevHeight = $imgPrevHeight;

        return $this;
    }

    public function getImgPrevHeight(): ?int
    {
        return $this->imgPrevHeight;
    }

    public function setNavListe(?bool $navListe): static
    {
        $this->navListe = $navListe;

        return $this;
    }

    /**
     * Get navListe
     *
     * @return boolean
     */
    public function getNavListe(): bool
    {
        return $this->navListe;
    }

    public function setNavDetail(?bool $navDetail): static
    {
        $this->navDetail = $navDetail;

        return $this;
    }

    /**
     * Get navDetail
     *
     * @return boolean
     */
    public function getNavDetail(): bool
    {
        return $this->navDetail;
    }

    public function setNavWidth(?int $navWidth): static
    {
        $this->navWidth = $navWidth;

        return $this;
    }

    public function getNavWidth(): ?int
    {
        return $this->navWidth;
    }

    public function setNavPos(?string $navPos): static
    {
        $this->navPos = $navPos;

        return $this;
    }

    public function getNavPos(): ?string
    {
        return $this->navPos;
    }

    public function setFilterRubrik(?bool $filterRubrik): static
    {
        $this->filterRubrik = $filterRubrik;

        return $this;
    }

    /**
     * Get filterRubrik
     *
     * @return boolean
     */
    public function getFilterRubrik(): bool
    {
        return $this->filterRubrik;
    }

    public function setFilterNat(?bool $filterNat): static
    {
        $this->filterNat = $filterNat;

        return $this;
    }

    /**
     * Get filterNat
     *
     * @return boolean
     */
    public function getFilterNat(): bool
    {
        return $this->filterNat;
    }

    /**
     * Set filterPlz
     *
     * @param boolean $filterPlz
     *
     * @return DfxKonf
     */
    public function setFilterPlz(bool $filterPlz): static
    {
        $this->filterPlz = $filterPlz;

        return $this;
    }

    /**
     * Get filterPlz
     *
     * @return boolean
     */
    public function getFilterPlz(): bool
    {
        return $this->filterPlz;
    }

    public function setFilterplzarea(?bool $filterplzarea): static
    {
        $this->filterplzarea = $filterplzarea;

        return $this;
    }

    /**
     * Get filterplzarea
     *
     * @return bool|string
     */
    public function getFilterplzarea(): bool|string
    {
        return $this->filterplzarea;
    }

    public function setFilterUmkreis(?bool $filterUmkreis): static
    {
        $this->filterUmkreis = $filterUmkreis;

        return $this;
    }

    /**
     * Get filterUmkreis
     *
     * @return boolean
     */
    public function getFilterUmkreis(): bool
    {
        return $this->filterUmkreis;
    }

    /**
     * Set filterUmkreiskm
     *
     * @param integer $filterUmkreiskm
     *
     * @return DfxKonf
     */
    public function setFilterUmkreiskm(int $filterUmkreiskm): static
    {
        $this->filterUmkreiskm = $filterUmkreiskm;

        return $this;
    }

    /**
     * Get filterUmkreiskm
     *
     * @return integer
     */
    public function getFilterUmkreiskm(): int
    {
        return $this->filterUmkreiskm;
    }

    public function setFilterOrt(?bool $filterOrt): static
    {
        $this->filterOrt = $filterOrt;

        return $this;
    }

    /**
     * Get filterOrt
     *
     * @return boolean
     */
    public function getFilterOrt(): bool
    {
        return $this->filterOrt;
    }

    public function setFilterRegion(?bool $filterRegion): static
    {
        $this->filterRegion = $filterRegion;

        return $this;
    }

    /**
     * Get filterRegion
     *
     * @return boolean
     */
    public function getFilterRegion(): bool
    {
        return $this->filterRegion;
    }

    public function setFilterLocation(?bool $filterLocation): static
    {
        $this->filterLocation = $filterLocation;

        return $this;
    }

    /**
     * Get filterLocation
     *
     * @return boolean
     */
    public function getFilterLocation(): bool
    {
        return $this->filterLocation;
    }

    public function setFilterVeranstalter(?bool $filterVeranstalter): static
    {
        $this->filterVeranstalter = $filterVeranstalter;

        return $this;
    }

    /**
     * Get filterVeranstalter
     *
     * @return boolean
     */
    public function getFilterVeranstalter(): bool
    {
        return $this->filterVeranstalter;
    }

    public function setFeldImg(?bool $feldImg): static
    {
        $this->feldImg = $feldImg;

        return $this;
    }

    /**
     * Get feldImg
     *
     * @return boolean
     */
    public function getFeldImg(): bool
    {
        return $this->feldImg;
    }

    public function setFeldPdf(?bool $feldPdf): static
    {
        $this->feldPdf = $feldPdf;

        return $this;
    }

    /**
     * Get feldPdf
     *
     * @return boolean
     */
    public function getFeldPdf(): bool
    {
        return $this->feldPdf;
    }

    public function setFeldNat(?bool $feldNat): static
    {
        $this->feldNat = $feldNat;

        return $this;
    }

    /**
     * Get feldNat
     *
     * @return boolean
     */
    public function getFeldNat(): bool
    {
        return $this->feldNat;
    }

    public function setFeldPlz(?bool $feldPlz): static
    {
        $this->feldPlz = $feldPlz;

        return $this;
    }

    /**
     * Get feldPlz
     *
     * @return boolean
     */
    public function getFeldPlz(): bool
    {
        return $this->feldPlz;
    }

    public function setFeldOrt(?bool $feldOrt): static
    {
        $this->feldOrt = $feldOrt;

        return $this;
    }

    /**
     * Get feldOrt
     *
     * @return boolean
     */
    public function getFeldOrt(): bool
    {
        return $this->feldOrt;
    }

    public function setFeldKarten(?bool $feldKarten): static
    {
        $this->feldKarten = $feldKarten;

        return $this;
    }

    /**
     * Get feldKarten
     *
     * @return boolean
     */
    public function getFeldKarten(): bool
    {
        return $this->feldKarten;
    }

    /**
     * Set feldKategorien
     *
     * @param boolean $feldKategorien
     *
     * @return DfxKonf
     */
    public function setFeldKategorien(bool $feldKategorien): static
    {
        $this->feldKategorien = $feldKategorien;

        return $this;
    }

    /**
     * Get feldKategorien
     *
     * @return boolean
     */
    public function getFeldKategorien(): bool
    {
        return $this->feldKategorien;
    }

    public function setFeldAnmeldung(?bool $feldAnmeldung): static
    {
        $this->feldAnmeldung = $feldAnmeldung;

        return $this;
    }

    /**
     * Get feldAnmeldung
     *
     * @return boolean
     */
    public function getFeldAnmeldung(): bool
    {
        return $this->feldAnmeldung;
    }

    public function setFeldPlatzlimit(?bool $feldPlatzlimit): static
    {
        $this->feldPlatzlimit = $feldPlatzlimit;

        return $this;
    }

    /**
     * Get feldPlatzlimit
     *
     * @return boolean
     */
    public function getFeldPlatzlimit(): bool
    {
        return $this->feldPlatzlimit;
    }

    public function setFeldLocation(?bool $feldLocation): static
    {
        $this->feldLocation = $feldLocation;

        return $this;
    }

    /**
     * Get feldLocation
     *
     * @return boolean
     */
    public function getFeldLocation(): bool
    {
        return $this->feldLocation;
    }

    public function setFeldVeranstalter(?bool $feldVeranstalter): static
    {
        $this->feldVeranstalter = $feldVeranstalter;

        return $this;
    }

    /**
     * Get feldVeranstalter
     *
     * @return boolean
     */
    public function getFeldVeranstalter(): bool
    {
        return $this->feldVeranstalter;
    }

    public function setFeldLink(?bool $feldLink): static
    {
        $this->feldLink = $feldLink;

        return $this;
    }

    /**
     * Get feldLink
     *
     * @return boolean
     */
    public function getFeldLink(): bool
    {
        return $this->feldLink;
    }

    public function setFeldRegion(?bool $feldRegion): static
    {
        $this->feldRegion = $feldRegion;

        return $this;
    }

    /**
     * Get feldRegion
     *
     * @return boolean
     */
    public function getFeldRegion(): bool
    {
        return $this->feldRegion;
    }

    public function setAllowInputAll(?bool $allowInputAll): static
    {
        $this->allowInputAll = $allowInputAll;

        return $this;
    }

    /**
     * Get allowInputAll
     *
     * @return boolean
     */
    public function getAllowInputAll(): bool
    {
        return $this->allowInputAll;
    }

    public function setAllowPubAll(?bool $allowPubAll): static
    {
        $this->allowPubAll = $allowPubAll;

        return $this;
    }

    /**
     * Get allowPubAll
     *
     * @return boolean
     */
    public function getAllowPubAll(): bool
    {
        return $this->allowPubAll;
    }

    public function setSprache(?string $sprache): static
    {
        $this->sprache = $sprache;

        return $this;
    }

    public function getSprache(): ?string
    {
        return $this->sprache;
    }


    public function setAllowRemind(?bool $allowRemind): static
    {
        $this->allowRemind = $allowRemind;

        return $this;
    }

    /**
     * Get allowRemind
     *
     * @return boolean
     */
    public function getAllowRemind(): bool
    {
        return $this->allowRemind;
    }

    public function setAllowMail(?bool $allowMail): static
    {
        $this->allowMail = $allowMail;

        return $this;
    }

    /**
     * Get allowMail
     *
     * @return boolean
     */
    public function getAllowMail(): bool
    {
        return $this->allowMail;
    }

    public function setAllowExport(?bool $allowExport): static
    {
        $this->allowExport = $allowExport;

        return $this;
    }

    /**
     * Get allowExport
     *
     * @return boolean
     */
    public function getAllowExport(): bool
    {
        return $this->allowExport;
    }

    public function setAllowPrint(?bool $allowPrint): static
    {
        $this->allowPrint = $allowPrint;

        return $this;
    }

    /**
     * Get allowPrint
     *
     * @return boolean
     */
    public function getAllowPrint(): bool
    {
        return $this->allowPrint;
    }

    public function setUseIcons(?bool $useIcons): static
    {
        $this->useIcons = $useIcons;

        return $this;
    }

    /**
     * Get useIcons
     *
     * @return boolean
     */
    public function getUseIcons(): bool
    {
        return $this->useIcons;
    }

    public function setDfxTpl(?string $dfxTpl): static
    {
        $this->dfxTpl = $dfxTpl;

        return $this;
    }

    public function getDfxTpl(): ?string
    {
        return $this->dfxTpl;
    }

    public function setDfxTplVersion(?string $dfxTplVersion): static
    {
        $this->dfxTplVersion = $dfxTplVersion;

        return $this;
    }

    public function getDfxTplVersion(): ?string
    {
        return $this->dfxTplVersion;
    }

    public function setFrontendUrl(?string $frontendUrl): static
    {
        $this->frontendUrl = $frontendUrl;

        return $this;
    }

    public function getFrontendUrl(): ?string
    {
        return $this->frontendUrl;
    }



    /**
     * Set id
     *
     * @param integer $id
     * @return DfxKonf
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

    /**
     * @var boolean
     */
    private bool $feldLead = true;

    /**
     * @var boolean
     */
    private bool $feldBeschreibung = true;

    public function setFeldLead(?bool $feldLead): static
    {
        $this->feldLead = $feldLead;

        return $this;
    }

    /**
     * Get feldLead
     *
     * @return boolean
     */
    public function getFeldLead(): bool
    {
        return $this->feldLead;
    }

    public function setFeldBeschreibung(?bool $feldBeschreibung): static
    {
        $this->feldBeschreibung = $feldBeschreibung;

        return $this;
    }

    /**
     * Get feldBeschreibung
     *
     * @return boolean
     */
    public function getFeldBeschreibung(): bool
    {
        return $this->feldBeschreibung;
    }

    /**
     * @var boolean
     */
    private bool $useMap = true;

    /**
     * @var boolean
     */
    private bool $allowApi = true;

    /**
     * @var integer
     */
    private int $maxApiItems = 1000;

    /**
     * @var integer
     */
    private int $pageApiItems = 20;

    /**
     * @var integer
     */
    private int $initZoom = 13;





    public function setUseMap(?bool $useMap): static
    {
        $this->useMap = $useMap;

        return $this;
    }

    /**
     * Get useMap
     *
     * @return boolean
     */
    public function getUseMap(): bool
    {
        return $this->useMap;
    }

    public function setAllowApi(?bool $allowApi): static
    {
        $this->allowApi = $allowApi;

        return $this;
    }

    public function getAllowApi(): bool
    {
        return $this->allowApi;
    }

    public function setMaxApiItems(?int $maxApiItems): static
    {
        $this->maxApiItems = $maxApiItems;

        return $this;
    }

    public function getMaxApiItems(): ?int
    {
        return $this->maxApiItems;
    }

    public function setPageApiItems(?int $pageApiItems): static
    {
        $this->pageApiItems = $pageApiItems;

        return $this;
    }

    public function getPageApiItems(): ?int
    {
        return $this->pageApiItems;
    }

    public function setInitZoom(?int $initZoom): static
    {
        $this->initZoom = $initZoom;

        return $this;
    }

    public function getInitZoom(): ?int
    {
        return $this->initZoom;
    }



    /**
     * @var string
     */
    private ?string $dfxFarbe = null;

    /**
     * @var boolean
     */
    private bool $dfxRadius = true;

    public function setDfxFarbe(?string $dfxFarbe): static
    {
        $this->dfxFarbe = $dfxFarbe;

        return $this;
    }

    public function getDfxFarbe(): ?string
    {
        return $this->dfxFarbe;
    }

    public function setDfxRadius(?bool $dfxRadius): static
    {
        $this->dfxRadius = $dfxRadius;

        return $this;
    }

    /**
     * Get dfxRadius
     *
     * @return boolean
     */
    public function getDfxRadius(): bool
    {
        return $this->dfxRadius;
    }

    /**
     * @var string
     */
    private ?string $dfxFarbeEigen = null;

    public function setDfxFarbeEigen(?string $dfxFarbeEigen): static
    {
        $this->dfxFarbeEigen = $dfxFarbeEigen;

        return $this;
    }

    public function getDfxFarbeEigen(): ?string
    {
        return $this->dfxFarbeEigen;
    }

    /**
     * @var boolean
     */
    private bool $imgListe = true;

    public function setImgListe(?bool $imgListe): static
    {
        $this->imgListe = $imgListe;

        return $this;
    }

    /**
     * Get imgListe
     *
     * @return boolean
     */

    public function getImgListe(): bool
    {
        return $this->imgListe;
    }

    /**
     * @var boolean
     */
    private bool $feldTicketlink = false;

    public function setFeldTicketlink(?bool $feldTicketlink): static
    {
        $this->feldTicketlink = $feldTicketlink;

        return $this;
    }

    /**
     * Get feldTicketlink
     *
     * @return boolean
     */
    public function getFeldTicketlink(): bool
    {
        return $this->feldTicketlink;
    }


    /**
     * @var string
     */
    private ?string $dfxCss = null;



    public function setDfxCss(?string $dfxCss): static
    {
        $this->dfxCss = $dfxCss;

        return $this;
    }

    public function getDfxCss(): ?string
    {
        return $this->dfxCss;
    }


    /**
     * @var boolean
     */
    private bool $filterKalender = true;

    /**
     * @var boolean
     */
    private bool $filterDatum = true;

    /**
     * @var boolean
     */
    private bool $filterSuche = true;

    public function setFilterKalender(?bool $filterKalender): static
    {
        $this->filterKalender = $filterKalender;

        return $this;
    }

    /**
     * Get filterKalender
     *
     * @return boolean
     */
    public function getFilterKalender(): bool
    {
        return $this->filterKalender;
    }

    public function setFilterDatum(?bool $filterDatum): static
    {
        $this->filterDatum = $filterDatum;

        return $this;
    }

    /**
     * Get filterDatum
     *
     * @return boolean
     */
    public function getFilterDatum(): bool
    {
        return $this->filterDatum;
    }

    public function setFilterSuche(?bool $filterSuche): static
    {
        $this->filterSuche = $filterSuche;

        return $this;
    }

    /**
     * Get filterSuche
     *
     * @return boolean
     */
    public function getFilterSuche(): bool
    {
        return $this->filterSuche;
    }

    /**
     * @var boolean
     */
    private bool $showPlz = true;

    /**
     * @var boolean
     */
    private bool $showOrt = true;

    /**
     * @var boolean
     */
    private bool $showLokal = true;

    /**
     * @var boolean
     */
    private bool $showStrasse = true;

    /**
     * @var boolean
     */
    private bool $zwueDatum = true;

    /**
     * @var string
     */
    private ?string $bgDatefix = '#fff';

    /**
     * @var string
     */
    private ?string $bgNav = '#fff';

    /**
     * @var string
     */
    private ?string $trennzeichen = '|';

    /**
     * @var string
     */
    private ?string $dfxFontSize = null;

    /**
     * @var string
     */
    private ?string $dfxFontColor = null;

    /**
     * @var boolean
     */
    private bool $paginationTop = true;

    /**
     * @var boolean
     */
    private bool $paginationBottom = true;

    public function setShowPlz(?bool $showPlz): static
    {
        $this->showPlz = $showPlz;

        return $this;
    }

    /**
     * Get showPlz
     *
     * @return boolean
     */
    public function getShowPlz(): bool
    {
        return $this->showPlz;
    }

    public function setShowOrt(?bool $showOrt): static
    {
        $this->showOrt = $showOrt;

        return $this;
    }

    /**
     * Get showOrt
     *
     * @return boolean
     */
    public function getShowOrt(): bool
    {
        return $this->showOrt;
    }

    public function setShowLokal(?bool $showLokal): static
    {
        $this->showLokal = $showLokal;

        return $this;
    }

    /**
     * Get showLokal
     *
     * @return boolean
     */
    public function getShowLokal(): bool
    {
        return $this->showLokal;
    }

    public function setShowStrasse(?bool $showStrasse): static
    {
        $this->showStrasse = $showStrasse;

        return $this;
    }

    /**
     * Get showStrasse
     *
     * @return boolean
     */
    public function getShowStrasse(): bool
    {
        return $this->showStrasse;
    }

    public function setZwueDatum(?bool $zwueDatum): static
    {
        $this->zwueDatum = $zwueDatum;

        return $this;
    }

    /**
     * Get zwueDatum
     *
     * @return boolean
     */
    public function getZwueDatum(): bool
    {
        return $this->zwueDatum;
    }

    public function setBgDatefix(?string $bgDatefix): static
    {
        $this->bgDatefix = $bgDatefix;

        return $this;
    }

    public function getBgDatefix(): ?string
    {
        return $this->bgDatefix;
    }

    public function setBgNav(?string $bgNav): static
    {
        $this->bgNav = $bgNav;

        return $this;
    }

    public function getBgNav(): ?string
    {
        return $this->bgNav;
    }

    public function setTrennzeichen(?string $trennzeichen): static
    {
        $this->trennzeichen = $trennzeichen;

        return $this;
    }

    public function getTrennzeichen(): ?string
    {
        return $this->trennzeichen;
    }

    public function setDfxFontSize(?string $dfxFontSize): static
    {
        $this->dfxFontSize = $dfxFontSize;

        return $this;
    }

    public function getDfxFontSize(): ?string
    {
        return $this->dfxFontSize;
    }

    public function setDfxFontColor(?string $dfxFontColor): static
    {
        $this->dfxFontColor = $dfxFontColor;

        return $this;
    }

    public function getDfxFontColor(): ?string
    {
        return $this->dfxFontColor;
    }

    public function setPaginationTop(?bool $paginationTop): static
    {
        $this->paginationTop = $paginationTop;

        return $this;
    }

    /**
     * Get paginationTop
     *
     * @return boolean
     */
    public function getPaginationTop(): bool
    {
        return $this->paginationTop;
    }

    public function setPaginationBottom(?bool $paginationBottom): static
    {
        $this->paginationBottom = $paginationBottom;

        return $this;
    }

    /**
     * Get paginationBottom
     *
     * @return boolean
     */
    public function getPaginationBottom(): bool
    {
        return $this->paginationBottom;
    }

    /**
     * @var boolean
     */
    private bool $ownFont = false;

    public function setOwnFont(?bool $ownFont): static
    {
        $this->ownFont = $ownFont;

        return $this;
    }

    /**
     * Get ownFont
     *
     * @return boolean
     */
    public function getOwnFont(): bool
    {
        return $this->ownFont;
    }

    /**
     * @var string
     */
    private ?string $dfxFontType = null;

    public function setDfxFontType(?string $dfxFontType): static
    {
        $this->dfxFontType = $dfxFontType;

        return $this;
    }

    public function getDfxFontType(): ?string
    {
        return $this->dfxFontType;
    }

    /**
     * @var boolean
     */
    private bool $feldVideo = false;

    public function setFeldVideo(?bool $feldVideo): static
    {
        $this->feldVideo = $feldVideo;

        return $this;
    }

    /**
     * Get feldVideo
     *
     * @return boolean
     */
    public function getFeldVideo(): bool
    {
        return $this->feldVideo;
    }

    /**
     * @var boolean
     */
    private bool $feldEintritt = true;

    public function setFeldEintritt(?bool $feldEintritt): static
    {
        $this->feldEintritt = $feldEintritt;

        return $this;
    }

    /**
     * Get feldEintritt
     *
     * @return boolean
     */
    public function getFeldEintritt(): bool
    {
        return $this->feldEintritt;
    }

    /**
     * @var integer
     */
    private int $dfxTplDetail;

    public function setDfxTplDetail(?int $dfxTplDetail): static
    {
        $this->dfxTplDetail = $dfxTplDetail;

        return $this;
    }

    public function getDfxTplDetail(): ?int
    {
        return $this->dfxTplDetail;
    }

    /**
     * @var boolean
     */
    private bool $isMeta = false;

    /**
     * @var boolean
     */
    private bool $isGroup = false;

    public function setIsMeta(?bool $isMeta): static
    {
        $this->isMeta = $isMeta;

        return $this;
    }

    /**
     * Get isMeta
     *
     * @return boolean
     */
    public function getIsMeta(): bool
    {
        return $this->isMeta;
    }

    public function setIsGroup(?bool $isGroup): static
    {
        $this->isGroup = $isGroup;

        return $this;
    }

    /**
     * Get isGroup
     *
     * @return boolean
     */
    public function getIsGroup(): bool
    {
        return $this->isGroup;
    }

    /**
     * @var integer
     */
    private int $archivTage;

    public function setArchivTage(?int $archivTage): static
    {
        $this->archivTage = $archivTage;

        return $this;
    }

    public function getArchivTage(): ?int
    {
        return $this->archivTage;
    }

    /**
     * @var boolean
     */
    private bool $showPlatzlimit = true;

    public function setShowPlatzlimit(?bool $showPlatzlimit): static
    {
        $this->showPlatzlimit = $showPlatzlimit;

        return $this;
    }

    /**
     * Get showPlatzlimit
     *
     * @return boolean
     */
    public function getShowPlatzlimit(): bool
    {
        return $this->showPlatzlimit;
    }

    /**
     * @var boolean
     */
    private bool $allowPubMetaAll = false;

    /**
     * @var boolean
     */
    private bool $allowPubGroupAll = false;

    /**
     * @var boolean
     */
    private bool $infoToAdmin = true;

    public function setAllowPubMetaAll(?bool $allowPubMetaAll): static
    {
        $this->allowPubMetaAll = $allowPubMetaAll;

        return $this;
    }

    /**
     * Get allowPubMetaAll
     *
     * @return boolean
     */
    public function getAllowPubMetaAll(): bool
    {
        return $this->allowPubMetaAll;
    }

    public function setAllowPubGroupAll(?bool $allowPubGroupAll): static
    {
        $this->allowPubGroupAll = $allowPubGroupAll;

        return $this;
    }

    /**
     * Get allowPubGroupAll
     *
     * @return boolean
     */
    public function getAllowPubGroupAll(): bool
    {
        return $this->allowPubGroupAll;
    }

    public function setInfoToAdmin(?bool $infoToAdmin): static
    {
        $this->infoToAdmin = $infoToAdmin;

        return $this;
    }

    /**
     * Get infoToAdmin
     *
     * @return boolean
     */
    public function getInfoToAdmin(): bool
    {
        return $this->infoToAdmin;
    }

    /**
     * @var boolean
     */
    private bool $allowSocial = true;

    public function setAllowSocial(?bool $allowSocial): static
    {
        $this->allowSocial = $allowSocial;

        return $this;
    }

    /**
     * Get allowSocial
     *
     * @return boolean
     */
    public function getAllowSocial(): bool
    {
        return $this->allowSocial;
    }

    /**
     * @var boolean
     */
    private bool $feldSubtitel = false;

    public function setFeldSubtitel(?bool $feldSubtitel): static
    {
        $this->feldSubtitel = $feldSubtitel;

        return $this;
    }

    /**
     * Get feldSubtitel
     *
     * @return boolean
     */
    public function getFeldSubtitel(): bool
    {
        return $this->feldSubtitel;
    }

    /**
     * @var array
     */
    private array $toGroup = [];

    public function setToGroup(?array $toGroup): static
    {
        $this->toGroup = $toGroup ?? [];

        return $this;
    }

    public function getToGroup(): array
    {
        return $this->toGroup ?? [];
    }

    /**
     * @var boolean
     */
    private bool $PubMetaAll = false;

    /**
     * @var boolean
     */
    private bool $PubGroupAll = false;

    /**
     * @var boolean
     */
    private bool $InheritMeta = false;

    /**
     * @var boolean
     */
    private bool $InheritGroup = false;

    public function setPubMetaAll(?bool $PubMetaAll): static
    {
        $this->PubMetaAll = $PubMetaAll;

        return $this;
    }

    /**
     * Get PubMetaAll
     *
     * @return boolean 
     */
    public function getPubMetaAll(): bool
    {
        return $this->PubMetaAll;
    }

    public function setPubGroupAll(?bool $PubGroupAll): static
    {
        $this->PubGroupAll = $PubGroupAll;

        return $this;
    }

    /**
     * Get PubGroupAll
     *
     * @return boolean 
     */
    public function getPubGroupAll(): bool
    {
        return $this->PubGroupAll;
    }

    public function setInheritMeta(?bool $InheritMeta): static
    {
        $this->InheritMeta = $InheritMeta;

        return $this;
    }

    /**
     * Get InheritMeta
     *
     * @return boolean 
     */
    public function getInheritMeta(): bool
    {
        return $this->InheritMeta;
    }

    public function setInheritGroup(?bool $InheritGroup): static
    {
        $this->InheritGroup = $InheritGroup;

        return $this;
    }

    /**
     * Get InheritGroup
     *
     * @return boolean 
     */
    public function getInheritGroup(): bool
    {
        return $this->InheritGroup;
    }

    /**
     * @var string
     */
    private ?string $dfxFarbeRaster = null;

    /**
     * @var string
     */
    private ?string $dfxFarbeRasterEigen = null;

    public function setDfxFarbeRaster(?string $dfxFarbeRaster): static
    {
        $this->dfxFarbeRaster = $dfxFarbeRaster;

        return $this;
    }

    public function getDfxFarbeRaster(): ?string
    {
        return $this->dfxFarbeRaster;
    }

    public function setDfxFarbeRasterEigen(?string $dfxFarbeRasterEigen): static
    {
        $this->dfxFarbeRasterEigen = $dfxFarbeRasterEigen;

        return $this;
    }

    public function getDfxFarbeRasterEigen(): ?string
    {
        return $this->dfxFarbeRasterEigen;
    }

    /**
     * @var boolean
     */
    private bool $feldGalerie;

    public function setFeldGalerie(?bool $feldGalerie): static
    {
        $this->feldGalerie = $feldGalerie;

        return $this;
    }

    /**
     * Get feldGalerie
     *
     * @return boolean 
     */
    public function getFeldGalerie(): bool
    {
        return $this->feldGalerie;
    }



    /**
     * @var integer
     */
    private ?int $maxLengthLead = null;

    /**
     * @var integer
     */
    private ?int $maxLengthBeschreibung = null;

    public function setMaxLengthLead(?int $maxLengthLead): static
    {
        $this->maxLengthLead = $maxLengthLead;

        return $this;
    }

    public function getMaxLengthLead(): ?int
    {
        return $this->maxLengthLead;
    }

    public function setMaxLengthBeschreibung(?int $maxLengthBeschreibung): static
    {
        $this->maxLengthBeschreibung = $maxLengthBeschreibung;

        return $this;
    }

    public function getMaxLengthBeschreibung(): ?int
    {
        return $this->maxLengthBeschreibung;
    }

    private ?string $imgLogo = null;

    private ?string $imgBanner = null;

    /**
     * @var string
     */
    private ?string $adresse = null;


    public function setImgLogo(?string $imgLogo): static
    {
        $this->imgLogo = $imgLogo;

        return $this;
    }

    public function getImgLogo(): ?string
    {
        return $this->imgLogo;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }



    /**
     * @var boolean
     */
    private bool $feldMedia = false;

    public function setFeldMedia(?bool $feldMedia): static
    {
        $this->feldMedia = $feldMedia;

        return $this;
    }

    /**
     * Get feldMedia
     *
     * @return boolean 
     */
    public function getFeldMedia(): bool
    {
        return $this->feldMedia;
    }


    /**
     * @var boolean
     */
    private bool $filterZielgruppe = true;




    public function setFilterZielgruppe(?bool $filterZielgruppe): static
    {
        $this->filterZielgruppe = $filterZielgruppe;

        return $this;
    }

    /**
     * Get filterZielgruppe
     *
     * @return boolean 
     */
    public function getFilterZielgruppe(): bool
    {
        return $this->filterZielgruppe;
    }

    /**
     * @var array
     */
    private array $zielgruppen;

    /**
     * @var boolean
     */
    private bool $InheritZielgruppenMeta = false;

    /**
     * @var boolean
     */
    private bool $InheritZielgruppenGroup = false;

    public function setZielgruppen(?array $zielgruppen): static
    {
        $this->zielgruppen = $zielgruppen;

        return $this;
    }

    public function getZielgruppen(): ?array
    {
        return $this->zielgruppen;
    }

    public function setInheritZielgruppenMeta(?bool $InheritZielgruppenMeta): static
    {
        $this->InheritZielgruppenMeta = $InheritZielgruppenMeta;

        return $this;
    }

    /**
     * Get InheritZielgruppenMeta
     *
     * @return boolean 
     */
    public function getInheritZielgruppenMeta(): bool
    {
        return $this->InheritZielgruppenMeta;
    }

    public function setInheritZielgruppenGroup(?bool $InheritZielgruppenGroup): static
    {
        $this->InheritZielgruppenGroup = $InheritZielgruppenGroup;

        return $this;
    }

    /**
     * Get InheritZielgruppenGroup
     *
     * @return boolean 
     */
    public function getInheritZielgruppenGroup(): bool
    {
        return $this->InheritZielgruppenGroup;
    }

    /**
     * @var string
     */
    private ?string $datenschutzUrl = null;

    /**
     * @var string
     */
    private ?string $impressumUrl = null;

    public function setDatenschutzUrl(?string $datenschutzUrl): static
    {
        $this->datenschutzUrl = $datenschutzUrl;

        return $this;
    }

    public function getDatenschutzUrl(): ?string
    {
        return $this->datenschutzUrl;
    }

    public function setImpressumUrl(?string $impressumUrl): static
    {
        $this->impressumUrl = $impressumUrl;

        return $this;
    }

    public function getImpressumUrl(): ?string
    {
        return $this->impressumUrl;
    }

    /**
     * @var boolean
     */
    private bool $feldOrtdb = false;

    public function setFeldOrtdb(?bool $feldOrtdb): static
    {
        $this->feldOrtdb = $feldOrtdb;

        return $this;
    }

    /**
     * Get feldOrtdb
     *
     * @return boolean 
     */
    public function getFeldOrtdb(): bool
    {
        return $this->feldOrtdb;
    }

    /**
     * @var boolean
     */
    private bool $filterDozenten = false;

    /**
     * @var boolean
     */
    private bool $feldDozenten = true;

    public function setFilterDozenten(?bool $filterDozenten): static
    {
        $this->filterDozenten = $filterDozenten;

        return $this;
    }

    /**
     * Get filterDozenten
     *
     * @return boolean 
     */
    public function getFilterDozenten(): bool
    {
        return $this->filterDozenten;
    }

    public function setFeldDozenten(?bool $feldDozenten): static
    {
        $this->feldDozenten = $feldDozenten;

        return $this;
    }

    /**
     * Get feldDozenten
     *
     * @return boolean 
     */
    public function getFeldDozenten(): bool
    {
        return $this->feldDozenten;
    }

    public function getFeldOnlinetermin(): ?bool
    {
        return $this->feldOnlinetermin;
    }

    public function setFeldOnlinetermin(?bool $feldOnlinetermin): static
    {
        $this->feldOnlinetermin = $feldOnlinetermin;

        return $this;
    }



    public function isNavListe(): ?bool
    {
        return $this->navListe;
    }

    public function isNavDetail(): ?bool
    {
        return $this->navDetail;
    }

    public function isFilterKalender(): ?bool
    {
        return $this->filterKalender;
    }

    public function isFilterRubrik(): ?bool
    {
        return $this->filterRubrik;
    }

    public function isFilterZielgruppe(): ?bool
    {
        return $this->filterZielgruppe;
    }

    public function isFilterDatum(): ?bool
    {
        return $this->filterDatum;
    }

    public function isFilterSuche(): ?bool
    {
        return $this->filterSuche;
    }

    public function isFilterNat(): ?bool
    {
        return $this->filterNat;
    }

    public function isFilterplzarea(): ?bool
    {
        return $this->filterplzarea;
    }

    public function isFilterUmkreis(): ?bool
    {
        return $this->filterUmkreis;
    }

    public function isFilterOrt(): ?bool
    {
        return $this->filterOrt;
    }

    public function isFilterRegion(): ?bool
    {
        return $this->filterRegion;
    }

    public function isFilterLocation(): ?bool
    {
        return $this->filterLocation;
    }

    public function isFilterVeranstalter(): ?bool
    {
        return $this->filterVeranstalter;
    }

    public function isFilterDozenten(): ?bool
    {
        return $this->filterDozenten;
    }

    public function isFeldSubtitel(): ?bool
    {
        return $this->feldSubtitel;
    }

    public function isFeldLead(): ?bool
    {
        return $this->feldLead;
    }

    public function isFeldBeschreibung(): ?bool
    {
        return $this->feldBeschreibung;
    }

    public function isFeldEintritt(): ?bool
    {
        return $this->feldEintritt;
    }

    public function isFeldImg(): ?bool
    {
        return $this->feldImg;
    }

    public function isFeldGalerie(): ?bool
    {
        return $this->feldGalerie;
    }

    public function isFeldVideo(): ?bool
    {
        return $this->feldVideo;
    }

    public function isFeldPdf(): ?bool
    {
        return $this->feldPdf;
    }

    public function isFeldMedia(): ?bool
    {
        return $this->feldMedia;
    }

    public function isFeldNat(): ?bool
    {
        return $this->feldNat;
    }

    public function isFeldRegion(): ?bool
    {
        return $this->feldRegion;
    }

    public function isFeldPlz(): ?bool
    {
        return $this->feldPlz;
    }

    public function isFeldOrt(): ?bool
    {
        return $this->feldOrt;
    }

    public function isFeldOrtdb(): ?bool
    {
        return $this->feldOrtdb;
    }

    public function isFeldKarten(): ?bool
    {
        return $this->feldKarten;
    }

    public function isFeldAnmeldung(): ?bool
    {
        return $this->feldAnmeldung;
    }

    public function isFeldPlatzlimit(): ?bool
    {
        return $this->feldPlatzlimit;
    }

    public function isShowPlatzlimit(): ?bool
    {
        return $this->showPlatzlimit;
    }

    public function isFeldLocation(): ?bool
    {
        return $this->feldLocation;
    }

    public function isFeldVeranstalter(): ?bool
    {
        return $this->feldVeranstalter;
    }

    public function isFeldDozenten(): ?bool
    {
        return $this->feldDozenten;
    }

    public function isFeldLink(): ?bool
    {
        return $this->feldLink;
    }

    public function isFeldTicketlink(): ?bool
    {
        return $this->feldTicketlink;
    }

    public function isFeldOnlinetermin(): ?bool
    {
        return $this->feldOnlinetermin;
    }

    public function isShowPlz(): ?bool
    {
        return $this->showPlz;
    }

    public function isShowOrt(): ?bool
    {
        return $this->showOrt;
    }

    public function isShowLokal(): ?bool
    {
        return $this->showLokal;
    }

    public function isShowStrasse(): ?bool
    {
        return $this->showStrasse;
    }

    public function isAllowInputAll(): ?bool
    {
        return $this->allowInputAll;
    }

    public function isAllowPubAll(): ?bool
    {
        return $this->allowPubAll;
    }

    public function isAllowPubMetaAll(): ?bool
    {
        return $this->allowPubMetaAll;
    }

    public function isAllowPubGroupAll(): ?bool
    {
        return $this->allowPubGroupAll;
    }

    public function isPubMetaAll(): ?bool
    {
        return $this->PubMetaAll;
    }

    public function isPubGroupAll(): ?bool
    {
        return $this->PubGroupAll;
    }

    public function isInheritMeta(): ?bool
    {
        return $this->InheritMeta;
    }

    public function isInheritGroup(): ?bool
    {
        return $this->InheritGroup;
    }

    public function isInheritZielgruppenMeta(): ?bool
    {
        return $this->InheritZielgruppenMeta;
    }

    public function isInheritZielgruppenGroup(): ?bool
    {
        return $this->InheritZielgruppenGroup;
    }

    public function isInfoToAdmin(): ?bool
    {
        return $this->infoToAdmin;
    }

    public function isAllowSocial(): ?bool
    {
        return $this->allowSocial;
    }

    public function isAllowRemind(): ?bool
    {
        return $this->allowRemind;
    }

    public function isAllowMail(): ?bool
    {
        return $this->allowMail;
    }

    public function isAllowExport(): ?bool
    {
        return $this->allowExport;
    }

    public function isAllowPrint(): ?bool
    {
        return $this->allowPrint;
    }

    public function isUseIcons(): ?bool
    {
        return $this->useIcons;
    }

    public function isUseMap(): ?bool
    {
        return $this->useMap;
    }



    public function isZwueDatum(): ?bool
    {
        return $this->zwueDatum;
    }

    public function isOwnFont(): ?bool
    {
        return $this->ownFont;
    }

    public function isPaginationTop(): ?bool
    {
        return $this->paginationTop;
    }

    public function isPaginationBottom(): ?bool
    {
        return $this->paginationBottom;
    }

    public function isDfxRadius(): ?bool
    {
        return $this->dfxRadius;
    }

    public function isIsMeta(): ?bool
    {
        return $this->isMeta;
    }

    public function isIsGroup(): ?bool
    {
        return $this->isGroup;
    }

    public function getImgBanner(): ?string
    {
        return $this->imgBanner;
    }

    public function setImgBanner(?string $imgBanner): static
    {
        $this->imgBanner = $imgBanner;
        return $this;
    }

    public function getHtmlHead(): ?string
    {
        return $this->htmlHead;
    }

    public function setHtmlHead(?string $htmlHead): static
    {
        $this->htmlHead = $htmlHead;

        return $this;
    }

    public function getHtmlFooter(): ?string
    {
        return $this->htmlFooter;
    }

    public function setHtmlFooter(?string $htmlFooter): static
    {
        $this->htmlFooter = $htmlFooter;

        return $this;
    }

    public function getTitel(): ?string
    {
        return $this->titel;
    }

    public function isMeta(): ?bool
    {
        return $this->isMeta;
    }

    public function isGroup(): ?bool
    {
        return $this->isGroup;
    }
}
