<?php
session_start();

// Si no hay carrito, crear uno vacío
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Eliminar un ítem
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    unset($_SESSION['carrito'][$id]);
    header("Location: carrito.php");
    exit();
}

// Vaciar todo
if (isset($_GET['vaciar'])) {
    $_SESSION['carrito'] = [];
    header("Location: carrito.php");
    exit();
}

$total = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2 class="mb-3">🛒 Mi Carrito</h2>

    <?php if (empty($_SESSION['carrito'])): ?>
        <div class="alert alert-warning">Tu carrito está vacío.</div>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['producto']) ?></td>
                        <td>$<?= htmlspecialchars($item['precio']) ?></td>
                        <td>
                            <a href="carrito.php?eliminar=<?= $id ?>" class="btn btn-sm btn-danger">Eliminar</a>
                        </td>
                    </tr>
                    <?php $total += $item['precio']; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Total: $<?= $total ?></h4>

        <a href="carrito.php?vaciar=1" class="btn btn-warning mt-2">Vaciar carrito</a>
        <button class="btn btn-success mt-2">Finalizar compra</button>
    <?php endif; ?>
</div>

</body>
</html>
