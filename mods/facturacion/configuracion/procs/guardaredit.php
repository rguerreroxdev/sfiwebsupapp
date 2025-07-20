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

$configuracionPorSucursalId = $_POST["csid"];
$usuarioId = $_POST["uid"];
$prefijo = $_POST["prefijo"];
$siguienteCorrelativo = $_POST["siguientecorrelativo"];
$impuesto = $_POST["impuesto"];

//-----------------------------------------------

$objConfiguracion = new FacConfiguracionesPorSucursal($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objConfiguracion->editarRegistro($configuracionPorSucursalId,
    ["PREFIJODECORRELATIVO", $prefijo, "SIGUIENTECORRELATIVO", $siguienteCorrelativo, "IMPUESTOSPORCENTAJE", $impuesto,
    "USUARIOIDMODIFICACION", $usuarioId, "FECHAMODIFICACION", $fechaModificacion]
);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objConfiguracion->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------