<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use App\Form\DfxTermineType;
use App\Form\KalenderFilterType;
use App\Security\StatelessCsrfTokenManager;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class FrontendTerminFormFactory
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly StatelessCsrfTokenManager $statelessCsrfTokenManager,
    ) {
    }

    /**
     * @param array{key:string} $captcha
     * @param array<string, mixed> $routeParams
     * @param array<string, mixed> $extraOptions
     */
    public function createWriteForm(
        DfxTermine $entity,
        array $captcha,
        string $routeName,
        array $routeParams,
        string $submitLabel,
        array $extraOptions = [],
    ): FormInterface {
        $konf = $entity->getDatefix();
        $internalPath = $this->urlGenerator->generate($routeName, $routeParams);
        $action = $konf !== null ? $this->frontendBridgeAction($konf, $internalPath) : $internalPath;

        $form = $this->formFactory->create(DfxTermineType::class, $entity, array_merge([
            'action' => $action,
            'method' => 'POST',
            'konf' => $konf,
            'csrf_token_manager' => $this->statelessCsrfTokenManager,
        ], $extraOptions));

        $form
            ->add('gastEmail', EmailType::class, ['label' => 'Eingeber E-Mail', 'mapped' => false, 'required' => true, 'attr' => ['placeholder' => 'Ihre E-Mail-Adresse']])
            ->add('gastVorname', TextType::class, ['label' => 'Eingeber Vorname', 'mapped' => false, 'required' => true, 'attr' => ['placeholder' => 'Ihr Vorname']])
            ->add('gastNachname', TextType::class, ['label' => 'Eingeber Nachname ', 'mapped' => false, 'required' => true, 'attr' => ['placeholder' => 'Ihr Nachname']])
            ->add('cCode', TextType::class, ['label' => false, 'required' => true, 'mapped' => false, 'attr' => ['placeholder' => '4-stellige Codezahl', 'aria-label' => '4-stellige Codezahl']])
            ->add('key', HiddenType::class, ['data' => $captcha['key'], 'mapped' => false])
            ->add('datenschutz', CheckboxType::class, ['label' => false, 'required' => true, 'attr' => ['noFormControl' => true, 'aria-label' => 'Checkbox Datenschutz']])
            ->add('submit', SubmitType::class, ['label' => $submitLabel, 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);

        return $form;
    }

    private function frontendBridgeAction(DfxKonf $konf, string $internalPath): string
    {
        $separator = str_contains($konf->getFrontendUrl(), '?') ? '&' : '?';

        return $konf->getFrontendUrl() . $separator . http_build_query(['dfxpath' => $internalPath]);
    }

    public function createFilterForm(DfxKonf $konf, ?CalendarScope $calendarScope = null): FormInterface
    {
        $scope = $calendarScope ?? $this->calendarScopeResolver->resolveReadScope($konf);

        return $this->formFactory->create(KalenderFilterType::class, null, [
            'konf' => $konf,
            'calendar_scope' => $scope,
        ]);
    }
}
