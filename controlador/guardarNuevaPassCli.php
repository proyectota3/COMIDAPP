<?php
// 🚫 NADA antes de <?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 Validar sesión
if (!isset($_SESSION['id'])) {
    header("Location: ../loginApp.php");
    exit();
}

require_once "../modelo/connectionComidApp.php";

// 🔹 Datos
$pass1 = $_POST['pass1'] ?? '';
$pass2 = $_POST['pass2'] ?? '';

// 🔹 Validaciones
if ($pass1 === '' || $pass2 === '') {
    header("Location: ../pages/cambiarPassCli.php?e=Campos%20vacíos");
    exit();
}

if ($pass1 !== $pass2) {
    header("Location: ../pages/cambiarPassCli.php?e=No%20coinciden");
    exit();
}

if (strlen($pass1) < 6) {
    header("Location: ../pages/cambiarPassCli.php?e=Mínimo%206%20caracteres");
    exit();
}

// 🔌 BD
$db  = new DatabaseComidApp();
$pdo = $db->getConnection();

// 🔐 Hash
$nuevoHash = password_hash($pass1, PASSWORD_DEFAULT);

// 🔄 Update
$sql = "UPDATE usuariosweb 
        SET pass = :pass, DebeCambiarPass = 0 
        WHERE ID = :id";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':pass', $nuevoHash);
$stmt->bindValue(':id', $_SESSION['id'], PDO::PARAM_INT);

if (!$stmt->execute()) {
    die("Error al actualizar contraseña");
}

// 🧹 Limpiar flag
unset($_SESSION['forzar_cambio_pass']);

// ✅ REDIRECCIÓN FINAL
header("Location: ../indexApp.php");
exit();
