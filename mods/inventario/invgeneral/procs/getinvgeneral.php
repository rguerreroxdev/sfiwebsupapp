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
$sucursalId = isset($_GET["sid"]) && trim($_GET["sid"]) != "" ? $_GET["sid"] : -1;
$categoriaId = isset($_GET["cid"]) && trim($_GET["cid"]) != "" ? $_GET["cid"] : -1;
$colorId = isset($_GET["colid"]) && trim($_GET["colid"]) != "" ? $_GET["colid"] : -1;
$stockTypeId = isset($_GET["stid"]) && trim($_GET["stid"]) != "" ? $_GET["stid"] : -1;
$numeroRecepcion = isset($_GET["nr"]) && trim($_GET["nr"]) != "" ? $_GET["nr"] : -1;
$stockMayorDeCero = isset($_GET["stock"]) ? $_GET["stock"] : 0;
$stockMayorDeCero = $stockMayorDeCero == 1 ? true : false;

$offset = isset($_GET["offset"]) && trim($_GET["offset"]) != "" ? $_GET["offset"] : 1;
$tamanoDePagina = isset($_GET["limit"]) && trim($_GET["limit"]) != "" ? $_GET["limit"] : 25;
$buscar = isset($_GET["search"]) && trim($_GET["search"]) != "" ? trim($_GET["search"]) : "";

$numeroDePagina = $offset / $tamanoDePagina;

//-----------------------------------------------

$objInventario = new Inventario($conn);

$listado = $objInventario->invGeneralXSucursalYCategoria(
    $usuarioId, $sucursalId, $categoriaId, $colorId, $stockTypeId, $stockMayorDeCero, $numeroRecepcion, $buscar, $numeroDePagina, $tamanoDePagina
);

$resultado = $listado;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------