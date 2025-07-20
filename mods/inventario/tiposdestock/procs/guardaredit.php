<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/TiposDeStock.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$tipoDeStockId = $_POST["tsid"];
$usuarioId = $_POST["uid"];
$proveedorId = $_POST["proveedorid"];
$nombreCorto = $_POST["nombrecorto"];
$porcentaje = $_POST["porcentaje"];

//-----------------------------------------------

$objTipoDeStock = new TiposDeStock($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objTipoDeStock->editarRegistro($tipoDeStockId,
    [
        "PROVEEDORID", $proveedorId,
        "NOMBRECORTO", $nombreCorto,
        "PORCENTAJE", $porcentaje,
        "USUARIOIDMODIFICACION", $usuarioId,
        "FECHAMODIFICACION", $fechaModificacion
    ]
);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objTipoDeStock->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------