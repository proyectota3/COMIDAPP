<?php
session_start();

// Crear carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verificar datos obligatorios
if (isset($_POST['producto']) && isset($_POST['precio'])) {

    // Datos obligatorios
    $producto = $_POST['producto'];
    $precio   = $_POST['precio'];

    // Datos opcionales (por si vienen)
    $codigoArt = $_POST['codigoArt'] ?? null;
    $idLocal   = $_POST['idLocal'] ?? null;

    $_SESSION['carrito'][] = [
        'producto'  => $producto,
        'precio'    => $precio,
        'codigoArt' => $codigoArt,  // ahora queda guardado
        'idLocal'   => $idLocal     // ahora queda guardado
    ];
}

// SIEMPRE REDIRIGIR
header("Location: ../indexApp.php");
exit();
