<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepositoryInterface;
use App\Validation\Validator;

final class AuthService
{
    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * @return array{errors: array<string,string>, user: ?array}
     */
    public function register(string $name, string $email, string $password, string $confirm): array
    {
        $name  = $this->normalizeSpaces($name);
        $email = strtolower(trim($email));

        $v = new Validator();
        $v->required('name', $name, 'Nombre')
          ->minLength('name', $name, 2, 'Nombre')
          ->maxLength('name', $name, 100, 'Nombre')
          ->regex('name', $name, '/^[\p{L}\p{M}][\p{L}\p{M}\' .\-]*$/u',
              'El campo Nombre solo admite letras, espacios, apóstrofes, puntos y guiones.');
        $v->required('email', $email, 'Correo')
          ->maxLength('email', $email, 150, 'Correo')
          ->email('email', $email, 'Correo');
        $v->password('password', $password);
        $v->matches('confirm', $confirm, $password, 'Las contraseñas no coinciden.');

        $errors = $v->errors();
        if (!isset($errors['email']) && $this->users->findByEmail($email) !== null) {
            $errors['email'] = 'Ese correo ya está registrado.';
        }
        if ($errors) {
            return ['errors' => $errors, 'user' => null];
        }

        $id = $this->users->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
        return ['errors' => [], 'user' => ['id' => $id, 'name' => $name, 'email' => $email]];
    }

    /** @return array<string,string> */
    public function validateLogin(string $email, string $password): array
    {
        $v = new Validator();
        $v->required('email', $email, 'Correo')->email('email', trim($email), 'Correo');
        $v->required('password', $password, 'Contraseña');
        return $v->errors();
    }

    /** Devuelve el usuario si las credenciales son correctas, o null. */
    public function attempt(string $email, string $password): ?array
    {
        if ($this->validateLogin($email, $password) !== []) {
            return null;
        }
        $user = $this->users->findByEmail(strtolower(trim($email)));
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }

    private function normalizeSpaces(string $s): string
    {
        return preg_replace('/\s+/u', ' ', trim($s)) ?? '';
    }
}
