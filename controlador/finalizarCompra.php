<?php
session_start();

// 1) Verificar login del cliente (rol 3)
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] != 3) {
    header("Location: ../loginApp.php");
    exit();
}

// En tu diseño, asumimos que $_SESSION['id'] ES el IDCli de la tabla cliente
$idCliente = $_SESSION['id'];

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

// 4) Conexión BD
require_once __DIR__ . "/../modelo/connectionComidApp.php";
$db = new DatabaseComidApp();
$conexion = $db->getConnection();

try {
    $conexion->beginTransaction();

    $fechaHoy = date('Y-m-d');

    // 5) Preparar consultas: una para VENDE y otra para COMPRA
    $sqlVende = "
        SELECT FechaIniPrecio
        FROM vende
        WHERE IDLoc = ? AND CodigoArt = ?
        ORDER BY FechaIniPrecio DESC
        LIMIT 1
    ";
    $stmtVende = $conexion->prepare($sqlVende);

    $sqlCompra = "
        INSERT INTO compra
            (IDCli, IDLoc, CodigoArt, Cantidad, Fecha, FechaIniPrecio, Valida)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ";
    $stmtCompra = $conexion->prepare($sqlCompra);

    // 6) Insertar UNA FILA POR PRODUCTO DEL CARRITO
    foreach ($carrito as $item) {

        $idLoc     = $item['idLocal'];       // debe venir en el carrito
        $codigoArt = $item['codigoArt'];     // Código del artículo
        $cantidad  = $item['cantidad'] ?? 1; // si no manejás cantidad, queda 1

        // Buscar precio/fecha de VENDE
        $stmtVende->execute([$idLoc, $codigoArt]);
        $rowVende = $stmtVende->fetch(PDO::FETCH_ASSOC);

        if (!$rowVende) {
            throw new Exception("No existe precio en VENDE para local $idLoc y artículo $codigoArt");
        }

        $fechaIniPrecio = $rowVende['FechaIniPrecio'];

        // Insertar compra
        $stmtCompra->execute([
            $idCliente,      // OJO: ahora es directamente $_SESSION['id']
            $idLoc,
            $codigoArt,
            $cantidad,
            $fechaHoy,
            $fechaIniPrecio
        ]);
    }

    $conexion->commit();

    // 7) Vaciar carrito
    unset($_SESSION['carrito']);

    // 8) Redirigir al historial de compras
    header("Location: ../pages/misCompras.php?ok=1");
    exit();

} catch (Exception $e) {
    $conexion->rollBack();

    // DEBUG PROVISORIO:
    echo "<h2>Error al procesar la compra</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p><a href=\"../pages/verCarrito.php\">Volver al carrito</a></p>";
    exit();
}

