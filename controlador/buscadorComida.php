<?php
require_once "../modelo/connectionComidApp.php"; // Incluye la conexión

header('Content-Type: application/json');

try {
    // Conexión a la base de datos
    $db = new DatabaseComidApp();
    $conexion = $db->getConnection();

    if (!$conexion) {
        throw new Exception("No se pudo conectar a la base de datos.");
    }

    // Obtén el término de búsqueda
    $termino = isset($_GET['q']) ? trim($_GET['q']) : '';

    if (!empty($termino)) {
        // Consulta para buscar por Nombre o Dirección
        $stmt = $conexion->prepare("
            SELECT Nombre, Direccion 
            FROM local 
            WHERE Nombre LIKE :termino OR Direccion LIKE :termino 
            LIMIT 10
        ");
        $stmt->bindValue(':termino', '%' . $termino . '%');
        $stmt->execute();

        // Devuelve los resultados en formato JSON
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($resultados);
    } else {
        echo json_encode([]); // Si no hay término de búsqueda, devuelve un arreglo vacío
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
