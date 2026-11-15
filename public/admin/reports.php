<?php
require '../../config/db.php';
require '../../config/auth.php';
requireAdmin();

include '../../templates/header.php';

$salesByMonth = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS mes,
        COUNT(*) AS total_pedidos,
        SUM(total) AS total_vendido
    FROM orders
    GROUP BY mes
    ORDER BY mes DESC
")->fetchAll();

$salesByYear = $pdo->query("
    SELECT 
        YEAR(created_at) AS ano,
        COUNT(*) AS total_pedidos,
        SUM(total) AS total_vendido
    FROM orders
    GROUP BY ano
    ORDER BY ano DESC
")->fetchAll();

$bestCustomers = $pdo->query("
    SELECT 
        customer_name,
        customer_email,
        COUNT(id) AS total_pedidos,
        SUM(total) AS total_gasto
    FROM orders
    GROUP BY customer_email, customer_name
    HAVING total_pedidos > 1
    ORDER BY total_gasto DESC
")->fetchAll();
?>

<h2 class="fw-bold mb-4">Relatórios</h2>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h4 class="fw-bold mb-3">Vendas por mês</h4>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Mês</th>
                    <th>Total de pedidos</th>
                    <th>Total vendido</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($salesByMonth as $row): ?>
                    <tr>
                        <td><?= e($row['mes']) ?></td>
                        <td><?= e($row['total_pedidos']) ?></td>
                        <td>R$ <?= number_format($row['total_vendido'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h4 class="fw-bold mb-3">Vendas por ano</h4>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Ano</th>
                    <th>Total de pedidos</th>
                    <th>Total vendido</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($salesByYear as $row): ?>
                    <tr>
                        <td><?= e($row['ano']) ?></td>
                        <td><?= e($row['total_pedidos']) ?></td>
                        <td>R$ <?= number_format($row['total_vendido'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <h4 class="fw-bold mb-3">Clientes recorrentes</h4>
        <p class="text-muted">
            Clientes com mais de 1 pedido. Bons candidatos para campanhas, cupons e remarketing.
        </p>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Cliente</th>
                    <th>E-mail</th>
                    <th>Total de pedidos</th>
                    <th>Total gasto</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($bestCustomers as $customer): ?>
                    <tr>
                        <td><?= e($customer['customer_name']) ?></td>
                        <td><?= e($customer['customer_email']) ?></td>
                        <td><?= e($customer['total_pedidos']) ?></td>
                        <td>R$ <?= number_format($customer['total_gasto'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($bestCustomers)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Nenhum cliente recorrente ainda.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>