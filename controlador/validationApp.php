// Este código se conecta a la base de datos usando DatabaseComidApp,
// obtiene el ID enviado por GET, ejecuta una consulta preparada para
// buscar la sucursal correspondiente y devuelve los datos en formato JSON.
// Si el ID no existe en la tabla, responde un mensaje indicando que no se encontró.


<?php
session_start();
require_once '../modelo/connectionComidApp.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user = trim($_POST['user']);
    $pass = trim($_POST['pass']);

    // Validar campos vacíos
    if (empty($user) || empty($pass)) {
        header("Location: ../loginApp.php?error=empty");
        exit();
    }

    $db = new DatabaseComidApp();
    $conn = $db->getConnection();

    // OJO: la columna se llama "pass" y "idRol"
    $sql = "SELECT * FROM usuariosweb WHERE Mail = :mail AND pass = :pass";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':mail', $user);
    $stmt->bindParam(':pass', $pass);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {

        // Guardamos datos útiles de sesión
        $_SESSION['id']     = $resultado['ID'];
        $_SESSION['user']   = $resultado['Mail'];
        $_SESSION['rol']    = $resultado['idRol'];      // 👈 AQUÍ EL ROL CORRECTO
        $_SESSION['nombre'] = $resultado['Nombre'];

        header("Location: ../indexApp.php");
        exit();

    } else {
        header("Location: ../loginApp.php?error=badlogin");
        exit();
    }
}
?>
