<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$usuarioId = $_POST["uid"];
$nombre = $_POST["nombre"];
$direccion = $_POST["direccion"];
$telefono = $_POST["telefono"];

//-----------------------------------------------

$objEmpresa = new Empresa($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objEmpresa->editarRegistro(
    [
        "NOMBRE", $nombre,
        "DIRECCION", $direccion,
        "TELEFONO", $telefono,
        "USUARIOIDMODIFICACION", $usuarioId,
        "FECHAMODIFICACION", $fechaModificacion
    ]
);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objEmpresa->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------