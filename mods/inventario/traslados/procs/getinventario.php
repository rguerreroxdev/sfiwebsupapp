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

$offset = isset($_GET["offset"]) && trim($_GET["offset"]) != "" ? $_GET["offset"] : 1;
$tamanoDePagina = isset($_GET["limit"]) && trim($_GET["limit"]) != "" ? $_GET["limit"] : 25;
$buscar = isset($_GET["search"]) && trim($_GET["search"]) != "" ? trim($_GET["search"]) : "";
$sucursalId = isset($_GET["soid"]) && trim($_GET["soid"]) != "" ? trim($_GET["soid"]) : -1;
$categoriaId = isset($_GET["cid"]) && trim($_GET["cid"]) != "" ? trim($_GET["cid"]) : -1;

$numeroDePagina = $offset / $tamanoDePagina;

//-----------------------------------------------

$objInventario = new Inventario($conn);

$listaDeInventario = $objInventario->getInventarioDeSucursalConPaginacion($sucursalId, $categoriaId, $buscar, $numeroDePagina, $tamanoDePagina);

$resultado = $listaDeInventario;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------