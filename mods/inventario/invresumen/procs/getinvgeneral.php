<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Inventario.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$sucursalId = isset($_GET["sid"]) && trim($_GET["sid"]) != "" ? $_GET["sid"] : -1;

//-----------------------------------------------

$objInventario = new Inventario($conn);

$listado = $objInventario->getInvResumenGeneral($sucursalId);

$resultado = $listado;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------