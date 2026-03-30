<?php

namespace App\Controller;

use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use App\Service\Calendar\CalendarPublicationQueryHelper;
use App\Service\Calendar\CalendarScopeResolver;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * DfxKalender controller.
 */
class DfxCustomController extends AbstractController
{
    public function __construct(
        private readonly CalendarPublicationQueryHelper $calendarPublicationQueryHelper,
        private readonly CalendarScopeResolver $calendarScopeResolver,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route(path: '/js/kalender/{kid}/export/json', name: 'json_export')]
    public function getJsonItems(int $kid): JsonResponse
    {
        $konf = $this->loadKonf($kid);

        $query = $this->buildPublishedTermineQuery($konf)
            ->andWhere('t.datum >= CURRENT_DATE()')
            ->orderBy('t.id');

        $entities = $query->getQuery()->getResult();
        $frontendUrlQ = str_contains('?', $konf->getFrontendUrl()) ? $konf->getFrontendUrl() . '&' : $konf->getFrontendUrl() . '?';

        $termine = [];
        foreach ($entities as $entity) {
            if ($entity->getDatumVon() === null) {
                continue;
            }

            $img = $entity->getImg() !== null && !str_contains((string) $entity->getImg(), ' ')
                ? $this->getParameter('datefix_url') . '/images/dfx/' . $entity->getImg()
                : '';

            $termine[] = [
                'dfx_id' => $entity->getId(),
                'rubriken' => implode(',', $entity->getRubrik() ?? []),
                'zielgruppen' => implode(',', $entity->getZielgruppe() ?? []),
                'titel' => $entity->getTitel(),
                'bescheibung' => $entity->getBeschreibung(),
                'datumvon' => $entity->getDatumVon()?->format('d.m.Y') ?? '',
                'zeitab' => $entity->getZeit()?->format('H:i') ?? '',
                'datumbis' => $entity->getDatum()?->format('d.m.Y') ?? '',
                'zeitbis' => $entity->getZeitBis()?->format('H:i') ?? '',
                'url' => $frontendUrlQ . 'dfxid=' . $entity->getId(),
                'lokal' => $entity->getLokal(),
                'latitude' => $entity->getBg() ?? 0,
                'longitude' => $entity->getLg() ?? 0,
                'strasse' => $entity->getLokalStrasse(),
                'plz' => $entity->getPlz() ?? 0,
                'ort' => $entity->getOrt(),
                'email' => $entity->getMail(),
                'eintritt' => $entity->getEintritt(),
                'bild' => $img,
            ];
        }

        return new JsonResponse($termine);
    }

    #[Route(path: '/js/kalender/nms/json/{kid}', name: 'json_nms')]
    public function getJsonNms(int $kid): JsonResponse
    {
        $konf = $this->loadKonf($kid);

        $query = $this->buildPublishedTermineQuery($konf)
            ->andWhere("t.datum BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), 90 , 'day')")
            ->orderBy('t.id');

        $entities = $query->getQuery()->getResult();
        $frontendUrlQ = str_contains('?', $konf->getFrontendUrl()) ? $konf->getFrontendUrl() . '&' : $konf->getFrontendUrl() . '?';

        $isSerie = false;
        $multidate = false;
        $code = null;
        $sCounter = 0;
        $i = 0;
        $termine = [];
        $serientermine = [];

        foreach ($entities as $entity) {
            if ($entity->getDatumVon() === null) {
                continue;
            }

            $datumVon = $entity->getDatumVon()?->format('Y-m-d') ?? '';
            $datum = $entity->getDatum()?->format('Y-m-d') ?? '';
            $zeit = $entity->getZeit()?->format('H:i') ?? '';
            $zeitBis = $entity->getZeitBis()?->format('H:i') ?? '';

            if ($isSerie && $code !== $entity->getCode()) {
                if ($sCounter > 0) {
                    $termine[$i - 1]['eventtime'] = $serientermine;
                }
                $isSerie = false;
                $multidate = false;
                $code = null;
            }

            if (!$isSerie) {
                if ($entity->getCode() !== null) {
                    $isSerie = true;
                    $multidate = true;
                    $code = $entity->getCode();
                    $sCounter = 0;
                    $serientermine = [];
                }

                $beschreibung = str_replace(["\r\n", "\r", "\n", "\t"], '', $entity->getBeschreibung());
                $beschreibung = str_replace(["<br />", "</li>", "</div>"], "\n", $beschreibung);
                $beschreibung = str_replace(["</p>", "</h1>", "</h2>", "</h3>", "</h4>"], "\n\n", $beschreibung);
                $beschreibung = strip_tags($beschreibung);
                $allday = $entity->getZeit() === null && $entity->getZeitBis() === null;
                $img = $entity->getImg() !== null && !str_contains((string) $entity->getImg(), ' ')
                    ? $this->getParameter('datefix_url') . '/images/dfx/' . $entity->getImg()
                    : '';
                $bg = $entity->getBg() ?? 0;
                $lg = $entity->getLg() ?? 0;
                $plz = $entity->getPlz() ?? 0;

                $eventtypeId = 24;
                $eventtypes = ["Ausstellungen" => 1, "Musik" => 7, "Schule" => 24, "Messen" => 10, "Führungen" => 5, "Veranstaltungen" => 24, "Märkte" => 9, "Politik" => 24, "Religion" => 24, "Bühne" => 16, "Film/Kino" => 4, "Sightseeing" => 24, "Seminar" => 25, "Sonstige" => 24, "Stadt- und Straßenfeste" => 3, "Vorträge" => 17, "Wirtschaft" => 24, "Workshop" => 25, "Sport" => 14, "Kinder" => 27];
                foreach (($entity->getRubrik() ?? []) as $rubrik) {
                    if (isset($eventtypes[$rubrik])) {
                        $eventtypeId = $eventtypes[$rubrik];
                    }
                }

                $termine[$i] = [
                    'external_id' => $entity->getId(),
                    'eventtype_id' => $eventtypeId,
                    'sports_id' => 0,
                    'title' => $entity->getTitel(),
                    'description' => $beschreibung,
                    'multidate' => $multidate,
                    'starts' => $datumVon,
                    'starts_time' => $zeit,
                    'ends' => $datum,
                    'ends_time' => $zeitBis,
                    'allday' => $allday,
                    'url' => $frontendUrlQ . 'dfxid=' . $entity->getId(),
                    'location' => $entity->getLokal(),
                    'latitude' => $bg,
                    'longitude' => $lg,
                    'street' => $entity->getLokalStrasse(),
                    'zip' => $plz,
                    'city' => $entity->getOrt(),
                    'email' => $entity->getMail(),
                    'phone' => '',
                    'additional_info' => $entity->getEintritt(),
                    'picture' => $img,
                ];
                $i++;
                continue;
            }

            $serientermine[] = [
                'external_id' => $entity->getId(),
                'details' => '',
                'startdate' => $datumVon,
                'starttime' => $zeit,
                'enddate' => $datum,
                'endtime' => $zeitBis,
                'canceled' => false,
                'soldout' => false,
            ];
            $sCounter++;
        }

        return new JsonResponse($termine);
    }

    #[Route(path: '/js/kalender/json/smartCity/{kid}', name: 'json_smartCity', defaults: ['kid' => '1'])]
    public function getJsonSmartCity(string $kid): JsonResponse
    {
        $konf = $this->loadKonf((int) $kid);

        $query = $this->buildPublishedTermineQuery($konf)
            ->andWhere("t.datum BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), 360 , 'day')")
            ->orderBy('t.id');

        $entities = $query->getQuery()->getResult();
        $frontendUrlQ = str_contains('?', $konf->getFrontendUrl()) ? $konf->getFrontendUrl() . '&' : $konf->getFrontendUrl() . '?';

        $isSerie = false;
        $code = null;
        $i = 0;
        $termine = [];
        $serientermine = [];
        $statusMap = ["rescheduled","finished","Restkarten","cancelled","postponed","closed","scheduled"];

        foreach ($entities as $entity) {
            $datumVon = $entity->getDatumVon()?->format('Y-m-d') ?? '';
            $datum = $entity->getDatum()?->format('Y-m-d') ?? '';
            $zeit = $entity->getZeit() ? 'T' . $entity->getZeit()->format('H:i:s') : '';
            $zeitBis = $entity->getZeitBis() ? 'T' . $entity->getZeitBis()->format('H:i:s') : '';
            $start = new DateTime($datumVon . $zeit);
            $ende = new DateTime($datum . $zeitBis);

            if ($isSerie && $code !== $entity->getCode()) {
                $termine[$indexSerieStart]['subevent'] = $serientermine;
                $isSerie = false;
                $code = null;
            }

            if ($entity->getCode() !== null && !$isSerie) {
                $isSerie = true;
                $indexSerieStart = $i;
                $code = $entity->getCode();
                $serientermine = ['dfx-' . $entity->getId()];
                $termine[$i]['superEvent'] = 'dfx-' . $entity->getId();
                $superevent = 'dfx-' . $entity->getId();
            } elseif ($entity->getCode() !== null && $isSerie) {
                $serientermine[] = 'dfx-' . $entity->getId();
                $termine[$i]['superEvent'] = $superevent;
            }

            $status = $entity->getOptionsMenue() === null ? 'scheduled' : $statusMap[$entity->getOptionsMenue()];
            $beschreibung = str_replace(["\r\n", "\r", "\n", "\t"], '', $entity->getBeschreibung());
            $beschreibung = str_replace(["<br />", "</li>", "</div>"], "\n", $beschreibung);
            $beschreibung = str_replace(["</p>", "</h1>", "</h2>", "</h3>", "</h4>"], "\n\n", $beschreibung);
            $beschreibung = strip_tags($beschreibung);

            $termine[$i]['id'] = 'dfx-' . $entity->getId();
            $termine[$i]['type'] = 'Event';
            $termine[$i]['name'] = $entity->getTitel();
            $termine[$i]['alternateName'] = $entity->getSubtitel();
            $termine[$i]['seeAlso'] = $entity->getLink();
            $termine[$i]['locationName'] = $entity->getLokal();
            $termine[$i]['areaServed'] = $entity->getLokal();
            $termine[$i]['address'] = ['addressLocality' => $entity->getOrt(), 'postalCode' => $entity->getPlz(), 'streetAddress' => $entity->getLokalStrasse(), 'adressRegion' => $entity->getRegion() !== null ? $entity->getRegion()->getRegion() : null];
            $termine[$i]['location'] = ['type' => 'Point', 'coordinates' => [$entity->getBg(), $entity->getLg()]];
            $termine[$i]['contactPoint'] = $entity->getIdLocation() !== null
                ? ['telephone' => $entity->getIdLocation()->getTelefon(), 'email' => $entity->getIdLocation()->getEmail()]
                : ['telephone' => null, 'email' => null];
            $termine[$i]['dateCreated'] = $entity->getDatumInput()->format('Y-m-d H:i:s');
            $termine[$i]['dateModified'] = $entity->getDatumModified()?->format('Y-m-d H:i:s');
            $termine[$i]['category'] = $entity->getRubrik() ?? [];
            $termine[$i]['eventStatus'] = $status;
            $termine[$i]['title'] = $entity->getTitel();
            $termine[$i]['slogan'] = $entity->getSubtitel();
            $termine[$i]['startDate'] = $start;
            $termine[$i]['endDate'] = $ende;
            $termine[$i]['webSite'] = $entity->getLink();
            $termine[$i]['source'] = $frontendUrlQ . 'dfxid=' . $entity->getId();
            $termine[$i]['contentUrl'] = $entity->getImg() !== null && !str_contains((string) $entity->getImg(), ' ')
                ? $this->getParameter('datefix_url') . '/images/dfx/' . $entity->getDatefix()->getId() . '/' . $entity->getImg()
                : null;
            $termine[$i]['description'] = $beschreibung !== '' ? $beschreibung : null;
            $i++;
        }

        return new JsonResponse($termine);
    }

    #[Route(path: '/js/kalender/json/wms/{kid}', name: 'json_wms', defaults: ['kid' => '1'])]
    public function getJsonWms(int $kid): JsonResponse
    {
        $konf = $this->loadKonf($kid);

        $query = $this->buildPublishedTermineQuery($konf)->orderBy('t.id');
        $entities = $query->getQuery()->getResult();
        $frontendUrlQ = str_contains('?', $konf->getFrontendUrl()) ? $konf->getFrontendUrl() . '&' : $konf->getFrontendUrl() . '?';

        $isSerie = false;
        $code = null;
        $i = 0;
        $termine = [];
        $serientermine = [];
        $statusMap = ["rescheduled","finished","Restkarten","cancelled","postponed","closed","scheduled"];
        $availabilityMap = ["scheduled" => "InStock", "rescheduled" => "InStock", "finished" => "SoldOut", "Restkarten" => "LimitedAvailability", "cancelled" => "Discontinued", "postponed" => "InStock", "closed" => "LimitedAvailability"];

        foreach ($entities as $entity) {
            $datumVon = $entity->getDatumVon()?->format('Y-m-d') ?? '';
            $datum = $entity->getDatum()?->format('Y-m-d') ?? '';
            $zeit = $entity->getZeit() ? 'T' . $entity->getZeit()->format('H:i:s') : '';
            $zeitBis = $entity->getZeitBis() ? 'T' . $entity->getZeitBis()->format('H:i:s') : '';
            $start = new DateTime($datumVon . $zeit);
            $ende = new DateTime($datum . $zeitBis);

            if ($isSerie && $code !== $entity->getCode()) {
                $termine[$indexSerieStart]['subevent'] = $serientermine;
                $isSerie = false;
                $code = null;
            }

            if ($entity->getCode() !== null && !$isSerie) {
                $isSerie = true;
                $indexSerieStart = $i;
                $code = $entity->getCode();
                $serientermine = ['dfx-' . $entity->getId()];
                $termine[$i]['superEvent'] = 'dfx-' . $entity->getId();
                $superevent = 'dfx-' . $entity->getId();
            } elseif ($entity->getCode() !== null && $isSerie) {
                $serientermine[] = 'dfx-' . $entity->getId();
                $termine[$i]['superEvent'] = $superevent;
            }

            $status = $entity->getOptionsMenue() === null ? 'scheduled' : $statusMap[$entity->getOptionsMenue()];
            $img = null;
            if ($entity->getImg() !== null && !str_contains((string) $entity->getImg(), ' ')) {
                $img = $this->getParameter('datefix_url') . '/images/dfx/' . $entity->getDatefix()->getId() . '/' . $entity->getImg();
            }

            $beschreibung = str_replace(["\r\n", "\r", "\n"], '<br />', $entity->getBeschreibung());
            $lead = $entity->getLead() !== null ? str_replace(["\r\n", "\r", "\n"], '<br />', $entity->getLead()) : null;

            if ($entity->getIdVeranstalter() !== null) {
                $ver = $entity->getIdVeranstalter();
                $orgAddress = ['addressLocality' => $ver->getOrt(), 'postalCode' => $ver->getPlz(), 'streetAddress' => $ver->getStrasse()];
                $orgContact = ['telephone' => $ver->getTelefon(), 'email' => $ver->getEmail()];
                $orgUrl = $ver->getWww();
            } else {
                $orgAddress = [];
                $orgContact = ['email' => $entity->getMail()];
                $orgUrl = null;
            }

            $termine[$i]['id'] = 'dfx-' . $entity->getId();
            $termine[$i]['type'] = 'Event';
            $termine[$i]['name'] = $entity->getTitel();
            $termine[$i]['alternateName'] = $entity->getSubtitel();
            $termine[$i]['seeAlso'] = $entity->getLink();
            $termine[$i]['locationName'] = $entity->getLokal();
            $termine[$i]['areaServed'] = $entity->getLokal();
            $termine[$i]['address'] = ['addressLocality' => $entity->getOrt(), 'postalCode' => $entity->getPlz(), 'streetAddress' => $entity->getLokalStrasse(), 'adressRegion' => $entity->getRegion() !== null ? $entity->getRegion()->getRegion() : null];
            $termine[$i]['location'] = ['type' => 'Point', 'coordinates' => [$entity->getBg(), $entity->getLg()]];
            $termine[$i]['contactPoint'] = $entity->getIdLocation() !== null
                ? ['telephone' => $entity->getIdLocation()->getTelefon(), 'email' => $entity->getIdLocation()->getEmail()]
                : ['telephone' => null, 'email' => null];
            $termine[$i]['dateCreated'] = $entity->getDatumInput()->format('Y-m-d H:i:s');
            $termine[$i]['dateModified'] = $entity->getDatumModified()?->format('Y-m-d H:i:s');
            $termine[$i]['category'] = $entity->getRubrik() ?? [];
            $termine[$i]['eventStatus'] = $status;
            $termine[$i]['title'] = $entity->getTitel();
            $termine[$i]['slogan'] = $entity->getSubtitel();
            $termine[$i]['startDate'] = $start;
            $termine[$i]['endDate'] = $ende;
            $termine[$i]['webSite'] = $entity->getLink();
            $termine[$i]['source'] = $frontendUrlQ . 'dfxid=' . $entity->getId();
            $termine[$i]['contentUrl'] = $img;
            $termine[$i]['description'] = $beschreibung !== '' ? $beschreibung : null;
            $termine[$i]['lead'] = $lead;
            $termine[$i]['imgtext'] = $entity->getImgtext();
            $termine[$i]['imgcopyright'] = $entity->getImgcopyright();
            $termine[$i]['organization'] = [
                '@type' => 'organization',
                'name' => $entity->getVeranstalter(),
                'contactPoint' => $orgContact,
                'address' => $orgAddress,
                'url' => $orgUrl,
            ];

            if ($entity->getPlaetzeGesamt() > 0) {
                if ($entity->getMailTyp() === 'karten') {
                    $dfxpath = 'karten';
                    $ticketlinktext = 'Karten (' . $entity->getPlaetzeAktuell() . ' Karten verfügbar)';
                } else {
                    $dfxpath = 'anmeldungen';
                    $ticketlinktext = 'Anmeldung (' . $entity->getPlaetzeAktuell() . ' Plätze verfügbar)';
                }
                $termine[$i]['offers'] = [
                    '@type' => 'Offer',
                    'url' => $frontendUrlQ . 'dfxpath=/' . $dfxpath . '/new/' . $entity->getId(),
                    'description' => $ticketlinktext,
                    'price' => $entity->getEintritt(),
                    'availability' => $entity->getPlaetzeAktuell() < 1 ? 'SoldOut' : $availabilityMap[$status],
                ];
                $termine[$i]['ticketlink'] = $frontendUrlQ . 'dfxpath=/' . $dfxpath . '/new/' . $entity->getId();
                $termine[$i]['ticketlinktext'] = $ticketlinktext;
            } elseif ($entity->getTicketlink() !== null) {
                $termine[$i]['offers'] = [
                    '@type' => 'Offer',
                    'url' => $entity->getTicketlink(),
                    'description' => $entity->getTicketlinktext(),
                    'price' => $entity->getEintritt(),
                    'availability' => $availabilityMap[$status],
                ];
                $termine[$i]['ticketlink'] = $entity->getTicketlink();
                $termine[$i]['ticketlinktext'] = $entity->getTicketlinktext();
            } else {
                $termine[$i]['offers'] = null;
                $termine[$i]['ticketlink'] = null;
                $termine[$i]['ticketlinktext'] = null;
            }

            $termine[$i]['eintritt'] = $entity->getEintritt();
            $i++;
        }

        return new JsonResponse($termine);
    }

    private function loadKonf(int $kid): DfxKonf
    {
        $konf = $this->em->getRepository(DfxKonf::class)->find($kid);
        if ($konf === null) {
            throw $this->createNotFoundException('Kein Account gefunden für KalenderID ' . $kid);
        }

        return $konf;
    }

    private function buildPublishedTermineQuery(DfxKonf $konf)
    {
        $calendarIds = $this->calendarScopeResolver->resolveReadScope($konf)->ids();
        $query = $this->em->getRepository(DfxTermine::class)
            ->createQueryBuilder('t')
            ->select(['t']);

        if (!$konf->getIsMeta()) {
            $query->where('t.datefix IN (:kids)')
                ->setParameter('kids', $calendarIds);
        }

        $this->calendarPublicationQueryHelper->applyPublishedVisibility($query, 't', $konf);

        return $query;
    }

}
