<?php
// Iniciar sesión siempre antes de enviar HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMIDAPP - Mantenimiento</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tu CSS -->
    <link href="../styles.css" rel="stylesheet">
</head>
<body>

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
                <?php if (isset($_SESSION['id']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misCompras.php">
                            <i class="fa-solid fa-bag-shopping me-1"></i> Mis compras
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Empresa: Mis locales + Mis ventas (rol = 2) -->
                <?php if (isset($_SESSION['id']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 2): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misLocales.php">Mis locales</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misVentas.php">
                            <i class="fa-solid fa-chart-line me-1"></i> Mis ventas
                        </a>
                    </li>
                <?php endif; ?>

            </ul>

            <!-- BUSCADOR CENTRADO (deshabilitado por mantenimiento) -->
            <form class="d-flex mx-auto position-relative" style="width: 35%;" role="search" onsubmit="return false;">
                <input class="form-control" id="buscar" type="search" placeholder="Buscar sucursal (en mantenimiento)"
                       aria-label="Search" disabled>
                <ul id="resultados" class="list-group position-absolute mt-2"
                    style="z-index: 1000; width: 100%;"></ul>
            </form>

            <!-- ZONA DERECHA -->
            <ul class="navbar-nav d-flex align-items-center ms-3">

                <!-- 🔽 CARRITO TIPO VENTANA (solo clientes) -->
                <?php if (isset($_SESSION['id']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>

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
                             style="width: 420px; height: auto; max-height: none; overflow: visible; border-radius: 16px;">

                            <h5 class="mb-3 text-center">
                                <i class="fa-solid fa-cart-shopping"></i> Mi carrito
                            </h5>

                            <div class="alert alert-warning mb-0 text-center" style="border-radius: 12px;">
                                <i class="fa-solid fa-screwdriver-wrench me-1"></i>
                                Funcionalidad en mantenimiento por el momento.
                            </div>

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
                                <a class="dropdown-item" href="./proximamente.php">
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


<!-- CONTENIDO PRINCIPAL -->
<main class="container mt-5 flex-grow-1">

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-lg border-0" style="border-radius: 18px;">
                <div class="card-body p-5 text-center">

                    <div class="mb-4">
                        <i class="fa-solid fa-screwdriver-wrench fa-3x text-danger"></i>
                    </div>

                    <h1 class="fw-bold mb-2">Estamos en mantenimiento</h1>
                    <p class="text-muted mb-4" style="font-size: 1.05rem;">
                        Esta sección todavía no está disponible. Estamos ajustando cosas para que quede impecable 😄
                    </p>

                    <div class="alert alert-warning text-start" style="border-radius: 14px;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                            <div>
                                <strong>Por ahora no funciona:</strong>
                                <ul class="mb-0">
                                    <li>Acciones de compra/confirmación</li>
                                    <li>Búsqueda de sucursales</li>
                                    <li>Gestión de carrito desde esta página</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-2 justify-content-center mt-4">
                        <a href="../indexApp.php" class="btn btn-danger px-4" style="border-radius: 12px;">
                            <i class="fa-solid fa-house me-2"></i> Volver al inicio
                        </a>

                        <a href="./contacto.php" class="btn btn-outline-danger px-4" style="border-radius: 12px;">
                            <i class="fa-solid fa-envelope me-2"></i> Contacto
                        </a>
                    </div>

                    <p class="small text-muted mt-4 mb-0">
                        Gracias por la paciencia 💪
                    </p>

                </div>
            </div>

        </div>
    </div>

</main>

<!-- FOOTER -->
<footer class="footer bg-danger text-center text-white py-3 mt-5">
    <div class="container">
        <p class="mb-0">© 2024 ComidApp. Derechos Reservados, Uruguay.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
