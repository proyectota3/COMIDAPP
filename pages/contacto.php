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
    <title>COMIDAPP</title>

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

            <!-- BUSCADOR CENTRADO -->
            <form class="d-flex mx-auto position-relative" style="width: 35%;" role="search">
                <input class="form-control" id="buscar" type="search" placeholder="Buscar sucursal" aria-label="Search">
                <ul id="resultados" class="list-group position-absolute mt-2"
                    style="z-index: 1000; width: 100%;"></ul>
            </form>

            <!-- ZONA DERECHA -->
            <ul class="navbar-nav d-flex align-items-center ms-3">

                <!-- 🔽 CARRITO TIPO VENTANA (solo clientes) -->
                <?php if (isset($_SESSION['id']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>

                    <?php
                    $carrito = $_SESSION['carrito'] ?? [];
                    $cantidadCarrito = $cantidadCarrito ?? array_sum(array_map(fn($i)=> (int)($i['cantidad'] ?? 1), $carrito));
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

                            <?php if (empty($carrito)): ?>
                                <p class="text-center text-muted mb-0">El carrito está vacío.</p>
                            <?php else: ?>

                                <ul class="list-group mb-3" style="border-radius: 12px; overflow: hidden;">
                                    <?php foreach ($carrito as $idx => $item):
                                        $nombre   = htmlspecialchars($item['nombre'] ?? 'Producto');
                                        $precio   = isset($item['precio']) ? (float)$item['precio'] : 0;
                                        $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
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

                                    <form action="../controlador/finalizarCompra.php" method="POST" class="d-grid">
                                        <button type="submit" class="btn btn-success">Confirmar compra</button>
                                    </form>

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

                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>
                                <li>
                                    <a class="dropdown-item" href="./misCompras.php">
                                        <i class="fa-solid fa-bag-shopping me-2"></i> Mis compras
                                    </a>
                                </li>
                            <?php endif; ?>

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


<!-- CONTENIDO PRINCIPAL -->
<main class="container mt-5 flex-grow-1">

    <h1 class="text-center mb-4">Contáctanos</h1>

    <div class="row">
        <div class="col-md-6">

            <!-- ✅ ALERTAS (OK / DUP / ERROR) -->
            <?php if (isset($_GET['ok']) && $_GET['ok'] == 1): ?>
                <div class="alert alert-success">
                    ✅ ¡Tu solicitud fue enviada correctamente! Un administrador la revisará.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['dup']) && $_GET['dup'] == 1): ?>
                <div class="alert alert-warning">
                    ⚠️ Ya existe una solicitud pendiente con ese RUT o mail. Un administrador la revisará.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['e'])): ?>
                <div class="alert alert-danger">
                    ❌ No se pudo enviar la solicitud:
                    <strong><?php echo htmlspecialchars($_GET['e']); ?></strong>
                </div>
            <?php endif; ?>

            <!-- FORMULARIO -->
            <form method="POST" action="../controlador/solicitud.php" class="mt-3">
                <input type="hidden" name="solicitud" value="1">

                <div class="mb-3">
                    <label for="RUT" class="form-label">RUT de la empresa</label>
                    <input type="text" class="form-control" id="RUT" name="RUT"
                           placeholder="Ej: 11111111111" required>
                </div>

                <div class="mb-3">
                    <label for="Nombre" class="form-label">Razón social</label>
                    <input type="text" class="form-control" id="Nombre" name="Nombre"
                           placeholder="Nombre o razón social del negocio" required>
                </div>

                <div class="mb-3">
                    <label for="Direccion" class="form-label">Domicilio fiscal</label>
                    <input type="text" class="form-control" id="Direccion" name="Direccion"
                           placeholder="Dirección fiscal del negocio" required>
                </div>

                <div class="mb-3">
                    <label for="Telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="Telefono" name="Telefono"
                           placeholder="Ej: 099123456" required>
                </div>

                <div class="mb-4">
                    <label for="Mail" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="Mail" name="Mail"
                           placeholder="Correo de contacto" required>
                </div>

                <button type="submit" class="btn btn-danger w-100" name="btnEnviar">
                    Enviar
                </button>
            </form>

        </div>

        <div class="col-md-6">

            <h4>Información de Contacto</h4>
            <p><strong>Dirección:</strong> UTU Instituto Tecnológico ITI, Mercedes 1131, Montevideo, Uruguay</p>
            <p><strong>Teléfono:</strong> +598 93 754 113</p>
            <p><strong>Email:</strong> soporte@comidapp.com</p>

            <h4>Ubicación</h4>
            <div class="map-responsive">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3276.694156432854!2d-56.19385482511051!3d-34.90360627322138!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x959f81e6bc53d73f%3A0x8fe63b2149bde905!2sMercedes%201131%2C%2011100%20Montevideo%2C%20Uruguay!5e0!3m2!1ses!2suy!4v1698768121206!5m2!1ses!2suy"
                    width="100%" height="250" frameborder="0" style="border:0;" allowfullscreen="">
                </iframe>
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
