<?php
// modelo/menuModel.php

class MenuModel
{
    private $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /* ====== PARA INDEX (CLIENTE) ====== */

    // Menú solo activo para mostrar en indexApp
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

    /* ====== PARA ADMINISTRAR MENÚ (EMPRESA) ====== */

    // Info del local (para título, etc.)
    public function getLocalById($idLocal)
    {
        $sql = "SELECT * FROM local WHERE ID = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idLocal]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Menú completo del local (incluye activos/inactivos)
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

    // Lista de artículos del catálogo general
    public function getArticulos()
    {
        $sql = "SELECT Codigo, Nombre
                FROM articulos
                ORDER BY Nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizar precio y estado de una línea
    public function actualizarLinea($idLinea, $idLocal, $precio, $activo)
    {
        $sql = "UPDATE local_articulo
                SET Precio = ?, Activo = ?
                WHERE ID = ? AND IDLoc = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$precio, $activo, $idLinea, $idLocal]);
    }

    // Eliminar una línea del menú
    public function eliminarLinea($idLinea, $idLocal)
    {
        $sql = "DELETE FROM local_articulo
                WHERE ID = ? AND IDLoc = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idLinea, $idLocal]);
    }

    // Agregar nuevo artículo al menú
    public function agregarArticuloAlMenu($idLocal, $codigoArticulo, $precio)
    {
        $sql = "INSERT INTO local_articulo (IDLoc, CodigoArticulo, Precio, Activo)
                VALUES (?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idLocal, $codigoArticulo, $precio]);
    }
}
