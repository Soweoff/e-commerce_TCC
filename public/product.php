<?php
require '../config/db.php';
include '../templates/header.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo '<div class="alert alert-danger mt-4">Produto não encontrado.</div>';
    echo '<a href="/index.php" class="btn btn-primary rounded-pill px-4">Voltar</a>';
    include '../templates/footer.php';
    exit;
}
?>

<div class="row g-4 align-items-start">
    <!-- IMAGEM -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white text-center">
            <img 
                src="<?= e($product['image']) ?>" 
                class="img-fluid"
                style="height: 420px; object-fit: contain; padding: 20px;"
                alt="<?= e($product['name']) ?>"
            >
        </div>
    </div>

    <!-- DETALHES -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">

                <h2 class="fw-bold mb-2"><?= e($product['name']) ?></h2>

                <p class="text-muted">
                    <?= e($product['description']) ?>
                </p>

                <!-- PREÇO -->
                <div class="my-4">
                    <h3 class="text-success fw-bold mb-0">
                        R$ <?= number_format($product['price'], 2, ',', '.') ?>
                    </h3>
                </div>

                <!-- ESTOQUE -->
                <?php if ($product['stock'] > 0): ?>
                    <div class="mb-4">
                        <span class="badge bg-success px-3 py-2">
                            Disponível: <?= e($product['stock']) ?> unidade(s)
                        </span>
                    </div>

                    <!-- FORM -->
                    <form method="POST" action="/cart.php">

                        <input type="hidden" name="id" value="<?= e($product['id']) ?>">

                        <!-- QUANTIDADE -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Quantidade</label>

                            <div class="input-group" style="max-width: 180px;">
                                <button type="button" class="btn btn-outline-secondary" onclick="decreaseQty()">-</button>

                                <input 
                                    type="number" 
                                    name="qty" 
                                    id="qty"
                                    value="1" 
                                    min="1" 
                                    max="<?= e($product['stock']) ?>"
                                    class="form-control text-center fw-bold"
                                >

                                <button type="button" class="btn btn-outline-secondary" onclick="increaseQty()">+</button>
                            </div>
                        </div>

                        <!-- BOTÕES -->
                        <div class="d-flex flex-wrap gap-2">

                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold">
                                Adicionar ao carrinho
                            </button>

                            <a href="/index.php" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                                Voltar
                            </a>

                        </div>

                    </form>

                <?php else: ?>

                    <div class="mb-4">
                        <span class="badge bg-danger px-3 py-2">
                            Produto esgotado
                        </span>
                    </div>

                    <button class="btn btn-secondary btn-lg rounded-pill px-5" disabled>
                        Indisponível
                    </button>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- SCRIPT QUANTIDADE -->
<script>
function increaseQty() {
    let input = document.getElementById('qty');
    let max = parseInt(input.max);

    if (parseInt(input.value) < max) {
        input.value++;
    }
}

function decreaseQty() {
    let input = document.getElementById('qty');

    if (parseInt(input.value) > 1) {
        input.value--;
    }
}
</script>

<?php include '../templates/footer.php'; ?>