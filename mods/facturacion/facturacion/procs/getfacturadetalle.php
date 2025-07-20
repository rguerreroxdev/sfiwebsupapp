
<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/FacturasDetalle.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$facturaId = isset($_GET["fid"]) && trim($_GET["fid"]) != "" ? $_GET["fid"] : -1;
$facturaId = is_numeric($facturaId) ? $facturaId : -1;

//-----------------------------------------------

$objFacturaDetalle = new FacturasDetalle($conn);

$listaDeDetalles = $objFacturaDetalle->getAll($facturaId);

$resultado = $listaDeDetalles;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------