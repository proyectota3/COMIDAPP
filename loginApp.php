<?php
// 🔹 Iniciamos sesión al comienzo del archivo
// Es necesario para usar $_SESSION (login y mensaje DEMO)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMIDAPP LOGIN</title>
    
    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- CSS del login -->
    <link rel="stylesheet" href="loginApp.css">
</head>
<body>

<main>
    <div class="contenedor__todo">

        <!-- ================= CAJA TRASERA (MENSAJES) ================= -->
        <div class="caja__trasera">

            <!-- Mensaje para LOGIN -->
            <div class="caja__trasera-login">
                <h3>¿Ya tienes una cuenta?</h3>
                <p>Inicia sesión para entrar en la página</p>
                <button id="btn__iniciar-sesion">Iniciar Sesión</button>
            </div>

            <!-- Mensaje para REGISTER -->
            <div class="caja__trasera-register">
                <h3>¿Aún no tienes una cuenta?</h3>
                <p>Regístrate para que puedas iniciar sesión</p>
                <button id="btn__registrarse">Regístrarse</button>
            </div>

        </div>
        <!-- ================= FIN CAJA TRASERA ================= -->


        <!-- ================= CONTENEDOR DE FORMULARIOS ================= -->
        <div class="contenedor__login-register">

            <!-- ================= FORMULARIO LOGIN ================= -->
            <form action="controlador/validationApp.php" 
                  class="formulario__login" 
                  method="POST">

                <h2>Iniciar Sesión</h2>

                <!-- 🔹 MENSAJE DEMO (solo aparece luego de registrarse) -->
                <?php if (!empty($_SESSION["mail_generado"]) && !empty($_SESSION["pass_generada"])): ?>
                    <div class="demo-alert">
                        <b>Cuenta creada (DEMO)</b><br>
                        Mail: <?php echo htmlspecialchars($_SESSION["mail_generado"]); ?><br>
                        Contraseña temporal:
                        <b><?php echo htmlspecialchars($_SESSION["pass_generada"]); ?></b>
                    </div>

                    <?php
                    // 🔹 Se borra inmediatamente para que no vuelva a mostrarse
                    unset($_SESSION["mail_generado"], $_SESSION["pass_generada"]);
                    ?>
                <?php endif; ?>

                <!-- Campos login -->
                <input type="text" placeholder="Correo Electrónico" name="user" required>
                <input type="password" placeholder="Contraseña" name="pass" required>

                <button type="submit">Entrar</button>

                <!-- Error de login -->
                <?php if (isset($_GET['error']) && $_GET['error'] === 'credenciales'): ?>
                    <p style="color:red; margin-top:10px;">
                        Usuario o contraseña incorrectos
                    </p>
                <?php endif; ?>

            </form>
            <!-- ================= FIN LOGIN ================= -->


            <!-- ================= FORMULARIO REGISTRO ================= -->
            <form action="controlador/registrarApp.php" 
                  class="formulario__register" 
                  method="POST">

                <h2>Regístrarse</h2>

                <input type="text" placeholder="Nombre completo" name="nombre" required>
                <input type="text" placeholder="Dirección" name="direccion" required>
                <input type="text" placeholder="Correo Electrónico" name="mail" required>

                <button type="submit">Regístrarse</button>

                <!-- Errores / mensajes de registro -->
                <?php if (isset($_GET['reg']) && $_GET['reg'] === 'existe'): ?>
                    <p style="color:red; margin-top:10px;">
                        Ese mail ya está registrado.
                    </p>
                <?php endif; ?>

                <?php if (isset($_GET['reg']) && $_GET['reg'] === 'mail'): ?>
                    <p style="color:red; margin-top:10px;">
                        Mail inválido.
                    </p>
                <?php endif; ?>

                <?php if (isset($_GET['reg']) && $_GET['reg'] === 'ok'): ?>
                    <p style="color:green; margin-top:10px;">
                        Cuenta creada correctamente.
                    </p>
                <?php endif; ?>

                <?php if (isset($_GET['reg']) && $_GET['reg'] === 'error'): ?>
                    <p style="color:red; margin-top:10px;">
                        Error al registrar.
                    </p>
                <?php endif; ?>

            </form>
            <!-- ================= FIN REGISTRO ================= -->

        </div>
        <!-- ================= FIN CONTENEDOR ================= -->

    </div>
</main>

<!-- JS que controla el cambio Login / Register -->
<script src="loginApp.js"></script>

</body>
</html>
