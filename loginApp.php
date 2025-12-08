<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMIDAPP LOGIN</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="loginApp.css">
</head>
<body>

<main>
    <div class="contenedor__todo">
        <div class="caja__trasera">
            <div class="caja__trasera-login">
                <h3>¿Ya tienes una cuenta?</h3>
                <p>Inicia sesión para entrar en la página</p>
                <button id="btn__iniciar-sesion">Iniciar Sesión</button>
            </div>
            <div class="caja__trasera-register">
                <h3>¿Aún no tienes una cuenta?</h3>
                <p>Regístrate para que puedas iniciar sesión</p>
                <button id="btn__registrarse">Regístrarse</button>
            </div>
        </div>

        <!--Formulario de Login y registro-->
        <div class="contenedor__login-register">
            <!-- Login -->
            <form action="controlador/validationApp.php" class="formulario__login" method="POST">
                <h2>Iniciar Sesión</h2>

                <!-- ESTE CAMPO SE VA A USAR COMO MAIL -->
                <input type="text" placeholder="Correo Electronico" name="user" required>
                <input type="password" placeholder="Contraseña" name="pass" required>
                <button type="submit">Entrar</button>

                <!-- Mostrar error si viene por GET -->
                <?php if (isset($_GET['error']) && $_GET['error'] === 'credenciales'): ?>
                    <p style="color:red; margin-top:10px;">Usuario o contraseña incorrectos</p>
                <?php endif; ?>
            </form>

            <!-- Register (por ahora de adorno) -->
            <form action="" class="formulario__register">
                <h2>Regístrarse</h2>
                <input type="text" placeholder="Nombre completo">
                <input type="text" placeholder="Correo Electronico">
                <input type="text" placeholder="Usuario">
                <input type="password" placeholder="Contraseña">
                <button>Regístrarse</button>
            </form>
        </div>
    </div>
</main>

<script src="loginApp.js"></script>
</body>
</html>
