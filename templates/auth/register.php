<section class="card narrow">
    <h1>Crear cuenta</h1>
    <form method="post" action="index.php?r=register">
        <input type="hidden" name="_token" value="<?= e($csrf) ?>">
        <label>Nombre
            <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>" required>
            <?php if (isset($errors['name'])): ?><small class="err"><?= e($errors['name']) ?></small><?php endif; ?>
        </label>
        <label>Correo
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>
            <?php if (isset($errors['email'])): ?><small class="err"><?= e($errors['email']) ?></small><?php endif; ?>
        </label>
        <label>Contraseña (mín. 8)
            <input type="password" name="password" minlength="8" maxlength="72" required>
            <?php if (isset($errors['password'])): ?><small class="err"><?= e($errors['password']) ?></small><?php endif; ?>
        </label>
        <label>Confirmar contraseña
            <input type="password" name="confirm" required>
            <?php if (isset($errors['confirm'])): ?><small class="err"><?= e($errors['confirm']) ?></small><?php endif; ?>
        </label>
        <button class="btn">Registrarme</button>
    </form>
    <p class="muted">¿Ya tienes cuenta? <a href="index.php?r=login">Inicia sesión</a></p>
</section>
