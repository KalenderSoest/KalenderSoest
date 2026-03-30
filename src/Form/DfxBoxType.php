<?php

namespace App\Form;

use App\Entity\DfxBox;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DfxBoxType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('boxItems',TextType::class, ['label' => 'Zahl Termine', 'required' => false])
            ->add('boxOrt', CheckboxType::class, ['label' => 'Ort', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('boxDatum', CheckboxType::class, ['label' => 'Datum', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('boxUhr', CheckboxType::class, ['label' => 'Uhrzeit', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('boxTitel', CheckboxType::class, ['label' => 'Titel', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('boxSubtitel', CheckboxType::class, ['label' => 'Untertitel', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('boxLead', CheckboxType::class, ['label' => 'Kurzfassung', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('boxBeschreibung', CheckboxType::class, ['label' => 'Beschreibung', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('boxLokal', CheckboxType::class, ['label' => 'Veranstaltungsstätte', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('boxVeranstalter', CheckboxType::class, ['label' => 'Veranstalter', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('boxImage', CheckboxType::class, ['label' => 'Vorschaubild', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('boxCss', TextareaType::class, ['label' => 'CSS-Defintionen', 'required' => false, 'attr' => ['rows'=>'10']])
            ->add('boxTerminUrl', TextType::class, ['label' => 'Termin-URL', 'required' => false])
            ->add('boxTarget', TextType::class, ['label' => 'Link-Target', 'required' => false])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxBox::class
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'dfxbox';
    }
}
