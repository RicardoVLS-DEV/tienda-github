<?php
declare(strict_types=1);

namespace App\Controller;

use App\Support\Csrf;
use App\Support\Session;
use App\Support\View;

abstract class BaseController
{
    public function __construct(
        protected View $view,
        protected Session $session,
        protected Csrf $csrf
    ) {}

    protected function redirect(string $route, array $params = []): never
    {
        $qs = http_build_query(['r' => $route] + $params);
        header('Location: index.php?' . $qs);
        exit;
    }

    protected function requireAuth(): void
    {
        if ($this->session->user() === null) {
            $this->session->flash('error', 'Debes iniciar sesión.');
            $this->redirect('login');
        }
    }

    protected function requireCsrf(): void
    {
        if (!$this->csrf->valid($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('Token CSRF inválido.');
        }
    }
}
