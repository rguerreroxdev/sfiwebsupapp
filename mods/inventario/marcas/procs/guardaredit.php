<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Marcas.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$marcaId = $_POST["mid"];
$usuarioId = $_POST["uid"];
$nombre = $_POST["nombre"];

//-----------------------------------------------

$objMarcas = new Marcas($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objMarcas->editarRegistro($marcaId, ["NOMBRE", $nombre, "USUARIOIDMODIFICACION", $usuarioId, "FECHAMODIFICACION", $fechaModificacion]);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objMarcas->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------