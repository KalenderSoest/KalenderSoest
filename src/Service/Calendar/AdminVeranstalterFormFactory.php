<?php

namespace App\Service\Calendar;

use App\Entity\DfxVeranstalter;
use App\Form\DfxVeranstalterType;
use App\Form\VeranstalterFilterType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AdminVeranstalterFormFactory
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function createFilterForm(): FormInterface
    {
        return $this->formFactory->create(VeranstalterFilterType::class, [], [
            'action' => $this->urlGenerator->generate('admin_veranstalter'),
        ]);
    }

    public function createCreateForm(DfxVeranstalter $entity): FormInterface
    {
        return $this->buildWriteForm(
            $entity,
            [
                'action' => $this->urlGenerator->generate('veranstalter_create'),
                'method' => 'POST',
                'konf' => $entity->getDatefix(),
            ],
            'Datensatz speichern',
        );
    }

    public function createEditForm(DfxVeranstalter $entity): FormInterface
    {
        return $this->buildWriteForm(
            $entity,
            [
                'action' => $this->urlGenerator->generate('veranstalter_update', ['id' => $entity->getId()]),
                'method' => 'POST',
                'konf' => $entity->getDatefix(),
            ],
            'Änderungen speichern',
        );
    }

    private function buildWriteForm(DfxVeranstalter $entity, array $options, string $submitLabel): FormInterface
    {
        $form = $this->formFactory->create(DfxVeranstalterType::class, $entity, $options);

        $form
            ->add('submit', SubmitType::class, ['label' => $submitLabel, 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);

        return $form;
    }
}
