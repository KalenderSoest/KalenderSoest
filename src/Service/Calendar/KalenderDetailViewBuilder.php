<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use App\Entity\DfxTermine;
use App\Service\Presentation\TemplatePathResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class KalenderDetailViewBuilder
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TemplatePathResolver $templatePathResolver,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function build(DfxKonf $konf, DfxTermine $termin, int $kid, ?string $cb, mixed $filterForm, string $kaldata, ?array $calendar): array
    {
        $this->clearMissingImage($termin);
        $serie = $this->loadUpcomingSeries($termin);

        if ($termin->getOnline() === true) {
            $tplDetail = 'detail_online';
            $tplOwnDetail = 'detail_online';
        } else {
            $tplDetail = 'detail_' . $konf->getDfxTplDetail();
            $tplOwnDetail = 'detail';
        }

        $options = [
            'konf' => $konf,
            'termin' => $termin,
            'serie' => $serie,
            'latlong' => $termin->getBg() . ',' . $termin->getLg(),
            'lokal' => $termin->getLokal(),
            'nav' => $konf->getNavDetail(),
            'cb' => $cb,
            'tplform' => $this->templatePathResolver->resolveFormTemplatePrefix('Kalender', $kid),
            'custom' => $this->templatePathResolver->resolveCustomBasePrefix('Kalender', $kid, 'base_detail'),
            'kaldata' => $kaldata,
            'calendar' => $calendar,
        ];

        if ($filterForm !== null) {
            $options['filter_form'] = $filterForm;
        }

        return [
            'template' => $this->templatePathResolver->resolveKalenderDetail($konf, $tplDetail, $tplOwnDetail),
            'options' => $options,
        ];
    }

    private function clearMissingImage(DfxTermine $termin): void
    {
        $image = $termin->getImg();
        if ($image === null || $image === '') {
            return;
        }

        $imagePath = $this->projectDir . '/web/images/dfx/' . $termin->getDatefix()->getId() . '/' . $image;
        if (!is_file($imagePath)) {
            $termin->setImg(null);
        }
    }

    private function loadUpcomingSeries(DfxTermine $termin): ?array
    {
        $code = $termin->getCode();
        if ($code === null || $code === '') {
            return null;
        }

        return $this->em->getRepository(DfxTermine::class)
            ->createQueryBuilder('t')
            ->select('partial t.{id,datumVon,zeit}')
            ->where('t.code = :code')
            ->andWhere('t.datumVon >= CURRENT_DATE()')
            ->setParameter('code', $code)
            ->orderBy('t.datumVon', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }
}
