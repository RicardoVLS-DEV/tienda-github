<div class="row-between">
    <h1>Productos</h1>
    <?php if ($user): ?><a class="btn" href="index.php?r=product.create">+ Nuevo producto</a><?php endif; ?>
</div>

<?php if (!$products): ?>
    <p class="muted">Aún no hay productos.</p>
<?php else: ?>
<div class="grid">
    <?php foreach ($products as $p): ?>
    <article class="card">
        <h3><?= e($p['name']) ?></h3>
        <p class="muted"><?= e($p['description'] ?: 'Sin descripción') ?></p>
        <p class="price">$<?= number_format((float) $p['price'], 2) ?></p>
        <p><small>Stock: <?= (int) $p['stock'] ?></small></p>
        <?php if ($user): ?>
        <div class="actions">
            <a class="btn btn-ghost" href="index.php?r=product.edit&id=<?= (int) $p['id'] ?>">Editar</a>
            <form method="post" action="index.php?r=product.delete" class="inline"
                  onsubmit="return confirm('¿Eliminar este producto?');">
                <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button class="btn btn-danger">Eliminar</button>
            </form>
        </div>
        <?php endif; ?>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
