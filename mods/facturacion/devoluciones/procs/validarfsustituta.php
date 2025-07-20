<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/FacDevoluciones.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$facturaId = isset($_POST["fid"]) && trim($_POST["fid"]) != "" ? $_POST["fid"] : -1;

//-----------------------------------------------

$objDevolucion = new FacDevoluciones($conn);

$facturaExisteComoSustituta = $objDevolucion->existeFacturaSustitutaEnDevolucion($facturaId);

$existe = $facturaExisteComoSustituta ? 1 : 0;

//-----------------------------------------------

if ($existe)
{
    $objDevolucion->getByFacturaSustituta($facturaId);
    $resultado["devolucionid"] = $objDevolucion->devolucionId;
    $resultado["correlativocompuesto"] = $objDevolucion->prefijoCorrelativoDevolucion . "-" . $objDevolucion->correlativoDevolucion;
    $resultado["fecha"] = $objDevolucion->fechaDevolucion;
}

//-----------------------------------------------

$resultado["facturayautilizada"] = $existe;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------