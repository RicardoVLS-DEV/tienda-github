<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Controller\AuthController;
use App\Controller\ProductController;
use App\Repository\PdoProductRepository;
use App\Repository\PdoUserRepository;
use App\Service\AuthService;
use App\Service\ProductService;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\Session;
use App\Support\View;

$config  = require __DIR__ . '/../config/config.php';
$session = new Session();
$session->start();
$csrf = new Csrf($session);

try {
    $pdo = Database::connect($config['db']);
} catch (PDOException $e) {
    http_response_code(500);
    exit('No se pudo conectar a la base de datos. Revisa config/config.php');
}

$view = new View(__DIR__ . '/../templates', [
    'appName' => $config['app_name'],
    'user'    => $session->user(),
    'csrf'    => $csrf->token(),
    'flash'   => $session->pullFlash(),
]);

$auth     = new AuthController($view, $session, $csrf, new AuthService(new PdoUserRepository($pdo)));
$products = new ProductController($view, $session, $csrf, new ProductService(new PdoProductRepository($pdo)));

$routes = [
    'GET home'            => [$products, 'index'],
    'GET login'           => [$auth, 'showLogin'],
    'POST login'          => [$auth, 'login'],
    'GET register'        => [$auth, 'showRegister'],
    'POST register'       => [$auth, 'register'],
    'POST logout'         => [$auth, 'logout'],
    'GET product.create'  => [$products, 'create'],
    'POST product.store'  => [$products, 'store'],
    'GET product.edit'    => [$products, 'edit'],
    'POST product.update' => [$products, 'update'],
    'POST product.delete' => [$products, 'delete'],
];

$key = $_SERVER['REQUEST_METHOD'] . ' ' . ($_GET['r'] ?? 'home');
if (!isset($routes[$key])) {
    http_response_code(404);
    exit('404 - Página no encontrada');
}
[$controller, $method] = $routes[$key];
$controller->$method();
