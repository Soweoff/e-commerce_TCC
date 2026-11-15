<?php
require '../../config/db.php';
require '../../config/auth.php';
requireAdmin();

include '../../templates/header.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo '<div class="alert alert-danger">Produto não encontrado.</div>';
    include '../../templates/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPrice = $_POST['old_price'] !== '' ? $_POST['old_price'] : null;
    $isPromotion = isset($_POST['is_promotion']) ? 1 : 0;

    $stmt = $pdo->prepare("
        UPDATE products 
        SET name=?, description=?, price=?, image=?, stock=?, old_price=?, is_promotion=?, weight=?, width=?, height=?, length=?, capacity=?
        WHERE id=?
    ");

    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['image'],
        $_POST['stock'],
        $_POST['weight'],
        $_POST['width'],
        $_POST['height'],
        $_POST['length'],
        $_POST['capacity'],
        $oldPrice,
        $isPromotion,
        $id
    ]);

    header("Location: products.php");
    exit;
}
?>

<h2>Editar Produto</h2>

<form method="POST">
    <input class="form-control mb-2" name="name" value="<?= e($product['name']) ?>" required>

    <textarea class="form-control mb-2" name="description" required><?= e($product['description']) ?></textarea>

    <input class="form-control mb-2" type="number" step="0.01" name="price" value="<?= e($product['price']) ?>" required>

    <input class="form-control mb-2" name="image" value="<?= e($product['image']) ?>" required>

    <input class="form-control mb-2" type="number" name="stock" value="<?= e($product['stock']) ?>" required>

    <input class="form-control mb-2" type="number" step="0.01" name="old_price" value="<?= e($product['old_price']) ?>" placeholder="Preço antigo">

    <div class="form-check mb-3">
        <input 
            class="form-check-input" 
            type="checkbox" 
            name="is_promotion" 
            value="1"
            <?= $product['is_promotion'] ? 'checked' : '' ?>
        >

        <label class="form-check-label">
            Produto em promoção
        </label>
    </div>

    <hr>

    <h4 class="fw-bold mt-4">Medidas para frete</h4>

    <input class="form-control mb-2" type="number" step="0.001" name="weight" value="<?= e($product['weight']) ?>" placeholder="Peso em kg" required>

    <input class="form-control mb-2" type="number" step="0.01" name="width" value="<?= e($product['width']) ?>" placeholder="Largura/diâmetro em cm" required>

    <input class="form-control mb-2" type="number" step="0.01" name="height" value="<?= e($product['height']) ?>" placeholder="Altura em cm" required>

    <input class="form-control mb-2" type="number" step="0.01" name="length" value="<?= e($product['length']) ?>" placeholder="Comprimento em cm" required>

    <input class="form-control mb-2" name="capacity" value="<?= e($product['capacity'] ?? '') ?>" placeholder="Capacidade. Ex: 5,50 litros">

    <button class="btn btn-warning">Atualizar</button>
</form>

<?php include '../../templates/footer.php'; ?>