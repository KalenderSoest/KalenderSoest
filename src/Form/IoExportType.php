<?php

namespace App\Form;

use App\Entity\DfxKonf;
use App\Entity\DfxRegion;
use App\Service\Calendar\CalendarFieldChoiceProvider;
use App\Service\Calendar\CalendarScope;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class IoExportType extends AbstractType
{
    public function __construct(
        private readonly CalendarFieldChoiceProvider $calendarFieldChoiceProvider,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var DfxKonf $konf */
        $konf = $options['konf'];
        /** @var CalendarScope $calendarScope */
        $calendarScope = $options['calendar_scope'];

        $rubriken = [];
        foreach ($konf->getRubriken() ?? [] as $rubrik) {
            $rubriken[$rubrik] = $rubrik;
        }

        $zielgruppen = [];
        foreach ($konf->getZielgruppen() ?? [] as $zielgruppe) {
            $zielgruppen[$zielgruppe] = $zielgruppe;
        }

        $typen = [
            'all' => 'Excel/xls - Komplett (z. B für Datefix-Import)',
            'more' => 'Excel/xls - Basisdaten + Bilder, Links, Funktionen, ...',
            'base' => 'Excel/xls - Basisdaten (was, wann, wo, ...)',
            'newsletter' => 'HTML-Code zum Einfügen in Newsletter',
            'xml' => 'XML',
        ];

        $kids = $calendarScope->ids();

        $builder
            ->add('datum_von', DateType::class, ['widget' => 'single_text', 'html5' => true, 'label' => 'Datum von', 'required' => false, 'attr' => ['placeholder' => 'Datum von', 'class' => 'form-control']])
            ->add('datum_bis', DateType::class, ['widget' => 'single_text', 'html5' => true, 'label' => 'Datum bis', 'required' => false, 'attr' => ['placeholder' => 'Datum bis', 'class' => 'form-control']])
            ->add('datum_created', DateType::class, ['widget' => 'single_text', 'html5' => true, 'label' => 'Eingabe ab', 'required' => false, 'attr' => ['placeholder' => 'Eingabe ab', 'class' => 'form-control']])
            ->add('nat', ChoiceType::class, ['choices' => $this->calendarFieldChoiceProvider->forScope($calendarScope, 'nat', true), 'label' => false, 'required' => false, 'placeholder' => 'Land wählen'])
            ->add('plz', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Plz-Gebiet']])
            ->add('ort', ChoiceType::class, ['choices' => $this->calendarFieldChoiceProvider->forScope($calendarScope, 'ort', true), 'placeholder' => 'Ort wählen', 'label' => false, 'required' => false])
            ->add('lokal', ChoiceType::class, ['choices' => $this->calendarFieldChoiceProvider->forScope($calendarScope, 'lokal', true), 'placeholder' => 'Veranstaltungsstätte wählen', 'label' => false, 'required' => false])
            ->add('veranstalter', ChoiceType::class, ['choices' => $this->calendarFieldChoiceProvider->forScope($calendarScope, 'veranstalter', true), 'placeholder' => 'Veranstalter wählen', 'label' => false, 'required' => false])
            ->add('region', EntityType::class, [
                'class' => DfxRegion::class,
                'query_builder' => function (EntityRepository $er) use ($kids) {
                    $query = $er->createQueryBuilder('r')
                        ->select(['r']);
                    if (count($kids) > 0) {
                        $query->where('r.datefix IN (:kids)')
                            ->setParameter('kids', $kids);
                    }
                    $query->orderBy('r.region', 'ASC');

                    return $query;
                },
                'choice_label' => 'region',
                'placeholder' => 'Region wählen',
                'label' => false,
                'required' => false,
            ])
            ->add('rubrik', ChoiceType::class, ['choices' => $rubriken, 'placeholder' => 'Rubrik wählen', 'label' => false, 'required' => false])
            ->add('zielgruppe', ChoiceType::class, ['choices' => $zielgruppen, 'placeholder' => 'Zielgruppe wählen', 'label' => false, 'required' => false])
            ->add('suche', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Suche in Titel, Text']])
            ->add('stripTags', CheckboxType::class, ['label' => 'HTML-Code entfernen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('exportSub', CheckboxType::class, ['label' => 'Daten untergeordneter Kalender mit einbeziehen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('exportTyp', ChoiceType::class, ['expanded' => true, 'data' => 'all', 'choices' => array_flip($typen), 'label' => 'Exportoptionen', 'required' => false, 'placeholder' => false, 'attr' => ['cols' => 4, 'noFormControl' => true]])
            ->add('exportflag', TextType::class, ['label' => 'Flag für exportierte Datensätze z.B. RCE2020', 'required' => false, 'attr' => ['class' => 'form-control', 'placeholder' => 'Exportflag']])
            ->add('filter1', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter2', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter3', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter4', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filter5', CheckboxType::class, ['label' => false, 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('submit', SubmitType::class, ['label' => 'Export starten', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'method' => 'POST',
        ]);
        $resolver->setRequired(['konf', 'calendar_scope']);
        $resolver->setAllowedTypes('konf', DfxKonf::class);
        $resolver->setAllowedTypes('calendar_scope', CalendarScope::class);
    }
}
