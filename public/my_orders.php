<?php
require '../config/db.php';
require '../config/auth.php';
requireLogin();

include '../templates/header.php';

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY id DESC
");

$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>

<h2 class="fw-bold mb-4">Meus pedidos</h2>

<?php if (empty($orders)): ?>

    <div class="alert alert-warning">
        Você ainda não realizou nenhum pedido.
    </div>

<?php else: ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Pedido</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= e($order['id']) ?></td>

                                <td>
                                    R$ <?= number_format($order['total'], 2, ',', '.') ?>
                                </td>

                                <td>
                                    <span class="badge bg-warning text-dark">
                                        <?= e($order['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                </td>

                                <td class="text-end">
                                    <a href="order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                        Ver pedido
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php include '../templates/footer.php'; ?>