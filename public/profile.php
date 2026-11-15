<?php
require '../config/db.php';
require '../config/auth.php';
requireLogin();

include '../templates/header.php';

$userId = $_SESSION['user']['id'];
$success = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $zipCode = trim($_POST['zip_code'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $number = trim($_POST['number'] ?? '');
    $neighborhood = trim($_POST['neighborhood'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = strtoupper(trim($_POST['state'] ?? ''));

    $stmt = $pdo->prepare("
        UPDATE users SET
            name = ?,
            phone = ?,
            zip_code = ?,
            street = ?,
            number = ?,
            neighborhood = ?,
            city = ?,
            state = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $name,
        $phone,
        $zipCode,
        $street,
        $number,
        $neighborhood,
        $city,
        $state,
        $userId
    ]);

    $_SESSION['user']['name'] = $name;

    $success = "Dados atualizados com sucesso.";

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
}
?>

<h2 class="fw-bold mb-4">Minha conta</h2>

<?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="POST">
            <h4 class="fw-bold mb-3">Dados pessoais</h4>

            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="name" value="<?= e($user['name']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" value="<?= e($user['email']) ?>" class="form-control" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="text" name="phone" value="<?= e($user['phone']) ?>" class="form-control">
            </div>

            <hr class="my-4">

            <h4 class="fw-bold mb-3">Endereço</h4>

            <div class="mb-3">
                <label class="form-label">CEP</label>
                <input type="text" name="zip_code" value="<?= e($user['zip_code']) ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Rua</label>
                <input type="text" name="street" value="<?= e($user['street']) ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Número</label>
                <input type="text" name="number" value="<?= e($user['number']) ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Bairro</label>
                <input type="text" name="neighborhood" value="<?= e($user['neighborhood']) ?>" class="form-control">
            </div>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Cidade</label>
                    <input type="text" name="city" value="<?= e($user['city']) ?>" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado</label>
                    <input type="text" name="state" value="<?= e($user['state']) ?>" class="form-control" maxlength="2">
                </div>
            </div>

            <button class="btn btn-success rounded-pill px-4 fw-bold">
                Salvar alterações
            </button>
        </form>
    </div>
</div>

<?php include '../templates/footer.php'; ?>