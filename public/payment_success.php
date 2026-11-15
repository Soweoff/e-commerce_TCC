<?php
include '../templates/header.php';

$orderId = $_GET['order_id'] ?? null;
?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-5 text-center">
        <h2 class="fw-bold text-success">Pagamento recebido!</h2>
        <p class="text-muted">Seu pagamento está sendo confirmado pelo sistema.</p>

        <?php if ($orderId): ?>
            <a href="/order.php?id=<?= e($orderId) ?>" class="btn btn-primary rounded-pill px-4">
                Ver pedido
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include '../templates/footer.php'; ?>