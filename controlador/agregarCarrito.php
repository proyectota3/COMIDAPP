<?php
session_start();

// Crear carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verificar que lleguen los datos
if (isset($_POST['producto']) && isset($_POST['precio'])) {
    
    $producto = $_POST['producto'];
    $precio = $_POST['precio'];

    $_SESSION['carrito'][] = [
        'producto' => $producto,
        'precio' => $precio
    ];
}

// REDIRECCIÓN OBLIGATORIA
header("Location: ../indexApp.php");
exit();
