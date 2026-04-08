<?php

namespace App\Controller;
use App\Service\Frontend\FrontendContentRenderer;
use App\Service\Calendar\CalendarScope;
use App\Service\Calendar\CalendarPublicationQueryHelper;
use App\Service\Calendar\KalenderFilterData;
use App\Service\Calendar\KalenderDetailMonthContextBuilder;
use App\Service\Calendar\KalenderFilterQueryApplier;
use App\Service\Calendar\CalendarScopeResolver;
use App\Service\Calendar\KalenderDetailViewBuilder;
use App\Service\Calendar\KalenderMonthViewBuilder;
use App\Service\Analytics\UsageTrackingService;
use App\Service\Presentation\TemplatePathResolver;
use App\Service\Presentation\HtmlResponseService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DfxTermine;
use App\Entity\DfxKonf;
use App\Form\KalenderFilterType;
use Knp\Component\Pager\PaginatorInterface;
class DfxKalenderController extends AbstractController
{
    public function __construct(
        private readonly CalendarPublicationQueryHelper $calendarPublicationQueryHelper,
        private readonly KalenderFilterQueryApplier $kalenderFilterQueryApplier,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly KalenderDetailViewBuilder $kalenderDetailViewBuilder,
        private readonly KalenderDetailMonthContextBuilder $kalenderDetailMonthContextBuilder,
        private readonly KalenderMonthViewBuilder $kalenderMonthViewBuilder,
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly HtmlResponseService $htmlResponseService,
        private readonly UsageTrackingService $usageTrackingService,
        private readonly FrontendContentRenderer $frontendContentRenderer,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    #[Route(path: '/js/kalender/{kid}', name: 'kalender', methods: ['GET', 'POST'])]
    public function index(string $kid, Request $request, PaginatorInterface $paginator): Response
    {
        $konf = $this->loadKonf((int) $kid);
        $result = $this->frontendContentRenderer->renderCalendarList($konf, $request);

        return $this->htmlResponseService->raw($result['content']);
    }

    #[Route(path: '/js/kalender/widget/{kid}', name: 'kalender_widget', methods: ['GET'])]
    public function widget(int $kid, Request $request): Response
    {
        $konf = $this->loadKonf($kid);

        $form = $this->createFilterForm($konf, $this->calendarScopeResolver->resolveReadScope($konf));
        $form->handleRequest($request);
        $filterForm = $form->createView();
        $tpl = $this->templatePathResolver->resolve('DfxNfxWidgets','widget_kalender.html.twig', $konf);
        $options = ['konf' => $konf, 'filter_form' => $filterForm, 'widget' => 1];
        return $this->htmlResponseService->render($tpl, $options);
    }

    private function createFilterForm(DfxKonf $konf, ?CalendarScope $calendarScope = null): FormInterface
    {
        $calendarScope ??= $this->calendarScopeResolver->resolveReadScope($konf);
        return $this->createForm(KalenderFilterType::class, null, [
            'konf' => $konf,
            'calendar_scope' => $calendarScope,
            'action' => $this->generateUrl('kalender_fe', ['kid' => $konf->getId()]),
            'method' => 'GET',
            'csrf_protection' => false,
            'attr' => ['name' => 'filter', 'id' => 'filter'],
        ]);
    }

    private function createKalenderFilterDataFromForm(Request $request, FormInterface $form): KalenderFilterData
    {
        return new KalenderFilterData(
            $this->scalarQueryValue($request, 'rubrik', $form->get('rubrik')->getData()),
            $this->scalarQueryValue($request, 'zielgruppe', $form->get('zielgruppe')->getData()),
            [
                1 => (bool) $form->get('filter1')->getData(),
                2 => (bool) $form->get('filter2')->getData(),
                3 => (bool) $form->get('filter3')->getData(),
                4 => (bool) $form->get('filter4')->getData(),
                5 => (bool) $form->get('filter5')->getData(),
            ],
            $this->scalarQueryValue($request, 'veranstalter', $form->get('veranstalter')->getData()),
            $request->query->get('idVeranstalter') ?? $form->get('idVeranstalter')->getData(),
            $this->scalarQueryValue($request, 'lokal', $form->get('lokal')->getData()),
            $request->query->get('idLocation') ?? $form->get('idLocation')->getData(),
            $this->normalizeString($form->get('umkreis')->getData()),
            $this->scalarQueryValue($request, 'plz', $form->get('plz')->getData()),
            $this->scalarQueryValue($request, 'ort', $form->get('ort')->getData()),
            $this->normalizeFloat($form->get('bg')->getData()),
            $this->normalizeFloat($form->get('lg')->getData()),
            $this->scalarQueryValue($request, 'nat', $form->get('nat')->getData()),
            $request->query->get('region') ?? $form->get('region')->getData(),
            $this->normalizeString($form->get('suche')->getData()),
            $this->normalizeString($form->get('m')->getData()),
            $this->normalizeString($form->get('t')->getData()),
            $form->get('datum_von')->getData(),
            $form->get('datum_bis')->getData(),
        );
    }

    private function createKalenderFilterDataFromArray(array $formData): KalenderFilterData
    {
        return new KalenderFilterData(
            $this->normalizeString($formData['rubrik'] ?? null),
            $this->normalizeString($formData['zielgruppe'] ?? null),
            [
                1 => $this->normalizeCheckbox($formData['filter1'] ?? null),
                2 => $this->normalizeCheckbox($formData['filter2'] ?? null),
                3 => $this->normalizeCheckbox($formData['filter3'] ?? null),
                4 => $this->normalizeCheckbox($formData['filter4'] ?? null),
                5 => $this->normalizeCheckbox($formData['filter5'] ?? null),
            ],
            $this->normalizeString($formData['veranstalter'] ?? null),
            $formData['idVeranstalter'] ?? null,
            $this->normalizeString($formData['lokal'] ?? null),
            $formData['idLocation'] ?? null,
            $this->normalizeString($formData['umkreis'] ?? null),
            $this->normalizeString($formData['plz'] ?? null),
            $this->normalizeString($formData['ort'] ?? null),
            $this->normalizeFloat($formData['bg'] ?? null),
            $this->normalizeFloat($formData['lg'] ?? null),
            $this->normalizeString($formData['nat'] ?? null),
            $formData['region'] ?? null,
            $this->normalizeString($formData['suche'] ?? null),
            $this->normalizeString($formData['m'] ?? null),
            $this->normalizeString($formData['t'] ?? null),
            null,
            null,
        );
    }

    private function buildKalenderHeadline(KalenderFilterData $filterData, array $cfgMonths): string
    {
        $headline = '';

        foreach ([$filterData->rubrik, $filterData->zielgruppe, $filterData->veranstalter, $filterData->lokal] as $text) {
            if ($text !== null && $text !== '') {
                $headline .= $text . ' ';
            }
        }

        if (is_object($filterData->idVeranstalter) && method_exists($filterData->idVeranstalter, 'getName')) {
            $headline .= $filterData->idVeranstalter->getName() . ' ';
        }

        if (is_object($filterData->idLocation) && method_exists($filterData->idLocation, 'getName')) {
            $headline .= $filterData->idLocation->getName() . ' ';
        }

        if ($filterData->hasRadiusSearch()) {
            $headline .= 'im Umkreis von  ' . $filterData->umkreis . ' Km um ' . ($filterData->plz ?? '') . ' ' . ($filterData->ort ?? '') . ' ';
        } else {
            if ($filterData->nat !== null && $filterData->nat !== '') {
                $headline .= $filterData->nat . ' ';
            }
            if ($filterData->plz !== null && $filterData->plz !== '') {
                $headline .= 'im Postleitzahlgebiet  ' . $filterData->plz . ' ';
            }
            if ($filterData->ort !== null && $filterData->ort !== '') {
                $headline .= $filterData->ort . ' ';
            }
            if (is_object($filterData->region) && method_exists($filterData->region, 'getRegion')) {
                $headline .= 'Region ' . $filterData->region->getRegion() . ' ';
            }
        }

        if ($filterData->suche !== null && $filterData->suche !== '') {
            $headline .= 'mit Suchwort(en)  "' . $filterData->suche . '" ';
        }

        if ($filterData->t !== null && $filterData->t !== '') {
            $tag = explode('-', $filterData->t);
            return $headline . 'am ' . ($tag[2] ?? '') . '.' . ($tag[1] ?? '') . '.' . ($tag[0] ?? '');
        }

        if (!$filterData->hasExplicitDateSelection()) {
            return $headline . 'ab heute, ' . date('j.n.Y');
        }

        if ($filterData->datumVon !== null && $filterData->datumBis !== null) {
            if ($filterData->datumVon->format('Y-m-d') === $filterData->datumBis->format('Y-m-d')) {
                return $headline . 'am ' . $filterData->datumVon->format('d.m.Y');
            }

            return $headline . 'von ' . $filterData->datumVon->format('d.m.Y') . ' bis ' . $filterData->datumBis->format('d.m.Y');
        }

        if ($filterData->datumVon !== null) {
            return $headline . 'ab ' . $filterData->datumVon->format('d.m.Y');
        }

        if ($filterData->datumBis !== null) {
            return $headline . 'bis ' . $filterData->datumBis->format('d.m.Y');
        }

        if ($filterData->m !== null && $filterData->m !== '') {
            [$year, $month] = explode('-', $filterData->m) + [null, null];
            return $headline . ucfirst($cfgMonths[(int) $month] ?? '') . ' ' . $year;
        }

        return trim($headline);
    }

    private function buildKalenderFilterParams(KalenderFilterData $filterData): array
    {
        $params = [];

        foreach (['rubrik', 'zielgruppe', 'veranstalter', 'lokal', 'nat', 'plz', 'ort', 'suche', 'm', 't'] as $field) {
            $value = $filterData->{$field};
            if ($value !== null && $value !== '') {
                $params[$field] = $value;
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            if ($filterData->filterEnabled($i)) {
                $params['filter' . $i] = 1;
            }
        }

        if (is_object($filterData->idVeranstalter) && method_exists($filterData->idVeranstalter, 'getId')) {
            $params['idVeranstalter'] = $filterData->idVeranstalter->getId();
        }

        if (is_object($filterData->idLocation) && method_exists($filterData->idLocation, 'getId')) {
            $params['idLocation'] = $filterData->idLocation->getId();
        }

        if (is_object($filterData->region) && method_exists($filterData->region, 'getId')) {
            $params['region'] = $filterData->region->getId();
        }

        if ($filterData->hasRadiusSearch()) {
            $params['bg'] = $filterData->bg ?? 0;
            $params['lg'] = $filterData->lg ?? 0;
            $params['umkreis'] = (int) ($filterData->umkreis ?? 0);
        }

        if ($filterData->datumVon !== null) {
            $params['datum_von'] = $filterData->datumVon->format('Y-m-d');
        }

        if ($filterData->datumBis !== null) {
            $params['datum_bis'] = $filterData->datumBis->format('Y-m-d');
        }

        return $params;
    }

    private function buildPaginationFilter(array $filter): string
    {
        $strFilter = '';
        foreach ($filter as $key => $val) {
            if ($key === 'dfxp') {
                $strFilter .= '&' . $key . '=' . $val;
                continue;
            }

            $strFilter .= '&form%5B' . $key . '%5D=' . urlencode((string) $val);
        }

        return $strFilter;
    }

    private function scalarQueryValue(Request $request, string $name, mixed $fallback): ?string
    {
        return $this->normalizeString($request->query->get($name) ?? $fallback);
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function normalizeCheckbox(mixed $value): bool
    {
        return in_array($value, [1, '1', true, 'true', 'on'], true);
    }

    #[Route(path: '/js/kalender/json/widgets/{kid}/{callback}', name: 'json_widgets', defaults: ['callback' => 'true'], methods: ['GET'])]
    public function jsonAction(int $kid, $callback, Request $request): JsonResponse
    {
        $konf = $this->loadKonf($kid);

        $calendarScope = $this->calendarScopeResolver->resolveReadScope($konf);
        $repository = $this->em->getRepository(DfxTermine::class);
        $queryKal = $repository->createQueryBuilder('t')
            ->select(['t.datumVon']);

        if ($calendarScope->restrictsResults()) {
            $queryKal->where('t.datefix IN (:kids)')
                ->setParameter('kids', $calendarScope->ids());
        }

        $this->calendarPublicationQueryHelper->applyPublishedVisibility($queryKal, 't', $konf);
        $filterData = $this->createKalenderFilterDataFromArray((array) $request->query->get('form', []));
        $this->kalenderFilterQueryApplier->applySharedFilters($queryKal, $filterData);
        if (!$filterData->hasExplicitDateSelection()) {
            $queryKal->andWhere('t.datumVon >= CURRENT_DATE()');
        }
        $monthSelection = $this->kalenderFilterQueryApplier->applyCalendarMonth($queryKal, $filterData);

        $queryKal->groupBy('t.datumVon')
            ->orderBy('t.datumVon', 'ASC')
            ->getQuery();
        $tageKal = $queryKal->getArrayResult();

        $kalJahr = $monthSelection['year'] ?? (int) date('Y');
        $kalMonat = $monthSelection['month'] ?? (int) date('m');

        $strJson = $this->kalenderMonthViewBuilder->buildLegacyJson($tageKal, $kalJahr, $kalMonat);

        $response = new JsonResponse();
        $sender = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_HOST'];

        $response->headers->add([ 'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS','Access-Control-Allow-Origin' =>  $sender]);
        $response->setData($strJson);
        if($callback === 'true'){
            $response->setCallback('datefixKalender');
        }

        return $response;
    }


    #[Route(path: '/js/kalender/{kid}/detail/{id}/{titel}', defaults: ['titel' => ''], methods: ['GET'], name: 'detail')]
    public function terminShow(int $kid, int $id, Request $request): Response
    {
        $konf = $this->loadKonf($kid);
        $result = $this->frontendContentRenderer->renderCalendarDetail($konf, $request, $id);

        return $this->htmlResponseService->raw($result['content']);

    }

    private function loadKonf(int $kid): DfxKonf
    {
        $konf = $this->em->getRepository(DfxKonf::class)->find($kid);
        if ($konf === null) {
            throw $this->createNotFoundException('Kein Account gefunden für KalenderID ' . $kid);
        }

        return $konf;
    }

    private function loadReadableTermin(DfxKonf $konf, int $kid, int $id): DfxTermine
    {
        $termin = $this->em->getRepository(DfxTermine::class)->find($id);
        if ($termin === null) {
            throw $this->createNotFoundException('Kein Termin gefunden für ID ' . $id);
        }

        if ($termin->getDatefix()->getId() !== $kid && $konf->getIsMeta() != 1 && $konf->getIsGroup() != 1) {
            throw $this->createNotFoundException('Termin gehört nicht zu diesem Kalender.');
        }

        return $termin;
    }

}
