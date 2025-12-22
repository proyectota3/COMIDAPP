<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['id'])) { header("Location: ../loginApp.php"); exit(); }

require_once "../modelo/connectionComidApp.php";

$pass1 = trim($_POST["pass1"] ?? "");
$pass2 = trim($_POST["pass2"] ?? "");

if ($pass1 === "" || $pass2 === "") {
    header("Location: ../pages/cambiarPassCli.php?e=Campos%20vac%C3%ADos");
    exit();
}
if ($pass1 !== $pass2) {
    header("Location: ../pages/cambiarPassCli.php?e=No%20coinciden");
    exit();
}
if (strlen($pass1) < 6) {
    header("Location: ../pages/cambiarPasCli.php?e=M%C3%ADnimo%206%20caracteres");
    exit();
}

$db = new DatabaseComidApp();
$pdo = $db->getConnection();

$nuevoHash = password_hash($pass1, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("UPDATE usuariosweb SET pass = ?, DebeCambiarPass = 0 WHERE ID = ?");
$stmt->execute([$nuevoHash, $_SESSION['id']]);

header("Location: ../indexApp.php");
exit();
