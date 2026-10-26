<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? $appName) ?> · <?= e($appName) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="nav">
    <a class="brand" href="index.php">🛍️ <?= e($appName) ?></a>
    <nav>
        <?php if ($user): ?>
            <span>Hola, <?= e($user['name']) ?></span>
            <form method="post" action="index.php?r=logout" class="inline">
                <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                <button class="btn btn-ghost">Salir</button>
            </form>
        <?php else: ?>
            <a href="index.php?r=login">Entrar</a>
            <a class="btn" href="index.php?r=register">Registrarse</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
    <?= $content ?>
</main>
</body>
</html>
