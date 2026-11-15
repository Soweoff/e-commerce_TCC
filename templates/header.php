<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$count = 0;

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['qty'];
    }
}

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Wience Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
     <style>
    .product-img {
        width: 100%;
        height: 220px;
        object-fit: contain;
        background-color: #f8f9fa;
        padding: 10px;
    }

    .card:hover .product-img {
        transform: scale(1.05);
        transition: 0.3s;
    }
    </style>
</head>

<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/index.php">
            Wience Store
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <div class="ms-auto d-flex gap-2 align-items-center mt-3 mt-lg-0">
                <a href="/index.php" class="btn btn-outline-light btn-sm">Produtos</a>

                <a href="/cart.php" class="btn btn-warning btn-sm fw-bold position-relative">
                    Carrinho

                    <?php if ($count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $count ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if (isset($_SESSION['user'])): ?>

                    <div class="dropdown">
                        <button class="btn btn-outline-info btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            Olá, <?= e($_SESSION['user']['name']) ?>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="/admin/dashboard.php">
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/admin/messages.php">
                                        Mensagens
                                    </a>
                                </li>
                            <?php else: ?>
                                <li>
                                    <a class="dropdown-item" href="/profile.php">
                                        Minha conta
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/my_orders.php">
                                        Meus pedidos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/messages.php">
                                        Minhas mensagens
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item text-danger" href="/logout.php">
                                    Sair
                                </a>
                            </li>
                        </ul>
                    </div>

                <?php else: ?>

                    <a href="/login.php" class="btn btn-outline-light btn-sm">
                        Entrar
                    </a>

                    <a href="/register.php" class="btn btn-primary btn-sm">
                        Cadastrar
                    </a>

                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if ($_SERVER['REQUEST_URI'] === '/index.php' || $_SERVER['REQUEST_URI'] === '/'): ?>
<header class="bg-white border-bottom mb-4">
    <div class="container py-4">
        <h1 class="fw-bold mb-1">Wience Store</h1>
        <p class="text-muted mb-0">Produtos selecionados com compra simples e rápida.</p>
    </div>
</header>
<?php endif; ?>

<main class="container flex-grow-1  py-4">