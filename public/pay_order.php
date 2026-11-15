<?php
require '../config/db.php';
require '../config/auth.php';
require '../config/payment.php';

requireLogin();

$orderId = (int)($_GET['id'] ?? 0);
$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    die("Pedido não encontrado.");
}

$stmt = $pdo->prepare("
    SELECT oi.*, p.name
    FROM order_items oi
    INNER JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$mpItems = [];

foreach ($items as $item) {
    $mpItems[] = [
        'id' => (string)$item['product_id'],
        'title' => $item['name'],
        'quantity' => (int)$item['quantity'],
        'currency_id' => 'BRL',
        'unit_price' => (float)$item['price']
    ];
}

$payload = [
    'items' => $mpItems,

    'payer' => [
        'name' => $order['customer_name'],
        'email' => $order['customer_email']
    ],

    'shipments' => [
        'mode' => 'not_specified',
        'cost' => (float)$order['shipping_cost']
    ],

    'external_reference' => (string)$order['id'],

    'back_urls' => [
        'success' => SITE_URL . '/payment_success.php?order_id=' . $order['id'],
        'failure' => SITE_URL . '/payment_failure.php?order_id=' . $order['id'],
        'pending' => SITE_URL . '/payment_pending.php?order_id=' . $order['id']
    ],

    'notification_url' => SITE_URL . '/api/mercadopago_webhook.php',

    'auto_return' => 'approved'
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . MP_ACCESS_TOKEN
    ],
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($error) {
    die("Erro de conexão com Mercado Pago: " . $error);
}

$data = json_decode($response, true);

if ($statusCode < 200 || $statusCode >= 300) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    exit;
}

$preferenceId = $data['id'] ?? null;

if ($preferenceId) {
    $stmt = $pdo->prepare("UPDATE orders SET preference_id = ? WHERE id = ?");
    $stmt->execute([$preferenceId, $orderId]);
}

header("Location: " . $data['init_point']);
exit;