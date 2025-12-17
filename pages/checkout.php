<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * VISTA: checkout.php
 * -------------------
 * Pantalla intermedia antes de finalizar la compra.
 * - Pide forma de pago (Efectivo / Tarjeta)
 * - Pide delivery solo si el local lo ofrece
 * - Si delivery = Sí -> pide dirección
 *
 * Al enviar, hace POST a: ../controlador/finalizarCompra.php
 */

// Si no hay carrito, volver
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: ./verCarrito.php?error=carrito_vacio");
    exit();
}

$carrito = $_SESSION['carrito'];

// Tomar ID del local desde sesión (más confiable) o desde el primer item del carrito
$idLocalSesion = isset($_SESSION['local_carrito']) ? (int)$_SESSION['local_carrito'] : 0;
$idLocalCarrito = (int)($carrito[0]['idLocal'] ?? 0);
$idLocal = $idLocalSesion > 0 ? $idLocalSesion : $idLocalCarrito;

if ($idLocal <= 0) {
    header("Location: ./verCarrito.php?error=local_invalido");
    exit();
}

// Consultar si el local tiene delivery
require_once __DIR__ . "/../modelo/connectionComidApp.php";
$db = new DatabaseComidApp();
$conexion = $db->getConnection();

$stmt = $conexion->prepare("SELECT Delivery, Nombre FROM local WHERE ID = ? LIMIT 1");
$stmt->execute([$idLocal]);
$rowLocal = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe local -> por seguridad volvemos
if (!$rowLocal) {
    header("Location: ./verCarrito.php?error=local_invalido");
    exit();
}

$tieneDelivery = ((int)($rowLocal['Delivery'] ?? 0) === 1);
$nombreLocal = $rowLocal['Nombre'] ?? 'Local';

// Mensajes
$error = $_GET['error'] ?? null;
$msg = null;
if ($error === "forma_pago") $msg = "Tenés que elegir una forma de pago.";
if ($error === "delivery")   $msg = "Tenés que elegir si es con delivery o no.";
if ($error === "direccion")  $msg = "Si elegís delivery, tenés que poner una dirección.";

// Resumen total (opcional)
$total = 0;
$cantTotal = 0;
foreach ($carrito as $item) {
    $precio = isset($item['precio']) ? (float)$item['precio'] : 0;
    $cant   = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
    if ($cant < 1) $cant = 1;
    if ($precio < 0) $precio = 0;

    $total += $precio * $cant;
    $cantTotal += $cant;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout | ComidAPP</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="../styles.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">

  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="m-0"><i class="fa-solid fa-bag-shopping"></i> Checkout</h2>
    <a href="./verCarrito.php" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left"></i> Volver al carrito
    </a>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <div class="row g-3">

    <!-- FORM -->
    <div class="col-12 col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">

          <h5 class="mb-3">Compra en: <strong><?php echo htmlspecialchars($nombreLocal); ?></strong></h5>

          <form action="../controlador/finalizarCompra.php" method="POST">

            <!-- Forma de pago -->
            <div class="mb-3">
              <label class="form-label">Forma de pago</label>
              <select name="forma_pago" class="form-select" required>
                <option value="" selected disabled>Elegí una opción...</option>
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta">Tarjeta</option>
              </select>
            </div>

            <?php if ($tieneDelivery): ?>

              <!-- Delivery -->
              <div class="mb-3">
                <label class="form-label">¿Con delivery?</label>
                <select name="delivery" id="delivery" class="form-select" required>
                  <option value="" selected disabled>Elegí...</option>
                  <option value="0">No</option>
                  <option value="1">Sí</option>
                </select>
              </div>

              <!-- Dirección -->
              <div class="mb-3 d-none" id="direccionBox">
                <label class="form-label">Dirección de entrega</label>
                <input type="text" name="direccion" id="direccion" class="form-control"
                       placeholder="Ej: Av. Italia 1234, Apto 202">
              </div>

            <?php else: ?>

              <div class="alert alert-info">
                <i class="fa-solid fa-store"></i>
                Este local <strong>no ofrece delivery</strong> (solo retiro).
              </div>

              <!-- Forzar delivery=0 -->
              <input type="hidden" name="delivery" value="0">

            <?php endif; ?>

            <div class="d-flex gap-2">
              <a href="./verCarrito.php" class="btn btn-outline-secondary w-50">Cancelar</a>
              <button type="submit" class="btn btn-success w-50">
                <i class="fa-solid fa-check"></i> Confirmar compra
              </button>
            </div>

          </form>

        </div>
      </div>
    </div>

    <!-- RESUMEN -->
    <div class="col-12 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Resumen</h5>

          <div class="d-flex justify-content-between">
            <span>Productos</span>
            <strong><?php echo (int)$cantTotal; ?></strong>
          </div>

          <div class="d-flex justify-content-between mt-2">
            <span>Total</span>
            <strong><?php echo $total > 0 ? ('$' . number_format($total, 0, ',', '.')) : '--'; ?></strong>
          </div>

          <div class="text-muted mt-2" style="font-size:.9rem;">
            * El total se calcula con el precio guardado en el carrito.
          </div>
        </div>
      </div>
    </div>

  </div>

</div>

<?php if ($tieneDelivery): ?>
<script>
  const delivery = document.getElementById("delivery");
  const direccionBox = document.getElementById("direccionBox");
  const direccion = document.getElementById("direccion");

  delivery.addEventListener("change", () => {
    if (delivery.value === "1") {
      direccionBox.classList.remove("d-none");
      direccion.setAttribute("required", "required");
    } else {
      direccionBox.classList.add("d-none");
      direccion.removeAttribute("required");
      direccion.value = "";
    }
  });
</script>
<?php endif; ?>

</body>
</html>
