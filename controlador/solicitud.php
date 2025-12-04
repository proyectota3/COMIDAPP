<?php
// Incluye el archivo de conexión
require_once "../modelo/connectionComidApp.php";

try {
    $database = new DatabaseComidApp();
    $bd = $database->getConnection();
} catch (Exception $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}

// Verifica si se enviaron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['solicitud'])) {

    // Sanitización de datos
    $RUT       = isset($_POST['RUT']) ? trim(htmlspecialchars($_POST['RUT'])) : "";
    $Nombre    = isset($_POST['Nombre']) ? trim(htmlspecialchars($_POST['Nombre'])) : "";
    $Direccion = isset($_POST['Direccion']) ? trim(htmlspecialchars($_POST['Direccion'])) : "";
    $Mail      = isset($_POST['Mail']) ? trim($_POST['Mail']) : "";
    $Telefono  = isset($_POST['Telefono']) ? trim($_POST['Telefono']) : "";

    // Validaciones básicas
    if (!$RUT || !$Nombre || !$Direccion || !$Mail || !$Telefono) {
        die("Error: Todos los campos son obligatorios.");
    }

    if (!filter_var($Mail, FILTER_VALIDATE_EMAIL)) {
        die("Error: El correo electrónico no es válido.");
    }

    try {
        // Iniciar transacción
        $bd->beginTransaction();

        /*
         * 1) Verificar si el usuario ya existe en usuariosweb por Mail
         */
        $sql_check_user = $bd->prepare("SELECT ID FROM usuariosweb WHERE Mail = :Mail");
        $sql_check_user->bindParam(':Mail', $Mail);
        $sql_check_user->execute();
        $usuario_existente = $sql_check_user->fetch(PDO::FETCH_ASSOC);

        if ($usuario_existente) {
            // Ya existe → usamos ese ID
            $usuario_id = (int)$usuario_existente['ID'];

            // Opcional: podrías actualizar Direccion/Nombre si querés mantenerlos al día
            /*
            $sql_update_user = $bd->prepare("
                UPDATE usuariosweb
                SET Direccion = :Direccion, Nombre = :Nombre
                WHERE ID = :ID
            ");
            $sql_update_user->execute([
                ':Direccion' => $Direccion,
                ':Nombre'    => $Nombre,
                ':ID'        => $usuario_id
            ]);
            */
        } else {
            // 2) Insertar nuevo usuario en usuariosweb
            // OJO: la tabla tiene campo 'pass', acá le pongo una por defecto '123'
            $pass_por_defecto = '123';

            $sql_insert_user = $bd->prepare("
                INSERT INTO usuariosweb (Direccion, Mail, Nombre, pass)
                VALUES (:Direccion, :Mail, :Nombre, :pass)
            ");
            $sql_insert_user->execute([
                ':Direccion' => $Direccion,
                ':Mail'      => $Mail,
                ':Nombre'    => $Nombre,
                ':pass'      => $pass_por_defecto
            ]);

            $usuario_id = (int)$bd->lastInsertId();
        }

        /*
         * 3) Verificar si ya existe una empresa con ese RUT
         */
        $sql_check_emp = $bd->prepare("SELECT IDEmp FROM empresa WHERE RUT = :RUT");
        $sql_check_emp->bindParam(':RUT', $RUT);
        $sql_check_emp->execute();
        $empresa_existente = $sql_check_emp->fetch(PDO::FETCH_ASSOC);

        if ($empresa_existente) {
            // Ya existe empresa → usamos ese IDEmp
            $empresa_id = (int)$empresa_existente['IDEmp'];

            // Opcional: actualizar datos de la empresa
            /*
            $sql_update_emp = $bd->prepare("
                UPDATE empresa
                SET Direccion = :Direccion, Mail = :Mail, Nombre = :Nombre
                WHERE RUT = :RUT
            ");
            $sql_update_emp->execute([
                ':Direccion' => $Direccion,
                ':Mail'      => $Mail,
                ':Nombre'    => $Nombre,
                ':RUT'       => $RUT
            ]);
            */
        } else {
            // 4) Insertar NUEVA empresa
            // Validacion: 0 = pendiente / no validada
            $sql_insert_emp = $bd->prepare("
                INSERT INTO empresa (IDEmp, RUT, Direccion, Mail, Nombre, Validacion)
                VALUES (:IDEmp, :RUT, :Direccion, :Mail, :Nombre, 0)
            ");
            $sql_insert_emp->execute([
                ':IDEmp'     => $usuario_id,
                ':RUT'       => $RUT,
                ':Direccion' => $Direccion,
                ':Mail'      => $Mail,
                ':Nombre'    => $Nombre
            ]);

            $empresa_id = $usuario_id; // IDEmp es el mismo que ID de usuariosweb
        }

        /*
         * 5) Insertar teléfono en telefonosempresa
         *    La PK es (IDEmp, Telefono), por lo que un mismo IDEmp puede tener varios teléfonos.
         *    Si querés evitar duplicados exactos, podés chequear antes.
         */

        $sql_check_tel = $bd->prepare("
            SELECT 1 FROM telefonosempresa 
            WHERE IDEmp = :IDEmp AND Telefono = :Telefono
            LIMIT 1
        ");
        $sql_check_tel->execute([
            ':IDEmp'    => $empresa_id,
            ':Telefono' => $Telefono
        ]);

        if (!$sql_check_tel->fetch(PDO::FETCH_ASSOC)) {
            $sql_insert_tel = $bd->prepare("
                INSERT INTO telefonosempresa (IDEmp, Telefono)
                VALUES (:IDEmp, :Telefono)
            ");
            $sql_insert_tel->execute([
                ':IDEmp'    => $empresa_id,
                ':Telefono' => $Telefono
            ]);
        }

        /*
         * 6) Insertar solicitud
         *    Tabla: solicitud (ID AI, RUT, Fecha, Estado)
         *    FK: RUT -> empresa.RUT (por eso primero insertamos/aseguramos empresa)
         */
        $sql_insert_sol = $bd->prepare("
            INSERT INTO solicitud (RUT, Fecha, Estado)
            VALUES (:RUT, CURDATE(), 'Pendiente')
        ");
        $sql_insert_sol->execute([
            ':RUT' => $RUT
        ]);

        // Confirmar la transacción
        $bd->commit();

        // Redireccionar después de la inserción
        header('Location: ../pages/contacto.php?ok=1');
        exit;

    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $bd->rollBack();
        echo "Error al procesar la solicitud: " . $e->getMessage();
    }
}
?>
