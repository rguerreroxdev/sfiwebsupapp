<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Sucursales.php");
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
$esCasaMatriz = isset($_POST["escasamatriz"]) ? 1 : 0;
$direccion = $_POST["direccion"];
$direccionComplemento = $_POST["direccioncomplemento"];
$codigoPostal = $_POST["codigopostal"];
$telefono = $_POST["telefono"];
$telefonoServicio = $_POST["telefonoservicio"];

//-----------------------------------------------

$objSucursales = new Sucursales($conn);
$objEmpresa = new Empresa($conn);

$empresaId = $objEmpresa->getEmpresaId();

$rsAgregar = $objSucursales->agregarRegistro($empresaId, $nombre, $esCasaMatriz, $direccion, $direccionComplemento, $codigoPostal, $telefono, $telefonoServicio, $usuarioId);

if (!$rsAgregar)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objSucursales->mensajeError;
}
else
{
    $resultado["id"] = $objSucursales->sucursalId;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------