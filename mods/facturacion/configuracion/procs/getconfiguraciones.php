<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/FacConfiguracionesPorSucursal.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$offset = isset($_GET["offset"]) && trim($_GET["offset"]) != "" ? $_GET["offset"] : 1;
$tamanoDePagina = isset($_GET["limit"]) && trim($_GET["limit"]) != "" ? $_GET["limit"] : 25;
$buscar = isset($_GET["search"]) && trim($_GET["search"]) != "" ? trim($_GET["search"]) : "";

$sucursalId = isset($_GET["sucursal"]) && trim($_GET["sucursal"]) != "" ? $_GET["sucursal"] : -1;

$numeroDePagina = $offset / $tamanoDePagina;

//-----------------------------------------------

$objConfiguraciones = new FacConfiguracionesPorSucursal($conn);

$listaDeConfiguraciones = $objConfiguraciones->getAllConPaginacion($buscar, $sucursalId, $numeroDePagina, $tamanoDePagina);

$resultado = $listaDeConfiguraciones;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------