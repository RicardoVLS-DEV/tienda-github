<?php
declare(strict_types=1);

namespace App\Support;

use PDO;

final class Database
{
    public static function connect(array $c): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $c['host'], $c['port'], $c['name'], $c['charset']);
        return new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
}
