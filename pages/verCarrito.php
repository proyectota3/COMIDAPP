<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * VISTA: verCarrito.php
 * --------------------
 * - Muestra el carrito del cliente.
 * - Permite eliminar un ítem por índice (GET eliminar).
 * - Permite vaciar el carrito completo (GET vaciar).
 * - Calcula cantidad total y total $.
 *
 * IMPORTANTE:
 * - Ahora "Confirmar compra" NO ejecuta finalizarCompra.php directamente.
 * - "Confirmar compra" lleva a pages/checkout.php (pantalla intermedia).
 */

// 1) Asegurar carrito en la sesión
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
$carrito = &$_SESSION['carrito'];

// 2) ELIMINAR ÍTEM (por índice)
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];

    if (isset($carrito[$id])) {
        unset($carrito[$id]);

        // Reindexar para que los índices queden prolijos
        $carrito = array_values($carrito);
    }

    // Si el carrito quedó vacío, limpiamos el local guardado
    if (empty($carrito)) {
        unset($_SESSION['local_carrito']);
    }

    header("Location: verCarrito.php");
    exit();
}

// 3) VACIAR TODO EL CARRITO
if (isset($_GET['vaciar'])) {
    $carrito = [];
    unset($_SESSION['local_carrito']); // ✅ clave: liberar el local del carrito
    header("Location: verCarrito.php");
    exit();
}

// 4) Calcular totales
$total = 0;
$cantidadTotal = 0;

foreach ($carrito as $item) {
    $precio   = isset($item['precio']) ? (float)$item['precio'] : 0;
    $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
    if ($cantidad < 1) $cantidad = 1;
    if ($precio < 0) $precio = 0;

    $subtotal = $precio * $cantidad;
    $total += $subtotal;
    $cantidadTotal += $cantidad;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito | ComidAPP</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tu CSS -->
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-danger navbar-dark">
    <div class="container-fluid">

        <!-- LOGO -->
        <a class="navbar-brand text-white" href="../indexApp.php">
            <i class="fa-solid fa-burger"></i> ComidAPP
        </a>

        <!-- BOTÓN RESPONSIVE -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- CONTENIDO DEL NAV -->
        <div class="collapse navbar-collapse" id="navbarContent">

            <!-- MENÚ IZQUIERDO -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-white" href="./contacto.php">Contacto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="./descargar.php">Descargar</a>
                </li>

                <!-- SOLO CLIENTE -->
                <?php if (isset($_SESSION['id']) && ($_SESSION['rol'] ?? null) == 3): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misCompras.php">
                            <i class="fa-solid fa-bag-shopping me-1"></i> Mis compras
                        </a>
                    </li>
                <?php endif; ?>

                <!-- SOLO EMPRESA -->
                <?php if (isset($_SESSION['id']) && ($_SESSION['rol'] ?? null) == 2): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misVentas.php">
                            <i class="fa-solid fa-chart-line me-1"></i> Mis ventas
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- BUSCADOR CENTRAL (opcional) -->
            <form class="d-flex mx-auto position-relative" style="width: 35%;" role="search">
                <input class="form-control" id="buscar" type="search" placeholder="Buscar sucursal">
                <ul id="resultados" class="list-group position-absolute mt-2"
                    style="z-index: 1000; width: 100%;"></ul>
            </form>

            <!-- MENÚ DERECHA -->
            <ul class="navbar-nav d-flex align-items-center ms-3">

                <?php if (isset($_SESSION['id'])): ?>
                    <li class="nav-item me-3">
                        <a class="nav-link text-white d-flex align-items-center" href="./perfil.php">
                            <i class="fa-solid fa-user fa-lg me-2"></i>
                            <span><?php echo htmlspecialchars($_SESSION['nombre'] ?? ($_SESSION['user'] ?? 'Usuario')); ?></span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="../logout.php">Cerrar sesión</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="../loginApp.php">Iniciar sesión</a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<header class="bg-light py-4">
    <div class="container">
        <h1 class="h3 mb-0">
            <i class="fa-solid fa-cart-shopping"></i> Mi carrito
        </h1>
    </div>
</header>

<main class="container my-4">

    <!-- Mensajes -->
    <?php if (isset($_GET['error']) && $_GET['error'] == 'locales_distintos'): ?>
        <div class="alert alert-warning">
            No podés mezclar productos de distintos locales en una misma compra.
        </div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'carrito_vacio'): ?>
        <div class="alert alert-info">Tu carrito está vacío.</div>
    <?php endif; ?>

    <?php if (empty($carrito)): ?>

        <div class="alert alert-info">
            Tu carrito está vacío. <a href="../indexApp.php" class="alert-link">Seguir comprando</a>
        </div>

    <?php else: ?>

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <div>
                <strong>Ítems en el carrito:</strong> <?php echo (int)$cantidadTotal; ?>
            </div>
            <div>
                <strong>Total:</strong>
                $<?php echo number_format($total, 0, ',', '.'); ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-danger">
                    <tr>
                        <th>#</th>
                        <th>Artículo</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Precio unitario</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($carrito as $idx => $item):
                    $nombre   = htmlspecialchars($item['nombre'] ?? 'Producto');
                    $precio   = isset($item['precio']) ? (float)$item['precio'] : 0;
                    $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
                    if ($cantidad < 1) $cantidad = 1;
                    if ($precio < 0) $precio = 0;
                    $subtotal = $precio * $cantidad;
                ?>
                    <tr>
                        <td><?php echo $idx + 1; ?></td>
                        <td><?php echo $nombre; ?></td>
                        <td class="text-center"><?php echo $cantidad; ?></td>
                        <td class="text-end">$<?php echo number_format($precio, 0, ',', '.'); ?></td>
                        <td class="text-end">$<?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                        <td class="text-center">
                            <a href="verCarrito.php?eliminar=<?php echo $idx; ?>" class="btn btn-sm btn-outline-danger">
                                <i class="fa-solid fa-trash"></i> Quitar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ACCIONES -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="../indexApp.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left"></i> Seguir comprando
            </a>

            <div class="d-flex gap-2">
                <a href="verCarrito.php?vaciar=1" class="btn btn-outline-danger">
                    <i class="fa-solid fa-trash-can"></i> Vaciar carrito
                </a>

                <!-- ✅ Ahora confirmación va a CHECKOUT -->
                <a href="./checkout.php" class="btn btn-success">
                    <i class="fa-solid fa-check"></i> Confirmar compra
                </a>
            </div>
        </div>

    <?php endif; ?>

</main>

<footer class="footer bg-danger text-center text-white py-3 mt-4">
    <div class="container">
        <p class="mb-0">© 2024 ComidApp. Derechos Reservados, Uruguay.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
