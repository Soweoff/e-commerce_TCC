<?php
require '../../config/db.php';
require '../../config/auth.php';
requireAdmin();

include '../../templates/header.php';

$messages = $pdo->query("
    SELECT 
        m.*, 
        o.customer_name, 
        o.customer_email,
        (
            SELECT COUNT(*) 
            FROM message_replies mr 
            WHERE mr.message_id = m.id
        ) AS total_replies
    FROM messages m
    INNER JOIN orders o ON o.id = m.order_id
    ORDER BY m.id DESC
")->fetchAll();
?>

<h2 class="fw-bold mb-4">Mensagens dos clientes</h2>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Assunto</th>
                        <th>Status</th>
                        <th>Respostas</th>
                        <th>Data</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td>#<?= e($msg['order_id']) ?></td>

                            <td>
                                <strong><?= e($msg['customer_name']) ?></strong><br>
                                <small class="text-muted"><?= e($msg['customer_email']) ?></small>
                            </td>

                            <td><?= e($msg['subject']) ?></td>

                            <td>
                                <?php if ($msg['status'] === 'open'): ?>
                                    <span class="badge bg-success">Aberta</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Fechada</span>
                                <?php endif; ?>
                            </td>

                            <td><?= e($msg['total_replies']) ?></td>

                            <td><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></td>

                            <td class="text-end">
                                <a href="/message_view.php?id=<?= $msg['id'] ?>" class="btn btn-primary btn-sm">
                                    Abrir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                Nenhuma mensagem encontrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>