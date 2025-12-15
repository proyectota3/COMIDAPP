<?php
$data = require_once "../controlador/perfilControlador.php";
$tipo = $data["tipo"];
$perfil = $data["perfil"];
$mensaje = $data["mensaje"];
$error = $data["error"];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil | ComidAPP</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="../styles.css" rel="stylesheet">
</head>
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
                    // Aseguramos variable $carrito para evitar avisos
                    $carrito = $_SESSION['carrito'] ?? [];
                    // Si no te llega $cantidadCarrito desde el controlador, lo calculamos:
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

                        <!-- ⭐ VENTANA GRANDE DEL CARRITO ⭐ -->
                        <div class="dropdown-menu dropdown-menu-end p-4 shadow-lg"
                             aria-labelledby="carritoDropdown"
                             style="width: 420px; height: auto; max-height: none; overflow: visible; border-radius: 16px;">

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
                                                <small>$<?php echo $precio; ?> x <?php echo $cantidad; ?></small>
                                            </div>

                                            <!-- ELIMINAR ITEM -->
                                            <a href="./verCarrito.php?eliminar=<?php echo $idx; ?>" class="text-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <!-- BOTONES -->
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

<body>

<!-- Pegá tu NAV de /pages acá -->

<div class="container py-4" style="max-width: 900px;">
<h2 class="mb-3"><i class="fa-solid fa-user"></i> Mi Perfil</h2>

<?php if ($mensaje): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (!$perfil): ?>
    <div class="alert alert-warning">No se encontraron datos del perfil.</div>
<?php else: ?>

    <?php if ($tipo === "cliente"): ?>
    <div class="card shadow-sm">
        <div class="card-body">
        <h5 class="mb-3">Datos del cliente</h5>

        <form method="POST">
            <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input class="form-control" name="Nombre" required
                    value="<?php echo htmlspecialchars($perfil["Nombre"] ?? ""); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Apellido</label>
                <input class="form-control" name="Apellido" required
                    value="<?php echo htmlspecialchars($perfil["Apellido"] ?? ""); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Mail (no editable)</label>
                <input class="form-control" disabled
                    value="<?php echo htmlspecialchars($perfil["Mail"] ?? ""); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">CI (no editable)</label>
                <input class="form-control" disabled
                    value="<?php echo htmlspecialchars($perfil["CICli"] ?? ""); ?>">
            </div>

            <div class="col-12">
                <label class="form-label">Dirección</label>
                <input class="form-control" name="Direccion" required
                    value="<?php echo htmlspecialchars($perfil["Direccion"] ?? ""); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input class="form-control" name="Telefono"
                    value="<?php echo htmlspecialchars($perfil["Telefono"] ?? ""); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Forma de pago</label>
                <select class="form-select" name="FormaDePago">
                <?php
                    $fp = $perfil["FormaDePago"] ?? "";
                    $opciones = ["Tarjeta", "Efectivo"];
                    foreach ($opciones as $op) {
                        $sel = ($fp === $op) ? "selected" : "";
                        echo "<option $sel>" . htmlspecialchars($op) . "</option>";
                    }
                ?>
                </select>
            </div>

            <div class="col-12 mt-2">
                <button class="btn btn-danger">
                <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </div>

            </div>
        </form>
        </div>
    </div>

    <?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
        <h5 class="mb-3">Datos de la empresa</h5>

        <form method="POST">
            <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Nombre empresa</label>
                <input class="form-control" disabled
                    value="<?php echo htmlspecialchars($perfil["NombreEmpresa"] ?? ""); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">RUT</label>
                <input class="form-control" disabled
                    value="<?php echo htmlspecialchars($perfil["RUT"] ?? ""); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Mail empresa</label>
                <input class="form-control" disabled
                    value="<?php echo htmlspecialchars($perfil["MailEmpresa"] ?? ""); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Validación</label>
                <input class="form-control" disabled
                    value="<?php echo htmlspecialchars($perfil["Validacion"] ?? ""); ?>">
            </div>

            <div class="col-12">
                <label class="form-label">Dirección empresa (editable)</label>
                <input class="form-control" name="DireccionEmpresa" required
                    value="<?php echo htmlspecialchars($perfil["DireccionEmpresa"] ?? ""); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input class="form-control" name="Telefono"
                    value="<?php echo htmlspecialchars($perfil["Telefono"] ?? ""); ?>">
            </div>

            <div class="col-12 mt-2">
                <button class="btn btn-danger">
                <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </div>

            </div>
        </form>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
