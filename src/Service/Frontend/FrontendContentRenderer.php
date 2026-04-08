<?php

namespace App\Service\Frontend;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use App\Entity\DfxTermine;
use App\Form\KalenderFilterType;
use App\Form\NewsFrontendFilterType;
use App\Service\Analytics\UsageTrackingService;
use App\Service\Calendar\CalendarPublicationQueryHelper;
use App\Service\Calendar\CalendarScope;
use App\Service\Calendar\CalendarScopeResolver;
use App\Service\Calendar\KalenderDetailMonthContextBuilder;
use App\Service\Calendar\KalenderDetailViewBuilder;
use App\Service\Calendar\KalenderFilterData;
use App\Service\Calendar\KalenderFilterQueryApplier;
use App\Service\Calendar\KalenderMonthViewBuilder;
use App\Service\Calendar\NewsFrontendFilterData;
use App\Service\Calendar\NewsFrontendQueryFactory;
use App\Service\Presentation\HtmlResponseService;
use App\Service\Presentation\TemplatePathResolver;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontendContentRenderer
{
    public function __construct(
        private readonly CalendarPublicationQueryHelper $calendarPublicationQueryHelper,
        private readonly KalenderFilterQueryApplier $kalenderFilterQueryApplier,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly KalenderDetailViewBuilder $kalenderDetailViewBuilder,
        private readonly KalenderDetailMonthContextBuilder $kalenderDetailMonthContextBuilder,
        private readonly KalenderMonthViewBuilder $kalenderMonthViewBuilder,
        private readonly NewsFrontendQueryFactory $newsFrontendQueryFactory,
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly HtmlResponseService $htmlResponseService,
        private readonly UsageTrackingService $usageTrackingService,
        private readonly EntityManagerInterface $em,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    public function renderCalendarList(DfxKonf $konf, Request $request): array
    {
        $cfgMonths = ['1' => 'januar', '2' => 'februar', '3' => 'märz', '4' => 'april', '5' => 'mai', '6' => 'juni', '7' => 'juli', '8' => 'august', '9' => 'september', '10' => 'oktober', '11' => 'november', '12' => 'dezember'];
        $calendarScope = $this->calendarScopeResolver->resolveReadScope($konf);
        $repository = $this->em->getRepository(DfxTermine::class);
        $query = $repository->createQueryBuilder('t')->select(['t']);
        $queryKal = $repository->createQueryBuilder('t')->select(['t.datumVon']);

        if ($calendarScope->restrictsResults()) {
            $query->where('t.datefix IN (:kids)')->setParameter('kids', $calendarScope->ids());
            $queryKal->where('t.datefix IN (:kids)')->setParameter('kids', $calendarScope->ids());
        }

        $this->calendarPublicationQueryHelper->applyPublishedVisibility($query, 't', $konf);
        $this->calendarPublicationQueryHelper->applyPublishedVisibility($queryKal, 't', $konf);

        $form = $this->createKalenderFilterForm($konf, $calendarScope);
        $form->handleRequest($request);
        $filterData = $this->createKalenderFilterDataFromForm($request, $form);

        $this->kalenderFilterQueryApplier->applySharedFilters($query, $filterData);
        $this->kalenderFilterQueryApplier->applySharedFilters($queryKal, $filterData);
        $this->kalenderFilterQueryApplier->applyListDateWindow($query, $filterData);
        if (!$filterData->hasExplicitDateSelection()) {
            $queryKal->andWhere('t.datumVon >= CURRENT_DATE()');
        }
        $monthSelection = $this->kalenderFilterQueryApplier->applyCalendarMonth($queryKal, $filterData);

        $queryKal->groupBy('t.datumVon')
            ->orderBy('t.datumVon', 'ASC');
        $tageKal = $queryKal->getQuery()->getArrayResult();

        $kalJahr = $monthSelection['year'] ?? (int) date('Y');
        $kalMonat = $monthSelection['month'] ?? (int) date('m');

        $monthView = $this->kalenderMonthViewBuilder->build(
            $tageKal,
            $kalJahr,
            $kalMonat,
            $konf->getFrontendUrl(),
            ['cb' => 'all', ...$request->query->all()]
        );

        $query->orderBy('t.datumVon, t.zeit')->getQuery();
        $termine = $this->paginator->paginate(
            $query,
            $request->query->getInt('dfxp', 1),
            $konf->getItemsListe()
        );

        $termine->setUsedRoute($konf->getFrontendUrl());
        $termine->setParam('filter', $this->buildKalenderPaginationFilter($this->buildKalenderFilterParams($filterData)));

        $this->usageTrackingService->track($konf);

        $tpl = $this->templatePathResolver->resolveKalenderList($konf);
        $tplform = $this->templatePathResolver->resolveFormTemplatePrefix('Kalender', (int) $konf->getId());
        $custom = $this->templatePathResolver->resolveCustomBasePrefix('Kalender', (int) $konf->getId(), 'termine');
        $response = $this->htmlResponseService->render($tpl, [
            'termine' => $termine,
            'konf' => $konf,
            'nav' => $konf->getNavListe(),
            'headline' => $this->buildKalenderHeadline($filterData, $cfgMonths),
            'kaldata' => $monthView['kaldata'],
            'calendar' => $monthView['calendar'],
            'custom' => $custom,
            'tplform' => $tplform,
            'filter_form' => $form->createView(),
        ]);

        return [
            'content' => (string) $response->getContent(),
            'termin' => null,
            'artikel' => null,
        ];
    }

    public function renderCalendarDetail(DfxKonf $konf, Request $request, int $id): array
    {
        $termin = $this->loadReadableTermin($konf, $id);
        $form = $this->createKalenderFilterForm($konf);
        $monthView = $this->kalenderDetailMonthContextBuilder->build($konf, (int) $konf->getId(), $termin, ['cb' => 'all', ...$request->query->all()]);
        $detailView = $this->kalenderDetailViewBuilder->build($konf, $termin, (int) $konf->getId(), 'all', $form->createView(), $monthView['kaldata'], $monthView['calendar']);

        $this->usageTrackingService->track($konf, $termin);
        $response = $this->htmlResponseService->render($detailView['template'], $detailView['options']);

        return [
            'content' => (string) $response->getContent(),
            'termin' => $termin,
            'artikel' => null,
        ];
    }

    public function renderNewsList(DfxKonf $konf, Request $request): array
    {
        $calendarScope = $this->calendarScopeResolver->resolveReadScope($konf);
        $form = $this->createNewsFilterForm($konf, $calendarScope->ids());
        $form->handleRequest($request);
        $data = $form->isSubmitted() && $form->isValid() ? (array) $form->getData() : [];
        $filterData = new NewsFrontendFilterData(
            rubrik: ($data['rubrik'] ?? null) ?: null,
            zielgruppe: ($data['zielgruppe'] ?? null) ?: null,
            filter1: (bool) ($data['filter1'] ?? false),
            filter2: (bool) ($data['filter2'] ?? false),
            filter3: (bool) ($data['filter3'] ?? false),
            filter4: (bool) ($data['filter4'] ?? false),
            filter5: (bool) ($data['filter5'] ?? false),
            suche: ($data['suche'] ?? null) ?: null,
            datumVon: $data['datum_von'] ?? null,
            datumBis: $data['datum_bis'] ?? null,
        );
        $queryConfig = $this->newsFrontendQueryFactory->build($konf, $filterData);

        $news = $this->paginator->paginate($queryConfig['query'], $request->query->getInt('nfxp', 1), $konf->getItemsListe());
        $news->setParam('filter', $this->buildNewsPaginationFilter($queryConfig['filter']));
        $this->usageTrackingService->track($konf);

        $tpl = $this->templatePathResolver->resolveNewsList($konf);
        $tplform = $this->templatePathResolver->resolveFormTemplatePrefix('News', (int) $konf->getId());
        $response = $this->htmlResponseService->render($tpl, [
            'news' => $news,
            'konf' => $konf,
            'nav' => $konf->getNavListe(),
            'headline' => $queryConfig['header'],
            'tplform' => $tplform,
            'filter_form' => $form->createView(),
        ]);

        return [
            'content' => (string) $response->getContent(),
            'termin' => null,
            'artikel' => null,
        ];
    }

    public function renderNewsDetail(DfxKonf $konf, Request $request, int $id): array
    {
        $artikel = $this->loadReadableArtikel($konf, $id);
        $form = $this->createNewsFilterForm($konf);

        $imgPath = $this->projectDir() . '/web/images/dfx/' . $artikel->getDatefix()->getId() . '/';
        if ($artikel->getImg() && !file_exists($imgPath . $artikel->getImg())) {
            $artikel->setImg(null);
        }

        $tpl = $this->templatePathResolver->resolveNewsDetail($konf, 'detail', 'detail');
        $tplform = $this->templatePathResolver->resolveFormTemplatePrefix('News', (int) $konf->getId());
        $custom = $this->templatePathResolver->resolveCustomBasePrefix('News', (int) $konf->getId(), 'base_detail');
        $response = $this->htmlResponseService->render($tpl, [
            'konf' => $konf,
            'artikel' => $artikel,
            'nav' => $konf->getNavDetail(),
            'cb' => 'all',
            'tplform' => $tplform,
            'custom' => $custom,
            'filter_form' => $form->createView(),
        ]);

        $this->usageTrackingService->track($konf, null, $artikel);

        return [
            'content' => (string) $response->getContent(),
            'termin' => null,
            'artikel' => $artikel,
        ];
    }

    private function createKalenderFilterForm(DfxKonf $konf, ?CalendarScope $calendarScope = null): FormInterface
    {
        $calendarScope ??= $this->calendarScopeResolver->resolveReadScope($konf);

        return $this->formFactory->createNamed('form', KalenderFilterType::class, null, [
            'konf' => $konf,
            'calendar_scope' => $calendarScope,
            'action' => $this->urlGenerator->generate('kalender_fe', ['kid' => $konf->getId()]),
            'method' => 'GET',
            'csrf_protection' => false,
            'attr' => ['name' => 'filter', 'id' => 'filter'],
        ]);
    }

    private function createNewsFilterForm(DfxKonf $konf, ?array $calendarIds = null): FormInterface
    {
        $calendarIds ??= $this->calendarScopeResolver->resolveReadScope($konf)->ids();

        return $this->formFactory->createNamed('form', NewsFrontendFilterType::class, ['kids' => $calendarIds], [
            'konf' => $konf,
            'zielgruppe_enabled' => true,
            'action' => $this->urlGenerator->generate('news', ['kid' => $konf->getId()]),
            'method' => 'GET',
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

    private function buildKalenderPaginationFilter(array $filter): string
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

    private function buildNewsPaginationFilter(array $filter): string
    {
        $parts = [];
        foreach ($filter as $key => $val) {
            if ($key === 'nfxp') {
                $parts[] = '&' . $key . '=' . $val;
                continue;
            }

            $parts[] = '&form%5B' . $key . '%5D=' . urlencode((string) $val);
        }

        return implode('', $parts);
    }

    private function loadReadableTermin(DfxKonf $konf, int $id): DfxTermine
    {
        $termin = $this->em->getRepository(DfxTermine::class)->find($id);
        if (!$termin instanceof DfxTermine) {
            throw new \RuntimeException('Kein Termin gefunden für ID ' . $id);
        }

        $kid = (int) $konf->getId();
        if ($termin->getDatefix()->getId() !== $kid && $konf->getIsMeta() != 1 && $konf->getIsGroup() != 1) {
            throw new \RuntimeException('Termin gehört nicht zu diesem Kalender.');
        }

        return $termin;
    }

    private function loadReadableArtikel(DfxKonf $konf, int $id): DfxNews
    {
        $artikel = $this->em->getRepository(DfxNews::class)->find($id);
        if (!$artikel instanceof DfxNews) {
            throw new \RuntimeException('Artikel nicht mehr vorhanden');
        }

        if ($artikel->getDatefix()->getId() != $konf->getId() && $konf->getIsMeta() != 1 && $konf->getIsGroup() != 1) {
            throw new \RuntimeException('Artikel gehört nicht zu diesem Kalender.');
        }

        return $artikel;
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

    private function projectDir(): string
    {
        return dirname(__DIR__, 3);
    }
}
