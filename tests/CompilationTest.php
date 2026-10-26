<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * "Compila correctamente": PHP no se compila, pero podemos comprobar que
 * (1) todos los archivos tienen sintaxis valida y (2) todas las clases cargan.
 */
final class CompilationTest extends TestCase
{
    private static function phpFiles(string $dir): array
    {
        $files = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f->isFile() && $f->getExtension() === 'php') {
                $files[] = $f->getPathname();
            }
        }
        sort($files);
        return $files;
    }

    public function test_todos_los_archivos_php_tienen_sintaxis_valida(): void
    {
        if (!function_exists('exec')) {
            $this->markTestSkipped('exec() deshabilitado.');
        }
        $root = dirname(__DIR__);
        $files = [];
        foreach (['src', 'templates', 'public', 'config', 'tests'] as $d) {
            $files = array_merge($files, self::phpFiles("$root/$d"));
        }
        $files[] = "$root/bootstrap.php";

        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $out = [];
            exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1', $out, $code);
            $this->assertSame(0, $code, "Error de sintaxis en $file:\n" . implode("\n", $out));
        }
    }

    public function test_todas_las_clases_e_interfaces_de_src_se_cargan(): void
    {
        $src = dirname(__DIR__) . '/src';
        $checked = 0;
        foreach (self::phpFiles($src) as $file) {
            $relative = substr($file, strlen($src) + 1, -4);
            if ($relative === 'helpers') {
                continue;
            }
            $fqcn = 'App\\' . str_replace('/', '\\', $relative);
            $this->assertTrue(
                class_exists($fqcn) || interface_exists($fqcn),
                "No se pudo cargar $fqcn (revisa namespace y nombre del archivo)"
            );
            $checked++;
        }
        $this->assertGreaterThan(10, $checked);
    }

    public function test_funcion_helper_e_escapa_html(): void
    {
        $this->assertSame('&lt;b&gt;&quot;x&quot; &amp; &#039;y&#039;', e('<b>"x" & \'y\''));
    }
}
