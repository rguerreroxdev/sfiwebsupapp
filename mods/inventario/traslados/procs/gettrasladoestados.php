<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/TrasladosEstados.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$trasladoId = isset($_GET["tid"]) && trim($_GET["tid"]) != "" ? $_GET["tid"] : -1;
$trasladoId = is_numeric($trasladoId) ? $trasladoId : -1;

//-----------------------------------------------

$objCambiosDeEstado = new TrasladosEstados($conn);

$listaDeCambiosDeEstado = $objCambiosDeEstado->getAll($trasladoId);

$resultado = $listaDeCambiosDeEstado;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------