<?php
require '../config/db.php';
require '../config/auth.php';
requireLogin();

include '../templates/header.php';

$messageId = (int)($_GET['id'] ?? 0);
$userId = $_SESSION['user']['id'];

if (isAdmin()) {
    $stmt = $pdo->prepare("
        SELECT m.*, o.customer_name, o.customer_email
        FROM messages m
        INNER JOIN orders o ON o.id = m.order_id
        WHERE m.id = ?
    ");

    $stmt->execute([$messageId]);
} else {
    $stmt = $pdo->prepare("
        SELECT m.*, o.customer_name, o.customer_email
        FROM messages m
        INNER JOIN orders o ON o.id = m.order_id
        WHERE m.id = ? AND m.user_id = ?
    ");

    $stmt->execute([$messageId, $userId]);
}

$messageData = $stmt->fetch();

if (!$messageData) {
    echo '<div class="alert alert-danger">Mensagem não encontrada.</div>';
    echo '<a href="/messages.php" class="btn btn-primary">Voltar</a>';
    include '../templates/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply = trim($_POST['message'] ?? '');

    if ($reply && $messageData['status'] === 'open') {
        $stmt = $pdo->prepare("
            INSERT INTO message_replies (message_id, user_id, message)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$messageId, $userId, $reply]);

        header("Location: /message_view.php?id=" . $messageId);
        exit;
    }
}

$stmt = $pdo->prepare("
    SELECT mr.*, u.name, u.role
    FROM message_replies mr
    INNER JOIN users u ON u.id = mr.user_id
    WHERE mr.message_id = ?
    ORDER BY mr.id ASC
");

$stmt->execute([$messageId]);
$replies = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><?= e($messageData['subject']) ?></h2>
        <p class="text-muted mb-0">
            Pedido #<?= e($messageData['order_id']) ?> —
            Cliente: <?= e($messageData['customer_name']) ?>
        </p>
    </div>

    <?php if (isAdmin()): ?>
        <a href="/admin/messages.php" class="btn btn-outline-secondary rounded-pill px-4">
            Voltar
        </a>
    <?php else: ?>
        <a href="/messages.php" class="btn btn-outline-secondary rounded-pill px-4">
            Voltar
        </a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">

        <?php foreach ($replies as $reply): ?>
            <?php $isMine = $reply['user_id'] == $userId; ?>

            <div class="mb-3 d-flex <?= $isMine ? 'justify-content-end' : 'justify-content-start' ?>">
                <div 
                    class="p-3 rounded-4 <?= $isMine ? 'bg-primary text-white' : 'bg-light border' ?>" 
                    style="max-width: 75%;"
                >
                    <div class="small mb-1 <?= $isMine ? 'text-white-50' : 'text-muted' ?>">
                        <?= e($reply['name']) ?> 
                        <?= $reply['role'] === 'admin' ? '(Admin)' : '(Cliente)' ?> —
                        <?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?>
                    </div>

                    <div>
                        <?= nl2br(e($reply['message'])) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($replies)): ?>
            <div class="alert alert-warning">
                Nenhuma mensagem encontrada.
            </div>
        <?php endif; ?>

    </div>
</div>

<?php if ($messageData['status'] === 'open'): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-3">Responder</h4>

            <form method="POST">
                <textarea 
                    name="message" 
                    class="form-control mb-3" 
                    rows="4" 
                    placeholder="Digite sua resposta..."
                    required
                ></textarea>

                <button class="btn btn-success rounded-pill px-4 fw-bold">
                    Enviar resposta
                </button>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-secondary">
        Esta conversa está fechada.
    </div>
<?php endif; ?>

<?php include '../templates/footer.php'; ?>