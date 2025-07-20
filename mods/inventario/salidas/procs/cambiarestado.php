<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Salidas.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$salidaId = $_POST["sid"];
$usuarioId = $_POST["uid"];
$estado = $_POST["estado"];

//-----------------------------------------------

$objSalida = new Salidas($conn);
$estadoCambiado = $objSalida->cambiarEstado($salidaId, $usuarioId, $estado);

if (!$estadoCambiado)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objSalida->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------