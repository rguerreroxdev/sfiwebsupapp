<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/DevolucionesInv.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$usuarioId = isset($_GET["uid"]) && trim($_GET["uid"]) != "" ? $_GET["uid"] : -1;
$sucursalId = isset($_GET["sid"]) && trim($_GET["sid"]) != "" ? $_GET["sid"] : -1;
$offset = isset($_GET["offset"]) && trim($_GET["offset"]) != "" ? $_GET["offset"] : 1;
$tamanoDePagina = isset($_GET["limit"]) && trim($_GET["limit"]) != "" ? $_GET["limit"] : 25;
$buscar = isset($_GET["search"]) && trim($_GET["search"]) != "" ? trim($_GET["search"]) : "";

$correlativo = isset($_GET["correlativo"]) && trim($_GET["correlativo"]) != "" ? $_GET["correlativo"] : "";
$fechaDesde = isset($_GET["fechadesde"]) && trim($_GET["fechadesde"]) != "" ? $_GET["fechadesde"] : "";
$tipoDeDevolucionId = isset($_GET["tdid"]) && trim($_GET["tdid"]) != "" ? $_GET["tdid"] : -1;
$estado = isset($_GET["estado"]) && trim($_GET["estado"]) != "" ? $_GET["estado"] : "";

$fechaDesde = str_replace("-", "", $fechaDesde);

$numeroDePagina = $offset / $tamanoDePagina;

//-----------------------------------------------

$objDevoluciones = new DevolucionesInv($conn);

$listaDeDevoluciones = $objDevoluciones->getAllSucursalXUsuarioConPaginacion($usuarioId, $sucursalId, $tipoDeDevolucionId, $correlativo, $fechaDesde, $estado, $numeroDePagina, $tamanoDePagina);

$resultado = $listaDeDevoluciones;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------