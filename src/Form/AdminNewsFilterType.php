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

final class AdminNewsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var DfxKonf $konf */
        $konf = $options['konf'];

        $rubriken = [];
        foreach ($konf->getRubriken() as $rubrik) {
            $rubriken[$rubrik] = $rubrik;
        }
        asort($rubriken);

        $zielgruppen = [];
        if (is_array($konf->getZielgruppen())) {
            foreach ($konf->getZielgruppen() as $zielgruppe) {
                $zielgruppen[$zielgruppe] = $zielgruppe;
            }
            asort($zielgruppen);
        }

        $builder
            ->add('datum_bis', DateType::class, ['label' => false, 'widget' => 'single_text', 'html5' => true, 'required' => false, 'placeholder' => 'Datum bis', 'attr' => ['class' => 'form-control']])
            ->add('datum_von', DateType::class, ['label' => false, 'widget' => 'single_text', 'html5' => true, 'required' => false, 'placeholder' => 'Datum ab', 'attr' => ['class' => 'form-control']])
            ->add('rubrik', ChoiceType::class, ['choices' => $rubriken, 'placeholder' => 'Rubrik wählen', 'label' => false, 'required' => false])
            ->add('zielgruppe', ChoiceType::class, ['choices' => $zielgruppen, 'placeholder' => 'Zielgruppe wählen', 'label' => false, 'required' => false])
            ->add('suche', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Suche in Titel, Text']])
            ->add('hideSub', CheckboxType::class, ['label' => 'Daten untergeordneter Kalender nicht anzeigen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterPub', CheckboxType::class, ['label' => 'Nur Artikel mit Meta- oder Gruppen-Status "unveröffentlicht" anzeigen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('submit', SubmitType::class, ['label' => 'Suche starten', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['konf']);
        $resolver->setAllowedTypes('konf', DfxKonf::class);
    }
}
