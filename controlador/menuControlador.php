<?php
// controlador/menuControlador.php

session_start();

// Solo empresas
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {
    header("Location: ../loginApp.php");
    exit;
}

// Al loguear a la empresa, asegurate de guardar también el IDEmp:
// $_SESSION['idEmp'] = <IDEmp de esa empresa>;
$idEmp   = $_SESSION['idEmp'];
$idLocal = isset($_GET['idLocal']) ? (int)$_GET['idLocal'] : 0;

require_once '../modelo/connectionComidApp.php'; // debe crear $pdo (PDO)
require_once '../modelo/menuModel.php';

$menuModel = new MenuModel($pdo);

// 1) Verificar que el local pertenece a la empresa
$local = $menuModel->getLocalDeEmpresa($idLocal, $idEmp);
if (!$local) {
    die("No tenés permisos sobre este local.");
}

// 2) Procesar formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 2.1) Actualizar líneas existentes
    if (!empty($_POST['linea'])) {
        foreach ($_POST['linea'] as $idLinea => $data) {
            $precio = str_replace(',', '.', $data['precio']);
            $activo = isset($data['activo']) ? 1 : 0;
            $menuModel->actualizarLinea($idLinea, $idLocal, $precio, $activo);
        }
    }

    // 2.2) Agregar nuevo artículo
    if (!empty($_POST['nuevo_articulo']) && !empty($_POST['nuevo_precio'])) {
        $codigoArt   = (int)$_POST['nuevo_articulo'];
        $precioNuevo = str_replace(',', '.', $_POST['nuevo_precio']);
        $menuModel->agregarArticuloAlMenu($idLocal, $codigoArt, $precioNuevo);
    }

    // 2.3) Redirigir para evitar reenvío del formulario
    header("Location: ../controlador/menuControlador.php?idLocal=" . $idLocal);
    exit;
}

// 3) Cargar datos para la vista
$menu      = $menuModel->getMenuAdminByLocal($idLocal);
$articulos = $menuModel->getArticulos();

// 4) Cargar VISTA
require_once '../pages/menuLocal.php';
