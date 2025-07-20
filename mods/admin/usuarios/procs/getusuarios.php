<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Usuario.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$offset = isset($_GET["offset"]) && trim($_GET["offset"]) != "" ? $_GET["offset"] : 1;
$tamanoDePagina = isset($_GET["limit"]) && trim($_GET["limit"]) != "" ? $_GET["limit"] : 25;
$buscar = isset($_GET["search"]) && trim($_GET["search"]) != "" ? trim($_GET["search"]) : "";
$activo = isset($_GET["activo"]) && trim($_GET["activo"]) != "" ? trim($_GET["activo"]) : -1;
$perfilId = isset($_GET["perfil"]) && trim($_GET["perfil"]) != "" ? trim($_GET["perfil"]) : -1;

$numeroDePagina = $offset / $tamanoDePagina;

//-----------------------------------------------

$objUsuarios = new Usuario($conn);

$listaDeUsuarios = $objUsuarios->getAllConPaginacion($buscar, $perfilId, $activo, $numeroDePagina, $tamanoDePagina);

$resultado = $listaDeUsuarios;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------