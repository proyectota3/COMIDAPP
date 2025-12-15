<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../modelo/connectionComidApp.php";
require_once __DIR__ . "/../modelo/perfilModel.php";

if (!isset($_SESSION["id"]) || !isset($_SESSION["rol"])) {
    header("Location: ../pages/login.php");
    exit();
}

$id  = (int)$_SESSION["id"];
$rol = (int)$_SESSION["rol"];

$db  = new DatabaseComidApp();
$pdo = $db->getConnection();
$model = new PerfilModel($pdo);

$mensaje = "";
$error = "";
$tipo = ($rol === 3) ? "cliente" : "empresa";

// Guardar cambios
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($rol === 3) {
        $nombre     = trim($_POST["Nombre"] ?? "");
        $direccion  = trim($_POST["Direccion"] ?? "");
        $apellido   = trim($_POST["Apellido"] ?? "");
        $formaPago  = trim($_POST["FormaDePago"] ?? "");
        $telefono   = trim($_POST["Telefono"] ?? "");

        if ($nombre === "" || $apellido === "" || $direccion === "") {
            $error = "Nombre, Apellido y Dirección son obligatorios.";
        } else {
            $ok = $model->updateCliente($id, $nombre, $direccion, $apellido, $formaPago);

            if ($ok && $telefono !== "" && ctype_digit($telefono)) {
                $model->upsertTelefonoCliente($id, (int)$telefono);
            }

            $mensaje = $ok ? "Perfil actualizado ✅" : "";
            $error   = $ok ? "" : "No se pudo actualizar el perfil.";
        }
    } else {
        $direccionEmpresa = trim($_POST["DireccionEmpresa"] ?? "");
        $telefono = trim($_POST["Telefono"] ?? "");

        if ($direccionEmpresa === "") {
            $error = "La dirección de la empresa es obligatoria.";
        } else {
            $ok = $model->updateEmpresa($id, $direccionEmpresa);

            if ($ok && $telefono !== "" && ctype_digit($telefono)) {
                $model->upsertTelefonoEmpresa($id, (int)$telefono);
            }

            $mensaje = $ok ? "Perfil actualizado ✅" : "";
            $error   = $ok ? "" : "No se pudo actualizar el perfil.";
        }
    }
}

// Cargar datos para mostrar
if ($rol === 3) {
    $perfil = $model->getPerfilCliente($id);
} else {
    $perfil = $model->getPerfilEmpresa($id);
}

return [
    "tipo" => $tipo,
    "perfil" => $perfil,
    "mensaje" => $mensaje,
    "error" => $error
];
