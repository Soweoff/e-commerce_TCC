<?php
require '../config/db.php';
include '../templates/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || strlen($password) < 6) {
        $error = "Preencha todos os campos. A senha deve ter no mínimo 6 caracteres.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role)
                VALUES (?, ?, ?, 'cliente')
            ");

            $stmt->execute([$name, $email, $hash]);

            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $error = "Este e-mail já está cadastrado.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h2 class="fw-bold mb-4">Criar conta</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button class="btn btn-primary w-100 rounded-pill fw-bold">
                        Cadastrar
                    </button>
                </form>

                <p class="text-center mt-3 mb-0">
                    Já tem conta? <a href="login.php">Entrar</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>