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

// 3) Consultar historial de compras (una fila = un artículo de una factura)
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
$lineas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4) Agrupar líneas por NumFactura (una factura = varios artículos)
$facturas = [];
$totalGastado = 0;

foreach ($lineas as $c) {
    $nf = $c['NumFactura'];

    if (!isset($facturas[$nf])) {
        $facturas[$nf] = [
            'NumFactura'   => $nf,
            'Fecha'        => $c['Fecha'],
            'LocalNombre'  => $c['LocalNombre'],
            'totalFactura' => 0,
            'lineas'       => [],
        ];
    }

    $facturas[$nf]['lineas'][] = [
        'ArticuloNombre' => $c['ArticuloNombre'],
        'Cantidad'       => (int)$c['Cantidad'],
        'PrecioUnit'     => (float)$c['PrecioUnit'],
        'Subtotal'       => (float)$c['Subtotal'],
    ];

    $facturas[$nf]['totalFactura'] += (float)$c['Subtotal'];
    $totalGastado += (float)$c['Subtotal'];
}

// Retornar datos a la vista
return [
    "facturas"      => $facturas,
    "totalGastado"  => $totalGastado
];
