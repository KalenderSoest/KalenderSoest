<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use App\Form\DfxTermineType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AdminTerminFormFactory
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function createCreateForm(DfxTermine $entity, DfxKonf $konf, array $calendarIds): FormInterface
    {
        return $this->buildForm(
            $entity,
            $konf,
            $calendarIds,
            $this->urlGenerator->generate('termine_create'),
            'Datensatz speichern',
        );
    }

    public function createEditForm(DfxTermine $entity, DfxKonf $konf, int $serie, array $calendarIds): FormInterface
    {
        $code = $serie === 1 ? $entity->getCode() : null;

        return $this->buildForm(
            $entity,
            $konf,
            $calendarIds,
            $this->urlGenerator->generate('termine_update', ['id' => $entity->getId(), 'code' => $code]),
            'Änderungen speichern',
        );
    }

    public function createCopyForm(DfxTermine $entity, DfxKonf $konf, int $id, array $calendarIds): FormInterface
    {
        return $this->buildForm(
            $entity,
            $konf,
            $calendarIds,
            $this->urlGenerator->generate('termine_save_copy', ['id' => $id]),
            'Kopie speichern',
        );
    }

    private function buildForm(
        DfxTermine $entity,
        DfxKonf $konf,
        array $calendarIds,
        string $action,
        string $submitLabel,
    ): FormInterface {
        $form = $this->formFactory->create(DfxTermineType::class, $entity, [
            'action' => $action,
            'method' => 'POST',
            'konf' => $konf,
            'arKids' => $calendarIds,
        ]);

        $form
            ->add('submit', SubmitType::class, ['label' => $submitLabel, 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);

        return $form;
    }
}
