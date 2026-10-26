<?php
declare(strict_types=1);

namespace Tests\Fake;

use App\Repository\ProductRepositoryInterface;

final class InMemoryProductRepository implements ProductRepositoryInterface
{
    private array $items = [];
    private int $next = 1;

    public function all(): array { return array_reverse(array_values($this->items)); }
    public function find(int $id): ?array { return $this->items[$id] ?? null; }

    public function create(array $data): int
    {
        $id = $this->next++;
        $this->items[$id] = ['id' => $id] + $data;
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        if (!isset($this->items[$id])) { return false; }
        $this->items[$id] = ['id' => $id] + $data;
        return true;
    }

    public function delete(int $id): bool
    {
        $exists = isset($this->items[$id]);
        unset($this->items[$id]);
        return $exists;
    }
}
