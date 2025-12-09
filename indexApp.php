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

<?php include "./controlador/fotoLocal.php"; ?>

<?php
// 👉 NUEVO: crear PDO a partir de tu clase de conexión y cargar el modelo del menú
require_once "./modelo/connectionComidApp.php";
require_once "./modelo/menuModel.php";

$dbComidApp = new DatabaseComidApp();
$pdo = $dbComidApp->getConnection();

$menuModel = new MenuModel($pdo);
?>

<?php
session_start(); // Importante: al inicio del archivo donde está el nav
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php
// Aseguramos que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php
// contador del carrito
$cantidadCarrito = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;
?>

<nav class="navbar navbar-expand-lg bg-danger">
    <div class="container-fluid">

        <!-- LOGO -->
        <a class="navbar-brand text-white" href="./indexApp.php">
            <i class="fa-solid fa-burger"></i> ComidAPP
        </a>

        <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" 
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
                        <a class="nav-link text-white" href="./pages/misCompras.php">Mis compras</a>
                    </li>
                <?php endif; ?>

                <!-- Empresa: Mis locales + Mis ventas (rol = 2) -->
                <?php if (isset($_SESSION['id']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 2): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./pages/misLocales.php">Mis locales</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="./pages/misVentas.php">Mis ventas</a>
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

                <!-- Carrito (solo clientes) -->
                <?php if (isset($_SESSION['id']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>
                    <li class="nav-item me-3">
                        <a class="nav-link position-relative text-white" href="./pages/verCarrito.php">
                            <i class="fa-solid fa-cart-shopping fa-lg"></i>

                            <?php if ($cantidadCarrito > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                    <?php echo $cantidadCarrito; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Usuario logueado -->
                <?php if (isset($_SESSION['id'])): ?>

                    <li class="nav-item me-3">
                        <a class="nav-link text-white d-flex align-items-center" href="./pages/perfil.php">
                            <i class="fa-solid fa-user fa-lg me-1"></i>
                            <span><?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['user']); ?></span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="./logout.php">
                            Cerrar sesión
                        </a>
                    </li>

                <?php else: ?>

                    <!-- Invitado -->
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="./loginApp.php">
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
        <!-- Carrusel -->
        <div id="foodCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php
                $index = 0;
                foreach ($sucursales as $row) {
                    echo '<button type="button" data-bs-target="#foodCarousel" data-bs-slide-to="' . $index . '"' . ($index === 0 ? ' class="active"' : '') . ' aria-label="Slide ' . ($index + 1) . '"></button>';
                    $index++;
                }
                ?>
            </div>
            <div class="carousel-inner">
                <?php
                $index = 0;
                foreach ($sucursales as $row) {
                    ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($row['Foto']); ?>" class="d-block w-100" alt="Foto del carrito">
                        <div class="carousel-caption d-block">
                            <p class="store-name fw-bold text-dark"><?php echo htmlspecialchars($row['Nombre']); ?></p>
                            <p class="store-address text-dark"><?php echo htmlspecialchars($row['Direccion']); ?></p>
                        </div>
                    </div>
                    <?php
                    $index++;
                }
                ?>
            </div>
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
// 👉 AHORA: menú dinámico desde la BD
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

                <?php if (isset($_SESSION['id']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 3): ?>
                    <form action="controlador/agregarCarrito.php" method="POST" class="d-inline">
                        <!-- Datos del producto -->
                        <input type="hidden" name="producto" value="<?php echo htmlspecialchars($prod['Nombre']); ?>">
                        <input type="hidden" name="precio" value="<?php echo htmlspecialchars($prod['Precio']); ?>">

                        <!-- 🔴 IMPORTANTE: ID DEL LOCAL (VIENE DE $row['ID']) -->
                        <input type="hidden" name="idLocal" value="<?php echo (int)$row['ID']; ?>">

                        <!-- (Opcional) si tenés el código del artículo en la consulta -->
                        <?php if (isset($prod['Codigo'])): ?>
                            <input type="hidden" name="codigoArt" value="<?php echo (int)$prod['Codigo']; ?>">
                        <?php endif; ?>

                        <button class="btn btn-sm btn-primary">Agregar</button>
                    </form>
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
