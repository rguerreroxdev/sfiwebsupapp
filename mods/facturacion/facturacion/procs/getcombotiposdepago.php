<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/TiposDePago.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$soloMostrarPagoSinImpuesto = isset($_POST["solomostrarpagosinimpuesto"]) ? $_POST["solomostrarpagosinimpuesto"] : 0;
$soloMostrarPagoSinImpuesto = $soloMostrarPagoSinImpuesto == 1;

//-----------------------------------------------

$objTiposDePago = new TiposDePago($conn);

$listaParaCombo = $objTiposDePago->getListaParaCombo($soloMostrarPagoSinImpuesto, "SELECT");

$resultado = $listaParaCombo;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------