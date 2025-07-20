
<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/SalidasDetalle.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$salidaId = isset($_GET["sid"]) && trim($_GET["sid"]) != "" ? $_GET["sid"] : -1;
$salidaId = is_numeric($salidaId) ? $salidaId : -1;

//-----------------------------------------------

$objSalidaDetalle = new SalidasDetalle($conn);

$listaDeDetalles = $objSalidaDetalle->getAll($salidaId);

$resultado = $listaDeDetalles;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------