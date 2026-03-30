<?php

namespace App\Form;

use App\Entity\DfxKonf;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NewsFrontendFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var DfxKonf $konf */
        $konf = $options['konf'];
        $rubriken = [];
        foreach ($konf->getRubriken() as $rubrik) {
            $rubriken[$rubrik] = $rubrik;
        }

        $zielgruppen = [];
        if ($options['zielgruppe_enabled']) {
            foreach ($konf->getZielgruppen() as $zielgruppe) {
                $zielgruppen[$zielgruppe] = $zielgruppe;
            }
        }

        asort($rubriken);
        asort($zielgruppen);

        $builder
            ->add('datum_bis', DateType::class, ['label' => false, 'widget' => 'single_text', 'html5' => true, 'required' => false, 'placeholder' => 'Datum bis', 'attr' => ['class' => 'form-control']])
            ->add('datum_von', DateType::class, ['label' => false, 'widget' => 'single_text', 'html5' => true, 'required' => false, 'placeholder' => 'Datum ab', 'attr' => ['class' => 'form-control']])
            ->add('rubrik', ChoiceType::class, ['choices' => $rubriken, 'placeholder' => 'Rubrik wählen', 'label' => false, 'required' => false])
            ->add('zielgruppe', ChoiceType::class, ['choices' => $zielgruppen, 'placeholder' => 'Zielgruppe wählen', 'label' => false, 'required' => false])
            ->add('filter1', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter2', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter3', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter4', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter5', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('suche', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Suche in Titel, Text']])
            ->add('submit', SubmitType::class, ['label' => 'suchen', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'verwerfen', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'konf' => null,
            'zielgruppe_enabled' => false,
            'csrf_protection' => false,
            'method' => 'GET',
            'attr' => ['name' => 'filter', 'id' => 'filter'],
        ]);
        $resolver->setAllowedTypes('konf', [DfxKonf::class]);
        $resolver->setAllowedTypes('zielgruppe_enabled', 'bool');
    }

    public function getBlockPrefix(): string
    {
        return 'form';
    }
}
