<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AnmeldungenFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datum_von', DateType::class, ['widget' => 'single_text', 'html5' => true, 'label' => false, 'required' => false, 'attr' => ['placeholder' => 'Datum von', 'class' => 'form-control']])
            ->add('datum_bis', DateType::class, ['widget' => 'single_text', 'html5' => true, 'label' => false, 'required' => false, 'attr' => ['placeholder' => 'Datum bis', 'class' => 'form-control']])
            ->add('suche', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Suche in Titel, Text']])
            ->add('submit', SubmitType::class, ['label' => 'Suche starten', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'attr' => ['name' => 'filter', 'id' => 'filter'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'filter';
    }
}
