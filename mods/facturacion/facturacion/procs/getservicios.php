<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/OtrosServiciosProductos.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$offset = isset($_GET["offset"]) && trim($_GET["offset"]) != "" ? $_GET["offset"] : 1;
$tamanoDePagina = isset($_GET["limit"]) && trim($_GET["limit"]) != "" ? $_GET["limit"] : 25;
$buscar = isset($_GET["search"]) && trim($_GET["search"]) != "" ? trim($_GET["search"]) : "";
$marcaId = isset($_GET["mid"]) && trim($_GET["mid"]) != "" ? trim($_GET["mid"]) : -1;

$numeroDePagina = $offset / $tamanoDePagina;

//-----------------------------------------------

$objOtrosServiciosProductos = new OtrosServiciosProductos($conn);

$listaDeOtrosServiciosProductos = $objOtrosServiciosProductos->getAllConPaginacion($buscar, $marcaId, $numeroDePagina, $tamanoDePagina);

$resultado = $listaDeOtrosServiciosProductos;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------