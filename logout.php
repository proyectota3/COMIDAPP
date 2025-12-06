<?php
session_start();

// Elimina todas las variables de sesión
$_SESSION = [];

// Destruye la sesión
session_destroy();

// Redirige a la página principal
header("Location: indexApp.php");
exit();
?>
