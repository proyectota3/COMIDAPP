<?php
session_start();

// Crear carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Aseguramos que exista la variable de local del carrito
if (!isset($_SESSION['local_carrito'])) {
    $_SESSION['local_carrito'] = null;
}

$carrito = &$_SESSION['carrito'];

// ELIMINAR ÍTEM
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    if (isset($carrito[$id])) {
        unset($carrito[$id]);
        $carrito = array_values($carrito); // Reindexa el array
    }

    // 💡 Si después de eliminar quedó vacío, liberamos el local
    if (empty($carrito)) {
        $_SESSION['local_carrito'] = null;
    }

    header("Location: verCarrito.php");
    exit();
}

// VACIAR TODO
if (isset($_GET['vaciar'])) {
    $carrito = [];                      // vaciamos productos
    $_SESSION['local_carrito'] = null;  // 💡 liberamos el local del carrito
    header("Location: verCarrito.php");
    exit();
}

$total = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito | ComidAPP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">

<div class="container mt-4">

    <h2 class="mb-4 text-center">
        <i class="fa-solid fa-cart-shopping"></i> Mi Carrito
    </h2>

    <?php
    // Mensajes opcionales desde finalizarCompra.php
    if (isset($_GET['error'])) {
        if ($_GET['error'] === 'carrito_vacio') {
            echo '<div class="alert alert-warning text-center">Tu carrito está vacío.</div>';
        } elseif ($_GET['error'] === 'locales_distintos') {
            echo '<div class="alert alert-danger text-center">Solo podés comprar productos de un mismo local por pedido.</div>';
        } elseif ($_GET['error'] === 'bd') {
            echo '<div class="alert alert-danger text-center">Ocurrió un error al procesar la compra. Intenta nuevamente.</div>';
        } elseif ($_GET['error'] === 'sin_cliente') {
            echo '<div class="alert alert-warning text-center">No se encontró el cliente asociado a tu usuario.</div>';
        }
    }
    ?>

    <?php if (empty($carrito)): ?>

        <div class="alert alert-info text-center">
            Tu carrito está vacío.
        </div>

        <div class="text-center">
            <a href="../indexApp.php" class="btn btn-primary">
                Volver a comprar
            </a>
        </div>

    <?php else: ?>

        <table class="table table-bordered table-striped">
            <thead class="table-danger text-center">
                <tr>
                    <th>Producto</th>
                    <th>Local</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Subtotal</th>
                    <th>Eliminar</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($carrito as $id => $item): ?>
                    <?php
                        // Nombre del producto (soporta 'producto' o 'nombre')
                        $nombre    = $item['producto'] ?? ($item['nombre'] ?? 'Producto');
                        // Cantidad (si no existe, asumimos 1)
                        $cantidad  = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
                        // Precio unitario
                        $precio    = isset($item['precio']) ? (float)$item['precio'] : 0;
                        // Subtotal
                        $subtotal  = $precio * $cantidad;
                        $total    += $subtotal;
                    ?>
                    <tr class="text-center">
                        <td><?= htmlspecialchars($nombre); ?></td>
                        <td>
                            <?php 
                                if (isset($item['idLocal'])) {
                                    echo "Local #".htmlspecialchars($item['idLocal']);
                                } else {
                                    echo "-";
                                }
                            ?>
                        </td>
                        <td><?= $cantidad; ?></td>
                        <td>$<?= number_format($precio, 0, ',', '.'); ?></td>
                        <td>$<?= number_format($subtotal, 0, ',', '.'); ?></td>
                        <td>
                            <a href="verCarrito.php?eliminar=<?= $id ?>" class="btn btn-danger btn-sm">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-end">
            <h4>Total: <strong>$<?= number_format($total, 0, ',', '.') ?></strong></h4>
        </div>

        <div class="d-flex justify-content-between mt-4">

            <a href="../indexApp.php" class="btn btn-secondary">
                Seguir comprando
            </a>

            <a href="verCarrito.php?vaciar=1" class="btn btn-warning">
                Vaciar carrito
            </a>

            <!-- El submit solo dispara finalizarCompra.php, que toma los datos desde $_SESSION['carrito'] -->
            <form action="../controlador/finalizarCompra.php" method="POST">
                <button class="btn btn-success">
                    Finalizar compra
                </button>
            </form>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
