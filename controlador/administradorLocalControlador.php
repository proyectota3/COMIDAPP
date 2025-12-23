<?php
// controlador/administrarLocalControlador.php

session_start();

// Solo empresas
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {
    header("Location: ../loginApp.php");
    exit;
}

// ID del local desde la URL
if (!isset($_GET['id'])) {
    die("Falta el ID del local.");
}
$idLocal = (int)$_GET['id'];

require_once "../modelo/connectionComidApp.php";
require_once "../modelo/menuModel.php";

// Crear conexión con tu clase
$dbComidApp = new DatabaseComidApp();
$pdo = $dbComidApp->getConnection();

$menuModel = new MenuModel($pdo);

// Opcional: acá podrías validar que el local pertenezca a la empresa logueada
$local = $menuModel->getLocalById($idLocal);
if (!$local) {
    die("Local no encontrado.");
}

// Procesar formulario (guardar cambios)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Actualizar / eliminar líneas existentes
    if (!empty($_POST['linea'])) {
        foreach ($_POST['linea'] as $idLinea => $data) {
            $precio = str_replace(',', '.', $data['precio']);
            $activo = isset($data['activo']) ? 1 : 0;

            if (isset($data['eliminar'])) {
                // Eliminar línea del menú
                $menuModel->eliminarLinea($idLinea, $idLocal);
            } else {
                // Actualizar precio y activo
                $menuModel->actualizarLinea($idLinea, $idLocal, $precio, $activo);
            }
        }
    }

    // 2) Agregar nuevo artículo al menú
    if (!empty($_POST['nuevo_articulo']) && $_POST['nuevo_articulo'] !== '' &&
        !empty($_POST['nuevo_precio'])) {

        $codigoArticulo = (int)$_POST['nuevo_articulo'];
        $precioNuevo    = str_replace(',', '.', $_POST['nuevo_precio']);

        $menuModel->agregarArticuloAlMenu($idLocal, $codigoArticulo, $precioNuevo);
    }

    // Evitar reenvío del formulario
    header("Location: ./administrarLocal.php?id=" . $idLocal);
    exit;
}

// Datos para la vista
$menu      = $menuModel->getMenuAdminByLocal($idLocal);
$articulos = $menuModel->getArticulos();
$cantidadCarrito = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;

// Devolver estructura al estilo de misLocalesControlador
return [
    "local"           => $local,
    "menu"            => $menu,
    "articulos"       => $articulos,
    "cantidadCarrito" => $cantidadCarrito
];