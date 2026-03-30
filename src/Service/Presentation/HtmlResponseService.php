<?php

namespace App\Service\Presentation;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class HtmlResponseService
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function render(string $tpl, array $options): Response
    {
        $sender = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_HOST'];
        $response = new Response();
        $response->headers->add([
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Origin' => $sender,
        ]);
        $response->setContent($this->twig->render($tpl, $options));

        return $response;
    }
}
