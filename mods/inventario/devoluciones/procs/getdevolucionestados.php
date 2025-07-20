<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/DevolucionesEstadosInv.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$devolucionId = isset($_GET["did"]) && trim($_GET["did"]) != "" ? $_GET["did"] : -1;
$devolucionId = is_numeric($devolucionId) ? $devolucionId : -1;

//-----------------------------------------------

$objCambiosDeEstado = new DevolucionesEstadosInv($conn);

$listaDeCambiosDeEstado = $objCambiosDeEstado->getAll($devolucionId);

$resultado = $listaDeCambiosDeEstado;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------