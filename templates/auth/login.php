<section class="card narrow">
    <h1>Iniciar sesión</h1>
    <?php if (!empty($errors['general'])): ?><div class="alert alert-error"><?= e($errors['general']) ?></div><?php endif; ?>
    <form method="post" action="index.php?r=login">
        <input type="hidden" name="_token" value="<?= e($csrf) ?>">
        <label>Correo
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>
        </label>
        <label>Contraseña
            <input type="password" name="password" required>
        </label>
        <button class="btn">Entrar</button>
    </form>
    <p class="muted">¿No tienes cuenta? <a href="index.php?r=register">Regístrate</a></p>
</section>
