<?php

namespace App\Controller;

use App\Entity\DfxKonf;
use App\Entity\DfxNfxCounter;
use App\Entity\DfxNews;
use App\Entity\DfxTermine;
use App\Service\Calendar\CalendarPublicationQueryHelper;
use App\Service\Calendar\CalendarScopeResolver;
use App\Service\Calendar\KalenderFilterData;
use App\Service\Calendar\KalenderFilterQueryApplier;
use App\Service\Api\ApiPayloadRendererResolver;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DfxApiController extends AbstractController
{
    public function __construct(
        private readonly CalendarPublicationQueryHelper $calendarPublicationQueryHelper,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly KalenderFilterQueryApplier $kalenderFilterQueryApplier,
        private readonly ApiPayloadRendererResolver $apiPayloadRendererResolver,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Lists all DfxTermine entities.
     *
     * API usage:
     * `GET /api/kalender/{kid}`
     *
     * Pagination:
     * - `page`
     * - `items`
     *
     * The endpoint accepts the same filter names as the calendar frontend.
     * Filters can be sent either directly as query parameters or grouped as `form[...]`.
     *
     * Example:
     * `/api/kalender/1?rubrik=Konzert&zielgruppe=Kinder&datum_von=2026-04-01&datum_bis=2026-04-30`
     * `/api/kalender/1?form[rubrik]=Konzert&form[m]=2026-04`
     *
     * Supported filters:
     * - `rubrik`
     * - `zielgruppe`
     * - `filter1`, `filter2`, `filter3`, `filter4`, `filter5`
     * - `veranstalter`
     * - `idVeranstalter`
     * - `lokal`
     * - `idLocation`
     * - `umkreis`
     * - `plz`
     * - `ort`
     * - `bg`
     * - `lg`
     * - `nat`
     * - `region`
     * - `suche`
     * - `m`
     * - `t`
     * - `datum_von`
     * - `datum_bis`
     */
    #[Route(path: '/api/kalender/{kid}', name: 'api', defaults: ['kid' => '1'], methods: ['GET'])]
    public function index(Request $request, #[MapEntity(id: 'kid')] DfxKonf $konf): JsonResponse
    {
        if (!$konf->getAllowApi()) {
            return $this->apiForbiddenResponse();
        }

        $this->incrementApiCounter($konf);

        $calendarScope = $this->calendarScopeResolver->resolveReadScope($konf);
        $arParams = [];
        $query = $this->em->getRepository(DfxTermine::class)->createQueryBuilder('t')
            ->select(['t']);

        if ($calendarScope->restrictsResults()) {
            $query->where('t.datefix IN (:kids)');
            $arParams['kids'] = $calendarScope->ids();
        }

        $this->calendarPublicationQueryHelper->applyPublishedVisibility($query, 't', $konf);
        $filter = $this->buildKalenderFilterData($request);
        $this->kalenderFilterQueryApplier->applySharedFilters($query, $filter, 't');
        $this->kalenderFilterQueryApplier->applyListDateWindow($query, $filter, 't');

        foreach ($arParams as $name => $value) {
            $query->setParameter($name, $value);
        }
        $query->orderBy('t.datumVon, t.zeit');

        $this->applyApiPagination($query, $request, $konf);

        /** @var list<DfxTermine> $entities */
        $entities = $query->getQuery()->getResult();

        return new JsonResponse($this->apiPayloadRendererResolver->forKonf($konf)->renderTerminList($entities, $konf));
    }
    #[Route(path: '/api/news/{kid}', name: 'api_news', defaults: ['kid' => '1'], methods: ['GET'])]
    public function news(Request $request, #[MapEntity(id: 'kid')] DfxKonf $konf): JsonResponse
    {
        if (!$konf->getAllowApi()) {
            return $this->apiForbiddenResponse();
        }

        $this->incrementApiCounter($konf);

        $calendarScope = $this->calendarScopeResolver->resolveReadScope($konf);
        $arParams = [];
        $query = $this->em->getRepository(DfxNews::class)->createQueryBuilder('n')
            ->select(['n']);

        $query->where('n.newsTyp = :newstyp');
        $arParams['newstyp'] = 'beitrag';

        if ($calendarScope->restrictsResults()) {
            $query->andWhere('n.datefix IN (:kids)');
            $arParams['kids'] = $calendarScope->ids();
        }

        $this->calendarPublicationQueryHelper->applyPublishedVisibility($query, 'n', $konf);

        foreach ($arParams as $name => $value) {
            $query->setParameter($name, $value);
        }

        $query->orderBy('n.datumVon', 'DESC');
        $this->applyApiPagination($query, $request, $konf);

        /** @var list<DfxNews> $entities */
        $entities = $query->getQuery()->getResult();

        return new JsonResponse($this->apiPayloadRendererResolver->forKonf($konf)->renderNewsList($entities, $konf));
    }

    #[Route(path: '/api/news/detail/{nfxid}', name: 'api_news_detail', methods: ['GET'])]
    public function newsDetail(#[MapEntity(id: 'nfxid')] DfxNews $entity): JsonResponse
    {
        $konf = $entity->getDatefix();
        if (!$konf->getAllowApi()) {
            return $this->apiForbiddenResponse();
        }

        if (
            $entity->getPub() !== true
            || ($konf->getIsMeta() && $entity->getPubMeta() !== true)
            || ($konf->getIsGroup() && $entity->getPubGroup() !== true)
        ) {
            return $this->apiEntityForbiddenResponse('Artikel ist nicht freigegeben.');
        }

        $this->incrementApiCounter($konf);

        return new JsonResponse($this->apiPayloadRendererResolver->forKonf($konf)->renderNewsDetail($entity));
    }

    #[Route(path: '/api/detail/{tid}', name: 'api_detail', methods: ['GET'])]
    public function detail(#[MapEntity(id: 'tid')] DfxTermine $entity): JsonResponse
    {
        $konf = $entity->getDatefix();
        $this->incrementApiCounter($konf);

        return new JsonResponse($this->apiPayloadRendererResolver->forKonf($konf)->renderTerminDetail($entity));
    }

    private function buildKalenderFilterData(Request $request): KalenderFilterData
    {
        $formValues = $request->query->all()['form'] ?? [];
        $value = function (string $name) use ($request, $formValues): mixed {
            $direct = $request->query->get($name);
            if ($direct !== null) {
                return $direct;
            }

            return $formValues[$name] ?? null;
        };

        return new KalenderFilterData(
            $this->normalizeString($value('rubrik')),
            $this->normalizeString($value('zielgruppe')),
            [
                1 => $this->toBool($value('filter1')),
                2 => $this->toBool($value('filter2')),
                3 => $this->toBool($value('filter3')),
                4 => $this->toBool($value('filter4')),
                5 => $this->toBool($value('filter5')),
            ],
            $this->normalizeString($value('veranstalter')),
            $this->normalizeScalar($value('idVeranstalter')),
            $this->normalizeString($value('lokal')),
            $this->normalizeScalar($value('idLocation')),
            $this->normalizeString($value('umkreis')),
            $this->normalizeString($value('plz')),
            $this->normalizeString($value('ort')),
            $this->toFloat($value('bg')),
            $this->toFloat($value('lg')),
            $this->normalizeString($value('nat')),
            $this->normalizeScalar($value('region')),
            $this->normalizeString($value('suche')),
            $this->normalizeString($value('m')),
            $this->normalizeString($value('t')),
            $this->toDate($value('datum_von')),
            $this->toDate($value('datum_bis')),
        );
    }

    private function normalizeString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeScalar(mixed $value): int|string|null
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        return ctype_digit($normalized) ? (int) $normalized : $normalized;
    }

    private function toFloat(mixed $value): ?float
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (!is_scalar($value)) {
            return false;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }

    private function toDate(mixed $value): ?DateTimeImmutable
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $normalized);

        return $date ?: null;
    }

    private function incrementApiCounter(DfxKonf $konf): void
    {
        $counter = $this->em->getRepository(DfxNfxCounter::class)->findOneBy(['datefix' => $konf]);
        if ($counter === null) {
            return;
        }

        $counter->setDfxApiDay(($counter->getDfxApiDay() ?? 0) + 1);
        $counter->setDfxApiSum(($counter->getDfxApiSum() ?? 0) + 1);
        $this->em->persist($counter);
        $this->em->flush();
    }

    private function applyApiPagination(QueryBuilder $query, Request $request, DfxKonf $konf): void
    {
        $maxItems = $konf->getMaxApiItems() ?? 1000;
        $pageParam = $request->query->get('page');
        $usePagination = $pageParam !== null && (int) $pageParam >= 1;

        if ($usePagination) {
            $page = (int) $pageParam;
            $items = (int) $request->query->get('items', 0);
            $pageItems = $items > 0 ? $items : ($konf->getPageApiItems() ?? 20);
            if ($pageItems > $maxItems) {
                $pageItems = $maxItems;
            }

            $query->setMaxResults($pageItems)
                ->setFirstResult(($page - 1) * $pageItems);

            return;
        }

        $query->setMaxResults($maxItems);
    }

    private function apiForbiddenResponse(): JsonResponse
    {
        return new JsonResponse(
            ['error' => 'Die Api ist vom Administrator dieses Veranstaltungskalenders gesperrt.'],
            Response::HTTP_FORBIDDEN
        );
    }

    private function apiEntityForbiddenResponse(string $message): JsonResponse
    {
        return new JsonResponse(['error' => $message], Response::HTTP_FORBIDDEN);
    }
}
