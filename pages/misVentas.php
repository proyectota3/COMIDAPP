<?php
/**
 * pages/misVentas.php
 * -------------------------------------------------------
 * VISTA para EMPRESA (rol 2)
 * - Muestra pedidos pendientes (Valida=0)
 * - Muestra pedidos entregados (Valida=1)
 * - Permite marcar como entregado llamando a:
 *      ../controlador/confirmarPedido.php
 */

// ✅ 0) Siempre iniciar sesión antes de usar $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ 1) Seguridad: solo EMPRESA (rol = 2)
if (!isset($_SESSION['id'], $_SESSION['rol']) || (int)$_SESSION['rol'] !== 2) {
    header("Location: ../loginApp.php");
    exit();
}

// ✅ 2) Ejecutar controlador (SOLO LECTURA)
// Este controller debe devolver:
//   ['pendientes' => [...], 'entregados' => [...]]
$data = require_once "../controlador/verVentas.php";

$pendientes = $data['pendientes'] ?? [];
$entregados = $data['entregados'] ?? [];

// ✅ 3) Mensajes para feedback (cuando confirmas entrega)
$ok     = $_GET['ok'] ?? null;
$error  = $_GET['error'] ?? null;

// Para debug: si confirmarPedido.php redirige con nf/loc en la url
$nf     = $_GET['nf'] ?? null;
$loc    = $_GET['loc'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMIDAPP - Mis ventas</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tu CSS -->
    <link href="../styles.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">

<!-- ================= NAVBAR (TU NAV TAL CUAL) ================= -->
<nav class="navbar navbar-expand-lg bg-danger navbar-dark">
    <div class="container-fluid">

        <!-- LOGO -->
        <a class="navbar-brand text-white" href="../indexApp.php">
            <i class="fa-solid fa-burger"></i> ComidAPP
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <!-- MENÚ IZQUIERDO -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <!-- Siempre visibles -->
                <li class="nav-item">
                    <a class="nav-link text-white" href="./contacto.php">Contacto</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white" href="./descargar.php">Descargar</a>
                </li>

                <!-- Cliente: Mis compras (rol = 3) -->
                <?php if (isset($_SESSION['id'], $_SESSION['rol']) && (int)$_SESSION['rol'] === 3): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misCompras.php">
                            <i class="fa-solid fa-bag-shopping me-1"></i> Mis compras
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Empresa: Mis locales + Mis ventas (rol = 2) -->
                <?php if (isset($_SESSION['id'], $_SESSION['rol']) && (int)$_SESSION['rol'] === 2): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misLocales.php">Mis locales</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white active" href="./misVentas.php">
                            <i class="fa-solid fa-chart-line me-1"></i> Mis ventas
                        </a>
                    </li>
                <?php endif; ?>

            </ul>

            <!-- BUSCADOR CENTRADO -->
            <form class="d-flex mx-auto position-relative" style="width: 35%;" role="search">
                <input class="form-control" id="buscar" type="search" placeholder="Buscar sucursal" aria-label="Search">
                <ul id="resultados" class="list-group position-absolute mt-2"
                    style="z-index: 1000; width: 100%;"></ul>
            </form>

            <!-- ZONA DERECHA -->
            <ul class="navbar-nav d-flex align-items-center ms-3">

                <!-- 🔽 CARRITO (solo clientes) -->
                <?php if (isset($_SESSION['id'], $_SESSION['rol']) && (int)$_SESSION['rol'] === 3): ?>

                    <?php
                    $carrito = $_SESSION['carrito'] ?? [];
                    $cantidadCarrito = array_sum(array_map(fn($i)=> (int)($i['cantidad'] ?? 1), $carrito));
                    ?>

                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative text-white dropdown-toggle" href="#"
                           id="carritoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-cart-shopping fa-lg"></i>

                            <?php if ($cantidadCarrito > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                    <?php echo $cantidadCarrito; ?>
                                </span>
                            <?php endif; ?>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end p-4 shadow-lg"
                             aria-labelledby="carritoDropdown"
                             style="width: 420px; border-radius: 16px;">

                            <h5 class="mb-3 text-center">
                                <i class="fa-solid fa-cart-shopping"></i> Mi carrito
                            </h5>

                            <?php if (empty($carrito)): ?>
                                <p class="text-center text-muted mb-0">El carrito está vacío.</p>
                            <?php else: ?>

                                <ul class="list-group mb-3" style="border-radius: 12px; overflow: hidden;">
                                    <?php foreach ($carrito as $idx => $item):
                                        $nombre   = htmlspecialchars($item['nombre'] ?? 'Producto');
                                        $precio   = (float)($item['precio'] ?? 0);
                                        $cantidad = (int)($item['cantidad'] ?? 1);
                                        if ($cantidad < 1) $cantidad = 1;
                                    ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo $nombre; ?></strong><br>
                                                <small>$<?php echo $precio; ?> x <?php echo $cantidad; ?></small>
                                            </div>

                                            <a href="./verCarrito.php?eliminar=<?php echo $idx; ?>" class="text-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <div class="d-grid gap-2">
                                    <a href="./verCarrito.php" class="btn btn-primary">Ver carrito</a>
                                    <a href="./checkout.php" class="btn btn-success">Ir a checkout</a>
                                    <a href="./verCarrito.php?vaciar=1" class="btn btn-outline-danger">Vaciar carrito</a>
                                </div>

                            <?php endif; ?>
                        </div>
                    </li>

                <?php endif; ?>

                <!-- 🔽 DROPDOWN USUARIO -->
                <?php if (isset($_SESSION['id'])): ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#"
                           id="usuarioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user fa-lg me-1"></i>
                            <span><?php echo htmlspecialchars($_SESSION['nombre'] ?? ($_SESSION['user'] ?? 'Mi cuenta')); ?></span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="usuarioDropdown">
                            <li>
                                <a class="dropdown-item" href="./perfil.php">
                                    <i class="fa-solid fa-id-card me-2"></i> Perfil
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="./infoUsuario.php">
                                    <i class="fa-solid fa-circle-info me-2"></i> Información
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item text-danger" href="../logout.php">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar sesión
                                </a>
                            </li>
                        </ul>
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

<!-- ================= HEADER ================= -->
<header class="bg-light py-4">
    <div class="container">
        <h1 class="h3 mb-0">
            <i class="fa-solid fa-chart-line"></i> Mis ventas
        </h1>
        <p class="text-muted mb-0">Pedidos realizados por clientes</p>
    </div>
</header>

<!-- ================= CONTENIDO ================= -->
<main class="container my-4 flex-grow-1">

    <!-- ✅ Alertas de feedback -->
    <?php if ($ok == 1): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check me-1"></i>
            Pedido marcado como <strong>entregado</strong>.
        </div>
    <?php endif; ?>

    <?php if ($error === "datos"): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Faltan datos para confirmar el pedido.
        </div>
    <?php elseif ($error === "no_update"): ?>
        <div class="alert alert-warning">
            <i class="fa-solid fa-circle-info me-1"></i>
            No se actualizó ningún pedido (capaz ya estaba entregado o no coincide).
            <?php if ($nf && $loc): ?>
                <div class="small mt-1">
                    Debug: NumFactura=<?php echo htmlspecialchars($nf); ?> | IDLoc=<?php echo htmlspecialchars($loc); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ================= PENDIENTES ================= -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0">
            <i class="fa-solid fa-clock"></i> Pendientes
        </h4>
        <span class="badge bg-warning text-dark">
            <?php echo count($pendientes); ?>
        </span>
    </div>

    <?php if (empty($pendientes)): ?>
        <div class="alert alert-info">No hay pedidos pendientes.</div>
    <?php else: ?>
        <?php foreach ($pendientes as $p): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Factura #<?php echo (int)$p['NumFactura']; ?></strong>
                        <span class="badge bg-warning text-dark ms-2">Pendiente</span><br>
                        <small class="text-muted">
                            Fecha: <?php echo htmlspecialchars($p['Fecha'] ?? ''); ?> |
                            Local: <?php echo htmlspecialchars($p['LocalNombre'] ?? ''); ?>
                        </small>
                    </div>
                    <div class="text-end">
                        <strong>Total</strong>
                        <div class="fs-5">
                            $<?php echo number_format((float)($p['Total'] ?? 0), 0, ',', '.'); ?>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <span class="badge bg-light text-dark me-2">
                        Pago: <?php echo htmlspecialchars($p['FormaPago'] ?? '-'); ?>
                    </span>

                    <span class="badge bg-light text-dark">
                        Delivery: <?php echo ((int)($p['Delivery'] ?? 0) === 1) ? 'Sí' : 'No'; ?>
                    </span>

                    <?php if (!empty($p['DireccionEntrega'])): ?>
                        <div class="small mt-2">
                            <strong>Dirección:</strong>
                            <?php echo htmlspecialchars($p['DireccionEntrega']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- ✅ Botón que llama al UPDATE (confirmarPedido.php) -->
                    <form action="../controlador/confirmarPedido.php" method="POST" class="text-end mt-3">
                        <input type="hidden" name="NumFactura" value="<?php echo (int)$p['NumFactura']; ?>">
                        <input type="hidden" name="IDLoc" value="<?php echo (int)$p['IDLoc']; ?>">
                        <button class="btn btn-success">
                            <i class="fa-solid fa-check"></i> Marcar como entregado
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <hr class="my-4">

    <!-- ================= ENTREGADOS ================= -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0">
            <i class="fa-solid fa-circle-check"></i> Entregados
        </h4>
        <span class="badge bg-success">
            <?php echo count($entregados); ?>
        </span>
    </div>

    <?php if (empty($entregados)): ?>
        <div class="alert alert-secondary">Aún no hay pedidos entregados.</div>
    <?php else: ?>
        <?php foreach ($entregados as $p): ?>
            <div class="card mb-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Factura #<?php echo (int)$p['NumFactura']; ?></strong>
                        <span class="badge bg-success ms-2">Entregado</span><br>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($p['Fecha'] ?? ''); ?> |
                            <?php echo htmlspecialchars($p['LocalNombre'] ?? ''); ?>
                        </small>
                    </div>
                    <div class="fw-bold">
                        $<?php echo number_format((float)($p['Total'] ?? 0), 0, ',', '.'); ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
