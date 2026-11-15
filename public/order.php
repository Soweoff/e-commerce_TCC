<?php
require '../config/db.php';
require '../config/auth.php';

requireLogin();

include '../templates/header.php';

$id = (int)($_GET['id'] ?? 0);

if (isAdmin()) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user']['id']]);
}

$order = $stmt->fetch();

if (!$order) {
    echo '<div class="alert alert-danger mt-4">Pedido não encontrado.</div>';
    echo '<a href="/index.php" class="btn btn-primary rounded-pill px-4">Voltar</a>';
    include '../templates/footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT oi.*, p.name 
    FROM order_items oi
    INNER JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$subtotal = $order['subtotal'] ?? 0;
$shippingCost = $order['shipping_cost'] ?? 0;
$total = $order['total'] ?? 0;
?>

<div class="mt-4 mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="fw-bold mb-1">Pedido #<?= e($order['id']) ?></h2>
        <p class="text-muted mb-0">Detalhes completos do pedido</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <?php if (!isAdmin()): ?>
            <a href="/message_create.php?order_id=<?= e($order['id']) ?>" class="btn btn-primary rounded-pill px-4 fw-bold">
                Falar sobre este pedido
            </a>

            <a href="/my_orders.php" class="btn btn-outline-secondary rounded-pill px-4">
                Meus pedidos
            </a>
        <?php else: ?>
            <a href="/admin/messages.php" class="btn btn-outline-primary rounded-pill px-4">
                Mensagens
            </a>

            <a href="/admin/orders.php" class="btn btn-outline-secondary rounded-pill px-4">
                Voltar aos pedidos
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Dados do pedido</h4>

                <p class="mb-2">
                    <strong>Cliente:</strong> <?= e($order['customer_name']) ?>
                </p>

                <p class="mb-2">
                    <strong>E-mail:</strong> <?= e($order['customer_email']) ?>
                </p>

                <p class="mb-2">
                    <strong>Status do pedido:</strong>

                    <?php if ($order['status'] === 'approved'): ?>
                        <span class="badge bg-success">Aprovado</span>
                    <?php elseif ($order['status'] === 'pending_payment'): ?>
                        <span class="badge bg-warning text-dark">Aguardando pagamento</span>
                    <?php elseif ($order['status'] === 'pending'): ?>
                        <span class="badge bg-warning text-dark">Pendente</span>
                    <?php elseif ($order['status'] === 'rejected'): ?>
                        <span class="badge bg-danger">Recusado</span>
                    <?php else: ?>
                        <span class="badge bg-secondary"><?= e($order['status']) ?></span>
                    <?php endif; ?>
                </p>

                <?php if (!empty($order['payment_status'])): ?>
                    <p class="mb-2">
                        <strong>Status do pagamento:</strong>

                        <?php if ($order['payment_status'] === 'approved'): ?>
                            <span class="badge bg-success">Pago</span>
                        <?php elseif ($order['payment_status'] === 'pending'): ?>
                            <span class="badge bg-warning text-dark">Pendente</span>
                        <?php elseif ($order['payment_status'] === 'rejected'): ?>
                            <span class="badge bg-danger">Recusado</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= e($order['payment_status']) ?></span>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($order['payment_id'])): ?>
                    <p class="mb-2">
                        <strong>ID do pagamento:</strong> <?= e($order['payment_id']) ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($order['created_at'])): ?>
                    <p class="mb-0">
                        <strong>Data:</strong>
                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Endereço de entrega</h4>

                <p class="mb-2">
                    <strong>CEP:</strong> <?= e($order['zip_code']) ?>
                </p>

                <p class="mb-2">
                    <strong>Endereço:</strong>
                    <?= e($order['street']) ?>, <?= e($order['number']) ?>
                </p>

                <p class="mb-2">
                    <strong>Bairro:</strong> <?= e($order['neighborhood']) ?>
                </p>

                <p class="mb-0">
                    <strong>Cidade/Estado:</strong>
                    <?= e($order['city']) ?> - <?= e($order['state']) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($order['shipping_cost'])): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-3">Frete</h4>

            <p class="mb-2">
                <strong>Transportadora:</strong>
                <?= e($order['shipping_company']) ?>
            </p>

            <p class="mb-2">
                <strong>Serviço:</strong>
                <?= e($order['shipping_name']) ?>
            </p>

            <?php if (!empty($order['shipping_delivery_time'])): ?>
                <p class="mb-2">
                    <strong>Prazo:</strong>
                    <?= e($order['shipping_delivery_time']) ?> dia(s)
                </p>
            <?php endif; ?>

            <p class="mb-0">
                <strong>Valor do frete:</strong>
                R$ <?= number_format($shippingCost, 2, ',', '.') ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-3">Itens do pedido</h4>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Produto</th>
                        <th>Qtd</th>
                        <th>Preço</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['name']) ?></td>
                            <td><?= e($item['quantity']) ?></td>
                            <td>
                                R$ <?= number_format($item['price'], 2, ',', '.') ?>
                            </td>
                            <td>
                                R$ <?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Nenhum item encontrado neste pedido.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="text-end mt-4">
            <?php if (!empty($order['subtotal'])): ?>
                <p class="mb-1">
                    Subtotal: R$ <?= number_format($subtotal, 2, ',', '.') ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($order['shipping_cost'])): ?>
                <p class="mb-1">
                    Frete: R$ <?= number_format($shippingCost, 2, ',', '.') ?>
                </p>
            <?php endif; ?>

            <p class="text-muted mb-1">Total do pedido</p>

            <h3 class="text-success fw-bold mb-0">
                R$ <?= number_format($total, 2, ',', '.') ?>
            </h3>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>