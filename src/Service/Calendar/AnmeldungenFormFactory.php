<?php

namespace App\Service\Calendar;

use App\Entity\DfxAnmeldungen;
use App\Form\AnmeldungenFilterType;
use App\Form\DfxAnmeldungenType;
use App\Security\StatelessCsrfTokenManager;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AnmeldungenFormFactory
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly StatelessCsrfTokenManager $statelessCsrfTokenManager,
    ) {
    }

    public function createFilterForm(): FormInterface
    {
        return $this->formFactory->create(AnmeldungenFilterType::class, null, [
            'action' => $this->urlGenerator->generate('admin_anmeldungen'),
        ]);
    }

    public function createCreateForm(DfxAnmeldungen $entity, int $terminId, array $captcha): FormInterface
    {
        $form = $this->formFactory->create(DfxAnmeldungenType::class, $entity, [
            'action' => $this->urlGenerator->generate('anmeldungen_create', ['id' => $terminId]),
            'method' => 'GET',
            'cKey' => $captcha['key'],
            'csrf_token_manager' => $this->statelessCsrfTokenManager,
        ]);

        $form
            ->add('submit', SubmitType::class, ['label' => 'Anmeldung senden', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);

        return $form;
    }

    public function createEditForm(DfxAnmeldungen $entity): FormInterface
    {
        $form = $this->formFactory->create(DfxAnmeldungenType::class, $entity, [
            'action' => $this->urlGenerator->generate('anmeldungen_update', ['id' => $entity->getId()]),
            'method' => 'PUT',
        ]);

        $form
            ->add('submit', SubmitType::class, ['label' => 'Änderungen speichern', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);

        return $form;
    }
}
