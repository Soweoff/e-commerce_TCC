<?php
session_start();

require '../config/db.php';
require '../config/shipping.php';

header('Content-Type: application/json');

$zipCode = preg_replace('/\D/', '', $_POST['zip_code'] ?? '');
$cart = $_SESSION['cart'] ?? [];

if (strlen($zipCode) !== 8) {
    echo json_encode(['success' => false, 'message' => 'CEP inválido.']);
    exit;
}

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Carrinho vazio.']);
    exit;
}

$products = [];
$totalValue = 0;

foreach ($cart as $item) {
    $stmt = $pdo->prepare("
        SELECT id, name, price, weight, width, height, length
        FROM products
        WHERE id = ?
    ");

    $stmt->execute([$item['id']]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado.']);
        exit;
    }

    $qty = (int)$item['qty'];
    $totalValue += $product['price'] * $qty;

    $products[] = [
        'id' => (string)$product['id'],
        'width' => max(1, (float)$product['width']),
        'height' => max(1, (float)$product['height']),
        'length' => max(1, (float)$product['length']),
        'weight' => max(0.1, (float)$product['weight']),
        'insurance_value' => (float)$product['price'],
        'quantity' => $qty
    ];
}

$payload = [
    'from' => ['postal_code' => STORE_ZIP_CODE],
    'to' => ['postal_code' => $zipCode],
    'products' => $products,
    'options' => [
        'receipt' => false,
        'own_hand' => false,
        'insurance_value' => $totalValue
    ],
    'services' => ''
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => MELHOR_ENVIO_URL,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . MELHOR_ENVIO_TOKEN,
        'User-Agent: WienceStore (contato@wiencestore.com)'
    ],
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($error) {
    echo json_encode(['success' => false, 'message' => 'Erro na conexão com a API de frete.']);
    exit;
}

$data = json_decode($response, true);

if ($statusCode < 200 || $statusCode >= 300) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao calcular frete.',
        'status' => $statusCode,
        'response' => $data
    ]);
    exit;
}

$options = [];
$_SESSION['shipping_options'] = [];

foreach ($data as $service) {
    if (isset($service['error'])) {
        continue;
    }

    $id = (string)($service['id'] ?? '');

    if (!$id) {
        continue;
    }

    $option = [
        'id' => $id,
        'name' => $service['name'] ?? '',
        'company' => $service['company']['name'] ?? '',
        'price' => (float)($service['price'] ?? 0),
        'delivery_time' => (int)($service['delivery_time'] ?? 0)
    ];

    $options[] = $option;
    $_SESSION['shipping_options'][$id] = $option;
}

echo json_encode([
    'success' => true,
    'options' => $options
]);