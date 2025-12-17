<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id'], $_SESSION['rol']) || $_SESSION['rol'] != 2) {
  header("Location: ../loginApp.php");
  exit();
}

$idEmpresa = (int)$_SESSION['id']; // OJO: si tu session id es de usuariosweb y no IDEmp, ajustamos

require_once __DIR__ . "/../modelo/connectionComidApp.php";
$db = new DatabaseComidApp();
$cn = $db->getConnection();

/*
  Ajustá este JOIN según tu modelo:
  empresa -> local (local.IDEmp = empresa.IDEmp) por ejemplo.
*/
$sql = "
SELECT
  c.NumFactura,
  c.Fecha,
  c.IDLoc,
  l.Nombre AS LocalNombre,
  c.FormaPago,
  c.Delivery,
  c.DireccionEntrega,
  c.Valida,
  SUM(v.Precio * c.Cantidad) AS Total
FROM compra c
JOIN local l ON c.IDLoc = l.ID
JOIN vende v ON v.IDLoc = c.IDLoc AND v.CodigoArt = c.CodigoArt AND v.FechaIniPrecio = c.FechaIniPrecio
WHERE c.Valida = 0
  AND l.IDEmp = :idEmp
GROUP BY c.NumFactura, c.Fecha, c.IDLoc, l.Nombre, c.FormaPago, c.Delivery, c.DireccionEntrega, c.Valida
ORDER BY c.Fecha DESC, c.NumFactura DESC
";

$stmt = $cn->prepare($sql);
$stmt->execute([':idEmp' => $idEmpresa]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

return ["pedidos" => $pedidos];
