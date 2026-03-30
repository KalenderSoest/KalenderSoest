<?php

namespace App\Service\Presentation;

use App\Entity\DfxKonf;
use Twig\Environment;

final class TemplatePathResolver
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function resolve(string $directory, string $file, DfxKonf $konf): string
    {
        $kid = $konf->getId();

        if ($this->exists($directory . '/custom/' . $file)) {
            return $directory . '/custom/' . $file;
        }

        if ($this->exists($directory . '/' . $kid . '_' . $file)) {
            return $directory . '/' . $kid . '_' . $file;
        }

        return $directory . '/' . $file;
    }

    public function resolveKalenderList(DfxKonf $konf): string
    {
        $kid = $konf->getId();
        $typed = 'Kalender/liste_' . $konf->getDfxTplVersion() . '_' . $konf->getDfxTpl() . '.html.twig';

        if ($this->exists('Kalender/custom/liste_' . $konf->getDfxTplVersion() . '_' . $konf->getDfxTpl() . '.html.twig')) {
            return 'Kalender/custom/liste_' . $konf->getDfxTplVersion() . '_' . $konf->getDfxTpl() . '.html.twig';
        }

        if ($this->exists('Kalender/' . $kid . '/liste.html.twig')) {
            return 'Kalender/' . $kid . '/liste.html.twig';
        }

        if ($this->exists('Kalender/custom/liste.html.twig')) {
            return 'Kalender/custom/liste.html.twig';
        }

        return $typed;
    }

    public function resolveKalenderDetail(DfxKonf $konf, string $detailTemplate, string $ownDetailTemplate): string
    {
        return $this->resolveDomainDetail('Kalender', $konf, $detailTemplate, $ownDetailTemplate);
    }

    public function resolveNewsList(DfxKonf $konf): string
    {
        $kid = $konf->getId();

        if ($this->exists('News/' . $kid . '/liste.html.twig')) {
            return 'News/' . $kid . '/liste.html.twig';
        }

        if ($this->exists('News/custom/liste.html.twig')) {
            return 'News/custom/liste.html.twig';
        }

        return 'News/liste.html.twig';
    }

    public function resolveNewsDetail(DfxKonf $konf, string $detailTemplate, string $ownDetailTemplate): string
    {
        return $this->resolveDomainDetail('News', $konf, $detailTemplate, $ownDetailTemplate);
    }

    public function resolveFormTemplatePrefix(string $domain, int $kid): string
    {
        if ($this->exists($domain . '/' . $kid . '/form.html.twig')) {
            return $kid . '/form';
        }

        if ($this->exists($domain . '/custom/form.html.twig')) {
            return 'custom/form';
        }

        return 'form';
    }

    public function resolveCustomBasePrefix(string $domain, int $kid, string $baseTemplate): ?string
    {
        if ($this->exists($domain . '/' . $kid . '/' . $baseTemplate . '.html.twig')) {
            return $kid . '/';
        }

        if ($this->exists($domain . '/custom/' . $baseTemplate . '.html.twig')) {
            return 'custom/';
        }

        return null;
    }

    public function resolveEmail(string $template, string $kid): string
    {
        if ($this->exists('Emails/custom/' . $kid . '/' . $template)) {
            return 'Emails/custom/' . $kid . '/' . $template;
        }

        if ($this->exists('Emails/custom/' . $template)) {
            return 'Emails/custom/' . $template;
        }

        return 'Emails/' . $template;
    }

    public function resolveDomainDetail(string $domain, DfxKonf $konf, string $detailTemplate, string $ownDetailTemplate): string
    {
        $kid = $konf->getId();

        if ($this->exists($domain . '/' . $kid . '/' . $ownDetailTemplate . '.html.twig')) {
            return $domain . '/' . $kid . '/' . $ownDetailTemplate . '.html.twig';
        }

        if ($this->exists($domain . '/custom/' . $detailTemplate . '.html.twig')) {
            return $domain . '/custom/' . $detailTemplate . '.html.twig';
        }

        if ($this->exists($domain . '/custom/' . $ownDetailTemplate . '.html.twig')) {
            return $domain . '/custom/' . $ownDetailTemplate . '.html.twig';
        }

        return $domain . '/' . $detailTemplate . '.html.twig';
    }

    private function exists(string $template): bool
    {
        return $this->twig->getLoader()->exists($template);
    }
}
