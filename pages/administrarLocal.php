<?php
// Ejecutar el controlador
$data = require "../controlador/administradorLocalControlador.php";

$local           = $data["local"];
$menu            = $data["menu"];
$articulos       = $data["articulos"];
$cantidadCarrito = $data["cantidadCarrito"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar menú - ComidAPP</title>

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

                <!-- Empresa: Mis locales + Mis ventas (idRol = 2) -->
                <?php if (isset($_SESSION['id']) && $_SESSION['rol'] == 2): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misLocales.php">Mis locales</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./misVentas.php">Mis ventas</a>
                    </li>
                <?php endif; ?>

            </ul>

            <!-- Derecha: perfil + logout -->
            <ul class="navbar-nav d-flex align-items-center ms-3">
                <?php if (isset($_SESSION['id'])): ?>
                    <li class="nav-item me-3">
                        <a class="nav-link text-white d-flex align-items-center" href="./perfil.php">
                            <i class="fa-solid fa-user fa-lg me-2"></i>
                            <span><?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['user']); ?></span>
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

<div class="container mt-4">
    <h2 class="mb-3">Administrar menú de: <?php echo htmlspecialchars($local['Nombre']); ?></h2>
    <p><strong>Dirección:</strong> <?php echo htmlspecialchars($local['Direccion']); ?></p>

    <form method="post" action="./administrarLocal.php?id=<?php echo $local['ID']; ?>">

        <!-- MENÚ ACTUAL -->
        <h4 class="mt-4">Productos actuales</h4>

        <?php if (empty($menu)): ?>
            <div class="alert alert-info">Este local aún no tiene productos cargados.</div>
        <?php else: ?>
            <table class="table table-striped mt-2">
                <thead>
                    <tr>
                        <th>Artículo</th>
                        <th style="width:150px;">Precio</th>
                        <th style="width:80px;">Activo</th>
                        <th style="width:80px;">Quitar</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($menu as $linea): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($linea['Nombre']); ?></td>
                        <td>
                            <input type="text"
                                   name="linea[<?php echo $linea['ID']; ?>][precio]"
                                   value="<?php echo htmlspecialchars($linea['Precio']); ?>"
                                   class="form-control">
                        </td>
                        <td class="text-center">
                            <input type="checkbox"
                                   name="linea[<?php echo $linea['ID']; ?>][activo]"
                                   <?php echo $linea['Activo'] ? 'checked' : ''; ?>>
                        </td>
                        <td class="text-center">
                            <input type="checkbox"
                                   name="linea[<?php echo $linea['ID']; ?>][eliminar]">
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- AGREGAR NUEVO PRODUCTO -->
        <h4 class="mt-4">Agregar artículo al menú</h4>

        <div class="row g-2 align-items-center mb-3">
            <div class="col-md-6">
                <select name="nuevo_articulo" class="form-select">
                    <option value="">-- Seleccioná un artículo --</option>
                    <?php foreach ($articulos as $art): ?>
                        <option value="<?php echo $art['Codigo']; ?>">
                            <?php echo htmlspecialchars($art['Nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <input type="text" name="nuevo_precio" class="form-control" placeholder="Precio">
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-success w-100">
                    Guardar cambios
                </button>
            </div>
        </div>

    </form>

    <a href="./misLocales.php" class="btn btn-secondary mt-3">
        ← Volver a Mis locales
    </a>
</div>

<footer class="footer bg-danger text-center text-white py-3 mt-4">
    <p class="mb-0">© 2024 ComidApp. Derechos Reservados, Uruguay.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
