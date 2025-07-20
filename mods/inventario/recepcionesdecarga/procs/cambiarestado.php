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
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$recepcionDeCargaId = $_POST["rid"];
$usuarioId = $_POST["uid"];
$estado = $_POST["estado"];

//-----------------------------------------------

$objRecepcionDeCarga = new RecepcionesDeCarga($conn);
$estadoCambiado = $objRecepcionDeCarga->cambiarEstado($recepcionDeCargaId, $usuarioId, $estado);

if (!$estadoCambiado)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objRecepcionDeCarga->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------