
<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/FacDevolucionesPagos.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$devolucionId = isset($_GET["did"]) && trim($_GET["did"]) != "" ? $_GET["did"] : -1;
$devolucionId = is_numeric($devolucionId) ? $devolucionId : -1;

//-----------------------------------------------

$objDevolucionPagos = new FacDevolucionesPagos($conn);

$listaDePagos = $objDevolucionPagos->getAll($devolucionId);

$resultado = $listaDePagos;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------