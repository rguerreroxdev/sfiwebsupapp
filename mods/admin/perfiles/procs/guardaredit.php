<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Perfiles.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$usuarioId = $_POST["uid"];
$perfilId = $_POST["pid"];
$nombre = $_POST["nombre"];

//-----------------------------------------------

$objPerfil = new Perfiles($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objPerfil->editarRegistro(
    $perfilId,
    [
        "NOMBRE", $nombre,
        "USUARIOIDMODIFICACION", $usuarioId,
        "FECHAMODIFICACION", $fechaModificacion
    ]
);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objPerfil->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------