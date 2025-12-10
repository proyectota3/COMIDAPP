<?php
// Ejecutar el controlador
$data = require_once "../controlador/verCompras.php";

$facturas     = $data["facturas"];
$totalGastado = $data["totalGastado"];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMIDAPP - Mis compras</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../styles.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg bg-danger">
    <div class="container-fluid">

        <!-- LOGO -->
        <a class="navbar-brand text-white" href="../indexApp.php">
            <i class="fa-solid fa-burger"></i> ComidAPP
        </a -->

        <!-- BOTÓN RESPONSIVE -->
        <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- CONTENIDO DEL NAV -->
        <div class="collapse navbar-collapse" id="navbarContent">

            <!-- MENÚ IZQUIERDO -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <!-- Siempre visibles -->
                <li class="nav-item">
                    <a class="nav-link text-white" href="./contacto.php">Contacto</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white" href="./descargar.php">Descargar</a>
                </li>

                <!-- SOLO CLIENTE -->
                <?php if (isset($_SESSION['id']) && ($_SESSION['rol'] ?? null) == 3): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misCompras.php">Mis compras</a>
                    </li>
                <?php endif; ?>

                <!-- SOLO EMPRESA -->
                <?php if (isset($_SESSION['id']) && ($_SESSION['rol'] ?? null) == 2): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misVentas.php">Mis ventas</a>
                    </li>
                <?php endif; ?>

            </ul>

            <!-- BUSCADOR CENTRAL -->
            <form class="d-flex mx-auto position-relative" style="width: 35%;" role="search">
                <input class="form-control" id="buscar" type="search" placeholder="Buscar sucursal">
                <ul id="resultados" class="list-group position-absolute mt-2"
                    style="z-index: 1000; width: 100%;"></ul>
            </form>

            <!-- MENÚ DERECHA -->
            <ul class="navbar-nav d-flex align-items-center ms-3">

                <?php if (isset($_SESSION['id'])): ?>

                    <!-- PERFIL -->
                    <li class="nav-item me-3">
                        <a class="nav-link text-white d-flex align-items-center" href="./perfil.php">
                            <i class="fa-solid fa-user fa-lg me-2"></i>
                            <span><?= htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['user']); ?></span>
                        </a>
                    </li>

                    <!-- CERRAR SESIÓN -->
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="../logout.php">
                            Cerrar sesión
                        </a>
                    </li>

                <?php else: ?>

                    <!-- INICIAR SESIÓN -->
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="../loginApp.php">
                            Iniciar sesión
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>
    </div>
</nav>

<header class="bg-light py-4">
    <div class="container">
        <h1 class="h3 mb-0">
            <i class="fa-solid fa-receipt"></i> Mis compras
        </h1>
    </div>
</header>

<main class="container my-4">

    <!-- Mensaje de compra exitosa -->
    <?php if (isset($_GET['ok']) && $_GET['ok'] == 1): ?>
        <div class="alert alert-success">
            ¡Tu compra se registró correctamente!
        </div>
    <?php endif; ?>

    <!-- Si no hay compras -->
    <?php if (empty($facturas)): ?>

        <div class="alert alert-info">
            Aún no tienes compras realizadas.
        </div>

    <?php else: ?>

        <div class="mb-3 text-end">
            <strong>Total gastado:</strong>
            $<?php echo number_format($totalGastado, 0, ',', '.'); ?>
        </div>

        <!-- UNA CARD POR FACTURA -->
        <?php foreach ($facturas as $factura): ?>
            <div class="card mb-4 shadow-sm">

                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Factura N° <?php echo htmlspecialchars($factura['NumFactura']); ?></strong><br>
                        <small>Fecha: <?php echo htmlspecialchars($factura['Fecha']); ?></small><br>
                        <small>Local: <?php echo htmlspecialchars($factura['LocalNombre']); ?></small>
                    </div>
                    <div class="text-end">
                        <span><strong>Total:</strong></span><br>
                        <span class="fs-5">
                            $<?php echo number_format($factura['totalFactura'], 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Artículo</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio unitario</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($factura['lineas'] as $linea): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($linea['ArticuloNombre']); ?></td>
                                        <td class="text-center"><?php echo (int)$linea['Cantidad']; ?></td>
                                        <td class="text-end">
                                            $<?php echo number_format($linea['PrecioUnit'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-end">
                                            $<?php echo number_format($linea['Subtotal'], 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</main>

<footer class="footer bg-danger text-center text-white py-3">
    <div class="container">
        <p class="mb-0">© 2024 ComidApp. Derechos Reservados, Uruguay.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
