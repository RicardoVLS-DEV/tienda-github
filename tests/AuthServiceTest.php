<?php
declare(strict_types=1);

namespace Tests;

use App\Service\AuthService;
use PHPUnit\Framework\TestCase;
use Tests\Fake\InMemoryUserRepository;

final class AuthServiceTest extends TestCase
{
    private AuthService $auth;

    protected function setUp(): void
    {
        $this->auth = new AuthService(new InMemoryUserRepository());
    }

    public function test_registro_valido_normaliza_datos(): void
    {
        $r = $this->auth->register("  Ana   María ", '  ANA@Mail.com ', 'secreto123', 'secreto123');
        $this->assertSame([], $r['errors']);
        $this->assertSame('Ana María', $r['user']['name']);
        $this->assertSame('ana@mail.com', $r['user']['email']);
    }

    public function test_rechaza_correo_duplicado(): void
    {
        $this->auth->register('Ana', 'ana@mail.com', 'secreto123', 'secreto123');
        $r = $this->auth->register('Otra', 'ANA@mail.com', 'secreto123', 'secreto123');
        $this->assertArrayHasKey('email', $r['errors']);
    }

    public function test_rechaza_password_corta_y_no_coincidente(): void
    {
        $r = $this->auth->register('Ana', 'ana@mail.com', '123', '456');
        $this->assertArrayHasKey('password', $r['errors']);
        $this->assertArrayHasKey('confirm', $r['errors']);
    }

    public function test_rechaza_password_sin_numero_o_sin_letra(): void
    {
        $this->assertArrayHasKey('password', $this->auth->register('Ana', 'a@mail.com', 'soloLetras', 'soloLetras')['errors']);
        $this->assertArrayHasKey('password', $this->auth->register('Ana', 'a@mail.com', '123456789', '123456789')['errors']);
    }

    /** @dataProvider nombresInvalidos */
    public function test_rechaza_nombres_invalidos(string $name): void
    {
        $r = $this->auth->register($name, 'ana@mail.com', 'secreto123', 'secreto123');
        $this->assertArrayHasKey('name', $r['errors'], "Nombre: $name");
    }

    public static function nombresInvalidos(): array
    {
        return [[''], ['   '], ['A'], ['<script>'], ['1234'], [str_repeat('a', 101)]];
    }

    /** @dataProvider nombresValidos */
    public function test_acepta_nombres_validos(string $name): void
    {
        $r = $this->auth->register($name, uniqid() . '@mail.com', 'secreto123', 'secreto123');
        $this->assertArrayNotHasKey('name', $r['errors'], "Nombre: $name");
    }

    public static function nombresValidos(): array
    {
        return [['Ana'], ["O'Neil"], ['José Núñez'], ['Mary-Jane Smith'], ['Dr. Pérez']];
    }

    public function test_rechaza_correo_invalido_o_vacio(): void
    {
        $this->assertArrayHasKey('email', $this->auth->register('Ana', 'no-es-correo', 'secreto123', 'secreto123')['errors']);
        $this->assertArrayHasKey('email', $this->auth->register('Ana', '', 'secreto123', 'secreto123')['errors']);
    }

    public function test_login_correcto_e_incorrecto(): void
    {
        $this->auth->register('Ana', 'ana@mail.com', 'secreto123', 'secreto123');
        $this->assertNotNull($this->auth->attempt('ana@mail.com', 'secreto123'));
        $this->assertNotNull($this->auth->attempt('  ANA@mail.com ', 'secreto123'));
        $this->assertNull($this->auth->attempt('ana@mail.com', 'mala'));
        $this->assertNull($this->auth->attempt('nadie@mail.com', 'secreto123'));
    }

    public function test_validacion_de_login(): void
    {
        $this->assertSame([], $this->auth->validateLogin('ana@mail.com', 'x'));
        $this->assertArrayHasKey('email', $this->auth->validateLogin('', 'x'));
        $this->assertArrayHasKey('email', $this->auth->validateLogin('mal', 'x'));
        $this->assertArrayHasKey('password', $this->auth->validateLogin('ana@mail.com', ''));
        $this->assertNull($this->auth->attempt('', ''));
    }
}
