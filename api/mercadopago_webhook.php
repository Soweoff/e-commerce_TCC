<?php
require '../config/db.php';
require '../config/payment.php';

$input = json_decode(file_get_contents('php://input'), true);

$paymentId = null;

if (isset($input['type']) && $input['type'] === 'payment') {
    $paymentId = $input['data']['id'] ?? null;
}

if (!$paymentId && isset($_GET['type']) && $_GET['type'] === 'payment') {
    $paymentId = $_GET['data_id'] ?? $_GET['id'] ?? null;
}

if (!$paymentId) {
    http_response_code(200);
    echo 'No payment id';
    exit;
}

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/' . $paymentId,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . MP_ACCESS_TOKEN
    ],
]);

$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

$data = json_decode($response, true);

if ($statusCode < 200 || $statusCode >= 300) {
    http_response_code(200);
    echo 'Payment not found';
    exit;
}

$orderId = (int)($data['external_reference'] ?? 0);
$paymentStatus = $data['status'] ?? 'unknown';

if (!$orderId) {
    http_response_code(200);
    echo 'No order reference';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(200);
    echo 'Order not found';
    exit;
}

try {
    $pdo->beginTransaction();

    if ($paymentStatus === 'approved' && $order['status'] !== 'approved') {
        $stmt = $pdo->prepare("
            SELECT product_id, quantity
            FROM order_items
            WHERE order_id = ?
        ");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();

        foreach ($items as $item) {
            $stmt = $pdo->prepare("
                UPDATE products
                SET stock = stock - ?
                WHERE id = ? AND stock >= ?
            ");

            $stmt->execute([
                $item['quantity'],
                $item['product_id'],
                $item['quantity']
            ]);
        }

        $stmt = $pdo->prepare("
            UPDATE orders
            SET status = 'approved',
                payment_status = ?,
                payment_id = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $paymentStatus,
            $paymentId,
            $orderId
        ]);

    } elseif (in_array($paymentStatus, ['rejected', 'cancelled'])) {
        $stmt = $pdo->prepare("
            UPDATE orders
            SET status = 'rejected',
                payment_status = ?,
                payment_id = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $paymentStatus,
            $paymentId,
            $orderId
        ]);

    } else {
        $stmt = $pdo->prepare("
            UPDATE orders
            SET payment_status = ?,
                payment_id = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $paymentStatus,
            $paymentId,
            $orderId
        ]);
    }

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
}

http_response_code(200);
echo 'OK';