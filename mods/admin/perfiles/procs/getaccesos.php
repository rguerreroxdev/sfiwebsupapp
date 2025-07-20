<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Perfiles.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$perfilId = isset($_GET["pid"]) && trim($_GET["pid"]) != "" ? $_GET["pid"] : -1;
$moduloId = isset($_GET["modid"]) && trim($_GET["modid"]) != "" ? $_GET["modid"] : "";
$codigoMenu = isset($_GET["menu"]) && trim($_GET["menu"]) != "" ? $_GET["menu"] : "";

//-----------------------------------------------

$objPerfiles = new Perfiles($conn);

$listaDeAccesos = $objPerfiles->getAccesosDePerfil($perfilId, $moduloId, $codigoMenu);

$resultado = $listaDeAccesos;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------