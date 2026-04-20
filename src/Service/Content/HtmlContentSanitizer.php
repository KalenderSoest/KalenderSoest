<?php

namespace App\Service\Content;

use HTMLPurifier;
use HTMLPurifier_Config;

final class HtmlContentSanitizer
{
    private ?HTMLPurifier $purifier = null;

    public function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $normalized = trim($html);
        if ($normalized == '') {
            return null;
        }

        $clean = $this->getPurifier()->purify($normalized);
        $clean = preg_replace('#<a>(.*?)</a>#is', '$1', $clean ?? '') ?? '';
        $clean = preg_replace('/(?:\s*<br>\s*){3,}/i', '<br><br>', $clean ?? '') ?? '';
        $clean = trim($clean);

        return $clean !== '' ? $clean : null;
    }

    private function getPurifier(): HTMLPurifier
    {
        if ($this->purifier instanceof HTMLPurifier) {
            return $this->purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('Cache.DefinitionImpl', null);
        $config->set(
            'HTML.Allowed',
            implode(',', [
                'p',
                'br',
                'strong',
                'b',
                'em',
                'i',
                'u',
                's',
                'strike',
                'sub',
                'sup',
                'blockquote',
                'pre',
                'code',
                'ul',
                'ol',
                'li',
                'a[href|target|rel|title]',
                'table[border|cellpadding|cellspacing|width|style]',
                'thead[style]',
                'tbody[style]',
                'tfoot[style]',
                'tr[style]',
                'th[colspan|rowspan|scope|width|height|style]',
                'td[colspan|rowspan|width|height|style]',
                'caption',
                'colgroup[span|width]',
                'col[span|width]',
                'h1',
                'h2',
                'h3',
                'h4',
                'h5',
                'h6',
                'hr',
            ])
        );
        $config->set(
            'CSS.AllowedProperties',
            [
                'width',
                'height',
                'text-align',
                'vertical-align',
                'background-color',
                'border',
                'border-top',
                'border-right',
                'border-bottom',
                'border-left',
                'border-collapse',
            ]
        );
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self']);
        $config->set('Attr.EnableID', false);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
            'tel' => true,
        ]);

        $this->purifier = new HTMLPurifier($config);

        return $this->purifier;
    }
}
