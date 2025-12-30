<?php
// 🔹 SIEMPRE: iniciar sesión ANTES de cualquier HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔹 Incluye las sucursales (fotoLocal debería cargarte $sucursales)
include "./controlador/fotoLocal.php";

// Seguridad: si por algún motivo fotoLocal.php no define $sucursales:
if (!isset($sucursales) || !is_array($sucursales)) {
    $sucursales = [];
}

// 🔹 Conexión y modelo del menú
require_once "./modelo/connectionComidApp.php";
require_once "./modelo/menuModel.php";

$dbComidApp = new DatabaseComidApp();
$pdo = $dbComidApp->getConnection();
$menuModel = new MenuModel($pdo);

// 🔹 Carrito siempre como array
$carrito = (isset($_SESSION['carrito']) && is_array($_SESSION['carrito']))
    ? $_SESSION['carrito']
    : [];

$cantidadCarrito = count($carrito);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMIDAPP</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Enlace a tu CSS -->
    <link href="styles.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-danger navbar-dark">
    <div class="container-fluid">

        <!-- LOGO -->
        <a class="navbar-brand text-white" href="./indexApp.php">
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
                    <a class="nav-link text-white" href="./pages/contacto.php">Contacto</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white" href="./pages/descargar.php">Descargar</a>
                </li>

                <!-- Cliente: Mis compras (rol = 3) -->
                <?php if (isset($_SESSION['id']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./pages/misCompras.php">
                            <i class="fa-solid fa-bag-shopping me-1"></i> Mis compras
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Empresa: Mis locales + Mis ventas (rol = 2) -->
                <?php if (isset($_SESSION['id']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 2): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./pages/misLocales.php">Mis locales</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="./pages/misVentas.php">
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
                    // Aseguramos variable $carrito para evitar avisos
                    $carrito = $_SESSION['carrito'] ?? [];
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

                        <!-- ⭐ VENTANA GRANDE DEL CARRITO ⭐ -->
                        <div class="dropdown-menu dropdown-menu-end p-4 shadow-lg"
                             aria-labelledby="carritoDropdown"
                             style="
                                width: 420px;
                                height: auto;
                                max-height: none;
                                overflow: visible;
                                border-radius: 16px;
                             ">

                            <h5 class="mb-3 text-center">
                                <i class="fa-solid fa-cart-shopping"></i> Mi carrito
                            </h5>

                            <?php if (empty($carrito)): ?>
                                <p class="text-center text-muted mb-0">El carrito está vacío.</p>
                            <?php else: ?>

                                <!-- LISTA DE PRODUCTOS -->
                                <ul class="list-group mb-3" style="border-radius: 12px; overflow: hidden;">
                                    <?php foreach ($carrito as $idx => $item):
                                        $nombre   = htmlspecialchars($item['nombre'] ?? 'Producto');
                                        $precio   = isset($item['precio']) ? (float)$item['precio'] : 0;
                                        $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
                                    ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo $nombre; ?></strong><br>
                                                <small>
                                                    $<?php echo $precio; ?> x <?php echo $cantidad; ?>
                                                </small>
                                            </div>

                                            <a href="./pages/verCarrito.php?eliminar=<?php echo $idx; ?>" class="text-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <!-- BOTONES -->
                                <div class="d-grid gap-2">
                                    <a href="./pages/verCarrito.php" class="btn btn-primary">
                                        Ver carrito
                                    </a>

                                    <form action="./controlador/finalizarCompra.php" method="POST" class="d-grid">
                                        <button type="submit" class="btn btn-success">
                                            Confirmar compra
                                        </button>
                                    </form>

                                    <a href="./pages/verCarrito.php?vaciar=1" class="btn btn-outline-danger">
                                        Vaciar carrito
                                    </a>
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
                            <span>
                                <?php echo htmlspecialchars($_SESSION['nombre'] ?? ($_SESSION['user'] ?? 'Mi cuenta')); ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="usuarioDropdown">
                            <li>
                                <a class="dropdown-item" href="./pages/perfil.php">
                                    <i class="fa-solid fa-id-card me-2"></i> Perfil
                                </a>
                            </li>

                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>
                                <li>
                                    <a class="dropdown-item" href="./pages/misCompras.php">
                                        <i class="fa-solid fa-bag-shopping me-2"></i> Mis compras
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li>
                         <a class="dropdown-item" href="./pages/proximamente.php">
                                    <i class="fa-solid fa-circle-info me-2"></i> Información
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </li>

                <?php else: ?>

                    <!-- ✅ Invitado: Iniciar sesión -->
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="loginApp.php">
                            Iniciar sesión
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>
    </div>
</nav>

<!-- 🔴 MENSAJES DE CARRITO / ERRORES / OK -->
<?php if (isset($_GET['error']) && $_GET['error'] === 'local_distinto'): ?>
    <div class="alert alert-warning alert-dismissible fade show text-center mb-0" role="alert">
        No podés seleccionar menú de diferentes carritos. Terminá o vaciá el carrito actual antes de elegir otro local.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'datos_faltantes'): ?>
    <div class="alert alert-danger alert-dismissible fade show text-center mb-0" role="alert">
        Ocurrió un error al agregar el producto. Faltan datos obligatorios.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['ok']) && $_GET['ok'] === 'agregado'): ?>
    <div class="alert alert-success alert-dismissible fade show text-center mb-0" role="alert">
        Producto agregado al carrito correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<main class="flex-grow-1">
    <div class="container mt-4">

        <!-- Carrusel (solo marketing) -->
        <div id="foodCarousel" class="carousel slide mb-4" data-bs-ride="carousel">

            <!-- Indicadores -->
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#foodCarousel" data-bs-slide-to="0"
                        class="active" aria-current="true" aria-label="Promo 1"></button>
                <button type="button" data-bs-target="#foodCarousel" data-bs-slide-to="1"
                        aria-label="Promo 2"></button>
                <button type="button" data-bs-target="#foodCarousel" data-bs-slide-to="2"
                        aria-label="Promo 3"></button>
            </div>

            <div class="carousel-inner">

                <!-- Banner 1 -->
                <div class="carousel-item active">
                    <div class="d-flex align-items-center justify-content-center text-center"
                         style="height:360px; background:linear-gradient(135deg,#dc3545,#ff6b6b); color:white;">
                        <div class="px-3">
                            <h2 class="fw-bold mb-2">🎉 ¡Recompensa ComidAPP!</h2>
                            <p class="fs-4 mb-3">
                                Si alcanzás <strong>$5000</strong> en compras totales<br>
                                tenés <strong>25% OFF</strong> en tu próximo pedido 🍔
                            </p>
                            <span class="badge bg-light text-danger fs-6 px-3 py-2">
                                Promo por cliente
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Banner 2 -->
                <div class="carousel-item">
                    <div class="d-flex align-items-center justify-content-center text-center"
                         style="height:360px; background:linear-gradient(135deg,#198754,#20c997); color:white;">
                        <div class="px-3">
                            <h2 class="fw-bold mb-2">⚡ Delivery rápido</h2>
                            <p class="fs-4 mb-3">
                                Pedí en tu local favorito y recibí tu pedido<br>
                                <strong>más rápido</strong> y sin complicarte 🛵
                            </p>
                            <span class="badge bg-light text-success fs-6 px-3 py-2">
                                Fácil • Rápido • Seguro
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Banner 3 -->
                <div class="carousel-item">
                    <div class="d-flex align-items-center justify-content-center text-center"
                         style="height:360px; background:linear-gradient(135deg,#0d6efd,#6f42c1); color:white;">
                        <div class="px-3">
                            <h2 class="fw-bold mb-2">🍟 Más locales, más variedad</h2>
                            <p class="fs-4 mb-3">
                                Explorá menús, guardá tus favoritos<br>
                                y encontrá <strong>promos todos los días</strong> 🔥
                            </p>
                            <span class="badge bg-light text-primary fs-6 px-3 py-2">
                                Descubrí • Elegí • Comprá
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Controles -->
            <button class="carousel-control-prev" type="button" data-bs-target="#foodCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#foodCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>

        </div>

        <!-- Lista de sucursales con modales -->
        <div class="store-list mt-4">
            <?php foreach ($sucursales as $row): ?>
                <div class="store">
                    <img src="<?php echo htmlspecialchars($row['Foto']); ?>" alt="<?php echo htmlspecialchars($row['Nombre']); ?>" class="store-img">
                    <p class="store-name"><?php echo htmlspecialchars($row['Nombre']); ?></p>
                    <p class="store-address"><?php echo htmlspecialchars($row['Direccion']); ?></p>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $row['ID']; ?>">
                        Ver productos
                    </button>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="productModal<?php echo $row['ID']; ?>" tabindex="-1" aria-labelledby="productModalLabel<?php echo $row['ID']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title" id="productModalLabel<?php echo $row['ID']; ?>">
                                    Productos de <?php echo htmlspecialchars($row['Nombre']); ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($row['Direccion']); ?></p>
                                <p><strong>Menú:</strong></p>

                                <?php
                                // 👉 menú dinámico desde la BD
                                $menuLocal = $menuModel->getMenuClienteByLocal($row['ID']);
                                ?>

                                <?php if (empty($menuLocal)): ?>
                                    <p>Este local aún no tiene productos cargados.</p>
                                <?php else: ?>
                                    <ul class="list-group">
                                        <?php foreach ($menuLocal as $prod): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>
                                                    <?php echo htmlspecialchars($prod['Nombre']); ?> -
                                                    $<?php echo htmlspecialchars($prod['Precio']); ?>
                                                </span>

                                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>

    <!-- ✅ Cliente: puede comprar -->
    <form action="controlador/agregarCarrito.php" method="POST" class="d-inline">
        <input type="hidden" name="producto" value="<?php echo htmlspecialchars($prod['Nombre']); ?>">
        <input type="hidden" name="precio" value="<?php echo htmlspecialchars($prod['Precio']); ?>">
        <input type="hidden" name="idLocal" value="<?php echo (int)$row['ID']; ?>">
        <input type="hidden" name="codigoArt" value="<?php echo (int)$prod['Codigo']; ?>">
        <input type="hidden" name="cantidad" value="1">
        <button class="btn btn-sm btn-primary">Agregar</button>
    </form>

<?php elseif (!isset($_SESSION['id'])): ?>

    <!-- ✅ Invitado: pedir login -->
    <a class="btn btn-sm btn-outline-primary" href="loginApp.php">
        Iniciar sesión para comprar
    </a>

<?php else: ?>

    <!-- ✅ Empresa u otro rol: no mostrar login -->
    <span class="badge bg-secondary">Solo clientes pueden comprar</span>

<?php endif; ?>

                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>

                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    </div>
</main>

<div class="text-center py-1 bg-warning">
    <h2>¿Quieres ser parte de nosotros?</h2>
    <p>Únete a ComidAPP y descubre más sobre nuestras oportunidades y beneficios.</p>
    <a href="./pages/contacto.php" class="btn btn-primary mt-2">¡Únete ahora!</a>
</div>

<footer class="footer bg-danger text-center text-white py-3">
    <div class="container">
        <p class="mb-0">© 2024 ComidApp. Derechos Reservados, Uruguay.</p>
    </div>
</footer>

<script src="buscador.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
