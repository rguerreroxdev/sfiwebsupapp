<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/OtrosServiciosProductos.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$servicioProductoId = $_POST["spid"];
$usuarioId = $_POST["uid"];
$marcaId = $_POST["marca"];
$modelo = $_POST["modelo"];
$descripcion = $_POST["descripcion"];

$marcaId = $marcaId == "" ? -1 : $marcaId;

//-----------------------------------------------

$objServicioProd = new OtrosServiciosProductos($conn);

$fechaModificacion = date("Ymd H:i:s");

$rsEdicion = $objServicioProd->editarRegistro($servicioProductoId,
    ["MARCAID", $marcaId,
    "MODELO", $modelo, "DESCRIPCION", $descripcion,
    "USUARIOIDMODIFICACION", $usuarioId, "FECHAMODIFICACION", $fechaModificacion]
);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objServicioProd->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------