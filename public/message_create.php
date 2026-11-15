<?php
require '../config/db.php';
require '../config/auth.php';
requireLogin();

include '../templates/header.php';

if (isAdmin()) {
    header("Location: /admin/messages.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$orderId = (int)($_GET['order_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE id = ? AND user_id = ?
");

$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    echo '<div class="alert alert-danger">Pedido não encontrado.</div>';
    echo '<a href="/my_orders.php" class="btn btn-primary">Voltar</a>';
    include '../templates/footer.php';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$subject || !$message) {
        $error = "Preencha o assunto e a mensagem.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO messages (order_id, user_id, subject, status)
            VALUES (?, ?, ?, 'open')
        ");

        $stmt->execute([$orderId, $userId, $subject]);

        $messageId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO message_replies (message_id, user_id, message)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$messageId, $userId, $message]);

        header("Location: /message_view.php?id=" . $messageId);
        exit;
    }
}
?>

<h2 class="fw-bold mb-4">Nova mensagem - Pedido #<?= e($orderId) ?></h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Assunto</label>
                <input 
                    type="text" 
                    name="subject" 
                    class="form-control" 
                    placeholder="Ex: Dúvida sobre entrega"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Mensagem</label>
                <textarea 
                    name="message" 
                    class="form-control" 
                    rows="5" 
                    placeholder="Digite sua dúvida ou observação..."
                    required
                ></textarea>
            </div>

            <button class="btn btn-success rounded-pill px-4 fw-bold">
                Enviar mensagem
            </button>

            <a href="/order.php?id=<?= e($orderId) ?>" class="btn btn-outline-secondary rounded-pill px-4">
                Cancelar
            </a>
        </form>
    </div>
</div>

<?php include '../templates/footer.php'; ?>