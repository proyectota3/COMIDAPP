<?php

class MenuModel
{
    private $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

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
