<?php
// pages/menuLocal.php
// Viene desde controlador/menuControlador.php
// Variables disponibles: $local, $menu, $articulos, $idLocal
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar menú - ComidAPP</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome (si lo usás en el nav) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="../loginApp.css" rel="stylesheet"> <!-- o tu styles.css -->
</head>
<body>

<?php
// Si tenés un nav común lo incluís acá, por ejemplo:
// include 'navbar.php';
?>

<div class="container mt-4">
    <h2>Administrar menú de: <?php echo htmlspecialchars($local['Nombre']); ?></h2>

    <form method="post" action="../controlador/menuControlador.php?idLocal=<?php echo $idLocal; ?>">

        <!-- Menú actual -->
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>Artículo</th>
                    <th style="width: 150px;">Precio</th>
                    <th style="width: 80px;">Activo</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($menu)): ?>
                <tr>
                    <td colspan="3">Este local aún no tiene productos cargados.</td>
                </tr>
            <?php else: ?>
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
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Agregar nuevo artículo -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Agregar nuevo artículo al menú</h5>
                <div class="row g-2 align-items-center">
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
            </div>
        </div>

    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
