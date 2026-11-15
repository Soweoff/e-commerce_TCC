<?php
require '../config/db.php';
include '../templates/header.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
        $_SESSION['success'] = "Carrinho limpo com sucesso.";
        header("Location: cart.php");
        exit;
    }

    if (isset($_POST['remove_index'])) {
        $index = (int) $_POST['remove_index'];

        if (isset($_SESSION['cart'][$index])) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            $_SESSION['success'] = "Produto removido do carrinho.";
        }

        header("Location: cart.php");
        exit;
    }

    if (isset($_POST['update_index'], $_POST['qty'])) {
        $index = (int) $_POST['update_index'];
        $qty = max(1, (int) $_POST['qty']);

        if (isset($_SESSION['cart'][$index])) {
            $productId = $_SESSION['cart'][$index]['id'];

            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if ($product) {
                if ($qty > $product['stock']) {
                    $qty = $product['stock'];
                    $_SESSION['error'] = "Quantidade ajustada ao limite do estoque.";
                }

                $_SESSION['cart'][$index]['qty'] = $qty;
                $_SESSION['success'] = "Quantidade atualizada.";
            }
        }

        header("Location: cart.php");
        exit;
    }

    if (isset($_POST['id'])) {
        $id = (int) $_POST['id'];

        $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if ($product && $product['stock'] > 0) {
            $found = false;

            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $product['id']) {
                    if ($item['qty'] < $product['stock']) {
                        $item['qty']++;
                        $_SESSION['success'] = "Quantidade do produto atualizada.";
                    } else {
                        $_SESSION['error'] = "Estoque máximo atingido para este produto.";
                    }

                    $found = true;
                    break;
                }
            }

            unset($item);

            if (!$found) {
                $_SESSION['cart'][] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'qty' => $qty ?? 1
                ];

                $_SESSION['success'] = "Produto adicionado ao carrinho.";
            }
        } else {
            $_SESSION['error'] = "Produto indisponível no estoque.";
        }

        header("Location: cart.php");
        exit;
    }
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= e($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= e($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Carrinho</h2>

    <?php if (!empty($cart)): ?>
        <form method="POST">
            <button type="submit" name="clear_cart" value="1" class="btn btn-outline-danger rounded-pill px-4">
                Limpar carrinho
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (empty($cart)): ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5 text-center">
            <h4 class="fw-bold">Seu carrinho está vazio</h4>
            <p class="text-muted">Adicione produtos para continuar sua compra.</p>
            <a href="index.php" class="btn btn-primary rounded-pill px-4 fw-bold">Ver produtos</a>
        </div>
    </div>

<?php else: ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Produto</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th class="text-end">Ação</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($cart as $index => $item): 
                        $subtotal = $item['price'] * $item['qty'];
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td class="fw-semibold"><?= e($item['name']) ?></td>

                            <td>
                                R$ <?= number_format($item['price'], 2, ',', '.') ?>
                            </td>

                            <td>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="update_index" value="<?= $index ?>">

                                    <input 
                                        type="number" 
                                        name="qty" 
                                        value="<?= $item['qty'] ?>" 
                                        min="1" 
                                        class="form-control form-control-sm" 
                                        style="width:80px"
                                    >

                                    <button class="btn btn-sm btn-outline-primary">
                                        Atualizar
                                    </button>
                                </form>
                            </td>

                            <td class="fw-bold">
                                R$ <?= number_format($subtotal, 2, ',', '.') ?>
                            </td>

                            <td class="text-end">
                                <form method="POST">
                                    <input type="hidden" name="remove_index" value="<?= $index ?>">

                                    <button class="btn btn-sm btn-outline-danger">
                                        Remover
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <p class="text-muted mb-1">Total da compra</p>

                <h3 class="fw-bold text-success mb-0">
                    R$ <?= number_format($total, 2, ',', '.') ?>
                </h3>
            </div>

            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
                    Continuar
                </a>

                <a href="checkout.php" class="btn btn-success rounded-pill px-4 fw-bold">
                    Finalizar compra
                </a>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php include '../templates/footer.php'; ?>