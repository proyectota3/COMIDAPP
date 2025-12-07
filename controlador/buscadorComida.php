<?php
require_once "../modelo/connectionComidApp.php"; // Conexión a la BD

header('Content-Type: application/json');

try {
    $db = new DatabaseComidApp();
    $conexion = $db->getConnection();

    if (!$conexion) {
        throw new Exception("No se pudo conectar a la base de datos.");
    }

    // Obtener término de búsqueda
    $termino = isset($_GET['q']) ? trim($_GET['q']) : '';

    if (!empty($termino)) {

        // Buscar en la tabla local (Nombre o Dirección)
        $stmt = $conexion->prepare("
            SELECT Nombre, Direccion 
            FROM local 
            WHERE Nombre LIKE :termino OR Direccion LIKE :termino 
            LIMIT 10
        ");

        $stmt->bindValue(':termino', '%' . $termino . '%');
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($resultados);
    } else {
        // Sin texto → array vacío
        echo json_encode([]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
