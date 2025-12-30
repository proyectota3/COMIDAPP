<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * CONTROLADOR: verVentas.php
 * --------------------------
 * Rol: Empresa (rol 2)
 *
 * Objetivo:
 * - Traer facturas (ventas) de los locales de la empresa
 * - Agrupar por NumFactura (una factura = varias líneas en compra)
 * - Calcular total de la factura (SUM(precio * cantidad))
 * - Separar en:
 *   - pendientes: Valida = 0
 *   - entregados: Valida = 1
 *
 * Devuelve:
 * [
 *   'pendientes' => [...],
 *   'entregados' => [...]
 * ]
 */

// 1) Seguridad: solo empresa (rol 2)
if (!isset($_SESSION['id'], $_SESSION['rol']) || (int)$_SESSION['rol'] !== 2) {
    header("Location: ../loginApp.php");
    exit();
}

/**
 * ⚠️ Nota importante:
 * Acá asumimos que $_SESSION['id'] representa el ID de la empresa (IDEmp)
 * y que local tiene un campo IDEmp que apunta a esa empresa.
 *
 * Si en tu sistema la sesión guarda otro id (ej: usuariosweb), hay que mapearlo.
 */
$idEmpresa = (int) $_SESSION['id'];

// 2) Conexión BD
require_once __DIR__ . "/../modelo/connectionComidApp.php";
$db = new DatabaseComidApp();
$cn = $db->getConnection();

// 3) Traer facturas agrupadas por NumFactura
$sql = "
SELECT
    c.NumFactura,
    c.Fecha,
    c.IDLoc,
    l.Nombre AS LocalNombre,

    -- ✅ Datos del cliente
    u.Nombre AS ClienteNombre,
    u.Mail   AS ClienteMail,

    -- Datos checkout
    c.FormaPago,
    c.Delivery,
    c.DireccionEntrega,

    -- Estado
    c.Valida,

    -- Total por factura
    SUM(v.Precio * c.Cantidad) AS Total
FROM compra c
JOIN local l
    ON l.ID = c.IDLoc
JOIN vende v
    ON v.IDLoc = c.IDLoc
   AND v.CodigoArt = c.CodigoArt
   AND v.FechaIniPrecio = c.FechaIniPrecio

-- ✅ JOIN cliente (acá está lo nuevo)
JOIN usuariosweb u
    ON u.ID = c.IDCli

WHERE l.IDEmp = :idEmp
GROUP BY
    c.NumFactura, c.Fecha, c.IDLoc, l.Nombre,
    u.Nombre, u.Mail,
    c.FormaPago, c.Delivery, c.DireccionEntrega, c.Valida
ORDER BY
    c.Valida ASC,
    c.Fecha DESC,
    c.NumFactura DESC
";


$stmt = $cn->prepare($sql);
$stmt->execute([':idEmp' => $idEmpresa]);
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4) Separar pendientes / entregados para que la VISTA no tenga lógica
$pendientes = [];
$entregados = [];

foreach ($facturas as $f) {
    $estado = (int)($f['Valida'] ?? 0);

    // Normalizar tipos por prolijidad
    $f['NumFactura'] = (int)$f['NumFactura'];
    $f['IDLoc'] = (int)$f['IDLoc'];
    $f['Delivery'] = (int)($f['Delivery'] ?? 0);
    $f['Valida'] = $estado;
    $f['Total'] = (float)($f['Total'] ?? 0);

    if ($estado === 1) {
        $entregados[] = $f;
    } else {
        $pendientes[] = $f;
    }
}

// 5) Retornar a la vista
return [
    "pendientes" => $pendientes,
    "entregados" => $entregados
];
