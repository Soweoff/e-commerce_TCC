<?php
require '../../config/db.php';
require '../../config/auth.php';
requireAdmin();

include '../../templates/header.php';

$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'cliente'")->fetchColumn();

$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders")->fetchColumn();

$latestOrders = $pdo->query("
    SELECT id, customer_name, customer_email, total, status, created_at
    FROM orders
    ORDER BY id DESC
    LIMIT 5
")->fetchAll();
?>

<h2 class="fw-bold mb-4">Painel Administrativo</h2>

<div class="row g-4 mb-4">

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <p class="text-muted mb-1">Produtos</p>
                <h2 class="fw-bold"><?= $totalProducts ?></h2>
                <a href="products.php" class="btn btn-primary btn-sm rounded-pill">
                    Gerenciar
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <p class="text-muted mb-1">Pedidos</p>
                <h2 class="fw-bold"><?= $totalOrders ?></h2>
                <a href="orders.php" class="btn btn-success btn-sm rounded-pill">
                    Ver pedidos
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <p class="text-muted mb-1">Clientes</p>
                <h2 class="fw-bold"><?= $totalUsers ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-dark text-white">
            <div class="card-body p-4">
                <p class="text-white-50 mb-1">Faturamento</p>
                <h2 class="fw-bold">
                    R$ <?= number_format($totalRevenue, 2, ',', '.') ?>
                </h2>
                <a href="reports.php" class="btn btn-light btn-sm rounded-pill">
                    Relatórios
                </a>
            </div>
        </div>
    </div>

</div>

<div class="row g-4">

    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Últimos pedidos</h4>
                    <a href="orders.php" class="btn btn-outline-dark btn-sm rounded-pill">
                        Ver todos
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($latestOrders as $order): ?>
                                <tr>
                                    <td>#<?= e($order['id']) ?></td>
                                    <td>
                                        <strong><?= e($order['customer_name']) ?></strong><br>
                                        <small class="text-muted"><?= e($order['customer_email']) ?></small>
                                    </td>
                                    <td>
                                        R$ <?= number_format($order['total'], 2, ',', '.') ?>
                                    </td>
                                    <td>
                                        <?php if ($order['status'] === 'approved'): ?>
                                            <span class="badge bg-success">Aprovado</span>
                                        <?php elseif ($order['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pendente</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= e($order['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($latestOrders)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Nenhum pedido registrado ainda.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h4 class="fw-bold">Ações rápidas</h4>

                <div class="d-grid gap-2 mt-3">
                    <a href="product_create.php" class="btn btn-primary rounded-pill">
                        Novo produto
                    </a>

                    <a href="products.php" class="btn btn-outline-primary rounded-pill">
                        Gerenciar produtos
                    </a>

                    <a href="orders.php" class="btn btn-outline-success rounded-pill">
                        Ver pedidos
                    </a>

                    <a href="reports.php" class="btn btn-outline-dark rounded-pill">
                        Relatórios
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include '../../templates/footer.php'; ?>