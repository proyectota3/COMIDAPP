<?php
require_once "../modelo/connectionComidApp.php";

$idLocal   = $_POST['idLocal'];
$codigoArt = $_POST['codigoArt'];
$precio    = $_POST['precio'];
$hoy       = date('Y-m-d');

// Opcional: cerrar precio anterior del mismo artículo en ese local
$sqlCerrar = "UPDATE vende 
              SET FechaFinPrecio = DATE_SUB(?, INTERVAL 1 DAY)
              WHERE IDLoc = ? AND CodigoArt = ? AND FechaFinPrecio IS NULL";
$stmtCerrar = $pdo->prepare($sqlCerrar);
$stmtCerrar->execute([$hoy, $idLocal, $codigoArt]);

// Insertar nuevo precio vigente
$sqlInsert = "INSERT INTO vende (IDLoc, CodigoArt, Precio, FechaIniPrecio, FechaFinPrecio)
              VALUES (?, ?, ?, ?, NULL)";
$stmtInsert = $pdo->prepare($sqlInsert);
$stmtInsert->execute([$idLocal, $codigoArt, $precio, $hoy]);

header("Location: formulario_vende.php?ok=1");
exit;
