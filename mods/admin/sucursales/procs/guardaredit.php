<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Sucursales.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$sucursalId = $_POST["sid"];
$usuarioId = $_POST["uid"];
$nombre = $_POST["nombre"];
$esCasaMatriz = isset($_POST["escasamatriz"]) ? 1 : 0;
$direccion = $_POST["direccion"];
$direccionComplemento = $_POST["direccioncomplemento"];
$codigoPostal = $_POST["codigopostal"];
$telefono = $_POST["telefono"];
$telefonoServicio = $_POST["telefonoservicio"];

//-----------------------------------------------

$objSucursales = new Sucursales($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objSucursales->editarRegistro(
    $sucursalId,
    [
        "NOMBRE", $nombre,
        "ESCASAMATRIZ", $esCasaMatriz,
        "DIRECCION", $direccion,
        "DIRECCIONCOMPLEMENTO", $direccionComplemento,
        "CODIGOPOSTAL", $codigoPostal,
        "TELEFONO", $telefono,
        "TELEFONOSERVICIO", $telefonoServicio,
        "USUARIOIDMODIFICACION", $usuarioId,
        "FECHAMODIFICACION", $fechaModificacion
    ]
);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objSucursales->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------