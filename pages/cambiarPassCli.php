<?php
// ✅ SIEMPRE iniciar sesión antes de cualquier HTML
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Seguridad: si no está logueado, al login
if (!isset($_SESSION['id'])) {
    header("Location: ../loginApp.php");
    exit();
}

$err = isset($_GET['e']) ? trim($_GET['e']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMIDAPP - Cambiar contraseña</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tu CSS -->
    <link href="../styles.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

<main class="flex-grow-1 d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">

                <div class="card shadow border-0 rounded-4">
                    <div class="card-header bg-danger text-white text-center rounded-top-4 py-4">
                        <h4 class="mb-0">
                            <i class="fa-solid fa-key me-2"></i>
                            Cambiar contraseña
                        </h4>
                        <small class="opacity-75">Por seguridad, elegí una contraseña nueva</small>
                    </div>

                    <div class="card-body p-4 p-md-4">

                        <?php if ($err !== ''): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <div><?php echo htmlspecialchars($err); ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="../controlador/guardarNuevaPassCli.php" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label class="form-label">Nueva contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                    <input type="password" name="pass1" class="form-control" placeholder="Ingresá tu nueva contraseña" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Repetir contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                    <input type="password" name="pass2" class="form-control" placeholder="Repetila para confirmar" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-danger w-100 py-2">
                                <i class="fa-solid fa-floppy-disk me-2"></i>
                                Guardar contraseña
                            </button>

                            <div class="text-center mt-3">
                                <a href="../indexApp.php" class="text-decoration-none">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Volver al inicio
                                </a>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<footer class="mt-auto bg-dark text-white text-center py-3">
    <div class="container">
        <small>© <?php echo date("Y"); ?> ComidAPP — Todos los derechos reservados</small>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
