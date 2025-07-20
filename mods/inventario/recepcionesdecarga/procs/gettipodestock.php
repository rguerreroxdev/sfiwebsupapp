<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/TiposDeStock.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$tipoDeStockId = isset($_POST["tsid"]) && trim($_POST["tsid"]) != "" ? $_POST["tsid"] : -1;

//-----------------------------------------------

$objTiposDeStock = new TiposDeStock($conn);

$objTiposDeStock->getById($tipoDeStockId);

$resultado["tipoDeStockId"] = $objTiposDeStock->tipoDeStockId;
$resultado["nombreCorto"] = $objTiposDeStock->nombreCorto;
$resultado["porcentaje"] = $objTiposDeStock->porcentaje;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------