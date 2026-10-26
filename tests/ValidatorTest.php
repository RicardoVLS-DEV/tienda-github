<?php
declare(strict_types=1);

namespace Tests;

use App\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function test_required(): void
    {
        $v = (new Validator())->required('a', '   ', 'A');
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('a', $v->errors());

        $this->assertFalse((new Validator())->required('a', 'x', 'A')->fails());
    }

    public function test_solo_guarda_el_primer_error_por_campo(): void
    {
        $v = (new Validator())->required('a', '', 'A')->minLength('a', '', 3, 'A');
        $this->assertCount(1, $v->errors());
        $this->assertStringContainsString('obligatorio', $v->errors()['a']);
    }

    public function test_longitudes(): void
    {
        $this->assertTrue((new Validator())->minLength('a', 'ab', 3, 'A')->fails());
        $this->assertFalse((new Validator())->minLength('a', 'abc', 3, 'A')->fails());
        $this->assertTrue((new Validator())->maxLength('a', 'abcd', 3, 'A')->fails());
        $this->assertFalse((new Validator())->maxLength('a', 'ñññ', 3, 'A')->fails()); // multibyte
    }

    /** @dataProvider emails */
    public function test_email(string $value, bool $valid): void
    {
        $this->assertSame(!$valid, (new Validator())->email('e', $value, 'Correo')->fails());
    }

    public static function emails(): array
    {
        return [
            ['ana@mail.com', true],
            ['a.b+c@sub.dominio.mx', true],
            ['sin-arroba.com', false],
            ['ana@', false],
            ['@mail.com', false],
            ['ana @mail.com', false],
        ];
    }

    /** @dataProvider decimales */
    public function test_decimal(string $value, bool $valid): void
    {
        $v = (new Validator())->decimal('p', $value, 0.01, 1000.00, 'Precio');
        $this->assertSame(!$valid, $v->fails(), "Valor: $value");
    }

    public static function decimales(): array
    {
        return [
            ['10', true], ['10.5', true], ['10.50', true], ['0.01', true], ['1000', true],
            ['0', false], ['0.00', false], ['-5', false], ['10.555', false],
            ['1e3', false], ['abc', false], ['', false], ['1000.01', false], ['0x1A', false],
        ];
    }

    /** @dataProvider enteros */
    public function test_integer(string $value, bool $valid): void
    {
        $v = (new Validator())->integer('s', $value, 0, 100, 'Stock');
        $this->assertSame(!$valid, $v->fails(), "Valor: $value");
    }

    public static function enteros(): array
    {
        return [
            ['0', true], ['50', true], ['100', true],
            ['101', false], ['-1', false], ['1.5', false], ['abc', false], ['', false],
            ['99999999999999999999', false],
        ];
    }

    /** @dataProvider passwords */
    public function test_password(string $value, bool $valid): void
    {
        $this->assertSame(!$valid, (new Validator())->password('p', $value)->fails(), "Valor: $value");
    }

    public static function passwords(): array
    {
        return [
            ['secreto123', true],
            ['corta1', false],            // < 8
            ['soloLetrasAqui', false],    // sin número
            ['123456789', false],         // sin letra
            [str_repeat('a1', 37), false],// 74 bytes > 72
        ];
    }

    public function test_matches_y_regex(): void
    {
        $this->assertTrue((new Validator())->matches('c', 'a', 'b', 'No coincide')->fails());
        $this->assertFalse((new Validator())->matches('c', 'a', 'a', 'No coincide')->fails());
        $this->assertTrue((new Validator())->regex('r', 'abc!', '/^[a-z]+$/', 'Inválido')->fails());
    }
}
