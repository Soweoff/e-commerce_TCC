<?php
require '../../config/db.php';
require '../../config/auth.php';
requireAdmin();



$id = $_GET['id'];

$pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);

header("Location: products.php");
exit;?>
<a 
  href="product_delete.php?id=<?= $p['id'] ?>" 
  class="btn btn-danger btn-sm"
  onclick="return confirm('Tem certeza que deseja excluir este produto?')"
>
  Excluir
</a>