<?php
session_start();

require '../config/db.php';
require '../config/auth.php';

requireLogin();

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    die("Carrinho vazio");
}

$userId = $_SESSION['user']['id'];

$name = trim($_POST['customer_name'] ?? '');
$email = filter_var($_POST['customer_email'] ?? '', FILTER_VALIDATE_EMAIL);

$zipCode = trim($_POST['zip_code'] ?? '');
$street = trim($_POST['street'] ?? '');
$number = trim($_POST['number'] ?? '');
$neighborhood = trim($_POST['neighborhood'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = strtoupper(trim($_POST['state'] ?? ''));

$shippingServiceId = $_POST['shipping_service_id'] ?? '';
$shippingOptions = $_SESSION['shipping_options'] ?? [];
$shipping = $shippingOptions[$shippingServiceId] ?? null;

if (!$name || !$email) {
    die("Nome e e-mail são obrigatórios.");
}

if (!$zipCode || !$street || !$number || !$neighborhood || !$city || !$state) {
    die("Endereço completo é obrigatório.");
}

if (!$shipping) {
    die("Selecione uma opção de frete.");
}

try {
    $pdo->beginTransaction();

    $subtotal = 0;
    $validatedItems = [];

    foreach ($cart as $item) {
        $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
        $stmt->execute([$item['id']]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new Exception("Produto não encontrado.");
        }

        if ((int)$product['stock'] < (int)$item['qty']) {
            throw new Exception("Estoque insuficiente para o produto: " . $product['name']);
        }

        $subtotal += $product['price'] * $item['qty'];

        $validatedItems[] = [
            'id' => $product['id'],
            'price' => $product['price'],
            'qty' => $item['qty']
        ];
    }

    $shippingCost = (float)$shipping['price'];
    $total = $subtotal + $shippingCost;

    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id,
            customer_name,
            customer_email,
            subtotal,
            shipping_service_id,
            shipping_name,
            shipping_company,
            shipping_cost,
            shipping_delivery_time,
            total,
            status,
            payment_status,
            street,
            number,
            neighborhood,
            city,
            state,
            zip_code
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $name,
        $email,
        $subtotal,
        $shipping['id'],
        $shipping['name'],
        $shipping['company'],
        $shippingCost,
        $shipping['delivery_time'],
        $total,
        'pending_payment',
        'pending',
        $street,
        $number,
        $neighborhood,
        $city,
        $state,
        $zipCode
    ]);

    $orderId = $pdo->lastInsertId();

    foreach ($validatedItems as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $orderId,
            $item['id'],
            $item['qty'],
            $item['price']
        ]);
    }

    $pdo->commit();

    unset($_SESSION['cart']);
    unset($_SESSION['shipping_options']);

    header("Location: /pay_order.php?id=" . $orderId);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erro ao finalizar pedido: " . $e->getMessage());
}