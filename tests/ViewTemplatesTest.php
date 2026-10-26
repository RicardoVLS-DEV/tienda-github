<?php
declare(strict_types=1);

namespace Tests;

use App\Support\View;
use ErrorException;
use PHPUnit\Framework\TestCase;

/**
 * Renderiza cada plantilla con datos de ejemplo. Cualquier warning/notice
 * (variable o indice indefinido, funcion inexistente) hace fallar la prueba.
 */
final class ViewTemplatesTest extends TestCase
{
    protected function setUp(): void
    {
        set_error_handler(function (int $no, string $msg, string $file, int $line): bool {
            throw new ErrorException($msg, 0, $no, $file, $line);
        });
    }

    protected function tearDown(): void
    {
        restore_error_handler();
    }

    private function render(string $template, array $data, ?array $user = null, ?array $flash = null): string
    {
        $view = new View(dirname(__DIR__) . '/templates', [
            'appName' => 'Mi Tienda', 'user' => $user, 'csrf' => 'tok123', 'flash' => $flash,
        ]);
        ob_start();
        try {
            $view->render($template, $data);
        } finally {
            $html = (string) ob_get_clean();
        }
        return $html;
    }

    public function test_login_muestra_formulario_y_token(): void
    {
        $html = $this->render('auth/login', ['title' => 'Iniciar sesión', 'errors' => [], 'old' => []]);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('value="tok123"', $html);
        $this->assertStringContainsString('Registrarse', $html);
    }

    public function test_login_escapa_valores_y_muestra_error(): void
    {
        $html = $this->render('auth/login', [
            'title' => 'x', 'errors' => ['general' => 'Credenciales incorrectas.'],
            'old' => ['email' => '"><script>alert(1)</script>'],
        ]);
        $this->assertStringContainsString('Credenciales incorrectas.', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_registro_muestra_errores_por_campo(): void
    {
        $html = $this->render('auth/register', [
            'title' => 'x', 'old' => ['name' => 'Ana', 'email' => 'a@a.com'],
            'errors' => ['email' => 'Ese correo ya está registrado.', 'confirm' => 'Las contraseñas no coinciden.'],
        ]);
        $this->assertStringContainsString('Ese correo ya está registrado.', $html);
        $this->assertStringContainsString('Las contraseñas no coinciden.', $html);
    }

    public function test_listado_vacio(): void
    {
        $html = $this->render('products/index', ['title' => 'Productos', 'products' => []]);
        $this->assertStringContainsString('Aún no hay productos', $html);
        $this->assertStringNotContainsString('Nuevo producto', $html);
    }

    private static function productos(): array
    {
        return [['id' => 7, 'name' => '<b>Taza</b>', 'description' => '', 'price' => '12.50', 'stock' => 3]];
    }

    public function test_listado_para_invitado_no_muestra_acciones(): void
    {
        $html = $this->render('products/index', ['title' => 'P', 'products' => self::productos()]);
        $this->assertStringContainsString('$12.50', $html);
        $this->assertStringContainsString('&lt;b&gt;Taza&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('Eliminar', $html);
    }

    public function test_listado_para_usuario_muestra_acciones_y_nombre(): void
    {
        $html = $this->render('products/index', ['title' => 'P', 'products' => self::productos()],
            ['id' => 1, 'name' => 'Ana', 'email' => 'a@a.com']);
        $this->assertStringContainsString('Editar', $html);
        $this->assertStringContainsString('Eliminar', $html);
        $this->assertStringContainsString('Hola, Ana', $html);
        $this->assertStringContainsString('index.php?r=product.edit&id=7', $html);
    }

    public function test_formulario_de_producto_crear_y_editar(): void
    {
        $crear = $this->render('products/form', [
            'title' => 'Nuevo producto', 'product' => [], 'errors' => [], 'action' => 'product.store', 'id' => null,
        ]);
        $this->assertStringContainsString('r=product.store', $crear);
        $this->assertStringNotContainsString('name="id"', $crear);

        $editar = $this->render('products/form', [
            'title' => 'Editar', 'product' => ['name' => 'Taza', 'price' => '5', 'stock' => 2, 'description' => 'x'],
            'errors' => ['price' => 'Precio inválido'], 'action' => 'product.update', 'id' => 7,
        ]);
        $this->assertStringContainsString('name="id" value="7"', $editar);
        $this->assertStringContainsString('Precio inválido', $editar);
    }

    public function test_mensaje_flash_en_layout(): void
    {
        $html = $this->render('products/index', ['title' => 'P', 'products' => []], null,
            ['type' => 'success', 'message' => 'Producto creado.']);
        $this->assertStringContainsString('alert-success', $html);
        $this->assertStringContainsString('Producto creado.', $html);
    }
}
