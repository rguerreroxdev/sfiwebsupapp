<?php
//-----------------------------------------------

session_start();

require_once("../../../inc/includes.inc.php");
require_once("../../../inc/class/Indicadores.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$objIndicadores = new Indicadores($conn);

$listaDeRecepciones = $objIndicadores->recepcionesDeCargaProximas();

$resultado = $listaDeRecepciones;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------