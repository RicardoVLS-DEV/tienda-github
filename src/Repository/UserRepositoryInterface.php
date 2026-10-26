<?php
declare(strict_types=1);

namespace App\Repository;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?array;

    public function create(string $name, string $email, string $passwordHash): int;
}
