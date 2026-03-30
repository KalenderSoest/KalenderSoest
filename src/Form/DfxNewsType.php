<?php
namespace App\Form;

use App\Entity\DfxNews;
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



class DfxNewsType extends AbstractType
{

    function __construct()
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

        $arNewstyp = [];
        $arNewstyp['beitrag'] = 'Beitrag, Artikel';
        $arNewstyp['seite'] = 'Seite';


        $builder
            ->add('datum_bis',DateType::class, ['widget' => 'single_text', 'html5' => true,'label' => 'Anzeigen bis', 'required' => false, 'by_reference' => true, 'attr' => ['picker' => 'date', 'placeholder' => 'Veröffentlichen bis', 'class' =>'form-control']])
            ->add('datum_von',DateType::class, ['widget' => 'single_text', 'html5' => true,'label' => 'Anzeigen ab', 'required' => false, 'by_reference' => true, 'attr' => ['placeholder' => 'Veröffentlichen ab', 'class' =>'form-control']])
            ->add('rubrik',ChoiceType::class,['label'=> false, 'choices' => $arRubriken, 'expanded' => true, 'multiple' => true, 'required' => false])
            ->add('zielgruppe',ChoiceType::class,['label'=> false, 'choices' => $arZielgruppen, 'expanded' => true, 'multiple' => true, 'required' => false])
            ->add('titel', TextType::class, ['label' => false, 'required' => true, 'attr' => ['placeholder' => 'Titel des Artikels *']])
            ->add('kurztitel', TextType::class, ['label' => 'Kurztitel für Menüeintrag von Seiten', 'required' => false, 'attr' => ['placeholder' => 'Kurztitel/Menüeintrag']])
            ->add('subtitel', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Untertitel des Artikels']])
            ->add('kurztext', TextareaType::class, ['label' => 'Kurzfassung Artikels', 'required' => false, 'attr' => ['rows'=>'2', 'placeholder' => 'Maximal 200 Zeichen empfohlen']])
            ->add('beschreibung', TinymceType::class, ['required' => false])
            ->add('link', UrlType::class, ['label' => false, 'required' => false, 'default_protocol' => 'https', 'attr' => ['placeholder' => 'Link "Mehr Informationen" https://...']])
            ->add('linktext', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Text für Link "Mehr Informationen"']])
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
            ->add('imgtext', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Bildtext']])
            ->add('imgcopyright', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Copyright - z.B. Foto: Name des Fotografen']])
            ->add('imgcopycheck',CheckboxType::class,['label' => 'Mit der Auswahl der Datei(en) bestätige ich, dass ich Inhaber der Dateirechte bin oder die Erlaubnis des Urhebers zur Veröffentlichung besitze', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imgtext2', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Bildtext unter Galerie']])
            ->add('imgcopyright2', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Copyright - z.B. Fotos: Namen der Fotografen']])
            ->add('imgcopycheck2',CheckboxType::class,['label' => 'Mit der Auswahl der Datei(en) bestätige ich, dass ich Inhaber der Dateirechte bin oder die Erlaubnis des Urhebers oder der Urheber zur Veröffentlichung besitze', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('pdfFile', FileType::class, ['mapped' => false, 'label' => 'PDF für Download, maximal 2 MB', 'required' => false, 'attr' => ['accept' => 'application/pdf','noFormControl' => true]])
            ->add('pdflinktext', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Text für PDF-Downloadlink']])
            ->add('pdfFileDelete', CheckboxType::class,['label' => 'PDF löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('mediaFileDelete', CheckboxType::class,['label' => 'Mediendatei löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('media', FileType::class, ['data_class' => null, 'label' => 'Medien zum Download, maximal 2 MB', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('medialinktext', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Text für Media-Downloadlink']])
            ->add('video', TextareaType::class,['label' => 'Videocode', 'required' => false])
            ->add('mail',EmailType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'E-Mail für Kontaktformulare']])
            ->add('newsTyp',ChoiceType::class,['label'=>'Beitrag verwenden als:', 'choices' => array_flip($arNewstyp), 'expanded' => true, 'multiple' => false, 'required' => false, 'placeholder' => false, 'attr' => ['cols' => 4]])
            ->add('menueeintrag',CheckboxType::class,['label' => 'Seite zum Menü hinzufügen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('reihenfolge', TextType::class, ['label' => 'Position in Liste/Menü', 'required' => false, 'attr' => ['placeholder' => '0']])
            ->add('pub',CheckboxType::class,['label' => 'Veröffentlichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('archiv',CheckboxType::class,['label' => 'Nach Ablauf archivieren', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('pubMeta',CheckboxType::class,['label' => 'Im Meta-Kalender veröffentlichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('pubGroup',CheckboxType::class,['label' => 'Im Gruppen-Kalender veröffentlichen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter1',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter2',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter3',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter4',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter5',CheckboxType::class,['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('text1',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text2',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text3',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text4',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('text5',TextType::class, ['label' => false,  'required' => false, 'attr' => ['placeholder' => '', 'noFormControl' => true, 'class' =>'form-control']])
            ->add('textbox1', TextareaType::class, ['label' => false, 'required' => false])
            ->add('textbox2', TextareaType::class, ['label' => false, 'required' => false, 'attr' => ['rows'=>'5', 'placeholder' => '']]);

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxNews::class,
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
        return 'news';
    }
}
