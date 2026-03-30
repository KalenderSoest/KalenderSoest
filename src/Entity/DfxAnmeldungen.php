<?php

namespace App\Entity;

use DateTime;

/**
 * DfxAnmeldungen
 */
class DfxAnmeldungen
{
    private ?string $nachname = null;

    private ?string $vorname = null;

    private ?string $org = null;


    private ?string $strasse = null;

    private ?string $plz = null;

    private ?string $ort = null;

    private ?string $email = null;

    private ?string $tel = null;

    private ?string $mobil = null;

    private ?int $anzahl = null;

    private ?DateTime $datum = null;

    /**
     * @var integer
     */
    private int $id;

    private ?DfxTermine $termin = null;

    private ?DfxKonf $datefix = null;

    public function setNachname(string $nachname): static
    {
        $this->nachname = $nachname;

        return $this;
    }

    public function getNachname(): ?string
    {
        return $this->nachname;
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

    public function setStrasse(?string $strasse): static
    {
        $this->strasse = $strasse;

        return $this;
    }

    public function getStrasse(): ?string
    {
        return $this->strasse;
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

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setTel(?string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setMobil(?string $mobil): static
    {
        $this->mobil = $mobil;

        return $this;
    }

    public function getMobil(): ?string
    {
        return $this->mobil;
    }

    public function setAnzahl(int $anzahl): static
    {
        $this->anzahl = $anzahl;

        return $this;
    }

    public function getAnzahl(): ?int
    {
        return $this->anzahl;
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTermin(?DfxTermine $termin): static
    {
        $this->termin = $termin;

        return $this;
    }

    public function getTermin(): ?DfxTermine
    {
        return $this->termin;
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
    
    private ?string $notiz = null;

    public function setNotiz(?string $notiz): static
    {
        $this->notiz = $notiz;

        return $this;
    }

    public function getNotiz(): ?string
    {
        return $this->notiz;
    }
    
    private ?bool $datenschutz = null;

    public function setDatenschutz(?bool $datenschutz): static
    {
        $this->datenschutz = $datenschutz;

        return $this;
    }

    /**
     * Get datenschutz
     *
     * @return bool|null
     */
    public function getDatenschutz(): ?bool
    {
        return $this->datenschutz;
    }

    /**
     * @var boolean
     */
    private bool $agb;

    public function setAgb(?bool $agb): static
    {
        $this->agb = $agb;

        return $this;
    }

    /**
     * Get agb
     *
     * @return boolean
     */
    public function getAgb(): bool
    {
        return $this->agb;
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

    private ?string $text6 = null;

    private ?string $text7 = null;

    private ?string $text8 = null;

    private ?string $text9 = null;

    private ?string $text10 = null;

    private ?string $text11 = null;

    private ?string $text12 = null;

    private ?string $text13 = null;

    private ?string $text14 = null;

    private ?string $text15 = null;

    private ?string $textbox1 = null;

    private ?string $textbox2 = null;

    private ?string $code = null;

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

    public function setText6(?string $text6): static
    {
        $this->text6 = $text6;

        return $this;
    }

    public function getText6(): ?string
    {
        return $this->text6;
    }

    public function setText7(?string $text7): static
    {
        $this->text7 = $text7;

        return $this;
    }

    public function getText7(): ?string
    {
        return $this->text7;
    }

    public function setText8(?string $text8): static
    {
        $this->text8 = $text8;

        return $this;
    }

    public function getText8(): ?string
    {
        return $this->text8;
    }

    public function setText9(?string $text9): static
    {
        $this->text9 = $text9;

        return $this;
    }

    public function getText9(): ?string
    {
        return $this->text9;
    }

    public function setText10(?string $text10): static
    {
        $this->text10 = $text10;

        return $this;
    }

    public function getText10(): ?string
    {
        return $this->text10;
    }

    public function setText11(?string $text11): static
    {
        $this->text11 = $text11;

        return $this;
    }

    public function getText11(): ?string
    {
        return $this->text11;
    }

    public function setText12(?string $text12): static
    {
        $this->text12 = $text12;

        return $this;
    }

    public function getText12(): ?string
    {
        return $this->text12;
    }

    public function setText13(?string $text13): static
    {
        $this->text13 = $text13;

        return $this;
    }

    public function getText13(): ?string
    {
        return $this->text13;
    }

    public function setText14(?string $text14): static
    {
        $this->text14 = $text14;

        return $this;
    }

    public function getText14(): ?string
    {
        return $this->text14;
    }

    public function setText15(?string $text15): static
    {
        $this->text15 = $text15;

        return $this;
    }

    public function getText15(): ?string
    {
        return $this->text15;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getOrg(): ?string
    {
        return $this->org;
    }

    public function setOrg(?string $org): static
    {
        $this->org = $org;

        return $this;
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

    public function isDatenschutz(): ?bool
    {
        return $this->datenschutz;
    }

    public function isAgb(): ?bool
    {
        return $this->agb;
    }
}
