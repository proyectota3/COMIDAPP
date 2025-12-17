<?php
/**
 * controlador/verCompras.php
 * ---------------------------------------------------------
 * CONTROLADOR (solo lectura) para el historial de compras del CLIENTE (rol 3)
 *
 * Devuelve:
 * - facturas: array agrupado por NumFactura (cada factura contiene líneas)
 * - totalGastado: suma total de todos los subtotales
 *
 * IMPORTANTE:
 * - La tabla compra tiene UNA FILA por artículo.
 * - Para mostrar estado por factura:
 *      MIN(Valida) = 0 => hay al menos un item pendiente => factura PENDIENTE
 *      MIN(Valida) = 1 => todos entregados => factura ENTREGADA
 */


// 1) Verificar que esté logueado y sea cliente
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || (int)$_SESSION['rol'] !== 3) {
    header("Location: ../loginApp.php");
    exit();
}

$idCliente = (int)$_SESSION['id'];

// 2) Conexión BD
require_once __DIR__ . "/../modelo/connectionComidApp.php";
$db = new DatabaseComidApp();
$conexion = $db->getConnection();

/**
 * 3) Consultar historial
 * - Traemos líneas de compra + precio unitario desde VENDE
 * - Traemos también Valida por línea
 *
 * OJO: la relación con VENDE incluye FechaIniPrecio (para agarrar el precio de ese momento)
 */
$sql = "
    SELECT 
        c.NumFactura,
        c.Fecha,
        c.Cantidad,
        c.Valida,                 -- ✅ Estado por línea (0 pendiente / 1 entregado)
        c.FormaPago,
        c.Delivery,
        c.DireccionEntrega,

        l.Nombre AS LocalNombre,
        a.Nombre AS ArticuloNombre,
        v.Precio AS PrecioUnit,
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
    $nf = (int)$c['NumFactura'];

    // Si es la primera vez que vemos esta factura, la creamos
    if (!isset($facturas[$nf])) {
        $facturas[$nf] = [
            'NumFactura'       => $nf,
            'Fecha'            => $c['Fecha'],
            'LocalNombre'      => $c['LocalNombre'],

            // ✅ Checkout info (si existe)
            'FormaPago'        => $c['FormaPago'] ?? null,
            'Delivery'         => isset($c['Delivery']) ? (int)$c['Delivery'] : null,
            'DireccionEntrega' => $c['DireccionEntrega'] ?? null,

            // ✅ Estado de la factura:
            // arrancamos con el estado de la primera línea y luego lo vamos “bajando” si aparece un 0
            'Valida'           => (int)($c['Valida'] ?? 0),

            // Totales y líneas
            'totalFactura'     => 0,
            'lineas'           => [],
        ];
    } else {
        /**
         * ✅ Si una factura tiene varias líneas:
         * - Si alguna línea está en 0 => factura debe quedar 0 (Pendiente)
         * - Si todas son 1 => queda 1 (Entregado)
         *
         * Esto lo logramos así: si aparece una línea Valida=0, forzamos la factura a 0.
         */
        if ((int)($c['Valida'] ?? 0) === 0) {
            $facturas[$nf]['Valida'] = 0;
        }
    }

    // Guardamos cada línea
    $facturas[$nf]['lineas'][] = [
        'ArticuloNombre' => $c['ArticuloNombre'],
        'Cantidad'       => (int)$c['Cantidad'],
        'PrecioUnit'     => (float)$c['PrecioUnit'],
        'Subtotal'       => (float)$c['Subtotal'],
    ];

    // Sumatorias
    $facturas[$nf]['totalFactura'] += (float)$c['Subtotal'];
    $totalGastado += (float)$c['Subtotal'];
}

// 5) Retornar datos a la vista
return [
    "facturas"      => $facturas,
    "totalGastado"  => $totalGastado
];
