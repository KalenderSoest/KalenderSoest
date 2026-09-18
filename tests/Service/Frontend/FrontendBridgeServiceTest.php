<?php

namespace App\Tests\Service\Frontend;

use App\Entity\DfxKonf;
use App\Entity\DfxNews;
use App\Entity\DfxTermine;
use App\Service\Frontend\FrontendBridgeService;
use App\Service\Frontend\FrontendContentRenderer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class FrontendBridgeServiceTest extends TestCase
{
    public function testDefaultCalendarRequestUsesInternalJsRoute(): void
    {
        $renderer = $this->createMock(FrontendContentRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('renderCalendarList')
            ->willReturn([
                'content' => 'ok',
                'termin' => null,
                'artikel' => null,
            ]);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $kernel->expects(self::never())->method('handle');

        $service = new FrontendBridgeService($this->createMock(EntityManagerInterface::class), $kernel, $renderer);

        $result = $service->renderContent((new DfxKonf())->setId(7), Request::create('/kalender/7', 'GET'));

        self::assertSame('ok', $result['content']);
        self::assertNull($result['termin']);
        self::assertNull($result['artikel']);
    }

    public function testRejectsInvalidFrontendPathBeforeSubrequest(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $kernel->expects(self::never())->method('handle');
        $renderer = $this->createMock(FrontendContentRenderer::class);
        $renderer->expects(self::never())->method($this->anything());

        $service = new FrontendBridgeService($this->createMock(EntityManagerInterface::class), $kernel, $renderer);

        $result = $service->renderContent(
            (new DfxKonf())->setId(7),
            Request::create('/kalender/7', 'GET', ['dfxpath' => '/kalender/7'])
        );

        self::assertSame('Ungültiger Frontend-Pfad.', $result['content']);
        self::assertNull($result['termin']);
        self::assertNull($result['artikel']);
    }

    public function testAllowsKnownLegacyFrontendPathOutsideJsNamespace(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $kernel
            ->expects(self::once())
            ->method('handle')
            ->with(self::callback(function (Request $request): bool {
                self::assertSame('/karten/new/12', $request->getPathInfo());

                return true;
            }), HttpKernelInterface::SUB_REQUEST)
            ->willReturn(new Response('karten'));

        $renderer = $this->createMock(FrontendContentRenderer::class);
        $renderer->expects(self::never())->method($this->anything());

        $service = new FrontendBridgeService($this->createMock(EntityManagerInterface::class), $kernel, $renderer);

        $result = $service->renderContent(
            (new DfxKonf())->setId(7),
            Request::create('/kalender/7', 'GET', ['dfxpath' => '/karten/new/12'])
        );

        self::assertSame('karten', $result['content']);
    }

    public function testSubrequestKeepsParentSession(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $request = Request::create('/kalender/7', 'GET', ['dfxpath' => '/karten/new/12']);
        $request->setSession($session);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $kernel
            ->expects(self::once())
            ->method('handle')
            ->with(self::callback(function (Request $subRequest) use ($session): bool {
                self::assertTrue($subRequest->hasSession());
                self::assertSame($session, $subRequest->getSession());

                return true;
            }), HttpKernelInterface::SUB_REQUEST)
            ->willReturn(new Response('karten'));

        $renderer = $this->createMock(FrontendContentRenderer::class);
        $renderer->expects(self::never())->method($this->anything());

        $service = new FrontendBridgeService($this->createMock(EntityManagerInterface::class), $kernel, $renderer);

        $result = $service->renderContent((new DfxKonf())->setId(7), $request);

        self::assertSame('karten', $result['content']);
    }

    public function testDetailLookupReturnsTerminAlongsideRenderedContent(): void
    {
        $termin = (new DfxTermine())->setId(15);
        $renderer = $this->createMock(FrontendContentRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('renderCalendarDetail')
            ->with(self::isInstanceOf(DfxKonf::class), self::isInstanceOf(Request::class), 15)
            ->willReturn([
                'content' => 'detail',
                'termin' => $termin,
                'artikel' => null,
            ]);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $kernel->expects(self::never())->method('handle');

        $service = new FrontendBridgeService($this->createMock(EntityManagerInterface::class), $kernel, $renderer);

        $result = $service->renderContent(
            (new DfxKonf())->setId(7),
            Request::create('/kalender/7', 'GET', ['dfxid' => 15])
        );

        self::assertSame('detail', $result['content']);
        self::assertSame($termin, $result['termin']);
        self::assertNull($result['artikel']);
    }

    public function testNewsDetailLookupReturnsArtikelAlongsideRenderedContent(): void
    {
        $artikel = (new DfxNews())->setId(22);
        $renderer = $this->createMock(FrontendContentRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('renderNewsDetail')
            ->with(self::isInstanceOf(DfxKonf::class), self::isInstanceOf(Request::class), 22)
            ->willReturn([
                'content' => 'news-detail',
                'termin' => null,
                'artikel' => $artikel,
            ]);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $kernel->expects(self::never())->method('handle');

        $service = new FrontendBridgeService($this->createMock(EntityManagerInterface::class), $kernel, $renderer);

        $result = $service->renderContent(
            (new DfxKonf())->setId(7),
            Request::create('/news/7', 'GET', ['nfxid' => 22])
        );

        self::assertSame('news-detail', $result['content']);
        self::assertNull($result['termin']);
        self::assertSame($artikel, $result['artikel']);
    }
}
