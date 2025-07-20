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

$inventarioId = isset($_POST["iid"]) && trim($_POST["iid"]) != "" ? $_POST["iid"] : -1;

//-----------------------------------------------

$objInventario = new Inventario($conn);
$listado = $objInventario->historialDeItem($inventarioId);
$resultado["historial"] = $listado;

$item = $objInventario->getByInventarioId($inventarioId);
$resultado["item"] = $item[0];

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------