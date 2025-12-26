<?php
// controlador/solicitud.php
require_once "../modelo/connectionComidApp.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/contacto.php?e=metodo");
    exit();
}

if (!isset($_POST["solicitud"])) {
    header("Location: ../pages/contacto.php?e=post");
    exit();
}

// 1) Tomar datos
$RUT       = trim($_POST["RUT"] ?? "");
$Nombre    = trim($_POST["Nombre"] ?? "");
$Direccion = trim($_POST["Direccion"] ?? "");
$Mail      = trim($_POST["Mail"] ?? "");
$Telefono  = trim($_POST["Telefono"] ?? "");

// 2) Validaciones básicas
if ($RUT === "" || $Nombre === "" || $Direccion === "" || $Mail === "" || $Telefono === "") {
    header("Location: ../pages/contacto.php?e=campos");
    exit();
}

if (!filter_var($Mail, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../pages/contacto.php?e=mail_invalido");
    exit();
}

try {
    // 3) Conexión
    $db = new DatabaseComidApp();
    $bd = $db->getConnection();

    if (!$bd) {
        throw new Exception("No se pudo conectar a la base de datos.");
    }

    // 4) Evitar duplicados: misma solicitud pendiente por RUT o Mail
    $stmt = $bd->prepare("
        SELECT 1
        FROM solicitud
        WHERE Estado = 'Pendiente'
          AND (RUT = :RUT OR Mail = :Mail)
        LIMIT 1
    ");
    $stmt->execute([
        ":RUT"  => $RUT,
        ":Mail" => $Mail
    ]);

    if ($stmt->fetchColumn()) {
        header("Location: ../pages/contacto.php?dup=1");
        exit();
    }

    // 5) Insertar solicitud (SIN crear usuario/empresa)
    $stmt = $bd->prepare("
        INSERT INTO solicitud (RUT, Nombre, Direccion, Mail, Telefono, Fecha, Estado)
        VALUES (:RUT, :Nombre, :Direccion, :Mail, :Telefono, CURDATE(), 'Pendiente')
    ");

    $stmt->execute([
        ":RUT"       => $RUT,
        ":Nombre"    => $Nombre,
        ":Direccion" => $Direccion,
        ":Mail"      => $Mail,
        ":Telefono"  => $Telefono
    ]);

    // 6) OK
    header("Location: ../pages/contacto.php?ok=1");
    exit();

} catch (Exception $e) {
    // ✅ Para debug real (recomendado mientras arreglás):
    header("Location: ../pages/contacto.php?e=" . urlencode($e->getMessage()));
    exit();

    // ✅ Para producción (cuando ya esté todo OK), usás:
    // header("Location: ../pages/contacto.php?e=error");
    // exit();
}
 ?>