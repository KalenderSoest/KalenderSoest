<?php

namespace App\Service\Frontend;

use App\Service\Support\ParameterBagService;
final class CodeChallengeService
{
    public function __construct(
        private readonly ParameterBagService $parameterBagService,
    ) {
    }

    public function create(): array
    {
        $labels = [
            'my' => 'null',
            'io' => 'eins',
            'cl' => 'zwei',
            'hl' => 'drei',
            'ae' => 'vier',
            'ea' => 'fünf',
            'lh' => 'sechs',
            'lc' => 'sieben',
            'oi' => 'acht',
            'ym' => 'neun',
        ];

        $digitToCode = [
            '0' => 'my',
            '1' => 'io',
            '2' => 'cl',
            '3' => 'hl',
            '4' => 'ae',
            '5' => 'ea',
            '6' => 'lh',
            '7' => 'lc',
            '8' => 'oi',
            '9' => 'ym',
        ];

        $rand = random_int(1000, 9999);
        $key = md5($rand + 1958);
        $code = (string) $rand;
        $toolUrl = (string) $this->parameterBagService->get('datefix_url');

        $markup = '<div class="input-group-addon">';
        for ($i = 0; $i < 4; $i++) {
            $codeKey = $digitToCode[$code[$i]];
            $label = $labels[$codeKey];
            $markup .= '<img src="' . $toolUrl . '/images/c' . $codeKey . '.gif" width="10" height="20" alt="Code Ziffer ' . ($i + 1) . ': ' . $label . '" aria-label="Code Ziffer ' . ($i + 1) . ': ' . $label . '" style="display:inline; margin-bottom: 0">';
        }
        $markup .= '</div>';

        return [
            'code' => $markup,
            'key' => $key,
        ];
    }

    public function isValid(string|int $code, string $key): bool
    {
        return md5((string) ((int) $code + 1958)) === $key;
    }
}
