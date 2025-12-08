<?php
session_start();

// 1) Verificar que sea empresa
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {
    header("Location: ../loginApp.php");
    exit();
}

/*
 * 2) Tomar el IDEmp de la sesión
 *    - Si ya guardás IDEmp en $_SESSION['idEmp'], lo usamos.
 *    - Si no, usamos $_SESSION['id'] (muchos proyectos guardan ahí el IDEmp directamente).
 */
$idEmp = null;

// Si existe idEmp explícito, usamos ese
if (isset($_SESSION['idEmp'])) {
    $idEmp = $_SESSION['idEmp'];
} else {
    // Fallback: usamos el id genérico como IDEmp
    $idEmp = $_SESSION['id'];
}

// Seguridad mínima
if ($idEmp === null) {
    die("Error: No se encontró el ID de la empresa en la sesión.");
}

// 3) Conexión BD
require_once "../modelo/connectionComidApp.php";
$db = new DatabaseComidApp();
$conexion = $db->getConnection();

// 4) Traer locales de ESA empresa
$stmt = $conexion->prepare("
    SELECT ID, Nombre, Direccion, Foto, Delivery
    FROM local
    WHERE IDEmp = ?
");
$stmt->execute([$idEmp]);
$locales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Carrito para el nav
$cantidadCarrito = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;

// 6) Devolver datos a la vista
return [
    "locales" => $locales,
    "cantidadCarrito" => $cantidadCarrito
];
