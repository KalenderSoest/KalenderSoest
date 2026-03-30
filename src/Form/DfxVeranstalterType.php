<?php

namespace App\Form;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use App\Entity\DfxVeranstalter;
use EmilePerron\TinymceBundle\Form\Type\TinymceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use App\Entity\DfxLocation;
use App\Entity\DfxRegion;
use App\Entity\DfxOrte;

class DfxVeranstalterType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nat', TextType::class, ['label' => false,'required' => false, 'attr' => ['placeholder' => 'Land', 'aria-label'=>'Land']])
            ->add('plz', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Plz', 'aria-label'=>'PLZ']])
            ->add('ort', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Ort', 'aria-label'=>'Ort']])
            ->add('name', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Name des veranstalters', 'aria-label'=>'Name des Veranstalters']])
            ->add('strasse', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Straße + Hausnummer', 'aria-label'=>'Straße + Hausnummer']])
            ->add('lg', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Längengrad', 'aria-label'=>'Längengrad']])
            ->add('bg', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Breitengrad', 'aria-label'=>'Breitengrad']])
            ->add('telefon', TextType::class, ['label' => false,'required' => false, 'attr' => ['placeholder' => 'Telefon', 'aria-label'=>'Telefon']])
            ->add('fax', TextType::class, ['label' => false,'required' => false, 'attr' => ['placeholder' => 'Telefax', 'aria-label'=>'Telefax']])
            ->add('www', UrlType::class, ['label' => false,'required' => false, 'default_protocol' => 'https', 'attr' => ['placeholder' => 'Link zur Homepage https://...', 'aria-label'=>'Link zur Homepage']])
            ->add('email', EmailType::class, ['label' => false,'required' => false, 'attr' => ['placeholder' => 'E-Mail-Adresse', 'aria-label'=>'E-Mail-Adresse']])
            ->add('ansprech', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Name Ansprechpartner', 'aria-label'=>'Name Ansprechpartner']])
            ->add('zusatz', TinymceType::class, ['label' => 'Beschreibung des Veranstalters', 'required' => false])
            ->add('imageFile', FileType::class, ['mapped' => false, 'label' => 'Foto/Grafik - nur jpg, png und gif erlaubt', 'required' => false, 'attr' => ['accept' => 'image/*','noFormControl' => true]])
            ->add('imageFile2', FileType::class, ['mapped' => false, 'label' => 'Foto/Grafik - nur jpg, png und gif erlaubt', 'required' => false, 'attr' => ['accept' => 'image/*','noFormControl' => true]])
            ->add('imageFile3', FileType::class, ['mapped' => false, 'label' => 'Foto/Grafik - nur jpg, png und gif erlaubt', 'required' => false, 'attr' => ['accept' => 'image/*','noFormControl' => true]])
            ->add('imageFile4', FileType::class, ['mapped' => false, 'label' => 'Foto/Grafik - nur jpg, png und gif erlaubt', 'required' => false, 'attr' => ['accept' => 'image/*','noFormControl' => true]])
            ->add('imageFile5', FileType::class, ['mapped' => false, 'label' => 'Foto/Grafik - nur jpg, png und gif erlaubt', 'required' => false, 'attr' => ['accept' => 'image/*','noFormControl' => true]])
            ->add('imgtext', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Bildtext', 'aria-label'=>'Bildtext']])
            ->add('imgcopyright', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Copyright - z.B. Foto: Name des Fotografen', 'aria-label'=>'Copyright - z.B. Foto: Name des Fotografen']])
            ->add('imgcopycheck',CheckboxType::class,['label' => 'Mit der Auswahl der Datei bestätige ich, dass ich Inhaber der Dateirechte bin oder die Erlaubnis des Urhebers zur Veröffentlichung besitze', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imageFileDelete', CheckboxType::class,['label' => 'Foto/Grafik löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imageFileDelete2', CheckboxType::class,['label' => 'Foto/Grafik löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imageFileDelete3', CheckboxType::class,['label' => 'Foto/Grafik löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imageFileDelete4', CheckboxType::class,['label' => 'Foto/Grafik löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('imageFileDelete5', CheckboxType::class,['label' => 'Foto/Grafik löschen', 'mapped' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
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

            ->add('location', EntityType::class, [
                'class' => DfxLocation::class,
                'query_builder' => fn(EntityRepository $er): QueryBuilder => $er->createQueryBuilder('v')
                ->select(['v'])
                ->where('v.datefix IN (:kid)')
                ->setParameters(new ArrayCollection([new Parameter('kid', $options['konf']->getId())]))
                ->orderBy('v.name', 'ASC'),
                'choice_label' => 'name',
                'placeholder' => 'Veranstaltungsstätte aus Datenbank wählen',
                'label' => 'Mit Veranstaltungsstätte verknüpfen',
                'required'=> false
            ])
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
                'attr' => ['aria-label'=>'Region aus Datenbank wählen']
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
                'attr' => ['aria-label'=>'Ort aus Datenbank wählen']
            ])
        ;
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxVeranstalter::class,
        	'konf' => ''
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'veranstalter';
    }
}
