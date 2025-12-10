<?php
session_start();

// 1) Verificar que esté logueado y sea cliente
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] != 3) {
    header("Location: ../loginApp.php");
    exit();
}

$idCliente = (int)$_SESSION['id'];

// 2) Conexión BD
require_once __DIR__ . "/../modelo/connectionComidApp.php";
$db = new DatabaseComidApp();
$conexion = $db->getConnection();

// 3) Consultar historial de compras
$sql = "
    SELECT 
        c.NumFactura,
        c.Fecha,
        c.Cantidad,
        l.Nombre      AS LocalNombre,
        a.Nombre      AS ArticuloNombre,
        v.Precio      AS PrecioUnit,
        (v.Precio * c.Cantidad) AS Subtotal
    FROM compra c
    JOIN local l 
        ON c.IDLoc = l.ID
    JOIN articulos a
        ON c.CodigoArt = a.Codigo
    JOIN vende v
        ON c.IDLoc = v.IDLoc
    AND c.CodigoArt = v.CodigoArt
    AND c.FechaIniPrecio = v.FechaIniPrecio
    WHERE c.IDCli = :idCli
    ORDER BY c.Fecha DESC, c.NumFactura DESC
";

$stmt = $conexion->prepare($sql);
$stmt->execute([':idCli' => $idCliente]);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4) Total gastado
$totalGastado = 0;
foreach ($compras as $c) {
    $totalGastado += $c['Subtotal'];
}

// Retornar datos a la vista
return [
    "compras"       => $compras,
    "totalGastado"  => $totalGastado
];
