<?php
// Ejecuto el controlador
$data = require "../controlador/misLocalesControlador.php";

// Variables que vienen desde el controlador
$locales = $data["locales"];
$cantidadCarrito = $data["cantidadCarrito"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis locales - ComidAPP</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tu CSS -->
    <link href="../styles.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-danger">
    <div class="container-fluid">

        <!-- LOGO -->
        <a class="navbar-brand text-white" href="../indexApp.php">
            <i class="fa-solid fa-burger"></i> ComidAPP
        </a>

        <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarSupportedContent">
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

                <!-- Cliente: Mis compras (idRol = 3) -->
                <?php if (isset($_SESSION['id']) && $_SESSION['rol'] == 3): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misCompras.php">Mis compras</a>
                    </li>
                <?php endif; ?>

                <!-- Empresa: Mis ventas (idRol = 2) -->
                <?php if (isset($_SESSION['id']) && $_SESSION['rol'] == 2): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misVentas.php">Mis ventas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misLocales.php">Mis ventas</a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- BUSCADOR -->
            <form class="d-flex mx-auto position-relative" style="width: 35%;" role="search">
                <input class="form-control" id="buscar" type="search" placeholder="Buscar sucursal">
                <ul id="resultados" class="list-group position-absolute mt-2"
                    style="z-index: 1000; width: 100%;"></ul>
            </form>

            <!-- DERECHA -->
            <ul class="navbar-nav d-flex align-items-center ms-3">

                <?php if (isset($_SESSION['id'])): ?>

                    <!-- Perfil -->
                    <li class="nav-item me-3">
                        <a class="nav-link text-white d-flex align-items-center" href="./perfil.php">
                            <i class="fa-solid fa-user fa-lg me-2"></i>
                            <span><?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['user']); ?></span>
                        </a>
                    </li>

                    <!-- Cerrar sesión -->
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="../logout.php">
                            Cerrar sesión
                        </a>
                    </li>

                <?php else: ?>

                    <!-- Iniciar sesión -->
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

<!-- ==================== CUERPO DE LA PÁGINA ==================== -->

<div class="container mt-4">
    <h2 class="mb-4">Mis locales</h2>

    <?php if (empty($locales)): ?>

        <div class="alert alert-info">Todavía no tienes locales registrados.</div>

    <?php else: ?>

        <div class="row">
            <?php foreach ($locales as $loc): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">

                        <?php if (!empty($loc['Foto'])): ?>
                            <img src="<?php echo htmlspecialchars($loc['Foto']); ?>" 
                                 class="card-img-top" alt="Foto del local">
                        <?php endif; ?>

                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($loc['Nombre']); ?></h5>
                            <p><strong>Dirección: </strong><?php echo htmlspecialchars($loc['Direccion']); ?></p>
                            <p><strong>Delivery: </strong><?php echo $loc['Delivery'] ? 'Sí' : 'No'; ?></p>

                            <a href="./administrarLocal.php?id=<?php echo $loc['ID']; ?>" 
                               class="btn btn-primary">Administrar menú</a>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<footer class="footer bg-danger text-center text-white py-3 mt-4">
    <p class="mb-0">© 2024 ComidApp. Derechos Reservados, Uruguay.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
