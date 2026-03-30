<?php

namespace App\Security;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class StatelessCsrfTokenManager implements CsrfTokenManagerInterface
{
    private StatelessCsrf $stateless;

    public function __construct(StatelessCsrf $stateless)
    {
        $this->stateless = $stateless;
    }

    public function getToken(string $tokenId): CsrfToken
    {
        return new CsrfToken($tokenId, $this->stateless->generate($tokenId));
    }

    public function refreshToken(string $tokenId): CsrfToken
    {
        return $this->getToken($tokenId);
    }

    public function removeToken(string $tokenId): ?string
    {
        return null;
    }

    public function isTokenValid(CsrfToken $token): bool
    {
        return $this->stateless->isValid($token->getId(), $token->getValue());
    }
}
