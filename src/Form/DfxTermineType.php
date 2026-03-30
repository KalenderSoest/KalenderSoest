<?php
namespace App\Form;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use App\Entity\DfxTermine;
use EmilePerron\TinymceBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use App\Entity\DfxLocation;
use App\Entity\DfxVeranstalter;
use App\Entity\DfxRegion;
use App\Entity\DfxOrte;



class DfxTermineType extends AbstractType
{
    function __construct(private readonly array $optionsRadio, private readonly array $optionsMenue, private readonly array $optionsCheckboxes, private readonly array $optionsMenueMulti)
    {
    }

     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

    	$arRubriken = [];
    	foreach ($options['konf']->getRubriken() AS $rubrik){
    		$arRubriken[$rubrik] = $rubrik;
    	}

        $arZielgruppen = [];
        if(is_array($options['konf']->getZielgruppen())){
            foreach ($options['konf']->getZielgruppen() AS $zielgruppe){
                    $arZielgruppen[$zielgruppe] = $zielgruppe;
            }
        }

    	$arKontakt=['kontakt' => 'Nur als Kontaktadresse'];
    	if($options['konf']->getFeldKarten() == 1){
            $arKontakt['karten'] = 'Karten bestellen';
        }

    	if($options['konf']->getFeldAnmeldung() == 1){
            $arKontakt['anmeldung'] = 'Anmeldung';
        }
        $arKontakt['kein'] = 'Kein E-Mail-Link';

    	$arTage = [1 => 'Mo', 2 => 'Di', 3 => 'Mi', 4 => 'Do', 5 => 'Fr', 6 => 'Sa', 0 => 'So', 7 => 'täglich'];

        $builder
            ->add('datum',DateType::class, ['widget' => 'single_text', 'html5' => true,'label' => 'Einzeltermin bis', 'required' => false, 'by_reference' => true, 'attr' => ['picker' => 'date', 'placeholder' => 'Datum bis', 'class' =>'form-control']])
            ->add('datum_von',DateType::class, ['widget' => 'single_text', 'html5' => true,'label' => 'Einzeltermin am / von', 'required' => true, 'by_reference' => true, 'attr' => ['placeholder' => 'Datum am / von', 'class' =>'form-control']])
            ->add('datum_s_von',DateType::class, ['widget' => 'single_text', 'html5' => true,'label' => 'Regelmäßiger Serientermin von', 'mapped' => false, 'required' => false, 'attr' => ['placeholder' => 'Erstes Datum', 'class' =>'form-control']])
            ->add('datum_s_bis',DateType::class, ['widget' => 'single_text', 'html5' => true,'label' => 'Regelmäßiger Serientermin bis', 'mapped' => false, 'required' => false, 'attr' => ['placeholder' => 'Letztes Datum', 'class' =>'form-control']])
            ->add('datum_s_liste',TextareaType::class,['label' => 'Automatisch ermittelte Terminserie', 'required' => false, 'mapped' => false])
            ->add('tage',ChoiceType::class,['label'=> false, 'choices' => array_flip($arTage), 'expanded' => true, 'multiple' => true, 'required' => false, 'mapped' => false])
            ->add('datumSerie',TextType::class,['label' => 'Serientermin aus Kalender', 'required' => false, 'attr' => ['placeholder' => '📅 Datum unregelmäßiger Termin auswählen', 'autocomplete' => 'off', 'class' =>'form-control']])
            ->add('zeit',TimeType::class,['label' => 'Uhrzeit', 'required' => false, 'by_reference' => true, 'attr' => ['noFormControl' => true]])
            ->add('zeitBis',TimeType::class,['label' => 'Uhrzeit bis','required' => false, 'by_reference' => true, 'attr' => ['noFormControl' => true]])
            ->add('nat', TextType::class, ['label' => false,'required' => false, 'attr' => ['placeholder' => 'Land', 'aria-label' => 'Land' ]])
            ->add('plz', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Plz', 'aria-label' => 'PLZ']])
            ->add('ort', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Ort', 'aria-label' => 'Ort']])
            ->add('lokal', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Veranstaltungsstätte', 'aria-label' => 'Veranstaltungsstätte']])
            ->add('lokalStrasse', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Straße + Hausnummer', 'aria-label' => 'Straße und Hausnummer']])
            ->add('lg', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Längengrad', 'aria-label' => 'Längengrad']])
            ->add('bg', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Breitengrad', 'aria-label' => 'Breitengrad']])
            ->add('region', EntityType::class, [
                        'class' => DfxRegion::class,
                        'query_builder' => function(EntityRepository $er) use ( $options ): QueryBuilder {
                                $query =  $er->createQueryBuilder('r')
                                ->select(['r']);
                                $query->orderBy('r.region', 'ASC');
                                return $query;
                        },
                        'choice_label' => 'region',
                        'placeholder' => 'Region wählen',
                        'label' => false,
                        'required'=> false,
                        'attr' => ['aria-label' => 'Region wählen']
            ])
            ->add('idOrt', EntityType::class, [
                        'class' => DfxOrte::class,
                        'query_builder' => function(EntityRepository $er) use ( $options ): QueryBuilder {
                                $query =  $er->createQueryBuilder('o')
                                ->select(['o']);
                                $query->orderBy('o.ort', 'ASC');
                                return $query;
                        },
                        'choice_label' => 'ort',
                        'placeholder' => 'Ort wählen',
                        'label' => false,
                        'required'=> false,
                        'attr' => ['aria-label' => 'Ort wählen']

            ])
            ->add('veranstalter', TextType::class, ['label' => false,'required' => false, 'attr' => ['placeholder' => 'Veranstalter', 'aria-label' => 'Veranstalter']])
            ->add('idVeranstalter', EntityType::class, [
                'class' => DfxVeranstalter::class,
                'query_builder' => function(EntityRepository $er) use ( $options ): QueryBuilder {
                        $query =  $er->createQueryBuilder('v')
                        ->select(['v']);
                        if(count($options['arKids']) > 0){
                                $query ->where('v.datefix IN (:kid)')
                                        ->setParameters(new ArrayCollection([new Parameter('kid', $options['arKids'])]));
                        }

                        $query->orderBy('v.name', 'ASC');
                        return $query;
                },
                'choice_label' => 'name',
                'placeholder' => 'Veranstalter aus Datenbank wählen',
                'label' => false,
                'required'=> false,
                'attr' => ['aria-label' => 'Veranstalter aus Datenbank wählen']
            ])
            ->add('idLocation', EntityType::class, [
                'class' => DfxLocation::class,
                'query_builder' => function(EntityRepository $er) use ( $options ): QueryBuilder {
                        $query =  $er->createQueryBuilder('v')
                        ->select(['v']);
                        if(count($options['arKids']) > 0){
                                $query ->where('v.datefix IN (:kid)')
                                        ->setParameters(new ArrayCollection([new Parameter('kid', $options['arKids'])]));
                        }

                        $query->orderBy('v.name', 'ASC');
                        return $query;
                },
                'choice_label' => 'name',
                'placeholder' => 'Veranstaltungsstätte aus Datenbank wählen',
                'label' => false,
                'required'=> false,
                'attr' => ['aria-label' => 'Veranstaltungsstätte aus Datenbank wählen']
            ])
            ->add('eintritt', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Textzeile Eintritt', 'aria-label' => 'Textzeile Eintritt']])
            ->add('rubrik',ChoiceType::class,['label'=> false, 'choices' => $arRubriken, 'expanded' => true, 'multiple' => true, 'required' => false])
            ->add('zielgruppe',ChoiceType::class,['label'=> false, 'choices' => $arZielgruppen, 'expanded' => true, 'multiple' => true, 'required' => false])
            ->add('titel', TextType::class, ['label' => false, 'required' => true, 'attr' => ['placeholder' => 'Titel der Veranstaltung *', 'aria-label' => 'Titel der Veranstaltung']])
            ->add('subtitel', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Untertitel der Veranstaltung', 'aria-label' => 'Untertitel der Veranstaltung']])
            ->add('lead', TextareaType::class, ['label' => 'Kurzfassung der Beschreibung', 'required' => false, 'attr' => ['rows'=>'2', 'placeholder' => 'Maximal 200 Zeichen empfohlen']])
            ->add('beschreibung', TinymceType::class, ['label' => 'Beschreibung der Veranstaltung', 'required' => false])
            ->add('link', UrlType::class, ['label' => false, 'required' => false, 'default_protocol' => 'https', 'attr' => ['placeholder' => 'Link "Mehr Informationen" https://...', 'aria-label' => 'URL zu mehr Informationen mit https://']])
            ->add('linktext', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Text für Link "Mehr Informationen"', 'aria-label' => 'Text für Veranstaltungslink']])
            ->add('konferenzlink', UrlType::class, ['label' => false, 'required' => false, 'default_protocol' => 'https', 'attr' => ['placeholder' => 'Link zur Online-Veranstaltung https://...', 'aria-label' => 'URL zur Online-Veranstaltung mit https://']])
            ->add('konferenzlinktext', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Text für Link zur Online-Veranstaltung', 'aria-label' => 'Text für Link zur Online-Veranstaltung']])
            ->add('ticketlink', UrlType::class, ['label' => false, 'required' => false, 'default_protocol' => 'https', 'attr' => ['placeholder' => 'Ticketlink https://...', 'aria-label' => 'Ticketlink https://...']])
            ->add('ticketlinktext', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Text für Ticketlink', 'aria-label' => 'Text für Ticketlink']])
            ->add('imageFile', FileType::class, ['mapped' => false, 'label' => 'Foto/Grafik - nur jpg, png und gif erlaubt, maximal 2 MB', 'required' => false, 'attr' => ['accept' => 'image/*']])
            ->add('imageFile2', FileType::class, ['mapped' => false, 'label' => 'Foto/Grafik - nur jpg, png und gif erlaubt, maximal 2 MB', 'required' => false, 'attr' => ['accept' => 'image/*','noFormControl' => true]])
            ->add('imageFile3', FileType::class, ['mapped' => false, 'label' => 'Foto/Grafik - nur jpg, png und gif erlaubt, maximal 2 MB', 'required' => false, 'attr' => ['accept' => 'image/*','noFormControl' => true]])
            ->add('imageFile4', FileType::class, ['mapped' => false, 'label' => 'Foto/Grafik - nur jpg, png und gif erlaubt, maximal 2 MB', 'required' => false, 'attr' => ['accept' => 'image/*','noFormControl' => true]])
            ->add('imageFile5', FileType::class, ['mapped' => false, 'label' => 'Foto/Grafik - nur jpg, png und gif erlaubt, maximal 2 MB', 'required' => false, 'attr' => ['accept' => 'image/*','noFormControl' => true]])
            ->add('imageFileDelete', CheckboxType::class,['label' => 'Foto/Grafik löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imageFileDelete2', CheckboxType::class,['label' => 'Foto/Grafik löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imageFileDelete3', CheckboxType::class,['label' => 'Foto/Grafik löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imageFileDelete4', CheckboxType::class,['label' => 'Foto/Grafik löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imageFileDelete5', CheckboxType::class,['label' => 'Foto/Grafik löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imgtext', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Bildtext', 'aria-label' => 'Text zum Bild']])
            ->add('imgcopyright', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Copyright - z.B. Foto: Name des Fotografen', 'aria-label' => 'Copyrighttext']])
            ->add('imgcopycheck',CheckboxType::class,['label' => 'Mit der Auswahl der Datei(en) bestätige ich, dass ich Inhaber der Dateirechte bin oder die Erlaubnis des Urhebers zur Veröffentlichung besitze', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imgtext2', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Bildtext unter Zusatzfoto(s)', 'aria-label' => 'Bildtext unter Zusatzfoto(s)']])
            ->add('imgcopyright2', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Copyright - z.B. Fotos: Namen der Fotografen', 'aria-label' => 'Copyrightvermerk Zusatzfoto(s)']])
            ->add('imgcopycheck2',CheckboxType::class,['label' => 'Mit der Auswahl der Datei(en) bestätige ich, dass ich Inhaber der Dateirechte bin oder die Erlaubnis des Urhebers oder der Urheber zur Veröffentlichung besitze', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('pdfFile', FileType::class, ['mapped' => false, 'label' => 'PDF für Download, maximal 2 MB', 'required' => false, 'attr' => ['accept' => 'application/pdf','noFormControl' => true]])
            ->add('pdflinktext', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Text für PDF-Downloadlink', 'aria-label' => 'Text für PDF-Downloadlink']])
            ->add('pdfFileDelete', CheckboxType::class,['label' => 'PDF löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('mediaFileDelete', CheckboxType::class,['label' => 'Mediendatei löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('media', FileType::class, ['data_class' => null, 'label' => 'Medien zum Download, maximal 2 MB', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('medialinktext', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Text für Media-Downloadlink', 'aria-label' => 'Text für Media-Downloadlink']])
            ->add('video', TextareaType::class,['label' => 'Videocode', 'required' => false])
            ->add('mail',EmailType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'E-Mail für Kontaktformulare', 'aria-label' => 'E-Mail für Kontaktformulare']])
            ->add('mailTyp',ChoiceType::class,['label'=>'E-Mail verwenden für:', 'choices' => array_flip($arKontakt), 'expanded' => true, 'multiple' => false, 'required' => false, 'placeholder' => false, 'attr' => ['cols' => 4]])
            ->add('online',CheckboxType::class,['label' => 'Reine Online-Veranstaltung', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('pub',CheckboxType::class,['label' => 'Veröffentlichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('archiv',CheckboxType::class,['label' => 'Nach Ablauf archivieren', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('pubMeta',CheckboxType::class,['label' => 'Im Meta-Kalender veröffentlichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('pubGroup',CheckboxType::class,['label' => 'Im Gruppen-Kalender veröffentlichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('plaetzeGesamt',TextType::class, ['label' => 'Plätze gesamt', 'required' => false, 'attr' => ['placeholder' => 'Plätze gesamt']])
            ->add('plaetzeAktuell',TextType::class, ['label' => 'Noch verfügbare Plätze', 'required' => false, 'attr' => ['placeholder' => 'Plätze verfügbar']])
            ->add('anmeldeschluss',DateType::class, ['widget' => 'single_text', 'html5' => true,'label' => false, 'required' => false, 'by_reference' => true, 'attr' => ['placeholder' => 'Datum Anmeldeschluss', 'class' =>'form-control']])
            ->add('gastEmail',EmailType::class, ['label' => 'Eingeber E-Mail',  'mapped' => false, 'required' => true, 'attr' => ['placeholder' => 'Ihre E-Mail-Adresse']])
            ->add('gastVorname',TextType::class, ['label' => 'Eingeber Vorname',  'mapped' => false, 'required' => true, 'attr' => ['placeholder' => 'Ihr Vorname']])
            ->add('gastNachname',TextType::class, ['label' => 'Eingeber Nachname ', 'mapped' => false, 'required' => true, 'attr' => ['placeholder' => 'Ihr Nachname']])
            ->add('filter1',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter2',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter3',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter4',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter5',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter6',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter7',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter8',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter9',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter10',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter11',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter12',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter13',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter14',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter15',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('text1',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text2',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text3',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text4',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text5',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text6',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text7',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text8',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text9',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text10',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('textbox1', TextareaType::class, ['label' => false, 'required' => false])
            ->add('textbox2', TextareaType::class, ['label' => false, 'required' => false, 'attr' => ['rows'=>'5', 'placeholder' => '']])
            ->add('optionsRadio',ChoiceType::class,['label'=> false, 'choices' => array_flip($this->optionsRadio), 'expanded' => true, 'multiple' => false, 'required' => false, 'placeholder' => false])
            ->add('optionsMenue',ChoiceType::class,['label'=> false, 'choices' => array_flip($this->optionsMenue), 'expanded' => false, 'multiple' => false, 'required' => false, 'placeholder' => 'Bitte wählen', 'empty_data' => null, 'attr' => []])
            ->add('optionsCheckboxes',ChoiceType::class,['label'=> false, 'choices' => array_flip($this->optionsCheckboxes), 'expanded' => true, 'multiple' => true, 'required' => false])
            ->add('optionsMenueMulti',ChoiceType::class,['label'=> false, 'choices' => array_flip($this->optionsMenueMulti), 'expanded' => false, 'multiple' => true, 'required' => false, 'placeholder' => 'Wählen Sie ein oder mehrere Elemente aus', 'attr' => []])
            ->add('datum3',DateType::class, ['widget' => 'single_text', 'html5' => true,'label' => false, 'required' => false, 'by_reference' => true, 'attr' => ['placeholder' => 'Datum wählen', 'class' =>'form-control']])

        ;

    }



    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxTermine::class,
        	'konf' => '',
        	'arKids' => [],

            'validation_groups' => false,
            'multi1' => ['']

        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'termine';
    }
}
