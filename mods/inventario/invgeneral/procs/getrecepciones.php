<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/RecepcionesDeCarga.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$usuarioId = isset($_GET["uid"]) && trim($_GET["uid"]) != "" ? $_GET["uid"] : 1;
$sucursalId = isset($_GET["sid"]) && trim($_GET["sid"]) != "" ? $_GET["sid"] : 1;
$offset = isset($_GET["offset"]) && trim($_GET["offset"]) != "" ? $_GET["offset"] : 1;
$tamanoDePagina = isset($_GET["limit"]) && trim($_GET["limit"]) != "" ? $_GET["limit"] : 25;
$buscar = isset($_GET["search"]) && trim($_GET["search"]) != "" ? trim($_GET["search"]) : "";

$correlativo = isset($_GET["correlativo"]) && trim($_GET["correlativo"]) != "" ? $_GET["correlativo"] : "";
$proveedorId = isset($_GET["provid"]) && trim($_GET["provid"]) != "" ? $_GET["provid"] : "-1";
$loadId = isset($_GET["loadid"]) && trim($_GET["loadid"]) != "" ? $_GET["loadid"] : "";
$fechaDesde = isset($_GET["fechadesde"]) && trim($_GET["fechadesde"]) != "" ? $_GET["fechadesde"] : "";
$estado = isset($_GET["estado"]) && trim($_GET["estado"]) != "" ? $_GET["estado"] : "";

$fechaDesde = str_replace("-", "", $fechaDesde);

$numeroDePagina = $offset / $tamanoDePagina;

//-----------------------------------------------

$objRecepciones = new RecepcionesDeCarga($conn);

$listaDeRecepciones = $objRecepciones->getAllSucursalXUsuarioConPaginacion($usuarioId, $sucursalId, $loadId, $correlativo, $proveedorId, $fechaDesde, $estado, $numeroDePagina, $tamanoDePagina);

$resultado = $listaDeRecepciones;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------