<?php
require '../../config/db.php';
require '../../config/auth.php';
requireAdmin();

include '../../templates/header.php';

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>

<h2 class="fw-bold mb-4">Produtos</h2>

<a href="product_create.php" class="btn btn-primary mb-3 rounded-pill px-4">
    Novo produto
</a>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= e($p['id']) ?></td>
                            <td><?= e($p['name']) ?></td>
                            <td>R$ <?= number_format($p['price'], 2, ',', '.') ?></td>
                            <td><?= e($p['stock']) ?></td>
                            <td class="text-end">
                                <a href="product_edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">
                                    Editar
                                </a>

                                <a 
                                    href="product_delete.php?id=<?= $p['id'] ?>" 
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Tem certeza que deseja excluir este produto?')"
                                >
                                    Excluir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Nenhum produto cadastrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>