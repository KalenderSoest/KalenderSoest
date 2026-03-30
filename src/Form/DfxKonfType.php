<?php

namespace App\Form;

use App\Entity\DfxKonf;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class DfxKonfType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    	$cfgSkins =["#818181"=>"grau","#FF9900"=>"orange","#F05513"=>"hellrot","#c63d4e"=>"dunkelrot","#996600"=>"braun","#5e95cc"=>"hellblau","#4182C2"=>"dunkelblau","#84b231"=>"hellgrün","#839636"=>"olivgrün"];
    	$cfgRadius = ["1" => "abgerundet", "0" => "eckig"];
        $cfgFontsize = ["0.8rem"=>"0.8rem", "0.9rem"=>"0.9rem", "1.0rem"=>"1.0rem", "1.1rem"=>"1.1rem", "1.2rem"=>"1.2rem"];
    	$cfgNavWidth = ["0" => "automatisch", "3" => "1/4 der Breite", "4" => "1/3 der Beite"];
    	$cfgNavpos = ["top" =>"oben" ,"left" => "links","right" => "rechts"];
    	$cfgImgListe = ["0" => "kleine Bilder / ohne Bild kein Einzug", "1" => "kleine Bilder / nur mit Bild Einzug", "2" => "Größere Bilder / ohne Bild kein Einzug"];
    	$cfgTplVersion = ["kurz" => "Liste 1-zeilig -> Detailansicht ", "zweizeilig" => "Liste 2-zeilig -> Detailansicht", "dreizeilig" => "Liste 3-zeilig -> Detailansicht", "kompakt" => "Liste mehrzeilig/Vorschaubild -> Detailansicht", "lang" => "Liste mit kompletten Daten"];
    	$cfgTpl= ["blank" => "reiner Text","raster" => "Raster hinterlegt", "linie" =>"Trennlinie", "block" => "Überschrift mit Raster"];
    	/*
    	$cfgTpl["kurz"] = array("blank" => "reiner Text","raster" => "Raster hinterlegt", "linie" =>"Trennlinie");
    	$cfgTpl["zweizeilig"] = array("blank" => "reiner Text","raster" => "Raster hinterlegt", "linie" =>"Trennlinie");
    	$cfgTpl["dreizeilig"] = array("raster" => "Raster hinterlegt", "linie" =>"Trennlinie");
    	$cfgTpl["kompakt"] = array("blank" => "reiner Text","raster" => "Raster hinterlegt", "linie" =>"Trennlinie", "block" => "Überschrift mit Raster");
    	$cfgTpl["lang"] = array("blank" => "reiner Text","raster" => "Raster hinterlegt", "linie" =>"Trennlinie", "block" => "Überschrift mit Raster");
    	*/
    	$cfgTplDetail = ["1" => "Spalten 1:1", "2" => "Spalten 2:1 / Karten rechts", "3" => "Spalten 2:1 / Karte unten", "4" => "Spalten 2:1 / Karte optional", "5" => "Flach (ohne Spalten) / Karte optional"];

    	$cfgSprachen = ["de" => "Deutsch", "en" => "Englisch"];
    	$cfgTrennzeichen = ["|" => "Strich", "," => "Komma"];

    	// $em = $this->Doctrine->getManager();
    	$repository = $options['em']->getRepository(DfxKonf::class);
    	$entities =  $repository->createQueryBuilder('k')
    	->select(['k.id, k.titel'])
    	->where('k.isGroup = 1')
    	->getQuery()->getArrayResult();

    	$choices=[];
    	foreach ($entities as $entity) {
    		if($entity['titel'] != NULL)
    			$choices[$entity['id']] = $entity['titel'];
    		else
    			$choices[$entity['id']] = $entity['id'];
    	}


        $builder
            ->add('rubriken', CollectionType::class, ['entry_type'   => TextType::class, 'prototype' => true,'allow_add' => true, 'allow_delete' => true,  'entry_options'  => ['required'  => false, 'attr' => ['class' => 'email-box']]])
            ->add('zielgruppen', CollectionType::class, ['entry_type'   => TextType::class, 'prototype' => true,'allow_add' => true, 'allow_delete' => true,  'entry_options'  => ['required'  => false, 'attr' => ['class' => 'email-box']]])
            ->add('lengthTeaser', TextType::class, ['label' => 'Zeichen bis zum "mehr"-Link', 'required' => false])
            ->add('maxLengthBeschreibung', TextType::class, ['label' => false, 'required' => false, 'attr' =>['placeholder' => 'Max Zeichen Text / leer = unbegrenzt', 'aria-label'=>'Max Zeichen Text / leer = unbegrenzt']])
            ->add('maxLengthLead', TextType::class, ['label' => false, 'required' => false, 'attr' =>['placeholder' => 'Max Zeichen Kurzfassung / leer = unbegrenzt', 'aria-label'=>'Max Zeichen Kurzfassung / leer = unbegrenzt']])
            ->add('itemsListe', TextType::class, ['label' => 'Anzahl Termine in Listenansicht', 'required' => false])
            ->add('zwueDatum', CheckboxType::class, ['label' => 'Datum als Zwischenüberschrift in Listen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('paginationTop', CheckboxType::class, ['label' => 'Blätterfunktion oben', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('paginationBottom', CheckboxType::class, ['label' => 'Blätterfunktion unten', 'required' => false, 'attr' => ['noFormControl' => true]])
			->add('dfxTplVersion', ChoiceType::class, ['label' => 'Vorlage für Listenansicht', 'placeholder' => 'Vorlagenversion auswählen', 'choices' => array_flip($cfgTplVersion)])
            ->add('dfxTpl', ChoiceType::class, ['label' => 'Vorlage auswählen', 'placeholder' => 'Vorlage auswählen', 'choices' => array_flip($cfgTpl)])
            ->add('dfxTplDetail', ChoiceType::class, ['label' => 'Vorlage Detailansicht auswählen', 'placeholder' => 'Vorlage Detailansicht auswählen', 'choices' => array_flip($cfgTplDetail)])
            ->add('dfxFarbe', ChoiceType::class, ['label' => 'Farbe Links/Buttons/Flächen', 'required' => false, 'placeholder' => 'Farbschema auswählen', 'choices' => array_flip($cfgSkins), 'empty_data' => null])
            ->add('dfxFarbeEigen', TextType::class, ['label' => 'Eigene Farbe Links/Buttons/Flächen','required' => false, 'attr' =>['placeholder' => 'HEX-Farbwert']])
            ->add('dfxFarbeRaster', ChoiceType::class, ['label' => 'Farbe Raster/Ränder/Linien', 'required' => false, 'placeholder' => 'Farbschema auswählen', 'choices' => array_flip($cfgSkins), 'empty_data' => null])
            ->add('dfxFarbeRasterEigen', TextType::class, ['label' => 'Eigene Farbe Raster/Ränder/Linien','required' => false, 'attr' =>['placeholder' => 'HEX-Farbwert']])
            ->add('bgDatefix', TextType::class, ['label' => 'Hintergrundfarbe','required' => false, 'attr' =>['placeholder' => 'HEX-Farbwert']])
            ->add('bgNav', TextType::class, ['label' => 'Hintergrundfarbe Navigation','required' => false, 'attr' =>['placeholder' => 'HEX-Farbwert']])
            /*
            ->add('ownFont', CheckboxType::class, array('label' => ' Vom HTML-Umfeld abweichende Schrifteinstellungen verwenden', 'required' => false, 'attr' => array('noFormControl' => true)))
            */
            ->add('dfxFontType', TextType::class, ['label' => 'Abweichende Schriftart','required' => false, 'attr' =>['placeholder' => 'Schrift/Schriftenliste']])
            ->add('dfxFontColor', TextType::class, ['label' => 'Abweichende Schriftfarbe','required' => false, 'attr' =>['placeholder' => 'HEX-Farbwert']])
            ->add('dfxFontSize', ChoiceType::class, ['label' => 'Schriftgröße in rem', 'choices' => $cfgFontsize, 'placeholder' => 'Schriftgröße in rem', 'required' => false, 'empty_data' => null])

             ->add('dfxRadius', ChoiceType::class, ['label' => 'Kanten/Ecken', 'choices' => array_flip($cfgRadius)])
             ->add('trennzeichen', ChoiceType::class, ['label' => 'Trennzeichen', 'choices' => array_flip($cfgTrennzeichen)])

            ->add('imgWidth', TextType::class, ['label' => 'Breite in px', 'required' => false, 'attr'=>['placeholder' => '600']])
            ->add('imgHeight', TextType::class, ['label' => 'Höhe in px', 'required' => false, 'attr'=>['placeholder' => '450']])
            ->add('imgPrevWidth', TextType::class, ['label' => 'Breite in px', 'required' => false, 'attr'=>['placeholder' => '140']])
            ->add('imgPrevHeight', TextType::class, ['label' => 'Höhe in px', 'required' => false, 'attr'=>['placeholder' => '105']])
            ->add('navListe', CheckboxType::class, ['label' => 'Anzeige Suchbox/Tageskalender in Listenansicht', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('navDetail', CheckboxType::class, ['label' => 'Anzeige Suchbox/Tageskalender in Detailansicht', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('dfxWidth', TextType::class, ['label' => 'Max. Gesamtbreite', 'required' => false, 'attr'=>['placeholder' => 'Wert + px']])
            ->add('navPos', ChoiceType::class, ['label' => 'Position Navigationselement', 'choices' => array_flip($cfgNavpos)])
            ->add('navWidth', ChoiceType::class, ['label' => 'Breite Navigationselement', 'choices' => array_flip($cfgNavWidth)])
            ->add('feldImg', CheckboxType::class, ['label' => 'Bildeingabe aktivieren', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imgListe', ChoiceType::class, ['label' => 'Vorschaubild in Listenansichten', 'choices' => array_flip($cfgImgListe), 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterKalender', CheckboxType::class, ['label' => 'Monatskalender', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterDatum', CheckboxType::class, ['label' => 'Datum von/bis', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterSuche', CheckboxType::class, ['label' => 'Suchfeld anbieten', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterRubrik', CheckboxType::class, ['label' => 'Rubrikfilter', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterZielgruppe', CheckboxType::class, ['label' => 'Zielgruppenfilter', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterNat', CheckboxType::class, ['label' => 'Länderfilter', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterPlzarea', CheckboxType::class, ['label' => 'Filter Plz/Plz-Gebiet', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterUmkreis', CheckboxType::class, ['label' => 'Umkreisfilter', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterOrt', CheckboxType::class, ['label' => 'Ortsfilter', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterRegion', CheckboxType::class, ['label' => 'Filter Regionen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterLocation', CheckboxType::class, ['label' => 'Filter Veranstaltungsstätten', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterVeranstalter', CheckboxType::class, ['label' => 'Filter Veranstalter', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterDozenten', CheckboxType::class, ['label' => 'Filter Dozenten', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('showPlz', CheckboxType::class, ['label' => 'Zeige Plz in Liste', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('showOrt', CheckboxType::class, ['label' => 'Zeige Ort in Liste', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('showStrasse', CheckboxType::class, ['label' => 'Zeige Straße in Liste', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('showLokal', CheckboxType::class, ['label' => 'Zeige Veranstaltungsort in Liste', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldSubtitel', CheckboxType::class, ['label' => 'Untertitel', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldLead', CheckboxType::class, ['label' => 'Kurztext für Liste', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldBeschreibung', CheckboxType::class, ['label' => 'Text für Detailansicht', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldEintritt', CheckboxType::class, ['label' => 'Textzeile Eintritt', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldOrt', CheckboxType::class, ['label' => 'Ortsname', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldOrtdb', CheckboxType::class, ['label' => 'Ortsdatenbank', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldPlz', CheckboxType::class, ['label' => 'PLZ', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldNat', CheckboxType::class, ['label' => 'Länderkürzel', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldRegion', CheckboxType::class, ['label' => 'Region', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldImg', CheckboxType::class, ['label' => 'Bildupload', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldGalerie', CheckboxType::class, ['label' => 'Galerie in Detailansichten von Terminen, Locations und Veranstaltern ermöglichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldVideo', CheckboxType::class, ['label' => 'Videocodes', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldPdf', CheckboxType::class, ['label' => 'Link zu PDF', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldLink', CheckboxType::class, ['label' => 'Infolink', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldTicketlink', CheckboxType::class, ['label' => 'Ticketlink', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldKarten', CheckboxType::class, ['label' => 'Kartenkauf ermöglichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldAnmeldung', CheckboxType::class, ['label' => 'Anmeldung ermöglichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldPlatzlimit', CheckboxType::class, ['label' => 'Eingabe Platzlimit', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldLocation', CheckboxType::class, ['label' => 'Veranstaltungsstätte', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldVeranstalter', CheckboxType::class, ['label' => 'Veranstalter', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldDozenten', CheckboxType::class, ['label' => 'Dozenten', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('feldOnlinetermin', CheckboxType::class, ['label' => 'Onlinetermine ermöglichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('allowInputAll', CheckboxType::class, ['label' => 'Dateneingabe auch nicht registrierten Benutzern erlauben', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('allowPubAll', CheckboxType::class, ['label' => 'Termine ohne Freigabe durch Admin veröffentlichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('sprache', ChoiceType::class, ['label' => 'Sprache *', 'choices' => $cfgSprachen])
            ->add('titel', TextType::class, ['label' => 'Titel des Kalenders *', 'required' => false, 'attr'=>['placeholder' => 'Titel des Kalenders']])
            ->add('imageFile', FileType::class, ['mapped' => false, 'label' => 'Logo - nur jpg, png und gif erlaubt', 'required' => false, 'attr' => ['accept' => 'image/*','noFormControl' => true]])
            ->add('imageFile2', FileType::class, ['mapped' => false, 'label' => 'Banner 1200 Pixel breit - nur jpg, png und gif erlaubt', 'required' => false, 'attr' => ['accept' => 'image/*','noFormControl' => true]])
            ->add('adresse', TextareaType::class, ['label' => 'Adresse für Impressum, PDF-Briefkopf, Mail-Footer', 'required' => false, 'attr' => ['rows'=>'4']])
            ->add('archivTage', TextType::class, ['label' => false, 'required' => true, 'attr' => ['aria-label'=>'Archiv Tage']])
            ->add('allowSocial', CheckboxType::class, ['label' => 'Links soziale Netzwerke', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('allowRemind', CheckboxType::class, ['label' => 'Erinnern erlauben', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('allowMail', CheckboxType::class, ['label' => 'Mailversand erlauben', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('allowExport', CheckboxType::class, ['label' => 'Export erlauben', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('allowPrint', CheckboxType::class, ['label' => 'Drucken erlauben', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('useMap', CheckboxType::class, ['label' => 'Kartenfunktion einbinden', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('allowApi', CheckboxType::class, ['label' => 'API erlauben', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('maxApiItems', TextType::class, ['label' => 'Max. API-Items', 'required' => false, 'attr' => ['aria-label' => 'Max. API-Items']])
            ->add('pageApiItems', TextType::class, ['label' => 'API-Items je Seite', 'required' => false, 'attr' => ['aria-label' => 'API-Items je Seite']])
            ->add('initZoom', TextType::class, ['label' => 'Karten-Zoom (Initialwert)', 'required' => false, 'attr' => ['aria-label' => 'Karten-Zoom (Initialwert)']])
            ->add('useIcons', CheckboxType::class, ['label' => 'Icons statt Textlinks', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('htmlHead',TextareaType::class, ['label' => 'HTML-Code Bereich unter Banner/Titel', 'required' => false])
            ->add('htmlFooter', TextareaType::class, ['label' => 'HTML-Code für Footer-Bereich', 'required' => false])
            ->add('dfxCss', TextareaType::class, ['label' => 'CSS Styles', 'required' => false])
            ->add('frontendUrl', TextType::class, ['label' => 'URI des Frontend', 'required' => false, 'attr' => ['placeholder' => 'https://....']])
            ->add('datenschutzUrl', TextType::class, ['label' => 'URI zur Datenschutzerklärung', 'required' => false, 'attr' => ['placeholder' => 'https://....']])
            ->add('impressumUrl', TextType::class, ['label' => 'URI zum Impressum', 'required' => false,  'attr' => ['placeholder' => 'https://....']])
            ->add('isMeta', CheckboxType::class, ['label' => 'Meta-Kalender', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('isGroup', CheckboxType::class, ['label' => 'Gruppenkalender', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('pubMetaAll', CheckboxType::class, ['label' => 'Termine standardmäßig in Meta-Kalender veröffentlichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('pubGroupAll', CheckboxType::class, ['label' => 'Termine standardmäßig in Gruppen-Kalender veröffentlichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('inheritMeta', CheckboxType::class, ['label' => 'Rubriken aus Meta-Kalender übernehmen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('inheritGroup', CheckboxType::class, ['label' => 'Rubriken aus Gruppen-Kalender übernehmen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('toGroup', ChoiceType::class, ['label' => false, 'choices' => array_flip($choices), 'placeholder' => 'Gruppe auswählen', 'multiple' => true, 'required' => false])
            ->add('inheritZielgruppenMeta', CheckboxType::class, ['label' => 'Zielgruppen aus Meta-Kalender übernehmen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('inheritZielgruppenGroup', CheckboxType::class, ['label' => 'Zielgruppen aus Gruppen-Kalender übernehmen', 'required' => false, 'attr' => ['noFormControl' => true]])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxKonf::class,
        	'attr' => ['class' => ''],
        	'em' => '',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'datefix_backendbundle_dfxkonf';
    }
}
