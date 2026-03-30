<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use App\Form\DfxNewsType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AdminNewsFormFactory
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function createCreateForm(DfxNews $entity, DfxKonf $konf, array $calendarIds): FormInterface
    {
        return $this->buildForm(
            $entity,
            $konf,
            [
                'action' => $this->urlGenerator->generate('news_create'),
                'method' => 'POST',
                'konf' => $konf,
                'arKids' => $calendarIds,
            ],
            'Datensatz speichern',
        );
    }

    public function createEditForm(DfxNews $entity, DfxKonf $konf): FormInterface
    {
        return $this->buildForm(
            $entity,
            $konf,
            [
                'action' => $this->urlGenerator->generate('news_update', ['id' => $entity->getId()]),
                'method' => 'POST',
                'konf' => $konf,
            ],
            'Änderungen speichern',
        );
    }

    private function buildForm(DfxNews $entity, DfxKonf $konf, array $options, string $submitLabel): FormInterface
    {
        $form = $this->formFactory->create(DfxNewsType::class, $entity, $options);

        $form
            ->add('submit', SubmitType::class, ['label' => $submitLabel, 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);

        return $form;
    }
}
