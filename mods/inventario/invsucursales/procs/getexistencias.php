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

$usuarioId = isset($_GET["uid"]) && trim($_GET["uid"]) != "" ? $_GET["uid"] : -1;
$productoId = isset($_GET["pid"]) && trim($_GET["pid"]) != "" ? $_GET["pid"] : -1;

//-----------------------------------------------

$objInventario = new Inventario($conn);

$listado = $objInventario->getExistenciasDeProductoXSucursal($usuarioId, $productoId);

$resultado = $listado;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------