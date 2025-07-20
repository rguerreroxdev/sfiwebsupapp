<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/FacConfiguracionesPorSucursal.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$usuarioId = $_POST["uid"];
$sucursalId = $_POST["sucursal"];
$prefijo = $_POST["prefijo"];
$siguienteCorrelativo = $_POST["siguientecorrelativo"];
$impuesto = $_POST["impuesto"];

//-----------------------------------------------

$objConfiguracion = new FacConfiguracionesPorSucursal($conn);

$rsAgregar = $objConfiguracion->agregarRegistro($sucursalId, $prefijo, $siguienteCorrelativo, $impuesto, $usuarioId);

if (!$rsAgregar)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objConfiguracion->mensajeError;
}
else
{
    $resultado["id"] = $objConfiguracion->configuracionPorSucursalId;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------