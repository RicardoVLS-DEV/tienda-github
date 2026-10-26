<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class PdoProductRepository implements ProductRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM products WHERE id = :id');
        $st->execute(['id' => $id]);
        return $st->fetch() ?: null;
    }

    public function create(array $d): int
    {
        $st = $this->pdo->prepare(
            'INSERT INTO products (name, description, price, stock) VALUES (:name, :description, :price, :stock)'
        );
        $st->execute($this->bind($d));
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        $st = $this->pdo->prepare(
            'UPDATE products SET name = :name, description = :description, price = :price, stock = :stock WHERE id = :id'
        );
        return $st->execute($this->bind($d) + ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        $st = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $st->execute(['id' => $id]);
        return $st->rowCount() > 0;
    }

    private function bind(array $d): array
    {
        return [
            'name'        => $d['name'],
            'description' => $d['description'],
            'price'       => $d['price'],
            'stock'       => $d['stock'],
        ];
    }
}
