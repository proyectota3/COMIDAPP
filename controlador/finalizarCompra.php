<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * CONTROLADOR: finalizarCompra.php
 * --------------------------------
 * Este controller se ejecuta SOLO cuando el cliente confirma en checkout.
 * Tareas:
 * 1) Validar sesión (cliente rol 3)
 * 2) Validar carrito (no vacío y de un solo local)
 * 3) Validar datos del checkout (forma de pago, delivery, dirección)
 * 4) Insertar compra en BD
 *    - MISMA factura (NumFactura) para todos los ítems
 *    - Estado inicial: PENDIENTE -> Valida = 0
 * 5) Vaciar carrito y redirigir a Mis Compras
 */

// 1) Verificar login del cliente (rol 3)
if (!isset($_SESSION['id'], $_SESSION['rol']) || (int)$_SESSION['rol'] !== 3) {
    header("Location: ../loginApp.php");
    exit();
}
$idCliente = (int) $_SESSION['id'];

// 2) Verificar carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
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

// ✅ 4) Asegurar que esto venga del checkout (si no hay POST, mandamos al checkout)
if (!isset($_POST['forma_pago']) && !isset($_POST['delivery'])) {
    header("Location: ../pages/checkout.php");
    exit();
}

// 5) Leer checkout (POST)
$formaPago = $_POST['forma_pago'] ?? null;
$delivery  = $_POST['delivery'] ?? null;
$direccion = trim($_POST['direccion'] ?? '');

// Validar forma de pago
$formasValidas = ['Efectivo', 'Tarjeta'];
if (!in_array($formaPago, $formasValidas, true)) {
    header("Location: ../pages/checkout.php?error=forma_pago");
    exit();
}

// Validar delivery (debe ser "0" o "1")
if ($delivery !== "0" && $delivery !== "1") {
    header("Location: ../pages/checkout.php?error=delivery");
    exit();
}

// 6) Conexión BD
require_once __DIR__ . "/../modelo/connectionComidApp.php";
$db = new DatabaseComidApp();
$conexion = $db->getConnection();

try {
    /**
     * 7) Validación REAL de delivery del local (server-side)
     * Aunque el front lo oculte, acá lo confirmamos desde BD.
     */
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

    // Si no hay delivery, dirección debe ir nula
    if ($delivery === "0") {
        $direccion = null;
    }

    // 8) Transacción (si algo falla, se revierte todo)
    $conexion->beginTransaction();

    $fechaHoy = date('Y-m-d');

    // 9) Obtener nuevo NumFactura (misma factura para todos los ítems)
    $sqlNum = "SELECT IFNULL(MAX(NumFactura), 0) + 1 AS nuevaFactura FROM compra";
    $stmtNum = $conexion->query($sqlNum);
    $rowNum = $stmtNum->fetch(PDO::FETCH_ASSOC);
    $numFactura = (int) ($rowNum['nuevaFactura'] ?? 1);

    // 10) Buscar FechaIniPrecio en VENDE para cada ítem
    $sqlVende = "
        SELECT FechaIniPrecio
        FROM vende
        WHERE IDLoc = ? AND CodigoArt = ?
        ORDER BY FechaIniPrecio DESC
        LIMIT 1
    ";
    $stmtVende = $conexion->prepare($sqlVende);

    /**
     * 11) Insertar cada producto como una fila en COMPRA.
     * ✅ Estado inicial: PENDIENTE -> Valida = 0
     */
    $sqlCompra = "
        INSERT INTO compra
            (NumFactura, IDCli, IDLoc, CodigoArt, Cantidad, Fecha, FechaIniPrecio, Valida, FormaPago, Delivery, DireccionEntrega)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?)
    ";
    $stmtCompra = $conexion->prepare($sqlCompra);

    // 12) Recorrer carrito e insertar cada artículo como detalle
    foreach ($carrito as $item) {

        $idLoc     = (int) ($item['idLocal'] ?? 0);
        $codigoArt = (int) ($item['codigoArt'] ?? 0);
        $cantidad  = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
        if ($cantidad < 1) $cantidad = 1;

        if ($idLoc <= 0 || $codigoArt <= 0) {
            throw new Exception("Datos inválidos en el carrito (local/artículo).");
        }

        // Buscar la FechaIniPrecio en VENDE para ese local y artículo
        $stmtVende->execute([$idLoc, $codigoArt]);
        $rowVende = $stmtVende->fetch(PDO::FETCH_ASSOC);

        if (!$rowVende) {
            throw new Exception("No existe precio en VENDE para local $idLoc y artículo $codigoArt");
        }

        $fechaIniPrecio = $rowVende['FechaIniPrecio'];

        // Insertar la línea de compra
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

    // 13) Confirmar transacción
    $conexion->commit();

    // 14) Vaciar carrito y liberar el local del carrito
    unset($_SESSION['carrito']);
    unset($_SESSION['local_carrito']);

    // 15) Redirigir al historial de compras
    header("Location: ../pages/misCompras.php?ok=1");
    exit();

} catch (Exception $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    echo "<h2>Error al procesar la compra</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p><a href=\"../pages/checkout.php\">Volver al checkout</a></p>";
    exit();
}
