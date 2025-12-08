<?php
// modelo/menuModel.php

class MenuModel
{
    private $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /* ======= MÉTODOS PARA EMPRESA ======= */

    // Verificar que el local pertenece a la empresa
    public function getLocalDeEmpresa($idLocal, $idEmp)
    {
        $sql = "SELECT * FROM local WHERE ID = ? AND IDEmp = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idLocal, $idEmp]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Menú completo de un local (admin ve activos e inactivos)
    public function getMenuAdminByLocal($idLocal)
    {
        $sql = "SELECT la.ID, a.Nombre, la.Precio, la.Activo
                FROM local_articulo la
                JOIN articulos a ON la.CodigoArticulo = a.Codigo
                WHERE la.IDLoc = ?
                ORDER BY a.Nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idLocal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de todos los artículos del catálogo
    public function getArticulos()
    {
        $sql = "SELECT Codigo, Nombre FROM articulos ORDER BY Nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizar una línea del menú
    public function actualizarLinea($idLinea, $idLocal, $precio, $activo)
    {
        $sql = "UPDATE local_articulo
                SET Precio = ?, Activo = ?
                WHERE ID = ? AND IDLoc = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$precio, $activo, $idLinea, $idLocal]);
    }

    // Agregar un nuevo artículo al menú del local
    public function agregarArticuloAlMenu($idLocal, $codigoArticulo, $precio)
    {
        $sql = "INSERT INTO local_articulo (IDLoc, CodigoArticulo, Precio, Activo)
                VALUES (?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idLocal, $codigoArticulo, $precio]);
    }

    /* ======= MÉTODOS PARA CLIENTE (indexApp) ======= */

    // Menú solo activo (para mostrar en indexApp)
    public function getMenuClienteByLocal($idLocal)
    {
        $sql = "SELECT a.Nombre, la.Precio
                FROM local_articulo la
                JOIN articulos a ON la.CodigoArticulo = a.Codigo
                WHERE la.IDLoc = ? AND la.Activo = 1
                ORDER BY a.Nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idLocal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
s