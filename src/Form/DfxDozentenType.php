<?php

namespace App\Form;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use App\Entity\DfxDozenten;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use App\Entity\DfxLocation;
use App\Entity\DfxVeranstalter;


class DfxDozentenType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nat', TextType::class, ['label' => false,'required' => false, 'attr' => ['placeholder' => 'Land', 'aria-label'=>'Land']])
            ->add('plz', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Plz', 'aria-label'=>'Plz']])
            ->add('ort', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Ort', 'aria-label'=>'Ort']])
            ->add('name', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Name des Dozenten', 'aria-label'=>'Name des Dozenten']])
            ->add('strasse', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Straße + Hausnummer', 'aria-label'=>'Straße + Hausnummer']])
            ->add('telefon', TextType::class, ['label' => false,'required' => false, 'attr' => ['placeholder' => 'Telefon', 'aria-label'=>'Telefon']])
            ->add('fax', TextType::class, ['label' => false,'required' => false, 'attr' => ['placeholder' => 'Telefax', 'aria-label'=>'Telefax']])
            ->add('www', UrlType::class, ['label' => false,'required' => false, 'default_protocol' => 'https', 'attr' => ['placeholder' => 'Link zur Homepage https://...', 'aria-label'=>'Link zur Homepage']])
            ->add(EmailType::class, EmailType::class, ['label' => false,'required' => false, 'attr' => ['placeholder' => 'E-Mail-Adresse', 'aria-label'=>'E-Mail-Adresse']])
            ->add('zusatz', TextareaType::class, ['label' => 'Beschreibung des Dzenten/Kompetenzen', 'required' => false, 'config' => ['uiColor' => '#f5f5f5']])
            // ->add('imageFile', VichImageType::class, array('allow_delete' => true,'label' => 'Foto/Grafik - nur jpg, png und gif erlaubt', 'required' => false, 'attr' => array('noFormControl' => true)))
            ->add('imgDozPos')
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
                'required'=> false,
                'attr' => ['aria-label'=>'Veranstaltungsstätte aus Datenbank wählen']
            ])
            ->add('veranstalter', EntityType::class, [
                'class' => DfxVeranstalter::class,
                'query_builder' => fn(EntityRepository $er): QueryBuilder => $er->createQueryBuilder('v')
                ->select(['v'])
                ->where('v.datefix IN (:kid)')
                ->setParameters(new ArrayCollection([new Parameter('kid', $options['konf']->getId())]))
                ->orderBy('v.name', 'ASC'),
                'choice_label' => 'name',
                'placeholder' => 'Veranstalter aus Datenbank wählen',
                'label' => 'Mit Veranstalter verknüpfen',
                'required'=> false,
                'attr' => ['aria-label'=>'Veranstalter aus Datenbank wählen']
            ])

        ;
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxDozenten::class,
        	'konf' => ''
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'dozenten';
    }
}
