
<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/FacturasPagos.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$facturaId = isset($_GET["fid"]) && trim($_GET["fid"]) != "" ? $_GET["fid"] : -1;
$facturaId = is_numeric($facturaId) ? $facturaId : -1;

//-----------------------------------------------

$objFacturaPagos = new FacturasPagos($conn);

$listaDePagos = $objFacturaPagos->getAll($facturaId);

$resultado = $listaDePagos;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------