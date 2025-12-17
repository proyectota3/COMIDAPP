<?php
session_start();

// 1) Verificar login del cliente (rol 3)
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] != 3) {
    header("Location: ../loginApp.php");
    exit();
}

$idCliente = (int) $_SESSION['id'];

// 2) Verificar carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: ../pages/verCarrito.php?error=carrito_vacio");
    exit();
}

$carrito = $_SESSION['carrito'];

// 3) Validar que todos los productos sean del mismo local
$primerLocal = $carrito[0]['idLocal'] ?? null;
foreach ($carrito as $item) {
    if (($item['idLocal'] ?? null) != $primerLocal) {
        header("Location: ../pages/verCarrito.php?error=locales_distintos");
        exit();
    }
}
$idLocal = (int) $primerLocal;

// 4) Leer checkout (POST)
$formaPago = $_POST['forma_pago'] ?? null;
$delivery  = $_POST['delivery'] ?? null;
$direccion = trim($_POST['direccion'] ?? '');

$formasValidas = ['Efectivo', 'Tarjeta'];
if (!in_array($formaPago, $formasValidas, true)) {
    header("Location: ../pages/checkout.php?error=forma_pago");
    exit();
}
if ($delivery !== "0" && $delivery !== "1") {
    header("Location: ../pages/checkout.php?error=delivery");
    exit();
}

// 5) Conexión BD
require_once __DIR__ . "/../modelo/connectionComidApp.php";
$db = new DatabaseComidApp();
$conexion = $db->getConnection();

try {
    // 6) Validación REAL de delivery del local (server-side)
    $stmtLocal = $conexion->prepare("SELECT Delivery FROM local WHERE ID = ? LIMIT 1");
    $stmtLocal->execute([$idLocal]);
    $rowLocal = $stmtLocal->fetch(PDO::FETCH_ASSOC);

    $tieneDelivery = ($rowLocal && (int)($rowLocal['Delivery'] ?? 0) === 1);

    // Si el local NO tiene delivery, forzamos delivery=0 y limpiamos dirección
    if (!$tieneDelivery) {
        $delivery = "0";
        $direccion = null;
    }

    // Si pidió delivery y el local sí tiene, exigir dirección
    if ($delivery === "1" && $direccion === "") {
        header("Location: ../pages/checkout.php?error=direccion");
        exit();
    }
    if ($delivery === "0") {
        $direccion = null;
    }

    $conexion->beginTransaction();

    $fechaHoy = date('Y-m-d');

    // 7) Nuevo NumFactura (misma factura para todos)
    $sqlNum = "SELECT IFNULL(MAX(NumFactura), 0) + 1 AS nuevaFactura FROM compra";
    $stmtNum = $conexion->query($sqlNum);
    $rowNum = $stmtNum->fetch(PDO::FETCH_ASSOC);
    $numFactura = (int) ($rowNum['nuevaFactura'] ?? 1);

    // 8) FechaIniPrecio desde VENDE
    $sqlVende = "
        SELECT FechaIniPrecio
        FROM vende
        WHERE IDLoc = ? AND CodigoArt = ?
        ORDER BY FechaIniPrecio DESC
        LIMIT 1
    ";
    $stmtVende = $conexion->prepare($sqlVende);

    // 9) INSERT (incluye checkout)
    $sqlCompra = "
        INSERT INTO compra
            (NumFactura, IDCli, IDLoc, CodigoArt, Cantidad, Fecha, FechaIniPrecio, Valida, FormaPago, Delivery, DireccionEntrega)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)
    ";
    $stmtCompra = $conexion->prepare($sqlCompra);

    // 10) Insert por item
    foreach ($carrito as $item) {

        $idLoc     = (int) $item['idLocal'];
        $codigoArt = (int) $item['codigoArt'];
        $cantidad  = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
        if ($cantidad < 1) $cantidad = 1;

        $stmtVende->execute([$idLoc, $codigoArt]);
        $rowVende = $stmtVende->fetch(PDO::FETCH_ASSOC);

        if (!$rowVende) {
            throw new Exception("No existe precio en VENDE para local $idLoc y artículo $codigoArt");
        }

        $fechaIniPrecio = $rowVende['FechaIniPrecio'];

        $stmtCompra->execute([
            $numFactura,
            $idCliente,
            $idLoc,
            $codigoArt,
            $cantidad,
            $fechaHoy,
            $fechaIniPrecio,
            $formaPago,
            (int)$delivery,
            $direccion
        ]);
    }

    $conexion->commit();

    unset($_SESSION['carrito']);

    header("Location: ../pages/misCompras.php?ok=1");
    exit();

} catch (Exception $e) {
    if ($conexion->inTransaction()) $conexion->rollBack();

    echo "<h2>Error al procesar la compra</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p><a href=\"../pages/verCarrito.php\">Volver al carrito</a></p>";
    exit();
}
