<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxLocation;
use App\Form\DfxLocationType;
use App\Form\LocationFilterType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AdminLocationFormFactory
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function createFilterForm(): FormInterface
    {
        return $this->formFactory->create(LocationFilterType::class, [], [
            'action' => $this->urlGenerator->generate('admin_locations'),
        ]);
    }

    public function createCreateForm(DfxLocation $entity): FormInterface
    {
        return $this->buildWriteForm(
            $entity,
            [
                'action' => $this->urlGenerator->generate('location_create'),
                'method' => 'POST',
                'konf' => $entity->getDatefix(),
            ],
            'Datensatz speichern',
        );
    }

    public function createEditForm(DfxLocation $entity): FormInterface
    {
        return $this->buildWriteForm(
            $entity,
            [
                'action' => $this->urlGenerator->generate('location_update', ['id' => $entity->getId()]),
                'method' => 'POST',
                'konf' => $entity->getDatefix(),
            ],
            'Änderungen speichern',
        );
    }

    private function buildWriteForm(DfxLocation $entity, array $options, string $submitLabel): FormInterface
    {
        $form = $this->formFactory->create(DfxLocationType::class, $entity, $options);

        $form
            ->add('submit', SubmitType::class, ['label' => $submitLabel, 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);

        return $form;
    }
}
