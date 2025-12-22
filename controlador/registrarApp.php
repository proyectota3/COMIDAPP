<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../modelo/connectionComidApp.php";

function generarPassword($length = 10) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
    $pass = '';
    for ($i = 0; $i < $length; $i++) {
        $pass .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pass;
}

function validarEmail($mail) {
    return filter_var($mail, FILTER_VALIDATE_EMAIL);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../loginApp.php");
    exit();
}

$nombre    = trim($_POST["nombre"] ?? "");
$direccion = trim($_POST["direccion"] ?? "");
$mail      = trim($_POST["mail"] ?? "");

if ($nombre === "" || $direccion === "" || !$mail || !validarEmail($mail)) {
    header("Location: ../loginApp.php?reg=error");
    exit();
}

try {
    $db = new DatabaseComidApp();
    $pdo = $db->getConnection();

    // mail repetido
    $stmt = $pdo->prepare("SELECT 1 FROM usuariosweb WHERE LOWER(Mail)=LOWER(?)");
    $stmt->execute([$mail]);
    if ($stmt->fetchColumn()) {
        header("Location: ../loginApp.php?reg=existe");
        exit();
    }

    // generar pass
    $passPlano = generarPassword(10);
    $hash      = password_hash($passPlano, PASSWORD_BCRYPT);

    // rol cliente
    $rolCliente = 3;

    // insertar
   $stmt = $pdo->prepare("
  INSERT INTO usuariosweb (idRol, Direccion, Mail, Nombre, pass, DebeCambiarPass)
  VALUES (?, ?, ?, ?, ?, 1)
");

    $stmt->execute([$rolCliente, $direccion, $mail, $nombre, $hash]);

    // DEMO: mostrar password una vez
    $_SESSION["mail_generado"] = $mail;
    $_SESSION["pass_generada"] = $passPlano;

    header("Location: ../loginApp.php?reg=ok");
    exit();

} catch (Exception $e) {
    header("Location: ../loginApp.php?reg=error");
    exit();
}
