<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Clientes.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$clienteId = $_POST["cid"];
$usuarioId = $_POST["uid"];
$nombre = $_POST["nombre"];
$direccion = $_POST["direccion"];
$direccionComplemento = $_POST["direccioncomplemento"];
$codigoPostal = $_POST["codigopostal"];
$telefono = $_POST["telefono"];
$correo = $_POST["correoelectronico"];

//-----------------------------------------------

$objClientes = new Clientes($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objClientes->editarRegistro($clienteId,
    ["NOMBRE", $nombre, "DIRECCION", $direccion, "DIRECCIONCOMPLEMENTO", $direccionComplemento,
    "CODIGOPOSTAL", $codigoPostal, "TELEFONO", $telefono, "CORREOELECTRONICO", $correo,
    "USUARIOIDMODIFICACION", $usuarioId, "FECHAMODIFICACION", $fechaModificacion]
);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objClientes->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------