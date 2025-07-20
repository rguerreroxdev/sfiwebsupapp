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
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$devolucionId = $_POST["did"];
$usuarioId = $_POST["uid"];
$estado = $_POST["estado"];

//-----------------------------------------------

$objDevolucion = new DevolucionesInv($conn);
$estadoCambiado = $objDevolucion->cambiarEstado($devolucionId, $usuarioId, $estado);

if (!$estadoCambiado)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objDevolucion->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------