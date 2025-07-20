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

$usuarioId = $_POST["uid"];
$proveedorId = $_POST["proveedorid"];
$nombreCorto = $_POST["nombrecorto"];
$porcentaje = $_POST["porcentaje"];

//-----------------------------------------------

$objTipoDeStock = new TiposDeStock($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsAgregar = $objTipoDeStock->agregarRegistro(
    $nombreCorto,
    $proveedorId,
    $porcentaje,
    $usuarioId
);

if (!$rsAgregar)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objTipoDeStock->mensajeError;
}
else
{
    $resultado["id"] = $objTipoDeStock->tipoDeStockId;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------