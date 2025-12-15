<?php

class PerfilModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /* =========================
       CLIENTE (rol = 3)
    ========================= */
    public function getPerfilCliente(int $id): ?array {
        $sql = "SELECT 
                    uw.ID,
                    uw.idRol,
                    uw.Nombre,
                    uw.Mail,
                    uw.Direccion,
                    c.Apellido,
                    c.CICli,
                    c.FormaDePago,
                    tc.Telefono
                FROM usuariosweb uw
                LEFT JOIN cliente c ON c.IDCli = uw.ID
                LEFT JOIN telefonoscliente tc ON tc.IDCli = uw.ID
                WHERE uw.ID = ? AND uw.idRol = 3
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateCliente(int $id, string $nombre, string $direccion, string $apellido, string $formaDePago): bool {
        $this->pdo->beginTransaction();
        try {
            $st1 = $this->pdo->prepare("UPDATE usuariosweb SET Nombre = ?, Direccion = ? WHERE ID = ? AND idRol = 3");
            $st1->execute([$nombre, $direccion, $id]);

            $st2 = $this->pdo->prepare("UPDATE cliente SET Apellido = ?, FormaDePago = ? WHERE IDCli = ?");
            $st2->execute([$apellido, $formaDePago, $id]);

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function upsertTelefonoCliente(int $id, int $telefono): bool {
        // si ya existe, update; si no, insert
        $st = $this->pdo->prepare("SELECT 1 FROM telefonoscliente WHERE IDCli = ? LIMIT 1");
        $st->execute([$id]);
        $exists = (bool)$st->fetchColumn();

        if ($exists) {
            $st2 = $this->pdo->prepare("UPDATE telefonoscliente SET Telefono = ? WHERE IDCli = ?");
            return $st2->execute([$telefono, $id]);
        } else {
            $st2 = $this->pdo->prepare("INSERT INTO telefonoscliente (IDCli, Telefono) VALUES (?, ?)");
            return $st2->execute([$id, $telefono]);
        }
    }

    /* =========================
       EMPRESA (rol != 3) (ej: rol = 2)
    ========================= */
    public function getPerfilEmpresa(int $id): ?array {
        $sql = "SELECT 
                    uw.ID,
                    uw.idRol,
                    uw.Nombre AS NombreUsuario,
                    uw.Mail AS MailUsuario,
                    uw.Direccion AS DireccionUsuario,
                    e.RUT,
                    e.Direccion AS DireccionEmpresa,
                    e.Mail AS MailEmpresa,
                    e.Nombre AS NombreEmpresa,
                    e.Validacion,
                    te.Telefono
                FROM usuariosweb uw
                LEFT JOIN empresa e ON e.IDEmp = uw.ID
                LEFT JOIN telefonosempresa te ON te.IDEmp = uw.ID
                WHERE uw.ID = ?
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateEmpresa(int $id, string $direccionEmpresa): bool {
        // en tu tabla empresa los editables típicos serían Direccion (y quizás Mail/Nombre si querés)
        $st = $this->pdo->prepare("UPDATE empresa SET Direccion = ? WHERE IDEmp = ?");
        return $st->execute([$direccionEmpresa, $id]);
    }

    public function upsertTelefonoEmpresa(int $id, int $telefono): bool {
        $st = $this->pdo->prepare("SELECT 1 FROM telefonosempresa WHERE IDEmp = ? LIMIT 1");
        $st->execute([$id]);
        $exists = (bool)$st->fetchColumn();

        if ($exists) {
            $st2 = $this->pdo->prepare("UPDATE telefonosempresa SET Telefono = ? WHERE IDEmp = ?");
            return $st2->execute([$telefono, $id]);
        } else {
            $st2 = $this->pdo->prepare("INSERT INTO telefonosempresa (IDEmp, Telefono) VALUES (?, ?)");
            return $st2->execute([$id, $telefono]);
        }
    }
}
