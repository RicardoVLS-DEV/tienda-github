<?php
declare(strict_types=1);

namespace App\Validation;

/**
 * Validador sin dependencias (no usa superglobales, BD ni sesión).
 * Cada regla registra solo el PRIMER error de cada campo.
 */
final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function fail(string $field, string $message): self
    {
        return $this->check($field, false, $message);
    }

    public function required(string $field, string $value, string $label): self
    {
        return $this->check($field, trim($value) !== '', sprintf('El campo %s es obligatorio.', $label));
    }

    public function minLength(string $field, string $value, int $min, string $label): self
    {
        return $this->check($field, mb_strlen($value) >= $min,
            sprintf('El campo %s debe tener al menos %d caracteres.', $label, $min));
    }

    public function maxLength(string $field, string $value, int $max, string $label): self
    {
        return $this->check($field, mb_strlen($value) <= $max,
            sprintf('El campo %s no puede superar %d caracteres.', $label, $max));
    }

    public function email(string $field, string $value, string $label): self
    {
        return $this->check($field, filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            sprintf('El campo %s debe ser un correo electrónico válido.', $label));
    }

    public function regex(string $field, string $value, string $pattern, string $message): self
    {
        return $this->check($field, preg_match($pattern, $value) === 1, $message);
    }

    public function matches(string $field, string $value, string $other, string $message): self
    {
        return $this->check($field, hash_equals($other, $value), $message);
    }

    /** Número positivo con punto decimal (ej. "12.50"). Sin notación científica ni signos. */
    public function decimal(string $field, string $value, float $min, float $max, string $label, int $maxDecimals = 2): self
    {
        $ok = preg_match('/^\d{1,12}(\.\d{1,' . $maxDecimals . '})?$/', $value) === 1
            && (float) $value >= $min
            && (float) $value <= $max;

        return $this->check($field, $ok,
            sprintf('El campo %s debe ser un número entre %s y %s (máx. %d decimales).', $label, $min, $max, $maxDecimals));
    }

    /** Entero sin signo ni decimales. */
    public function integer(string $field, string $value, int $min, int $max, string $label): self
    {
        $ok = preg_match('/^\d{1,10}$/', $value) === 1
            && (int) $value >= $min
            && (int) $value <= $max;

        return $this->check($field, $ok,
            sprintf('El campo %s debe ser un entero entre %d y %d.', $label, $min, $max));
    }

    /** Longitud mínima, máxima (72 bytes = límite de bcrypt) y al menos una letra y un número. */
    public function password(string $field, string $value, int $min = 8, int $max = 72): self
    {
        $this->check($field, mb_strlen($value) >= $min,
            sprintf('La contraseña debe tener al menos %d caracteres.', $min));
        $this->check($field, strlen($value) <= $max,
            sprintf('La contraseña no puede superar %d caracteres.', $max));

        return $this->check($field,
            preg_match('/\p{L}/u', $value) === 1 && preg_match('/\d/', $value) === 1,
            'La contraseña debe incluir al menos una letra y un número.');
    }

    private function check(string $field, bool $ok, string $message): self
    {
        if (!$ok && !isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
        return $this;
    }
}
