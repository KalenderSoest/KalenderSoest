<?php

namespace App\Tests\Controller;

use App\Controller\DfxApiController;
use App\Entity\DfxKonf;
use App\Entity\DfxNfxCounter;
use App\Entity\DfxTermine;
use App\Service\Api\ApiPayloadRendererResolver;
use App\Service\Api\SchemaOrgApiPayloadRenderer;
use App\Service\Calendar\CalendarPublicationQueryHelper;
use App\Service\Calendar\CalendarScopeResolver;
use App\Service\Calendar\KalenderFilterQueryApplier;
use App\Service\Calendar\NewsFrontendQueryFactory;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

final class DfxApiControllerTest extends TestCase
{
    public function testTerminDetailRejectsDisabledApi(): void
    {
        $konf = (new DfxKonf())->setAllowApi(false);
        $termin = (new DfxTermine())->setDatefix($konf)->setPub(true);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('getRepository');

        $response = $this->createController($em)->detail($termin);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertSame(
            ['error' => 'Die Api ist vom Administrator dieses Veranstaltungskalenders gesperrt.'],
            json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR)
        );
    }

    public function testTerminDetailRejectsUnpublishedTermin(): void
    {
        $konf = (new DfxKonf())->setId(1)->setAllowApi(true);
        $termin = (new DfxTermine())->setDatefix($konf)->setPub(false);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('getRepository');

        $response = $this->createController($em)->detail($termin);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertSame(
            ['error' => 'Termin ist nicht freigegeben.'],
            json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR)
        );
    }

    public function testTerminDetailRequiresGroupPublicationForGroupCalendar(): void
    {
        $konf = (new DfxKonf())
            ->setAllowApi(true)
            ->setIsGroup(true);
        $termin = (new DfxTermine())
            ->setDatefix($konf)
            ->setPub(true)
            ->setPubGroup(false);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('getRepository');

        $response = $this->createController($em)->detail($termin);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertSame(
            ['error' => 'Termin ist nicht freigegeben.'],
            json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR)
        );
    }

    public function testTerminDetailRequiresMetaPublicationForMetaCalendar(): void
    {
        $konf = (new DfxKonf())
            ->setAllowApi(true)
            ->setIsMeta(true);
        $termin = (new DfxTermine())
            ->setDatefix($konf)
            ->setPub(true)
            ->setPubMeta(false);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('getRepository');

        $response = $this->createController($em)->detail($termin);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertSame(
            ['error' => 'Termin ist nicht freigegeben.'],
            json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR)
        );
    }

    public function testTerminDetailReturnsPublishedTermin(): void
    {
        $konf = (new DfxKonf())->setId(1)->setAllowApi(true);
        $termin = (new DfxTermine())->setId(15)->setDatefix($konf)->setPub(true);

        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['datefix' => $konf])
            ->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em
            ->expects(self::once())
            ->method('getRepository')
            ->with(DfxNfxCounter::class)
            ->willReturn($repository);

        $renderer = $this->createMock(SchemaOrgApiPayloadRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('renderTerminDetail')
            ->with($termin)
            ->willReturn(['identifier' => 'dfx-15']);

        $response = $this->createController($em, $renderer)->detail($termin);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(
            ['identifier' => 'dfx-15'],
            json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR)
        );
    }

    private function createController(
        EntityManagerInterface $em,
        ?SchemaOrgApiPayloadRenderer $renderer = null,
    ): DfxApiController {
        $renderer ??= $this->createMock(SchemaOrgApiPayloadRenderer::class);
        $container = $this->createMock(ContainerInterface::class);

        return new DfxApiController(
            new CalendarPublicationQueryHelper(),
            $this->withoutConstructor(CalendarScopeResolver::class),
            new KalenderFilterQueryApplier(),
            $this->withoutConstructor(NewsFrontendQueryFactory::class),
            new ApiPayloadRendererResolver($renderer, $container),
            $em,
        );
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function withoutConstructor(string $class): object
    {
        return (new ReflectionClass($class))->newInstanceWithoutConstructor();
    }
}
