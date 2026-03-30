<?php

namespace App\Form;

use App\Entity\DfxKartenKat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DfxKartenKatType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('katName', TextType::class, ['label' => 'Kategorie Bezeichnung', 'required' => true])
            ->add('platzart', ChoiceType::class, ['label' => 'Platzart', 'choices' => ['Sitzplatz', 'Stehplatz'], 'required' => false])
            ->add('isSubKat', TextType::class, ['label' => false, 'required' => false, 'attr' => ['aria-label'=>'Checkbox ist Unterkategorie']])
            ->add('zahlPlaetze', TextType::class, ['label' => 'Platzart', 'required' => true])
            ->add('preis', TextType::class, ['label' => 'Platzart', 'required' => true])
            ->add('web', CheckboxType::class,['label' => 'Online', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('abendkasse', CheckboxType::class, ['label' => 'Abendkasse', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('vvk', CheckboxType::class, ['label' => 'Vorverkauf', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('katRgb', TextType::class, ['label' => 'Farbe (Hex Code)', 'required' => false])
            ->add('katNotiz', TextareaType::class, ['label' => 'Beschreibung', 'required' => false])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
     public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxKartenKat::class,

        	'cKey' => ''
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'dfxkartenorder';
    }
}
