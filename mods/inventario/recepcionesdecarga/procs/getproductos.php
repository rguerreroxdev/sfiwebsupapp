<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Productos.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$offset = isset($_GET["offset"]) && trim($_GET["offset"]) != "" ? $_GET["offset"] : 1;
$tamanoDePagina = isset($_GET["limit"]) && trim($_GET["limit"]) != "" ? $_GET["limit"] : 25;
$buscar = isset($_GET["search"]) && trim($_GET["search"]) != "" ? trim($_GET["search"]) : "";

$categoriaId = isset($_GET["categoria"]) && trim($_GET["categoria"]) != "" ? $_GET["categoria"] : -1;
$marcaId = isset($_GET["marca"]) && trim($_GET["marca"]) != "" ? $_GET["marca"] : -1;

$numeroDePagina = $offset / $tamanoDePagina;

//-----------------------------------------------

$objProductos = new Productos($conn);

$listaDeProductos = $objProductos->getAllConPaginacion($buscar, $categoriaId, $marcaId, $numeroDePagina, $tamanoDePagina);

$resultado = $listaDeProductos;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------