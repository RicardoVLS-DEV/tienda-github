<?php
declare(strict_types=1);

namespace App\Support;

final class View
{
    public function __construct(private string $path, private array $shared = []) {}

    public function render(string $template, array $data = []): void
    {
        $data = array_merge($this->shared, $data);
        extract($data, EXTR_SKIP);
        ob_start();
        require $this->path . '/' . $template . '.php';
        $content = ob_get_clean();
        require $this->path . '/layout.php';
    }
}
