<?php

namespace App\Form;

use App\Entity\DfxNfxKunden;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('kunde', TextType::class, ['label' => 'Kunde', 'required' => true, 'attr'=>['placeholder' => 'Verein, Organisation, Kommune, Firma, Privatperson ...']])
            ->add('name', TextType::class, ['label' => 'Nachname', 'required' => true, 'attr'=>['placeholder' => 'Ansprechpartner Nachname']])
            ->add('vorname', TextType::class, ['label' => 'Vorname', 'required' => true, 'attr'=>['placeholder' => 'Ansprechpartner Vorname']])
            ->add('strasse', TextType::class, ['label' => 'Straße', 'required' => true, 'attr'=>['placeholder' => 'Straße und Hausnummer']])
            ->add('nat', TextType::class, ['label' => 'Land', 'data' =>'D', 'required' => true])
            ->add('plz', TextType::class, ['label' => 'Plz', 'required' => true, 'attr'=>['placeholder' => 'Plz']])
            ->add('ort', TextType::class, ['label' => 'Ort', 'required' => true, 'attr'=>['placeholder' => 'Ort']])
            ->add('email', TextType::class, ['label' => 'E-Mail', 'required' => true, 'attr'=>['placeholder' => 'Email']])
            ->add('ustid', TextType::class, ['label' => 'UstId', 'required' => false, 'attr'=>['placeholder' => 'UstId - nur für Firmenkunden aus der EU ohne Sitz in Deutschland']])
        ;
    }



    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxNfxKunden::class
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'kunden';
    }
}
