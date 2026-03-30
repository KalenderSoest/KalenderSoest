<?php

namespace App\Controller;

use App\Entity\DfxLocation;
use App\Entity\DfxOrte;
use App\Entity\DfxReminder;
use App\Entity\DfxTermine;
use App\Entity\DfxVeranstalter;
use App\Form\DfxReminderType;
use App\Security\StatelessCsrfTokenManager;
use App\Service\Frontend\CodeChallengeService;
use App\Service\Messaging\MailDeliveryService;
use App\Service\Presentation\HtmlResponseService;
use App\Service\Presentation\PdfResponseService;
use App\Service\Presentation\TemplatePathResolver;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Entity\TimeZone as IcalTimeZone;
use Eluceo\iCal\Domain\ValueObject\DateTime as IcalDateTime;
use Eluceo\iCal\Domain\ValueObject\Location as IcalLocation;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\Routing\Attribute\Route;

class DfxKalenderTermineUtilityController extends AbstractController
{
    public function __construct(
        private readonly CodeChallengeService $codeChallengeService,
        private readonly HtmlResponseService $htmlResponseService,
        private readonly MailDeliveryService $mailDeliveryService,
        private readonly PdfResponseService $pdfResponseService,
        private readonly TemplatePathResolver $templatePathResolver,
        private readonly EntityManagerInterface $em,
        private readonly StatelessCsrfTokenManager $statelessCsrfTokenManager,
    ) {
    }

    #[Route(path: '/js/kalender/pdf/{id}', name: 'termin_fe_pdf', methods: ['GET'])]
    public function pdfAction(int $id): Response
    {
        $termin = $this->loadTermin($id);
        $konf = $termin->getDatefix();
        $tpl = $this->templatePathResolver->resolve('Kalender', 'detail_pdf.html.twig', $konf);
        $html = $this->render($tpl, ['termin' => $termin, 'konf' => $konf])->getContent();

        return $this->pdfResponseService->render($html);
    }

    #[Route(path: '/js/kalender/print/{id}', name: 'termin_print', methods: ['GET'])]
    public function print(int $id): Response
    {
        $termin = $this->loadTermin($id);
        $konf = $termin->getDatefix();
        $tpl = $this->templatePathResolver->resolve('Kalender', 'detail_print.html.twig', $konf);

        return $this->htmlResponseService->render($tpl, ['termin' => $termin, 'konf' => $konf]);
    }

    #[Route(path: '/js/kalender/location/{id}', name: 'location_show', methods: ['GET'])]
    public function showLocation(int $id): Response
    {
        $entity = $this->em->getRepository(DfxLocation::class)->find($id);
        $konf = $entity->getDatefix();
        $tpl = $this->templatePathResolver->resolve('Kalender', 'location.html.twig', $konf);

        return $this->htmlResponseService->render($tpl, ['entity' => $entity, 'konf' => $konf]);
    }

    #[Route(path: '/js/kalender/veranstalter/{id}', name: 'veranstalter_show')]
    public function showVeranstalter(int $id): Response
    {
        $entity = $this->em->getRepository(DfxVeranstalter::class)->find($id);
        $konf = $entity->getDatefix();
        $tpl = $this->templatePathResolver->resolve('Kalender', 'veranstalter.html.twig', $konf);

        return $this->htmlResponseService->render($tpl, ['entity' => $entity, 'konf' => $konf]);
    }

    #[Route(path: '/js/kalender/{kid}/mail/{id}', name: 'termine_mail', methods: ['GET', 'POST'])]
    public function mail(int $kid, int $id, Request $request): Response
    {
        $termin = $this->loadTermin($id);
        $konf = $termin->getDatefix();
        $captcha = $this->codeChallengeService->create();
        $mailformData = ['key' => $captcha['key']];

        $form = $this->createFormBuilder($mailformData, [
            'method' => 'GET',
            'attr' => ['name' => 'mailform', 'id' => 'mailform'],
            'csrf_token_manager' => $this->statelessCsrfTokenManager,
        ])
            ->setAction($this->generateUrl('termine_mail', ['kid' => $kid, 'id' => $id]))
            ->add('sendEmail', EmailType::class, ['label' => 'Absender E-Mail', 'required' => true, 'attr' => ['placeholder' => 'Ihre E-Mail-Adresse']])
            ->add('sendVorname', TextType::class, ['label' => 'Absender Vorname', 'required' => true, 'attr' => ['placeholder' => 'Ihr Vorname']])
            ->add('sendNachname', TextType::class, ['label' => 'Absender Nachname ', 'required' => true, 'attr' => ['placeholder' => 'Ihr Nachname']])
            ->add('empfEmail', EmailType::class, ['label' => 'Empfänger E-Mail', 'required' => true, 'attr' => ['placeholder' => 'E-Mail-Adresse des Empfängers']])
            ->add('kommentar', TextareaType::class, ['label' => 'Nachricht an Empfänger', 'required' => false, 'attr' => ['rows' => '4']])
            ->add('cCode', TextType::class, ['label' => false, 'required' => true, 'attr' => ['placeholder' => '4-stellige Codezahl']])
            ->add('key', HiddenType::class)
            ->add('datenschutz', CheckboxType::class, ['label' => false, 'required' => true, 'attr' => ['noFormControl' => true]])
            ->add('submit', SubmitType::class, ['label' => 'Termin als Mail versenden', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);
        $error = '';
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->codeChallengeService->isValid((string) $form->get('cCode')->getData(), (string) $form->get('key')->getData())) {
                $sendEmail = $form->get('sendEmail')->getData();
                $sendVorname = $form->get('sendVorname')->getData();
                $sendNachname = $form->get('sendNachname')->getData();
                $empfEmail = $form->get('empfEmail')->getData();
                $subject = 'Hinweis auf Termin von ' . $sendVorname . ' ' . $sendNachname;
                $options = ['termin' => $termin, 'get' => $_GET];
                $this->mailDeliveryService->sendTemplate('emailsent.html.twig', $kid, $options, $empfEmail, $subject, $sendEmail);

                return $this->htmlResponseService->render('Kalender/sent_mail.html.twig', [
                    'termin' => $termin,
                    'konf' => $konf,
                    'empfEmail' => $empfEmail,
                ]);
            }

            $error .= 'Fehler cC';
        }

        $error .= $form->getErrors(true);

        return $this->htmlResponseService->render('Kalender/form_mail.html.twig', [
            'termin' => $termin,
            'konf' => $konf,
            'form' => $form->createView(),
            'code' => $captcha,
            'error' => $error,
            'filter_form' => null,
        ]);
    }

    #[Route(path: '/js/kalender/{kid}/erinnern/{id}', name: 'termine_remind', methods: ['GET', 'POST'])]
    public function remind(int $kid, int $id, Request $request): Response
    {
        $termin = $this->loadTermin($id);
        $datefix = $termin->getDatefix();
        $captcha = $this->codeChallengeService->create();
        $reminder = new DfxReminder();
        $reminder->setTermin($termin);
        $reminder->setDatefix($datefix);
        $reminder->setCode(substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 20));

        $form = $this->createForm(DfxReminderType::class, $reminder, [
            'action' => $this->generateUrl('termine_remind', ['kid' => $kid, 'id' => $id]),
            'method' => 'GET',
            'cKey' => $captcha['key'],
            'csrf_token_manager' => $this->statelessCsrfTokenManager,
        ]);
        $form
            ->add('submit', SubmitType::class, ['label' => 'Datensatz speichern', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'zurücksetzen', 'attr' => ['class' => 'btn btn-primary']]);

        $form->handleRequest($request);
        $error = '';
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->codeChallengeService->isValid((string) $form->get('cCode')->getData(), (string) $form->get('key')->getData())) {
                $this->em->persist($reminder);
                $this->em->flush();

                return $this->htmlResponseService->render('Kalender/sent_reminder.html.twig', [
                    'termin' => $termin,
                    'reminder' => $reminder,
                    'konf' => $datefix,
                ]);
            }

            $error .= 'Fehler cC ';
        }

        $error .= $form->getErrors(true);

        return $this->htmlResponseService->render('Kalender/form_reminder.html.twig', [
            'termin' => $termin,
            'konf' => $datefix,
            'code' => $captcha,
            'form' => $form->createView(),
            'error' => $error,
            'filter_form' => null,
        ]);
    }

    #[Route(path: '/js/kalender/{kid}/ical/{id}', name: 'termine_ical', methods: ['GET'])]
    public function ical(int $kid, int $id): Response
    {
        $termin = $this->loadTermin($id);

        $datumVonObj = $termin->getDatumVon() ?? $termin->getDatum();
        $datumBisObj = $termin->getDatum() ?? $termin->getDatumVon();
        if ($datumVonObj === null || $datumBisObj === null) {
            return new Response('Termin ohne Datum', Response::HTTP_BAD_REQUEST);
        }

        $datumVon = $datumVonObj->format('Y-m-d');
        $datumBis = $datumBisObj->format('Y-m-d');
        $zeit = $termin->getZeit() !== null ? $termin->getZeit()->format('H:i:s') : '00:00:00';
        $start = new DateTimeImmutable($datumVon . ' ' . $zeit, new DateTimeZone('Europe/Berlin'));

        if ($termin->getZeitBis() !== null) {
            $zeitBis = $termin->getZeitBis()->format('H:i:s');
            $ende = new DateTimeImmutable($datumBis . ' ' . $zeitBis, new DateTimeZone('Europe/Berlin'));
        } else {
            $ende = (new DateTimeImmutable($datumVon . ' ' . $zeit, new DateTimeZone('Europe/Berlin')))->modify('+3 hours');
            $zeitBis = '00:00:00';
        }

        if ($datumVon !== $datumBis) {
            $ende = new DateTimeImmutable($datumBis . ' ' . $zeitBis, new DateTimeZone('Europe/Berlin'));
        }

        $ort = '';
        if ($termin->getLokal() !== null) {
            $ort .= $termin->getLokal() . ', ';
        }
        if ($termin->getLokalStrasse() !== null) {
            $ort .= $termin->getLokalStrasse() . ', ';
        }
        if ($termin->getPlz() !== null) {
            $ort .= $termin->getPlz() . ' ';
        }
        if ($termin->getOrt() !== null) {
            $ort .= $termin->getOrt() . ', ';
        }

        $event = (new Event())
            ->setSummary($termin->getTitel())
            ->setDescription((string) $termin->getBeschreibung())
            ->setOccurrence(
                new TimeSpan(
                    new IcalDateTime(DateTimeImmutable::createFromInterface($start), false),
                    new IcalDateTime(DateTimeImmutable::createFromInterface($ende), false)
                )
            );
        if (trim($ort) !== '') {
            $event->setLocation(new IcalLocation($ort));
        }

        $tz = new DateTimeZone('Europe/Berlin');
        $calendar = (new Calendar())
            ->addEvent($event)
            ->addTimeZone(IcalTimeZone::createFromPhpDateTimeZone($tz, $start, $ende));

        $calendarFactory = new CalendarFactory();

        return new Response(
            (string) $calendarFactory->createCalendar($calendar),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="datefix.ics"',
            ]
        );
    }

    #[Route(path: '/js/kalender/check/{code}/{key}', name: 'check', methods: ['GET'])]
    public function checkCoderand(int $code, string $key): JsonResponse
    {
        $checked = md5($code + 1958) == $key ? 'ok' : 'error';

        return $this->corsJsonResponse($checked);
    }

    #[Route(path: '/js/kalender/json_kal/tage', name: 'termine_json_tage', methods: ['GET'])]
    public function tageJson(Request $request): JsonResponse
    {
        $tage = explode(',', (string) $request->query->get('wt'));
        $datumVon = (string) $request->query->get('datum_von');
        $datumBis = (string) $request->query->get('datum_bis');

        $tVon = (int) substr($datumVon, 8, 2);
        $mVon = (int) substr($datumVon, 5, 2);
        $jVon = (int) substr($datumVon, 0, 4);
        $tBis = (int) substr($datumBis, 8, 2);
        $mBis = (int) substr($datumBis, 5, 2);
        $jBis = (int) substr($datumBis, 0, 4);

        $datumliste = null;
        for ($i = 0; $i < count($tage); $i++) {
            $tagNow = mktime(0, 0, 0, $mVon, $tVon, $jVon);
            $z = 0;
            while ($tagNow <= mktime(0, 0, 0, $mBis, $tBis, $jBis)) {
                if (date('w', $tagNow) == $tage[$i]) {
                    $datumliste .= date('Y-m-d', $tagNow) . ',';
                }

                $z++;
                $tagNow = mktime(0, 0, 0, $mVon, $tVon + $z, $jVon);
            }
        }

        return $this->corsJsonResponse($datumliste);
    }

    #[Route(path: '/js/kalender/json/location/{id}', methods: ['GET'])]
    public function jsonLocation(int $id): JsonResponse
    {
        $entity = $this->em->getRepository(DfxLocation::class)->find($id);
        $location = [
            'lokal' => $entity->getName(),
            'lokalStrasse' => $entity->getStrasse(),
            'nat' => $entity->getNat(),
            'plz' => $entity->getPlz(),
            'ort' => $entity->getOrt(),
            'lg' => $entity->getLg(),
            'bg' => $entity->getBg(),
        ];

        if ($entity->getRegion() !== null) {
            $location['rid'] = $entity->getRegion()->getId();
        }
        if ($entity->getIdOrt() !== null) {
            $location['oid'] = $entity->getIdOrt()->getId();
        }
        if ($entity->getVeranstalter() !== null) {
            $veranstalter = $entity->getVeranstalter();
            $location['ver'] = [
                'id' => $veranstalter->getId(),
                'name' => $veranstalter->getName(),
                'email' => $veranstalter->getEmail(),
                'ansprech' => $veranstalter->getAnsprech(),
                'telefon' => $veranstalter->getTelefon(),
            ];
        } else {
            $location['ver'] = null;
        }

        return $this->corsJsonResponse($location);
    }

    #[Route(path: '/js/kalender/json/veranstalter/{id}', methods: ['GET'])]
    public function jsonVeranstalter(int $id): JsonResponse
    {
        $entity = $this->em->getRepository(DfxVeranstalter::class)->find($id);
        $veranstalter = [
            'name' => $entity->getName(),
            'email' => $entity->getEmail(),
            'ansprech' => $entity->getAnsprech(),
            'telefon' => $entity->getTelefon(),
        ];

        if ($entity->getRegion() !== null) {
            $veranstalter['rid'] = $entity->getRegion()->getId();
        }

        if ($entity->getLocation() !== null) {
            $location = $entity->getLocation();
            $veranstalter['loc'] = [
                'id' => $location->getId(),
                'lokal' => $location->getName(),
                'lokalStrasse' => $location->getStrasse(),
                'nat' => $location->getNat(),
                'plz' => $location->getPlz(),
                'ort' => $location->getOrt(),
                'lg' => $location->getLg(),
                'bg' => $location->getBg(),
            ];

            if ($location->getRegion() !== null) {
                $veranstalter['loc']['rid'] = $location->getRegion()->getId();
            }
            if ($location->getIdOrt() !== null) {
                $veranstalter['loc']['oid'] = $location->getIdOrt()->getId();
            }
        } else {
            $veranstalter['loc'] = null;
        }

        return $this->corsJsonResponse($veranstalter);
    }

    #[Route(path: '/js/kalender/json/region/{id}', methods: ['GET'])]
    public function jsonRegion(int $id): JsonResponse
    {
        $orte = $id === 0
            ? $this->em->getRepository(DfxOrte::class)->findBy([], ['ort' => 'ASC'])
            : $this->em->getRepository(DfxOrte::class)->findBy(['region' => $id], ['ort' => 'ASC']);

        $orteJson = [];
        foreach ($orte as $ort) {
            $orteJson[] = [
                'id' => $ort->getId(),
                'ort' => $ort->getOrt(),
                'plz' => $ort->getPlz(),
            ];
        }

        return new JsonResponse($orteJson);
    }

    #[Route(path: '/js/kalender/json/ort/{id}', methods: ['GET'])]
    public function jsonOrt(int $id): JsonResponse
    {
        $ort = $this->em->getRepository(DfxOrte::class)->find($id);
        $ortJson = [
            'plz' => $ort->getPlz(),
            'ort' => $ort->getOrt(),
        ];

        if ($ort->getRegion() !== null) {
            $ortJson['rid'] = $ort->getRegion()->getId();
        }

        return new JsonResponse($ortJson);
    }

    private function loadTermin(int $id): DfxTermine
    {
        $termin = $this->em->getRepository(DfxTermine::class)->find($id);
        if ($termin === null) {
            throw new GoneHttpException('Termin nicht mehr vorhanden');
        }

        return $termin;
    }

    private function corsJsonResponse(mixed $data): JsonResponse
    {
        $response = new JsonResponse($data);
        $sender = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_HOST'] ?? '*';
        $response->headers->add([
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Origin' => $sender,
        ]);

        return $response;
    }
}
