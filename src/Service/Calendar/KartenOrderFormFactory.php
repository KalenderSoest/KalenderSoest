<?php

namespace App\Service\Calendar;

use App\Entity\DfxKartenOrder;
use App\Form\DfxKartenOrderType;
use App\Form\KartenOrderFilterType;
use App\Security\StatelessCsrfTokenManager;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class KartenOrderFormFactory
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly StatelessCsrfTokenManager $statelessCsrfTokenManager,
    ) {
    }

    public function createFilterForm(): FormInterface
    {
        return $this->formFactory->create(KartenOrderFilterType::class, null, [
            'action' => $this->urlGenerator->generate('admin_karten'),
        ]);
    }

    public function createCreateForm(DfxKartenOrder $entity, int $terminId, array $captcha): FormInterface
    {
        $form = $this->formFactory->create(DfxKartenOrderType::class, $entity, [
            'action' => $this->urlGenerator->generate('karten_create', ['id' => $terminId]),
            'method' => 'GET',
            'cKey' => $captcha['key'],
            'csrf_token_manager' => $this->statelessCsrfTokenManager,
        ]);

        $form
            ->add('submit', SubmitType::class, ['label' => 'Kartenreservierung senden', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);

        return $form;
    }

    public function createEditForm(DfxKartenOrder $entity): FormInterface
    {
        $form = $this->formFactory->create(DfxKartenOrderType::class, $entity, [
            'action' => $this->urlGenerator->generate('karten_update', ['id' => $entity->getId()]),
            'method' => 'PUT',
        ]);

        $form
            ->add('submit', SubmitType::class, ['label' => 'Änderungen speichern', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);

        return $form;
    }
}
