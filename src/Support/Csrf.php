<?php
declare(strict_types=1);

namespace App\Support;

final class Csrf
{
    public function __construct(private Session $session) {}

    public function token(): string
    {
        $t = $this->session->get('csrf');
        if (!$t) {
            $t = bin2hex(random_bytes(32));
            $this->session->set('csrf', $t);
        }
        return $t;
    }

    public function valid(?string $token): bool
    {
        $expected = (string) $this->session->get('csrf', '');
        return $expected !== '' && $token !== null && hash_equals($expected, $token);
    }
}
