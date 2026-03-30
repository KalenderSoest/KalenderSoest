<?php

namespace App\Service\Calendar;

use App\Entity\DfxDozenten;
use App\Form\DfxDozentenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AdminDozentenFormFactory
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function createCreateForm(DfxDozenten $entity): FormInterface
    {
        return $this->buildForm(
            $entity,
            [
                'action' => $this->urlGenerator->generate('dozent_create'),
                'method' => 'POST',
                'konf' => $entity->getDatefix(),
            ],
            'Datensatz speichern',
        );
    }

    public function createEditForm(DfxDozenten $entity): FormInterface
    {
        return $this->buildForm(
            $entity,
            [
                'action' => $this->urlGenerator->generate('dozent_update', ['id' => $entity->getId()]),
                'method' => 'PUT',
                'konf' => $entity->getDatefix(),
            ],
            'Änderungen speichern',
        );
    }

    private function buildForm(DfxDozenten $entity, array $options, string $submitLabel): FormInterface
    {
        $form = $this->formFactory->create(DfxDozentenType::class, $entity, $options);

        $form
            ->add('submit', SubmitType::class, ['label' => $submitLabel, 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);

        return $form;
    }
}
