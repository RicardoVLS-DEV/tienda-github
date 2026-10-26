<?php
declare(strict_types=1);

namespace Tests\Fake;

use App\Repository\UserRepositoryInterface;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    private array $users = [];
    private int $next = 1;

    public function findByEmail(string $email): ?array
    {
        foreach ($this->users as $u) {
            if ($u['email'] === $email) {
                return $u;
            }
        }
        return null;
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $id = $this->next++;
        $this->users[$id] = ['id' => $id, 'name' => $name, 'email' => $email, 'password_hash' => $passwordHash];
        return $id;
    }
}
