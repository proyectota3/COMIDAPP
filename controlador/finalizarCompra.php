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
    if ($item['idLocal'] != $primerLocal) {
        header("Location: ../pages/verCarrito.php?error=locales_distintos");
        exit();
    }
}
$idLocal = (int) $primerLocal;

// 4) Conexión BD
require_once __DIR__ . "/../modelo/connectionComidApp.php";
$db = new DatabaseComidApp();
$conexion = $db->getConnection();

try {
    $conexion->beginTransaction();

    $fechaHoy = date('Y-m-d');

    // 5) Obtener un nuevo NumFactura (MISMA FACTURA para todos los ítems del carrito)
    $sqlNum = "SELECT IFNULL(MAX(NumFactura), 0) + 1 AS nuevaFactura FROM compra";
    $stmtNum = $conexion->query($sqlNum);
    $rowNum = $stmtNum->fetch(PDO::FETCH_ASSOC);
    $numFactura = (int) ($rowNum['nuevaFactura'] ?? 1);

    // 6) Preparar consulta a VENDE para obtener FechaIniPrecio
    $sqlVende = "
        SELECT FechaIniPrecio
        FROM vende
        WHERE IDLoc = ? AND CodigoArt = ?
        ORDER BY FechaIniPrecio DESC
        LIMIT 1
    ";
    $stmtVende = $conexion->prepare($sqlVende);

    // 7) Preparar INSERT en COMPRA (una fila por artículo, mismo NumFactura)
    $sqlCompra = "
        INSERT INTO compra
            (NumFactura, IDCli, IDLoc, CodigoArt, Cantidad, Fecha, FechaIniPrecio, Valida)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, 1)
    ";
    $stmtCompra = $conexion->prepare($sqlCompra);

    // 8) Recorrer carrito e insertar cada artículo como detalle de la misma factura
    foreach ($carrito as $item) {

        $idLoc     = (int) $item['idLocal'];
        $codigoArt = (int) $item['codigoArt'];
        $cantidad  = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;

        if ($cantidad < 1) {
            $cantidad = 1;
        }

        // Buscar la FechaIniPrecio en VENDE para ese local y artículo
        $stmtVende->execute([$idLoc, $codigoArt]);
        $rowVende = $stmtVende->fetch(PDO::FETCH_ASSOC);

        if (!$rowVende) {
            throw new Exception("No existe precio en VENDE para local $idLoc y artículo $codigoArt");
        }

        $fechaIniPrecio = $rowVende['FechaIniPrecio'];

        // Insertar línea de compra (detalle) compartiendo el mismo NumFactura
        $stmtCompra->execute([
            $numFactura,      // 👉 MISMA FACTURA para todos los ítems del carrito
            $idCliente,
            $idLoc,
            $codigoArt,
            $cantidad,
            $fechaHoy,
            $fechaIniPrecio
        ]);
    }

    // 9) Confirmar transacción
    $conexion->commit();

    // 10) Vaciar carrito
    unset($_SESSION['carrito']);

    // 11) Redirigir al historial de compras
    header("Location: ../pages/misCompras.php?ok=1");
    exit();

} catch (Exception $e) {
    $conexion->rollBack();

    echo "<h2>Error al procesar la compra</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p><a href=\"../pages/verCarrito.php\">Volver al carrito</a></p>";
    exit();
}
