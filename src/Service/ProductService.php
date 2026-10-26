<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\ProductRepositoryInterface;
use App\Validation\Validator;

final class ProductService
{
    public const MAX_PRICE = 99999999.99;
    public const MAX_STOCK = 1000000;

    public function __construct(private ProductRepositoryInterface $products) {}

    public function list(): array
    {
        return $this->products->all();
    }

    public function get(int $id): ?array
    {
        return $id > 0 ? $this->products->find($id) : null;
    }

    /** @return array{errors: array<string,string>, id: ?int} */
    public function create(array $input): array
    {
        [$errors, $clean] = $this->validate($input);
        if ($errors) {
            return ['errors' => $errors, 'id' => null];
        }
        return ['errors' => [], 'id' => $this->products->create($clean)];
    }

    /** @return array{errors: array<string,string>, ok: bool} */
    public function update(int $id, array $input): array
    {
        if ($this->get($id) === null) {
            return ['errors' => ['general' => 'El producto no existe.'], 'ok' => false];
        }
        [$errors, $clean] = $this->validate($input);
        if ($errors) {
            return ['errors' => $errors, 'ok' => false];
        }
        return ['errors' => [], 'ok' => $this->products->update($id, $clean)];
    }

    public function delete(int $id): bool
    {
        return $id > 0 && $this->products->delete($id);
    }

    /** @return array{0: array<string,string>, 1: array} [errores, datos limpios] */
    public function validate(array $in): array
    {
        $name  = preg_replace('/\s+/u', ' ', trim((string) ($in['name'] ?? ''))) ?? '';
        $desc  = trim((string) ($in['description'] ?? ''));
        $price = str_replace(',', '.', trim((string) ($in['price'] ?? '')));
        $stock = trim((string) ($in['stock'] ?? ''));

        $v = new Validator();
        $v->required('name', $name, 'Nombre')
          ->minLength('name', $name, 2, 'Nombre')
          ->maxLength('name', $name, 150, 'Nombre');
        $v->maxLength('description', $desc, 2000, 'Descripción');
        $v->required('price', $price, 'Precio')
          ->decimal('price', $price, 0.01, self::MAX_PRICE, 'Precio');
        $v->required('stock', $stock, 'Stock')
          ->integer('stock', $stock, 0, self::MAX_STOCK, 'Stock');

        return [$v->errors(), [
            'name'        => $name,
            'description' => $desc,
            'price'       => round((float) $price, 2),
            'stock'       => (int) $stock,
        ]];
    }
}
