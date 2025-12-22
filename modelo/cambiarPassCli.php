<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['id'])) { header("Location: ../loginApp.php"); exit(); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Cambiar contraseña</title>
</head>
<body>
  <h2>Cambiar contraseña</h2>

  <?php if (isset($_GET['e'])): ?>
    <p style="color:red;">Error: <?php echo htmlspecialchars($_GET['e']); ?></p>
  <?php endif; ?>

  <form action="../controlador/guardarNuevaPass.php" method="POST">
    <input type="password" name="pass1" placeholder="Nueva contraseña" required>
    <input type="password" name="pass2" placeholder="Repetir contraseña" required>
    <button type="submit">Guardar</button>
  </form>
</body>
</html>
