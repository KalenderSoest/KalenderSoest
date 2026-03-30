<?php

namespace App\Security;

class StatelessCsrf
{
    private string $secret;
    private int $ttl;

    public function __construct(string $secret, int $ttl = 1800)
    {
        $this->secret = $secret;
        $this->ttl = $ttl;
    }

    public function generate(string $id): string
    {
        $ts = time();
        $sig = hash_hmac('sha256', $id . ':' . $ts, $this->secret, true);
        return $ts . ':' . rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');
    }

    public function isValid(string $id, ?string $token): bool
    {
        if (!$token || strpos($token, ':') === false) {
            return false;
        }

        [$ts, $sig] = explode(':', $token, 2);
        if (!ctype_digit($ts)) {
            return false;
        }
        if ($this->ttl > 0 && (time() - (int) $ts) > $this->ttl) {
            return false;
        }

        $expected = hash_hmac('sha256', $id . ':' . $ts, $this->secret, true);
        $expectedB64 = rtrim(strtr(base64_encode($expected), '+/', '-_'), '=');
        return hash_equals($expectedB64, $sig);
    }
}
