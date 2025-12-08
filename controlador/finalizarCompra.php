c<?php
session_start();

// 1) Verificar login del cliente
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] != 3) {
    header("Location: ../loginApp.php");
    exit();
}

$idCliente = $_SESSION['id'];

// 2) Verificar carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: ../pages/verCarrito.php?error=carrito_vacio");
    exit();
}

$carrito = $_SESSION['carrito'];

// 3) Validar que todos los productos sean del mismo local (regla como PedidosYa)
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

    // FECHA HOY
    $fecha = date('Y-m-d');

    // 5) Insertar UNA FILA POR PRODUCTO DEL CARRITO
    $sql = "
        INSERT INTO compra
        (IDCli, IDLoc, CodigoArt, Cantidad, Fecha, FechaIniPrecio, Valida)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ";

    $stmt = $conexion->prepare($sql);

    foreach ($carrito as $item) {

        $codigoArt = $item['codigoArt'];
        $cantidad  = 1; // porque el carrito agrega de a uno
        $fechaIniPrecio = date('Y-m-d'); // usás siempre hoy? (si querés uso del precio real)

        $stmt->execute([
            $idCliente,
            $item['idLocal'],
            $codigoArt,
            $cantidad,
            $fecha,
            $item['fechaIniPrecio'] ?? '2022-01-01' // o fecha actual
        ]);
    }

    $conexion->commit();

    // Vaciar carrito
    unset($_SESSION['carrito']);

    header("Location: ../pages/verCarrito.php?ok=1");
    exit();

} catch (Exception $e) {
    $conexion->rollBack();
    error_log("Error en compra: " . $e->getMessage());
    header("Location: ../pages/verCarrito.php?error=bd");
    exit();
}
