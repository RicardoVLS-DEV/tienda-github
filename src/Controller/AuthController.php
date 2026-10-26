<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Support\Csrf;
use App\Support\Session;
use App\Support\View;

final class AuthController extends BaseController
{
    public function __construct(View $v, Session $s, Csrf $c, private AuthService $auth)
    {
        parent::__construct($v, $s, $c);
    }

    public function showLogin(): void
    {
        $this->view->render('auth/login', ['title' => 'Iniciar sesión', 'errors' => [], 'old' => []]);
    }

    public function login(): void
    {
        $this->requireCsrf();
        $email    = (string) ($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $errors = $this->auth->validateLogin($email, $password);
        $user   = $errors ? null : $this->auth->attempt($email, $password);

        if ($user === null) {
            $this->view->render('auth/login', [
                'title'  => 'Iniciar sesión',
                'errors' => ['general' => $errors ? reset($errors) : 'Credenciales incorrectas.'],
                'old'    => ['email' => $email],
            ]);
            return;
        }
        $this->session->login($user);
        $this->session->flash('success', '¡Bienvenido, ' . $user['name'] . '!');
        $this->redirect('home');
    }

    public function showRegister(): void
    {
        $this->view->render('auth/register', ['title' => 'Crear cuenta', 'errors' => [], 'old' => []]);
    }

    public function register(): void
    {
        $this->requireCsrf();
        $res = $this->auth->register(
            (string) ($_POST['name'] ?? ''),
            (string) ($_POST['email'] ?? ''),
            (string) ($_POST['password'] ?? ''),
            (string) ($_POST['confirm'] ?? '')
        );
        if ($res['errors']) {
            $this->view->render('auth/register', [
                'title'  => 'Crear cuenta',
                'errors' => $res['errors'],
                'old'    => ['name' => $_POST['name'] ?? '', 'email' => $_POST['email'] ?? ''],
            ]);
            return;
        }
        $this->session->login($res['user']);
        $this->session->flash('success', 'Cuenta creada correctamente.');
        $this->redirect('home');
    }

    public function logout(): void
    {
        $this->requireCsrf();
        $this->session->logout();
        $this->session->flash('success', 'Sesión cerrada.');
        $this->redirect('login');
    }
}
