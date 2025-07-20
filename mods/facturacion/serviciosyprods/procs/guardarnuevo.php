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

$usuarioId = $_POST["uid"];
$marcaId = $_POST["marca"];
$modelo = $_POST["modelo"];
$descripcion = $_POST["descripcion"];

$marcaId = $marcaId == "" ? -1 : $marcaId;

//-----------------------------------------------

$objServiciosProds = new OtrosServiciosProductos($conn);

$rsAgregar = $objServiciosProds->agregarRegistro($marcaId, $modelo, $descripcion, $usuarioId);

if (!$rsAgregar)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objServiciosProds->mensajeError;
}
else
{
    $resultado["id"] = $objServiciosProds->otroServicioProductoId;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------