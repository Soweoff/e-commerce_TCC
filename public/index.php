<?php
require '../config/db.php';
include '../templates/header.php';

$q = trim($_GET['q'] ?? '');

if ($q) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$q%"]);
    $products = $stmt->fetchAll();
} else {
    $products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Produtos</h2>
</div>

<form method="GET" class="mb-4">
    <div class="input-group">
        <input 
            type="text" 
            name="q" 
            class="form-control" 
            placeholder="Buscar produto..."
            value="<?= e($q) ?>"
        >
        <button class="btn btn-dark">Buscar</button>
        <a href="index.php" class="btn btn-outline-secondary">Limpar</a>
    </div>
</form>

<?php if (empty($products)): ?>

    <div class="alert alert-warning">
        Nenhum produto encontrado.
    </div>

<?php else: ?>

    <div class="row g-4">
        <?php foreach ($products as $p): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden product-card">

                    <!-- Imagem -->
                    <div class="overflow-hidden">
                        <img 
                            src="<?= e($p['image']) ?>" 
                            class="card-img-top product-img"
                            alt="<?= e($p['name']) ?>"
                        >
                    </div>

                    <!-- Conteúdo -->
                    <div class="card-body p-4 d-flex flex-column">

                        <h5 class="card-title fw-bold"><?= e($p['name']) ?></h5>

                        <p class="card-text text-muted flex-grow-1">
                            <?= e($p['description']) ?>
                        </p>

                        <!-- Estoque -->
                        <?php if ($p['stock'] > 0): ?>
                            <span class="badge bg-success mb-3">
                                Em estoque: <?= $p['stock'] ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger mb-3">
                                Esgotado
                            </span>
                        <?php endif; ?>

                        <!-- Preço -->
                        <div class="d-flex justify-content-between align-items-center mt-auto">

                            <div>
                                <?php if ($p['is_promotion'] && $p['old_price'] > $p['price']): ?>

                                    <small class="text-muted text-decoration-line-through d-block">
                                        R$ <?= number_format($p['old_price'], 2, ',', '.') ?>
                                    </small>

                                    <span class="fs-5 fw-bold text-success">
                                        R$ <?= number_format($p['price'], 2, ',', '.') ?>
                                    </span>

                                    <span class="badge bg-danger ms-2">
                                        Promoção
                                    </span>

                                <?php else: ?>

                                    <span class="fs-5 fw-bold text-success">
                                        R$ <?= number_format($p['price'], 2, ',', '.') ?>
                                    </span>

                                <?php endif; ?>
                            </div>

                            <a href="product.php?id=<?= $p['id'] ?>" 
                               class="btn btn-primary rounded-pill px-4 fw-bold">
                                Ver
                            </a>

                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<?php include '../templates/footer.php'; ?>