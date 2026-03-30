<?php

namespace App\Service\Support;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class ParameterBagService
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function get(string $name): mixed
    {
        return $this->parameterBag->get($name);
    }
}
