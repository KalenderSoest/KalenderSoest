<?php

namespace App\Form;

use App\Entity\DfxKonf;
use App\Entity\DfxLocation;
use App\Entity\DfxRegion;
use App\Entity\DfxVeranstalter;
use App\Service\Calendar\CalendarFieldChoiceProvider;
use App\Service\Calendar\CalendarScope;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class KalenderFilterType extends AbstractType
{
    public function __construct(
        private readonly CalendarFieldChoiceProvider $calendarFieldChoiceProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var DfxKonf $konf */
        $konf = $options['konf'];
        /** @var CalendarScope $calendarScope */
        $calendarScope = $options['calendar_scope'];
        $calendarIds = $calendarScope->ids();

        $rubriken = [];
        foreach ($konf->getRubriken() as $rubrik) {
            $rubriken[$rubrik] = $rubrik;
        }

        $zielgruppen = [];
        if (is_array($konf->getZielgruppen())) {
            foreach ($konf->getZielgruppen() as $zielgruppe) {
                $zielgruppen[$zielgruppe] = $zielgruppe;
            }
        }

        asort($rubriken);
        asort($zielgruppen);

        $builder
            ->add('datum_bis', DateType::class, ['label' => false, 'widget' => 'single_text', 'html5' => true, 'required' => false, 'placeholder' => 'Datum bis', 'attr' => ['class' => 'form-control', 'aria-label' => 'Datum bis']])
            ->add('datum_von', DateType::class, ['label' => false, 'widget' => 'single_text', 'html5' => true, 'required' => false, 'placeholder' => 'Datum ab', 'attr' => ['class' => 'form-control', 'aria-label' => 'Datum ab']])
            ->add('nat', ChoiceType::class, ['choices' => $this->calendarFieldChoiceProvider->forScope($calendarScope, 'nat'), 'label' => false, 'required' => false, 'placeholder' => 'Land wählen', 'attr' => ['aria-label' => 'Land wählen']])
            ->add('plz', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Plz-Gebiet', 'aria-label' => 'Land wählen']])
            ->add('ort', ChoiceType::class, ['choices' => $this->calendarFieldChoiceProvider->forScope($calendarScope, 'ort'), 'placeholder' => 'Ort wählen', 'label' => false, 'required' => false, 'attr' => ['class' => 'noformcontrol', 'aria-label' => 'Ort wählen']])
            ->add('umkreis', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Umkreis Km', 'aria-label' => 'Umkreis Km']])
            ->add('lokal', ChoiceType::class, ['choices' => $this->calendarFieldChoiceProvider->forScope($calendarScope, 'lokal'), 'placeholder' => 'Veranstaltungsstätte wählen', 'label' => false, 'required' => false, 'attr' => ['aria-label' => 'Veranstaltungsstätte wählen']])
            ->add('veranstalter', ChoiceType::class, ['choices' => $this->calendarFieldChoiceProvider->forScope($calendarScope, 'veranstalter'), 'placeholder' => 'Veranstalter wählen', 'label' => false, 'required' => false, 'attr' => ['aria-label' => 'Veranstalter wählen']])
            ->add('region', EntityType::class, [
                'class' => DfxRegion::class,
                'query_builder' => function (EntityRepository $er) use ($calendarIds): QueryBuilder {
                    $query = $er->createQueryBuilder('r')->select(['r']);
                    if ($calendarIds !== []) {
                        $query->where('r.datefix IN (:kids)')
                            ->setParameter('kids', $calendarIds);
                    }

                    return $query->orderBy('r.region', 'ASC');
                },
                'choice_label' => 'region',
                'placeholder' => 'Region wählen',
                'label' => false,
                'required' => false,
                'attr' => ['aria-label' => 'Region wählen'],
            ])
            ->add('idVeranstalter', EntityType::class, [
                'class' => DfxVeranstalter::class,
                'query_builder' => function (EntityRepository $er) use ($calendarIds): QueryBuilder {
                    $query = $er->createQueryBuilder('v')->select(['v']);
                    if ($calendarIds !== []) {
                        $query->where('v.datefix IN (:kids)')
                            ->setParameter('kids', $calendarIds);
                    }

                    return $query->orderBy('v.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => 'Veranstalter aus Datenbank wählen',
                'label' => false,
                'required' => false,
                'attr' => ['aria-label' => 'Veranstalter aus Datenbank wählen'],
            ])
            ->add('idLocation', EntityType::class, [
                'class' => DfxLocation::class,
                'query_builder' => function (EntityRepository $er) use ($calendarIds): QueryBuilder {
                    $query = $er->createQueryBuilder('v')->select(['v']);
                    if ($calendarIds !== []) {
                        $query->where('v.datefix IN (:kids)')
                            ->setParameter('kids', $calendarIds);
                    }

                    return $query->orderBy('v.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => 'Veranstaltungsstätte aus Datenbank wählen',
                'label' => false,
                'required' => false,
                'attr' => ['aria-label' => 'Veranstaltungsstätte aus Datenbank wählen'],
            ])
            ->add('rubrik', ChoiceType::class, ['choices' => $rubriken, 'placeholder' => 'Rubrik wählen', 'label' => false, 'required' => false, 'attr' => ['aria-label' => 'Rubrik wählen']])
            ->add('zielgruppe', ChoiceType::class, ['choices' => $zielgruppen, 'placeholder' => 'Zielgruppe wählen', 'label' => false, 'required' => false, 'attr' => ['aria-label' => 'Zielgruppe wählen']])
            ->add('filter1', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter2', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter3', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter4', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter5', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('suche', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Suche in Titel, Text', 'aria-label' => 'Suche in Titel, Text']])
            ->add('bg', HiddenType::class)
            ->add('lg', HiddenType::class)
            ->add('m', HiddenType::class)
            ->add('t', HiddenType::class)
            ->add('submit', SubmitType::class, ['label' => 'suchen', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'verwerfen', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['konf', 'calendar_scope']);
        $resolver->setAllowedTypes('konf', DfxKonf::class);
        $resolver->setAllowedTypes('calendar_scope', CalendarScope::class);
    }

    public function getBlockPrefix(): string
    {
        return 'form';
    }
}
