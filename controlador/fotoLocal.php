<?php
require_once __DIR__ . "/../modelo/connectionComidApp.php";


$db = new DatabaseComidApp();
$conexion = $db->getConnection();

// Consulta a la base de datos para obtener las sucursales
$query = "SELECT * FROM local"; 
$resultado = $conexion->query($query);

// Almacena los resultados en un arreglo para pasarlos a la vista
$sucursales = $resultado->fetchAll(PDO::FETCH_ASSOC);

