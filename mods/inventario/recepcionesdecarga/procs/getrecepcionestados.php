<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/RecepcionesDeCargaEstados.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$recepcionDeCargaId = isset($_GET["rid"]) && trim($_GET["rid"]) != "" ? $_GET["rid"] : -1;
$recepcionDeCargaId = is_numeric($recepcionDeCargaId) ? $recepcionDeCargaId : -1;

//-----------------------------------------------

$objCambiosDeEstado = new RecepcionesDeCargaEstados($conn);

$listaDeCambiosDeEstado = $objCambiosDeEstado->getAll($recepcionDeCargaId);

$resultado = $listaDeCambiosDeEstado;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------