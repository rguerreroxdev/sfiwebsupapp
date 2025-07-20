<?php
//-----------------------------------------------

session_start();

require_once("../../../inc/includes.inc.php");
require_once("../../../inc/class/Indicadores.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$usuarioId = $_SESSION["usuarioId"];
$resultado = array();

//-----------------------------------------------

$objIndicadores = new Indicadores($conn);

$listaDeTraslados = $objIndicadores->trasladosProximos($usuarioId);

$resultado = $listaDeTraslados;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------