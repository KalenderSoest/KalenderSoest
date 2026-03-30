<?php

namespace App\Form;

use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use App\Security\CurrentContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AdminTermineFilterType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CurrentContext $currentContext,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

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
            ->add('datum_von', DateType::class, ['widget' => 'single_text', 'html5' => true, 'label' => false, 'required' => false, 'attr' => ['placeholder' => 'Datum von', 'class' => 'form-control']])
            ->add('datum_bis', DateType::class, ['widget' => 'single_text', 'html5' => true, 'label' => false, 'required' => false, 'attr' => ['placeholder' => 'Datum bis', 'class' => 'form-control']])
            ->add('nat', ChoiceType::class, ['choices' => $this->buildChoices('nat'), 'label' => false, 'required' => false, 'placeholder' => 'Land wählen'])
            ->add('plz', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Plz-Gebiet']])
            ->add('ort', ChoiceType::class, ['choices' => $this->buildChoices('ort'), 'placeholder' => 'Ort wählen', 'label' => false, 'required' => false])
            ->add('umkreis', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Umkreis Km']])
            ->add('lokal', ChoiceType::class, ['choices' => $this->buildChoices('lokal'), 'placeholder' => 'Veranstaltungsstätte wählen', 'label' => false, 'required' => false])
            ->add('veranstalter', ChoiceType::class, ['choices' => $this->buildChoices('veranstalter'), 'placeholder' => 'Veranstalter wählen', 'label' => false, 'required' => false])
            ->add('rubrik', ChoiceType::class, ['choices' => $rubriken, 'placeholder' => 'Rubrik wählen', 'label' => false, 'required' => false])
            ->add('zielgruppe', ChoiceType::class, ['choices' => $zielgruppen, 'placeholder' => 'Zielgruppe wählen', 'label' => false, 'required' => false])
            ->add('optionsRadio', ChoiceType::class, ['choices' => array_flip($options['options_radio']), 'placeholder' => 'Terminstatus wählen', 'label' => false, 'required' => false])
            ->add('suche', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Suche in Titel, Text']])
            ->add('hideSub', CheckboxType::class, ['label' => 'Daten untergeordneter Kalender nicht anzeigen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('filterPub', CheckboxType::class, ['label' => 'Nur Termine mit Meta- oder Gruppen-Status "unveröffentlicht" anzeigen', 'required' => false, 'attr' => ['noFormControl' => true]])
            ->add('submit', SubmitType::class, ['label' => 'Suche starten', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['konf', 'options_radio']);
        $resolver->setAllowedTypes('konf', DfxKonf::class);
        $resolver->setAllowedTypes('options_radio', 'array');
    }

    private function buildChoices(string $field): array
    {
        $query = $this->em->getRepository(DfxTermine::class)
            ->createQueryBuilder('t')
            ->select('t.' . $field);

        if (!$this->authorizationChecker->isGranted('ROLE_DFX_ALL')) {
            $uid = $this->currentContext->getUser()->getId();
            $query->where('t.user = :uid')
                ->setParameter('uid', $uid);
        }

        $entities = $query
            ->groupBy('t.' . $field)
            ->orderBy('t.' . $field, 'ASC')
            ->getQuery()
            ->getResult();

        $choices = [];
        foreach ($entities as $entity) {
            if (($entity[$field] ?? '') !== '') {
                $choices[$entity[$field]] = $entity[$field];
            }
        }

        return $choices;
    }
}
