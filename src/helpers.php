<?php
declare(strict_types=1);

/** Escapa texto para imprimirlo de forma segura en HTML. */
function e(mixed $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
