<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class PdoUserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $st->execute(['email' => $email]);
        return $st->fetch() ?: null;
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $st = $this->pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:n, :e, :p)');
        $st->execute(['n' => $name, 'e' => $email, 'p' => $passwordHash]);
        return (int) $this->pdo->lastInsertId();
    }
}
