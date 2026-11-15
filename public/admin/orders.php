<?php
require '../../config/db.php';
require '../../config/auth.php';
requireAdmin();

include '../../templates/header.php';

$orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
?>

<h2 class="fw-bold mb-4">Pedidos</h2>

<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Total</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= $o['id'] ?></td>
                <td><?= $o['customer_name'] ?></td>
                <td>R$ <?= number_format($o['total'],2,',','.') ?></td>
                <td><?= $o['status'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../../templates/footer.php'; ?>