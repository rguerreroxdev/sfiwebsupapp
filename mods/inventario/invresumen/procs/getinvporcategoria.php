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
$categoriaId = isset($_GET["cid"]) && trim($_GET["cid"]) != "" ? $_GET["cid"] : -1;
$soloConStock = isset($_GET["stock"]) && trim($_GET["stock"]) != "" ? $_GET["stock"] : 1;

//-----------------------------------------------

$objInventario = new Inventario($conn);

$listado = $objInventario->getInvResumenPorCategoria($sucursalId, $categoriaId, $soloConStock);

$resultado = $listado;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------