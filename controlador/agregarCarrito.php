<?php
session_start();

// Crear carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Guardar también el local del carrito
if (!isset($_SESSION['local_carrito'])) {
    $_SESSION['local_carrito'] = null;
}

// 💡 Si el carrito está vacío, “liberamos” el local
if (empty($_SESSION['carrito'])) {
    $_SESSION['local_carrito'] = null;
}

// Verificar datos obligatorios
if (
    !isset($_POST['producto']) ||
    !isset($_POST['precio'])   ||
    !isset($_POST['idLocal'])
) {
    // Faltan datos, volver al index con error
    header("Location: ../indexApp.php?error=datos_faltantes");
    exit();
}

// Datos del POST
$producto  = $_POST['producto'];
$precio    = (float) $_POST['precio'];
$idLocal   = (int) $_POST['idLocal'];
$codigoArt = $_POST['codigoArt'] ?? null;

// Si el carrito aún no tiene local asociado, se lo asignamos ahora
if ($_SESSION['local_carrito'] === null) {
    $_SESSION['local_carrito'] = $idLocal;
}

// Si el usuario intenta agregar un producto de otro local, no dejamos
if ($_SESSION['local_carrito'] !== $idLocal) {
    header("Location: ../indexApp.php?error=local_distinto");
    exit();
}

// Agregar al carrito
$_SESSION['carrito'][] = [
    'producto'  => $producto,
    'precio'    => $precio,
    'codigoArt' => $codigoArt,
    'idLocal'   => $idLocal
];

// Redirigir siempre
header("Location: ../indexApp.php?ok=agregado");
exit();
