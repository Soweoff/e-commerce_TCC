<?php
require '../config/db.php';
require '../config/auth.php';
requireLogin();

include '../templates/header.php';

$userId = $_SESSION['user']['id'];

if (isAdmin()) {
    header("Location: /admin/messages.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT m.*, o.total
    FROM messages m
    INNER JOIN orders o ON o.id = m.order_id
    WHERE m.user_id = ?
    ORDER BY m.id DESC
");

$stmt->execute([$userId]);
$messages = $stmt->fetchAll();
?>

<h2 class="fw-bold mb-4">Minhas mensagens</h2>

<?php if (empty($messages)): ?>

    <div class="alert alert-warning">
        Você ainda não possui conversas abertas.
    </div>

<?php else: ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <table class="table align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Pedido</th>
                        <th>Assunto</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td>#<?= e($msg['order_id']) ?></td>
                            <td><?= e($msg['subject']) ?></td>
                            <td>
                                <?php if ($msg['status'] === 'open'): ?>
                                    <span class="badge bg-success">Aberta</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Fechada</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></td>
                            <td class="text-end">
                                <a href="/message_view.php?id=<?= $msg['id'] ?>" class="btn btn-primary btn-sm">
                                    Abrir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<?php include '../templates/footer.php'; ?>