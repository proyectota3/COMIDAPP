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
        $sql = "SELECT a.Nombre, v.Precio
                FROM vende v
                JOIN articulos a ON v.CodigoArt = a.Codigo
                WHERE v.IDLoc = ? 
                  AND v.Activo = 1
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
        $sql = "SELECT v.ID, a.Nombre, v.Precio, v.Activo
                FROM vende v
                JOIN articulos a ON v.CodigoArt = a.Codigo
                WHERE v.IDLoc = ?
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
        $sql = "UPDATE vende
                SET Precio = ?, Activo = ?
                WHERE ID = ? AND IDLoc = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$precio, $activo, $idLinea, $idLocal]);
    }

    // Eliminar una línea del menú
    public function eliminarLinea($idLinea, $idLocal)
    {
        $sql = "DELETE FROM vende
                WHERE ID = ? AND IDLoc = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idLinea, $idLocal]);
    }

    // Agregar nuevo artículo al menú
    public function agregarArticuloAlMenu($idLocal, $codigoArticulo, $precio)
    {
        $sql = "INSERT INTO vende (IDLoc, CodigoArt, Precio, Activo, FechaIniPrecio, FechaFinPrecio)
                VALUES (?, ?, ?, 1, CURRENT_DATE, NULL)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idLocal, $codigoArticulo, $precio]);
    }
}
