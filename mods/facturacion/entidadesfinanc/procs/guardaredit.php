<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Financieras.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$entidadFinancieraId = $_POST["efid"];
$usuarioId = $_POST["uid"];
$nombre = $_POST["nombre"];

//-----------------------------------------------

$objEntidades = new Financieras($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objEntidades->editarRegistro($entidadFinancieraId, ["NOMBRE", $nombre, "USUARIOIDMODIFICACION", $usuarioId, "FECHAMODIFICACION", $fechaModificacion]);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objEntidades->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------