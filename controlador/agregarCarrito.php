<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * CONTROLADOR: agregarCarrito.php
 * --------------------------------
 * - Agrega productos al carrito guardado en $_SESSION['carrito'].
 * - Regla de negocio: el carrito SOLO puede tener productos de un mismo local.
 * - Si el producto ya existe (mismo local + mismo articulo) -> suma la cantidad.
 *
 * Estructura guardada por ítem:
 * [
 *   'idLocal'   => (int),
 *   'codigoArt' => (int),
 *   'nombre'    => (string),
 *   'precio'    => (float),
 *   'cantidad'  => (int)
 * ]
 */

// 1) Asegurar carrito en la sesión
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Helper: leer valores POST aceptando varios nombres posibles (por si cambian formularios)
function post_value($names, $default = null) {
    foreach ((array)$names as $name) {
        if (isset($_POST[$name]) && $_POST[$name] !== '') {
            return $_POST[$name];
        }
    }
    return $default;
}

// 2) Leer datos del formulario
$idLocal   = (int) post_value(['idLocal', 'IDLoc', 'idloc'], 0);
$codigoArt = (int) post_value(['codigoArt', 'CodigoArt', 'codigo', 'Codigo'], 0);
$nombre    = trim((string) post_value(['nombre', 'producto', 'Nombre'], ''));
$precio    = (float) post_value(['precio', 'Precio'], 0);
$cantidad  = (int) post_value(['cantidad'], 1);

// 3) Normalizar valores
if ($cantidad < 1) $cantidad = 1;
if ($precio < 0) $precio = 0;

// Validación mínima: ids válidos
if ($idLocal <= 0 || $codigoArt <= 0) {
    $destino = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../indexApp.php';
    header("Location: {$destino}?error=datos_carrito");
    exit();
}

if ($nombre === '') {
    $nombre = 'Producto sin nombre';
}

// 4) Regla de negocio: carrito de un solo local
// Guardamos el local del carrito en sesión para chequear fácilmente.
if (!isset($_SESSION['local_carrito']) || empty($_SESSION['carrito'])) {
    // Si es el primer producto, fijamos el local
    $_SESSION['local_carrito'] = $idLocal;
} else {
    // Si ya hay un local definido y quieren meter de otro local -> bloquear
    if ((int)$_SESSION['local_carrito'] !== $idLocal) {
        $destino = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../indexApp.php';
        header("Location: {$destino}?error=locales_distintos");
        exit();
    }
}

// 5) Referencia al carrito
$carrito = &$_SESSION['carrito'];

// 6) Si el producto ya está en el carrito, sumar cantidad
$encontrado = false;

foreach ($carrito as $index => $item) {
    if ((int)$item['idLocal'] === $idLocal && (int)$item['codigoArt'] === $codigoArt) {
        $carrito[$index]['cantidad'] = (int)$carrito[$index]['cantidad'] + $cantidad;

        // Por si el nombre/precio cambió en la vista, mantenemos el último:
        $carrito[$index]['nombre'] = $nombre;
        $carrito[$index]['precio'] = $precio;

        $encontrado = true;
        break;
    }
}

// 7) Si no estaba, lo agregamos como nuevo ítem
if (!$encontrado) {
    $carrito[] = [
        'idLocal'   => $idLocal,
        'codigoArt' => $codigoArt,
        'nombre'    => $nombre,
        'precio'    => $precio,
        'cantidad'  => $cantidad
    ];
}

// 8) Volver a la página desde donde vino
$destino = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../indexApp.php';
header("Location: {$destino}?ok=agregado");
exit();
