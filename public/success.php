<?php
include '../templates/header.php';

$orderId = $_GET['order_id'] ?? null;
?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-5 text-center">
        <h2 class="fw-bold text-success">Pedido realizado com sucesso!</h2>
        <p class="text-muted">Seu pedido foi registrado no sistema.</p>

        <?php if ($orderId): ?>
            <p class="fw-bold">Número do pedido: #<?= e($orderId) ?></p>

            <a href="order.php?id=<?= e($orderId) ?>" class="btn btn-primary rounded-pill px-4">
                Ver pedido
            </a>
        <?php endif; ?>

        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
            Voltar para loja
        </a>
    </div>
</div>

<?php include '../templates/footer.php'; ?>