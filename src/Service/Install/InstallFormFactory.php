<?php

namespace App\Service\Install;

use App\Entity\DfxNfxKunden;
use App\Entity\DfxNfxUser;
use App\Form\RegisterAccountType;
use App\Form\RegisterType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InstallFormFactory
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function createKundenForm(DfxNfxKunden $kunde): FormInterface
    {
        $form = $this->formFactory->create(RegisterType::class, $kunde, [
            'action' => $this->urlGenerator->generate('inst_kunden_create'),
            'method' => 'POST',
        ]);

        return $form
            ->add('submit', SubmitType::class, ['label' => 'Weiter mit Schritt 2 - Passwort festlegen', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function createAdminRegisterForm(DfxNfxKunden $kunde): FormInterface
    {
        $form = $this->formFactory->create(RegisterType::class, $kunde, [
            'action' => $this->urlGenerator->generate('account_create'),
            'method' => 'POST',
        ]);

        return $form
            ->add('password', RepeatedType::class, [
                'first_name' => 'first',
                'second_name' => 'second',
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Passwort'],
                'second_options' => ['label' => 'Passwort wiederholen'],
                'invalid_message' => 'Die Passwort-Eingaben stimmen nicht überein',
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Account fertigstellen', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function createAdminEditForm(DfxNfxKunden $kunde): FormInterface
    {
        return $this->formFactory->create(RegisterType::class, $kunde, [
            'action' => $this->urlGenerator->generate('kunde_update', ['id' => $kunde->getId()]),
            'method' => 'PUT',
        ])
            ->add('submit', SubmitType::class, ['label' => 'Änderungen speichern', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function createAccountForm(DfxNfxUser $user): FormInterface
    {
        $form = $this->formFactory->create(RegisterAccountType::class, $user, [
            'action' => $this->urlGenerator->generate('inst_account_create'),
        ]);

        return $form
            ->add('datenschutzUrl', TextType::class, ['label' => 'URL zur Datenschutzerklärung', 'mapped' => false, 'required' => true, 'attr' => ['placeholder' => 'https://....']])
            ->add('impressumUrl', TextType::class, ['label' => 'URL zum Impressum', 'mapped' => false, 'required' => true, 'attr' => ['placeholder' => 'https://....']])
            ->add('submit', SubmitType::class, ['label' => 'Account fertigstellen', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function createImportForm(): FormInterface
    {
        return $this->formFactory->createBuilder(null, null, ['method' => 'POST', 'attr' => ['name' => 'daba', 'id' => 'daba']])
            ->setAction($this->urlGenerator->generate('import_daba'))
            ->add('dbHost', TextType::class, ['label' => 'Datenabank Server', 'required' => false, 'mapped' => false, 'attr' => ['placeholder' => 'Datenbank Server']])
            ->add('dbName', TextType::class, ['label' => 'Datenbank Name', 'required' => false, 'mapped' => false, 'attr' => ['placeholder' => 'Datenbank Name']])
            ->add('dbUser', TextType::class, ['label' => 'Datenbank User', 'required' => false, 'mapped' => false, 'attr' => ['placeholder' => 'Datenbank User']])
            ->add('dbPassw', TextType::class, ['label' => 'Datenabank Passwort', 'required' => false, 'mapped' => false, 'attr' => ['placeholder' => 'Datenbank Passwort']])
            ->add('submit', SubmitType::class, ['label' => 'Import starten', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();
    }
}
