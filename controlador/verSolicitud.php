<?php
require_once "../modelo/connectionComidApp.php";

$db = new DatabaseComidApp();
$conexion = $db->getConnection();

if (!$conexion) {
    die("Error: No se pudo establecer la conexión a la base de datos.");
}

$sql = "
    SELECT 
        ID,
        RUT,
        Nombre,
        Mail,
        Telefono,
        Fecha,
        Estado
    FROM solicitud
    ORDER BY Fecha DESC, ID DESC
";

$stmt = $conexion->prepare($sql);
$stmt->execute();

$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ devolvemos la data para que la vista la use
return [
    "solicitudes" => $solicitudes
];
