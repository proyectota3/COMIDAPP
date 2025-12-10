<?php
session_start();

// 1) Asegurar carrito en la sesión
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Función helper para leer varios posibles nombres de campo del formulario
function post_value($names, $default = null) {
    foreach ((array)$names as $name) {
        if (isset($_POST[$name]) && $_POST[$name] !== '') {
            return $_POST[$name];
        }
    }
    return $default;
}

// 2) Leer datos del formulario (aceptamos varios nombres posibles)
$idLocal   = (int) post_value(['idLocal', 'IDLoc', 'idloc'], 0);
$codigoArt = (int) post_value(['codigoArt', 'CodigoArt', 'codigo', 'Codigo'], 0);
$nombre    = trim((string) post_value(['nombre', 'producto', 'Nombre'], ''));
$precio    = (float) post_value(['precio', 'Precio'], 0);
$cantidad  = (int) post_value(['cantidad'], 1);

// Aseguramos que la cantidad sea al menos 1
if ($cantidad < 1) {
    $cantidad = 1;
}

// 3) Validación mínima: solo exigimos ids válidos
if ($idLocal <= 0 || $codigoArt <= 0) {
    $destino = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../indexApp.php';
    header("Location: $destino?error=datos_carrito");
    exit();
}

// Si no vino nombre, al menos ponemos algo para que no aparezca en blanco
if ($nombre === '') {
    $nombre = 'Producto sin nombre';
}

// 4) Referencia al carrito
$carrito = &$_SESSION['carrito'];

// 5) Si el producto ya está en el carrito (mismo local + mismo código), sumar cantidad
$encontrado = false;

foreach ($carrito as $index => $item) {
    if ($item['idLocal'] == $idLocal && $item['codigoArt'] == $codigoArt) {
        $carrito[$index]['cantidad'] += $cantidad;
        $encontrado = true;
        break;
    }
}

// 6) Si no estaba, lo agregamos como nuevo ítem
if (!$encontrado) {
    $carrito[] = [
        'idLocal'   => $idLocal,
        'codigoArt' => $codigoArt,
        'nombre'    => $nombre,
        'precio'    => $precio,
        'cantidad'  => $cantidad
    ];
}

// 7) Volver a la página desde donde viniste (index, misLocales, etc.)
$destino = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../indexApp.php';
header("Location: $destino");
exit();
