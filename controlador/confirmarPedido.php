<?php
/**
 * controlador/confirmarPedido.php
 * -------------------------------------------------------
 * OBJETIVO:
 * - Este controlador lo usa la EMPRESA (rol 2) desde "Mis Ventas".
 * - Cuando aprieta "Marcar como entregado", se actualiza el estado
 *   de la compra en la tabla COMPRA:
 *
 *      Valida = 0  -> PENDIENTE
 *      Valida = 1  -> ENTREGADO
 *
 * IMPORTANTE:
 * - En tu tabla compra, una factura puede tener VARIAS filas (una por artículo).
 * - Por eso, actualizamos TODAS las filas de esa factura para ese local.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // ✅ Siempre iniciar sesión antes de usar $_SESSION
}

/**
 * 1) Seguridad: solo EMPRESA (rol = 2)
 * Si no está logueado o no es empresa, lo mandamos al login.
 */
if (!isset($_SESSION['id'], $_SESSION['rol']) || (int)$_SESSION['rol'] !== 2) {
    header("Location: ../loginApp.php");
    exit();
}

/**
 * 2) Datos del formulario (POST)
 * Estos campos vienen desde misVentas.php:
 *  - NumFactura
 *  - IDLoc
 */
$numFactura = (int)($_POST['NumFactura'] ?? 0);
$idLoc      = (int)($_POST['IDLoc'] ?? 0);

/**
 * 3) Validación básica: no se puede confirmar si faltan datos
 */
if ($numFactura <= 0 || $idLoc <= 0) {
    header("Location: ../pages/misVentas.php?error=datos");
    exit();
}

/**
 * 4) Conexión a base de datos
 */
require_once __DIR__ . "/../modelo/connectionComidApp.php";
$db = new DatabaseComidApp();
$cn = $db->getConnection();

try {
    /**
     * 5) Actualizar el estado del pedido a ENTREGADO
     *
     * - Actualizamos TODAS las líneas (filas) que correspondan a esa factura.
     * - Condición: Valida = 0 (solo pendientes)
     *
     * Esto evita que toque pedidos ya entregados.
     */
    $sql = "
        UPDATE compra
        SET Valida = 1
        WHERE NumFactura = ? AND IDLoc = ? AND Valida = 0
    ";

    $st = $cn->prepare($sql);
    $st->execute([$numFactura, $idLoc]);

    /**
     * 6) Debug/Control:
     * Si rowCount() == 0 significa:
     * - No encontró filas con esa factura/local en Valida=0
     * Puede pasar si:
     * - Ya estaban entregadas
     * - El NumFactura o IDLoc que llegó no coincide con lo guardado en la BD
     */
    if ($st->rowCount() === 0) {
        // Pasamos nf y loc para ayudarte a ver qué valores llegaron
        header("Location: ../pages/misVentas.php?error=no_update&nf=$numFactura&loc=$idLoc");
        exit();
    }

    /**
     * 7) Todo ok: volvemos a Mis Ventas con OK
     */
    header("Location: ../pages/misVentas.php?ok=1");
    exit();

} catch (Exception $e) {
    /**
     * 8) Manejo de error: mostramos mensaje (modo debug)
     * En producción podrías loguearlo y redirigir con error genérico.
     */
    echo "<h2>Error al confirmar pedido</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p><a href=\"../pages/misVentas.php\">Volver</a></p>";
    exit();
}
