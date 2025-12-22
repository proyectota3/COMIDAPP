<?php
// =====================================================
// VALIDACIÓN LOGIN COMIDAPP (MODO MIXTO + CAMBIO FORZADO)
// - Soporta pass vieja en texto plano (ej: 123)
// - Soporta pass hasheada (password_hash)
// - Convierte automáticamente texto plano a hash
// - Obliga a cambiar contraseña si DebeCambiarPass = 1
// =====================================================

// 🔹 Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../modelo/connectionComidApp.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../loginApp.php");
    exit();
}

// =====================================================
// 1) Tomar datos del formulario
// =====================================================
$user = trim($_POST['user'] ?? '');   // Mail
$pass = trim($_POST['pass'] ?? '');   // Contraseña ingresada

if ($user === '' || $pass === '') {
    header("Location: ../loginApp.php?error=empty");
    exit();
}

try {
    // =================================================
    // 2) Conexión a la BD
    // =================================================
    $db   = new DatabaseComidApp();
    $conn = $db->getConnection();

    // =================================================
    // 3) Buscar usuario por mail (case-insensitive)
    // =================================================
    $sql = "SELECT ID, idRol, Direccion, Mail, Nombre, pass, DebeCambiarPass
            FROM usuariosweb
            WHERE LOWER(Mail) = LOWER(:mail)
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':mail', $user, PDO::PARAM_STR);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resultado) {
        header("Location: ../loginApp.php?error=credenciales");
        exit();
    }

    // =================================================
    // 4) Preparar datos
    // =================================================
    $idUsuario       = (int)$resultado['ID'];
    $passGuardada    = trim((string)$resultado['pass']);
    $debeCambiarPass = (int)$resultado['DebeCambiarPass'];

    $loginOK = false;

    // =================================================
    // 5) Detectar si la contraseña es HASH o TEXTO PLANO
    // =================================================
    $pareceHash =
        str_starts_with($passGuardada, '$2y$') ||
        str_starts_with($passGuardada, '$2a$') ||
        str_starts_with($passGuardada, '$argon2');

    if ($pareceHash) {
        // 🔐 CONTRASEÑA HASHEADA
        if (password_verify($pass, $passGuardada)) {
            $loginOK = true;

            // (Opcional) Rehash si cambia el algoritmo
            if (password_needs_rehash($passGuardada, PASSWORD_BCRYPT)) {
                $nuevoHash = password_hash($pass, PASSWORD_BCRYPT);
                $up = $conn->prepare(
                    "UPDATE usuariosweb SET pass = :pass WHERE ID = :id"
                );
                $up->execute([
                    ':pass' => $nuevoHash,
                    ':id'   => $idUsuario
                ]);
            }
        }
    } else {
        // 🔓 CONTRASEÑA EN TEXTO PLANO (vieja)
        if ($passGuardada === $pass) {
            $loginOK = true;

            // ✅ Convertir automáticamente a hash
            $nuevoHash = password_hash($pass, PASSWORD_BCRYPT);
            $up = $conn->prepare(
                "UPDATE usuariosweb SET pass = :pass WHERE ID = :id"
            );
            $up->execute([
                ':pass' => $nuevoHash,
                ':id'   => $idUsuario
            ]);
        }
    }

    // =================================================
    // 6) Si no coincide la contraseña → error
    // =================================================
    if (!$loginOK) {
        header("Location: ../loginApp.php?error=credenciales");
        exit();
    }

    // =================================================
    // 7) Login correcto → guardar sesión
    // =================================================
    $_SESSION['id']        = $resultado['ID'];
    $_SESSION['user']      = $resultado['Mail'];
    $_SESSION['rol']       = $resultado['idRol'];
    $_SESSION['nombre']    = $resultado['Nombre'];
    $_SESSION['direccion'] = $resultado['Direccion'];

    // =================================================
    // 8) ¿Debe cambiar contraseña?
    // =================================================
    if ($debeCambiarPass === 1) {
        header("Location: ../pages/cambiarPass.php");
        exit();
    }

    // =================================================
    // 9) Login normal → index
    // =================================================
    header("Location: ../indexApp.php");
    exit();

} catch (Exception $e) {
    // En producción no mostramos el error
    header("Location: ../loginApp.php?error=credenciales");
    exit();
}
?>
