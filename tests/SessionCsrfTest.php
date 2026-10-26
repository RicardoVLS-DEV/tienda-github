<?php
declare(strict_types=1);

namespace Tests;

use App\Support\Csrf;
use App\Support\Session;
use PHPUnit\Framework\TestCase;

final class SessionCsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function test_get_set_y_valor_por_defecto(): void
    {
        $s = new Session();
        $this->assertNull($s->get('x'));
        $this->assertSame('d', $s->get('x', 'd'));
        $s->set('x', 5);
        $this->assertSame(5, $s->get('x'));
    }

    public function test_usuario_en_sesion(): void
    {
        $s = new Session();
        $this->assertNull($s->user());
        $s->set('user', ['id' => 1, 'name' => 'Ana', 'email' => 'a@a.com']);
        $this->assertSame('Ana', $s->user()['name']);
    }

    public function test_flash_se_lee_una_sola_vez(): void
    {
        $s = new Session();
        $s->flash('success', 'Hecho');
        $this->assertSame(['type' => 'success', 'message' => 'Hecho'], $s->pullFlash());
        $this->assertNull($s->pullFlash());
    }

    public function test_csrf_genera_token_estable_y_valida(): void
    {
        $csrf = new Csrf(new Session());
        $token = $csrf->token();
        $this->assertSame(64, strlen($token));
        $this->assertSame($token, $csrf->token());
        $this->assertTrue($csrf->valid($token));
    }

    public function test_csrf_rechaza_tokens_invalidos(): void
    {
        $csrf = new Csrf(new Session());
        $csrf->token();
        $this->assertFalse($csrf->valid(null));
        $this->assertFalse($csrf->valid(''));
        $this->assertFalse($csrf->valid('token-falso'));
    }

    public function test_csrf_sin_token_previo_no_valida_nada(): void
    {
        $this->assertFalse((new Csrf(new Session()))->valid(''));
    }
}
