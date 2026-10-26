<?php
declare(strict_types=1);

namespace Tests;

use App\Service\ProductService;
use PHPUnit\Framework\TestCase;
use Tests\Fake\InMemoryProductRepository;

final class ProductServiceTest extends TestCase
{
    private ProductService $svc;

    protected function setUp(): void
    {
        $this->svc = new ProductService(new InMemoryProductRepository());
    }

    private function valid(array $override = []): array
    {
        return $override + ['name' => 'Taza azul', 'description' => 'Cerámica', 'price' => '12.50', 'stock' => '5'];
    }

    public function test_crea_producto_valido_con_coma_decimal(): void
    {
        $r = $this->svc->create($this->valid(['price' => '12,50']));
        $this->assertSame([], $r['errors']);
        $this->assertSame(12.5, $this->svc->get($r['id'])['price']);
    }

    public function test_normaliza_espacios_del_nombre(): void
    {
        $r = $this->svc->create($this->valid(['name' => '  Taza    azul  ']));
        $this->assertSame('Taza azul', $this->svc->get($r['id'])['name']);
    }

    public function test_valida_campos_vacios(): void
    {
        $r = $this->svc->create(['name' => '', 'price' => '', 'stock' => '']);
        $this->assertArrayHasKey('name', $r['errors']);
        $this->assertArrayHasKey('price', $r['errors']);
        $this->assertArrayHasKey('stock', $r['errors']);
        $this->assertNull($r['id']);
    }

    /** @dataProvider nombres */
    public function test_validacion_de_nombre(string $name, bool $valid): void
    {
        $r = $this->svc->validate($this->valid(['name' => $name]));
        $this->assertSame(!$valid, isset($r[0]['name']), "Nombre: $name");
    }

    public static function nombres(): array
    {
        return [['Ab', true], [str_repeat('a', 150), true], ['A', false], ['  ', false], [str_repeat('a', 151), false]];
    }

    /** @dataProvider precios */
    public function test_validacion_de_precio(string $price, bool $valid): void
    {
        $r = $this->svc->validate($this->valid(['price' => $price]));
        $this->assertSame(!$valid, isset($r[0]['price']), "Precio: $price");
    }

    public static function precios(): array
    {
        return [
            ['0.01', true], ['99', true], ['10,5', true], ['99999999.99', true],
            ['0', false], ['-1', false], ['10.999', false], ['1e3', false], ['abc', false], ['100000000', false],
        ];
    }

    /** @dataProvider stocks */
    public function test_validacion_de_stock(string $stock, bool $valid): void
    {
        $r = $this->svc->validate($this->valid(['stock' => $stock]));
        $this->assertSame(!$valid, isset($r[0]['stock']), "Stock: $stock");
    }

    public static function stocks(): array
    {
        return [['0', true], ['1000000', true], ['-1', false], ['1.5', false], ['abc', false], ['1000001', false]];
    }

    public function test_descripcion_larga_es_rechazada(): void
    {
        $r = $this->svc->validate($this->valid(['description' => str_repeat('x', 2001)]));
        $this->assertArrayHasKey('description', $r[0]);
    }

    public function test_actualiza_y_elimina(): void
    {
        $id = $this->svc->create($this->valid())['id'];
        $this->assertTrue($this->svc->update($id, $this->valid(['name' => 'Taza roja']))['ok']);
        $this->assertSame('Taza roja', $this->svc->get($id)['name']);
        $this->assertTrue($this->svc->delete($id));
        $this->assertNull($this->svc->get($id));
    }

    public function test_actualizar_con_datos_invalidos_no_modifica(): void
    {
        $id = $this->svc->create($this->valid())['id'];
        $r = $this->svc->update($id, $this->valid(['price' => '-5']));
        $this->assertFalse($r['ok']);
        $this->assertSame(12.5, $this->svc->get($id)['price']);
    }

    public function test_ids_invalidos_o_inexistentes(): void
    {
        $this->assertFalse($this->svc->update(99, $this->valid())['ok']);
        $this->assertFalse($this->svc->update(0, $this->valid())['ok']);
        $this->assertFalse($this->svc->delete(-1));
        $this->assertFalse($this->svc->delete(99));
        $this->assertNull($this->svc->get(0));
    }
}
