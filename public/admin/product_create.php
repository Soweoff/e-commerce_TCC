<?php
require '../../config/db.php';
require '../../config/auth.php';
requireAdmin();

include '../../templates/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPrice = $_POST['old_price'] !== '' ? $_POST['old_price'] : null;
    $isPromotion = isset($_POST['is_promotion']) ? 1 : 0;

    $stmt = $pdo->prepare("
       INSERT INTO products (name, description, price, image, stock, old_price, is_promotion, weight, width, height, length, capacity)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
        $isPromotion
    ]);

    header("Location: products.php");
    exit;
}
?>

<h2>Novo Produto</h2>

<form method="POST">
    <input class="form-control mb-2" name="name" placeholder="Nome" required>

    <textarea class="form-control mb-2" name="description" placeholder="Descrição" required></textarea>

    <input class="form-control mb-2" type="number" step="0.01" name="price" placeholder="Preço atual" required>

    <input class="form-control mb-2" name="image" placeholder="URL imagem" required>

    <input class="form-control mb-2" type="number" name="stock" placeholder="Estoque" required>

    <input class="form-control mb-2" type="number" step="0.01" name="old_price" placeholder="Preço antigo">

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_promotion" value="1">
        <label class="form-check-label">
            Produto em promoção
        </label>
    </div>

    <hr>

    <h4 class="fw-bold mt-4">Medidas para frete</h4>

    <input class="form-control mb-2" type="number" step="0.001" name="weight" placeholder="Peso em kg. Ex: 1.200" required>

    <input class="form-control mb-2" type="number" step="0.01" name="width" placeholder="Largura/diâmetro em cm. Ex: 24" required>

    <input class="form-control mb-2" type="number" step="0.01" name="height" placeholder="Altura em cm. Ex: 14.5" required>

    <input class="form-control mb-2" type="number" step="0.01" name="length" placeholder="Comprimento em cm. Ex: 24" required>

    <input class="form-control mb-2" name="capacity" placeholder="Capacidade. Ex: 5,50 litros">

    <button class="btn btn-success">Salvar</button>
</form>

<?php include '../../templates/footer.php'; ?>