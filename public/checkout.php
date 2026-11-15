<?php
require '../config/db.php';
require '../config/auth.php';

requireLogin();

include '../templates/header.php';

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;

foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

if (empty($cart)) {
    echo '<div class="alert alert-warning">Seu carrinho está vazio.</div>';
    echo '<a href="/index.php" class="btn btn-primary rounded-pill px-4">Voltar para loja</a>';
    include '../templates/footer.php';
    exit;
}
?>

<h2 class="fw-bold mb-4">Checkout</h2>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="/create_order.php" id="checkoutForm">
                    <h4 class="fw-bold mb-3">Dados do comprador</h4>

                    <div class="mb-3">
                        <label class="form-label">Nome completo</label>
                        <input type="text" name="customer_name" class="form-control" value="<?= e($_SESSION['user']['name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="customer_email" class="form-control" value="<?= e($_SESSION['user']['email'] ?? '') ?>" required>
                    </div>

                    <hr class="my-4">

                    <h4 class="fw-bold mb-3">Endereço de entrega</h4>

                    <div class="mb-3">
                        <label class="form-label">CEP</label>
                        <input type="text" name="zip_code" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rua</label>
                        <input type="text" name="street" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Número</label>
                        <input type="text" name="number" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bairro</label>
                        <input type="text" name="neighborhood" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <input type="text" name="state" class="form-control" maxlength="2" required>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h4 class="fw-bold mb-3">Frete</h4>

                    <button type="button" id="calculateShippingBtn" class="btn btn-outline-primary rounded-pill px-4 mb-3">
                        Calcular frete
                    </button>

                    <div id="shippingResult" class="mb-3"></div>

                    <input type="hidden" name="shipping_service_id" id="shipping_service_id" required>

                    <button type="submit" id="finishOrderBtn" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold">
                        Ir para pagamento
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Resumo</h4>

                <?php foreach ($cart as $item): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?= e($item['name']) ?> x <?= e($item['qty']) ?></span>
                        <strong>R$ <?= number_format($item['price'] * $item['qty'], 2, ',', '.') ?></strong>
                    </div>
                <?php endforeach; ?>

                <hr>

                <div class="d-flex justify-content-between">
                    <span>Subtotal</span>
                    <strong>R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
                </div>

                <div class="d-flex justify-content-between mt-2">
                    <span>Frete</span>
                    <strong id="shippingCostText">Selecione</strong>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <h5>Total</h5>
                    <h5 class="text-success fw-bold" id="totalText">
                        R$ <?= number_format($subtotal, 2, ',', '.') ?>
                    </h5>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const subtotal = <?= json_encode((float)$subtotal) ?>;

document.getElementById('calculateShippingBtn').addEventListener('click', function () {
    const zip = document.querySelector('[name="zip_code"]').value;
    const result = document.getElementById('shippingResult');

    result.innerHTML = '<div class="alert alert-info">Calculando frete...</div>';

    fetch('/calculate_shipping.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'zip_code=' + encodeURIComponent(zip)
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            result.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            return;
        }

        let html = '<div class="list-group">';

        data.options.forEach(option => {
            html += `
                <label class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <input class="form-check-input me-2" type="radio" name="shipping_option_radio"
                            onclick="selectShipping('${option.id}', ${option.price})" required>

                        <strong>${option.company} - ${option.name}</strong><br>
                        <small>Prazo: ${option.delivery_time} dia(s)</small>
                    </div>

                    <strong>R$ ${Number(option.price).toFixed(2).replace('.', ',')}</strong>
                </label>
            `;
        });

        html += '</div>';
        result.innerHTML = html;
    })
    .catch(() => {
        result.innerHTML = '<div class="alert alert-danger">Erro ao calcular frete.</div>';
    });
});

function selectShipping(id, price) {
    document.getElementById('shipping_service_id').value = id;

    const total = subtotal + Number(price);

    document.getElementById('shippingCostText').innerText =
        'R$ ' + Number(price).toFixed(2).replace('.', ',');

    document.getElementById('totalText').innerText =
        'R$ ' + total.toFixed(2).replace('.', ',');
}

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    if (!document.getElementById('shipping_service_id').value) {
        e.preventDefault();
        alert('Calcule e selecione uma opção de frete.');
        return;
    }

    const btn = document.getElementById('finishOrderBtn');
    btn.disabled = true;
    btn.innerText = 'Processando...';
});
</script>

<?php include '../templates/footer.php'; ?>