<?php
declare(strict_types=1);

namespace App\Repository;

interface ProductRepositoryInterface
{
    public function all(): array;

    public function find(int $id): ?array;

    /** @param array{name:string,description:string,price:float,stock:int} $data */
    public function create(array $data): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
