<section class="card narrow">
    <h1><?= e($title) ?></h1>
    <?php if (!empty($errors['general'])): ?><div class="alert alert-error"><?= e($errors['general']) ?></div><?php endif; ?>
    <form method="post" action="index.php?r=<?= e($action) ?>">
        <input type="hidden" name="_token" value="<?= e($csrf) ?>">
        <?php if ($id): ?><input type="hidden" name="id" value="<?= (int) $id ?>"><?php endif; ?>
        <label>Nombre
            <input type="text" name="name" minlength="2" maxlength="150" value="<?= e($product['name'] ?? '') ?>" required>
            <?php if (isset($errors['name'])): ?><small class="err"><?= e($errors['name']) ?></small><?php endif; ?>
        </label>
        <label>Descripción
            <textarea name="description" rows="4" maxlength="2000"><?= e($product['description'] ?? '') ?></textarea>
            <?php if (isset($errors['description'])): ?><small class="err"><?= e($errors['description']) ?></small><?php endif; ?>
        </label>
        <label>Precio
            <input type="text" inputmode="decimal" name="price" value="<?= e($product['price'] ?? '') ?>" required>
            <?php if (isset($errors['price'])): ?><small class="err"><?= e($errors['price']) ?></small><?php endif; ?>
        </label>
        <label>Stock
            <input type="number" min="0" max="1000000" step="1" name="stock" value="<?= e($product['stock'] ?? 0) ?>" required>
            <?php if (isset($errors['stock'])): ?><small class="err"><?= e($errors['stock']) ?></small><?php endif; ?>
        </label>
        <div class="actions">
            <button class="btn">Guardar</button>
            <a class="btn btn-ghost" href="index.php">Cancelar</a>
        </div>
    </form>
</section>
