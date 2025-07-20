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

$perfilId = $_POST["pid"];
$usuarioId = $_POST["uid"];
$perfilDetalleId = $_POST["pdid"];
$estado = $_POST["estado"];

//-----------------------------------------------

$objPerfil = new Perfiles($conn);

$rsEdicion = $objPerfil->cambiarEstadoPerfilDetalle($perfilDetalleId, $estado);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objPerfil->editarRegistro(
    $perfilId,
    [
        "USUARIOIDMODIFICACION", $usuarioId,
        "FECHAMODIFICACION", $fechaModificacion
    ]
);

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------