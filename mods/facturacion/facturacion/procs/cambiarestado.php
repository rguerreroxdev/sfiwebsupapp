<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Facturas.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$facturaId = $_POST["fid"];
$usuarioId = $_POST["uid"];
$estado = $_POST["estado"];
$razonDeAnulacion = $_POST["razondeanulacion"];

//-----------------------------------------------

$objFactura = new Facturas($conn);
if ($estado == "ANU")
{
    $estadoCambiado = $objFactura->cambiarEstado($facturaId, $usuarioId, $estado, $razonDeAnulacion);
}
else
{
    $estadoCambiado = $objFactura->cambiarEstado($facturaId, $usuarioId, $estado);
}


if (!$estadoCambiado)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objFactura->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------