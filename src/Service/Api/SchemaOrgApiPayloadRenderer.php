<?php

namespace App\Service\Api;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use App\Entity\DfxTermine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SchemaOrgApiPayloadRenderer implements ApiPayloadRendererInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function renderTerminList(array $entities, DfxKonf $konf): array
    {
        $termine = [];
        $serientermine = [];

        foreach ($entities as $index => $entity) {
            $termine[$index] = $this->mapTermin($entity);

            if ($entity->getCode() === null) {
                continue;
            }

            if (isset($serientermine[$entity->getCode()])) {
                $serientermine[$entity->getCode()]['subevents'][] = 'dfx-' . $entity->getId();
                $termine[$index]['superEvent'] = $serientermine[$entity->getCode()]['superevent'];
                continue;
            }

            $serientermine[$entity->getCode()] = [
                'subevents' => [],
                'superevent' => 'dfx-' . $entity->getId(),
                'index' => $index,
            ];
        }

        foreach ($serientermine as $serie) {
            $termine[$serie['index']]['subevent'] = $serie['subevents'];
        }

        return array_values($termine);
    }

    public function renderTerminDetail(DfxTermine $entity): array
    {
        $termin = $this->mapTermin($entity);

        if ($entity->getCode() === null) {
            $termin['subevent'] = null;

            return $termin;
        }

        $subevents = $this->em->getRepository(DfxTermine::class)
            ->createQueryBuilder('t')
            ->select('partial t.{id}')
            ->where('t.code = :code')
            ->andWhere('t.datumVon >= CURRENT_DATE()')
            ->setParameter('code', $entity->getCode())
            ->orderBy('t.datumVon', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        $termin['subevent'] = $subevents;

        return $termin;
    }

    public function renderNewsList(array $entities, DfxKonf $konf): array
    {
        return array_values(array_map(fn (DfxNews $entity): array => $this->mapNews($entity), $entities));
    }

    public function renderNewsDetail(DfxNews $entity): array
    {
        return $this->mapNews($entity);
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapTermin(DfxTermine $entity): array
    {
        $datumVon = $entity->getDatumVon()?->format('Y-m-d') ?? '';
        $datum = $entity->getDatum()?->format('Y-m-d') ?? '';
        $zeit = $entity->getZeit()?->format('H:i:s');
        $zeitBis = $entity->getZeitBis()?->format('H:i:s');
        $status = $this->resolveStatus($entity);
        $pdf = $this->pdfUrl($entity->getDatefix(), $entity->getPdf());
        $veranstalter = $entity->getIdVeranstalter();
        $organizerAddress = $veranstalter !== null
            ? [
                '@type' => 'PostalAddress',
                'addressLocality' => $veranstalter->getOrt(),
                'postalCode' => $veranstalter->getPlz(),
                'streetAddress' => $veranstalter->getStrasse(),
            ]
            : [];
        $organizerContact = $veranstalter !== null
            ? ['@type' => 'ContactPoint', 'telephone' => $veranstalter->getTelefon(), 'email' => $veranstalter->getEmail()]
            : ['@type' => 'ContactPoint', 'email' => $entity->getMail()];

        return [
            '@context' => 'https://schema.org',
            '@type' => $this->resolveEventType($entity),
            'identifier' => 'dfx-' . $entity->getId(),
            'name' => $entity->getTitel(),
            'alternateName' => $entity->getSubtitel(),
            'keywords' => $entity->getRubrik() ?? [],
            'url' => $this->frontendUrlQ($entity->getDatefix()) . 'dfxid=' . $entity->getId(),
            'sameAs' => $entity->getLink(),
            'location' => $this->buildLocation($entity),
            'about' => [
                '@type' => 'CreativeWork',
                'dateCreated' => $entity->getDatumInput()?->format('Y-m-d H:i:s'),
                'dateModified' => $entity->getDatumModified()?->format('Y-m-d H:i:s'),
            ],
            'eventStatus' => $status,
            'startDate' => $datumVon . ($zeit !== null ? 'T' . $zeit : ''),
            'endDate' => $datum . ($zeitBis !== null ? 'T' . $zeitBis : ''),
            'eventAttendanceMode' => $entity->getOnline() ? 'online' : 'offline',
            'audience' => $entity->getZielgruppe() ?? [],
            'image' => $this->buildTerminImages($entity),
            'workFeatured' => [
                [
                    '@type' => 'VideoObject',
                    'embedUrl' => $entity->getVideo(),
                ],
                [
                    '@type' => 'DigitalDocument',
                    'url' => $pdf,
                    'description' => $entity->getPdflinktext(),
                ],
            ],
            'description' => $entity->getBeschreibung(),
            'disambiguatingDescription' => $entity->getLead(),
            'organizer' => [
                '@type' => 'Organization',
                'identifier' => $veranstalter !== null ? 'dfx-ver-' . $veranstalter->getId() : null,
                'name' => $entity->getVeranstalter(),
                'contactPoint' => $organizerContact,
                'address' => $organizerAddress,
                'url' => $veranstalter?->getWww(),
            ],
            'offers' => $this->buildOffers($entity, $status),
            'maximumAttendeeCapacity' => $entity->getPlaetzeGesamt() > 0 ? $entity->getPlaetzeGesamt() : null,
            'remainingAttendeeCapacity' => $entity->getPlaetzeGesamt() > 0 && $entity->getPlaetzeAktuell() >= 1 ? $entity->getPlaetzeAktuell() : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapNews(DfxNews $entity): array
    {
        $artikel = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'identifier' => 'dfx-news-' . $entity->getId(),
            'headline' => $entity->getTitel(),
            'alternativeHeadline' => $entity->getSubtitel(),
            'keywords' => $entity->getRubrik() ?? [],
            'url' => $this->frontendUrlQ($entity->getDatefix()) . 'nfxid=' . $entity->getId(),
            'sameAs' => $entity->getLink(),
            'description' => $entity->getKurztext() ?? $entity->getBeschreibung(),
            'articleBody' => $entity->getBeschreibung(),
        ];

        $datePublished = $entity->getDatumInput() ?? $entity->getDatumVon() ?? $entity->getInput();
        if ($datePublished !== null) {
            $artikel['datePublished'] = $datePublished->format('Y-m-d');
        }

        $dateModified = $entity->getDatumModified() ?? $entity->getModified();
        if ($dateModified !== null) {
            $artikel['dateModified'] = $dateModified->format('Y-m-d');
        }

        $authorName = $entity->getAutor();
        if ($authorName === null && $entity->getUser() !== null) {
            $authorName = $entity->getUser()->getUsername();
        }
        if ($authorName !== null) {
            $artikel['author'] = [
                '@type' => 'Person',
                'name' => $authorName,
            ];
        }

        $images = $this->buildNewsImages($entity);
        if ($images !== []) {
            $artikel['image'] = $images;
        }

        return $artikel;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLocation(DfxTermine $entity): array
    {
        $location = [
            '@type' => 'Place',
            'name' => $entity->getLokal(),
            'longitude' => $entity->getLg(),
            'latitude' => $entity->getBg(),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $entity->getOrt(),
                'postalCode' => $entity->getPlz(),
                'streetAddress' => $entity->getLokalStrasse(),
                'adressRegion' => $entity->getRegion()?->getRegion(),
            ],
        ];

        if ($entity->getIdLocation() === null) {
            $location['identifier'] = null;
            $location['telephone'] = null;
            $location['email'] = null;
            $location['image'] = null;
            $location['description'] = null;
            $location['url'] = null;
            $location['sameAs'] = null;

            return $location;
        }

        $loc = $entity->getIdLocation();
        $location['identifier'] = 'dfx-loc-' . $loc->getId();
        $location['telephone'] = $loc->getTelefon();
        $location['email'] = $loc->getEmail();
        $location['image'] = $loc->getImgLoc() !== null
            ? [
                '@type' => 'ImageObject',
                'contentUrl' => $this->datefixUrl() . '/images/dfx/' . $entity->getDatefix()->getId() . '/' . $loc->getImgLoc(),
                'description' => $loc->getImgtext(),
                'copyrightHolder' => $loc->getImgcopyright(),
            ]
            : null;
        $location['description'] = $loc->getZusatz();
        $location['url'] = $this->frontendUrlQ($entity->getDatefix()) . 'dfxpath=/location/' . $loc->getId();
        $location['sameAs'] = $loc->getWww();

        return $location;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildTerminImages(DfxTermine $entity): array
    {
        return [
            [
                '@type' => 'ImageObject',
                'contentUrl' => $this->imageUrl($entity->getDatefix(), $entity->getImg()),
                'description' => $entity->getImgtext(),
                'copyrightHolder' => $entity->getImgcopyright(),
            ],
            [
                '@type' => 'ImageObject',
                'contentUrl' => $this->imageUrl($entity->getDatefix(), $entity->getImg2()),
                'description' => null,
                'copyrightHolder' => null,
            ],
            [
                '@type' => 'ImageObject',
                'contentUrl' => $this->imageUrl($entity->getDatefix(), $entity->getImg3()),
                'description' => null,
                'copyrightHolder' => null,
            ],
            [
                '@type' => 'ImageObject',
                'contentUrl' => $this->imageUrl($entity->getDatefix(), $entity->getImg4()),
                'description' => null,
                'copyrightHolder' => null,
            ],
            [
                '@type' => 'ImageObject',
                'contentUrl' => $this->imageUrl($entity->getDatefix(), $entity->getImg5()),
                'description' => null,
                'copyrightHolder' => null,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildNewsImages(DfxNews $entity): array
    {
        return array_values(array_filter([
            $entity->getImg() !== null ? [
                '@type' => 'ImageObject',
                'contentUrl' => $this->imageUrl($entity->getDatefix(), $entity->getImg()),
                'description' => $entity->getImgtext(),
                'copyrightHolder' => $entity->getImgcopyright(),
            ] : null,
            $entity->getImg2() !== null ? [
                '@type' => 'ImageObject',
                'contentUrl' => $this->imageUrl($entity->getDatefix(), $entity->getImg2()),
                'description' => $entity->getImgtext2(),
                'copyrightHolder' => $entity->getImgcopyright2(),
            ] : null,
            $entity->getImg3() !== null ? [
                '@type' => 'ImageObject',
                'contentUrl' => $this->imageUrl($entity->getDatefix(), $entity->getImg3()),
                'description' => null,
                'copyrightHolder' => null,
            ] : null,
            $entity->getImg4() !== null ? [
                '@type' => 'ImageObject',
                'contentUrl' => $this->imageUrl($entity->getDatefix(), $entity->getImg4()),
                'description' => null,
                'copyrightHolder' => null,
            ] : null,
            $entity->getImg5() !== null ? [
                '@type' => 'ImageObject',
                'contentUrl' => $this->imageUrl($entity->getDatefix(), $entity->getImg5()),
                'description' => null,
                'copyrightHolder' => null,
            ] : null,
        ]));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildOffers(DfxTermine $entity, string $status): ?array
    {
        $availability = [
            'scheduled' => 'InStock',
            'rescheduled' => 'InStock',
            'finished' => 'SoldOut',
            'Restkarten' => 'LimitedAvailability',
            'cancelled' => 'Discontinued',
            'postponed' => 'InStock',
            'closed' => 'LimitedAvailability',
        ];

        if ($entity->getPlaetzeGesamt() > 0) {
            $path = null;
            $description = null;

            if ($entity->getMailTyp() === 'karten') {
                $path = 'karten';
                $description = 'Karten (' . $entity->getPlaetzeAktuell() . ' Karten verfügbar)';
            } elseif ($entity->getMailTyp() === 'anmeldung') {
                $path = 'anmeldungen';
                $description = 'Anmeldung (' . $entity->getPlaetzeAktuell() . ' Plätze verfügbar)';
            }

            $offer = [
                '@type' => 'Offer',
                'url' => $this->frontendUrlQ($entity->getDatefix()) . 'dfxpath=/' . $path . '/new/' . $entity->getId(),
                'description' => $description,
                'price' => $entity->getEintritt(),
            ];

            $offer['availability'] = $entity->getPlaetzeAktuell() < 1 ? 'SoldOut' : $availability[$status];

            return $offer;
        }

        if ($entity->getTicketlink() !== null) {
            return [
                '@type' => 'Offer',
                'url' => $entity->getTicketlink(),
                'description' => $entity->getTicketlinktext(),
                'price' => $entity->getEintritt(),
                'availability' => $availability[$status],
            ];
        }

        return null;
    }

    private function resolveStatus(DfxTermine $entity): string
    {
        $statusMap = ["rescheduled","finished","Restkarten","cancelled","postponed","closed","scheduled"];

        if ($entity->getOptionsMenue() === null) {
            return 'scheduled';
        }

        return $statusMap[$entity->getOptionsMenue()] ?? 'scheduled';
    }

    private function resolveEventType(DfxTermine $entity): string
    {
        $mapping = [
            'theater' => 'TheaterEvent',
            'dance' => 'DanceEvent',
            'sports' => 'SportsEvent',
            'music' => 'MusicEvent',
            'comedy' => 'ComedyEvent',
            'exhibition' => 'ExhibitionEvent',
            'screening' => 'ScreeningEvent',
            'literary' => 'LiteraryEvent',
            'education' => 'EducationEvent',
            'social' => 'SocialEvent',
            'business' => 'BusinessEvent',
            'festival' => 'Festival',
            'childrens' => 'ChildrensEvent',
        ];

        $rubriken = $entity->getRubrik() ?? [];
        foreach ($mapping as $parameter => $eventType) {
            $configured = $this->parameterBag->get($parameter);
            if (is_array($configured) && array_intersect($configured, $rubriken) !== []) {
                return $eventType;
            }
        }

        return 'Event';
    }

    private function frontendUrlQ(DfxKonf $konf): string
    {
        $url = (string) $konf->getFrontendUrl();

        return str_contains($url, '?') ? $url . '&' : $url . '?';
    }

    private function imageUrl(DfxKonf $konf, ?string $file): ?string
    {
        if ($file === null || str_contains($file, ' ')) {
            return null;
        }

        return $this->datefixUrl() . '/images/dfx/' . $konf->getId() . '/' . $file;
    }

    private function pdfUrl(DfxKonf $konf, ?string $file): ?string
    {
        if ($file === null || str_contains($file, ' ')) {
            return null;
        }

        return $this->datefixUrl() . '/pdf/dfx/' . $konf->getId() . '/' . $file;
    }

    private function datefixUrl(): string
    {
        return (string) $this->parameterBag->get('datefix_url');
    }
}
