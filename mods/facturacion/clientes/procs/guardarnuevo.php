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

$usuarioId = $_POST["uid"];
$nombre = $_POST["nombre"];
$direccion = $_POST["direccion"];
$direccionComplemento = $_POST["direccioncomplemento"];
$codigoPostal = $_POST["codigopostal"];
$telefono = $_POST["telefono"];
$correo = $_POST["correoelectronico"];

//-----------------------------------------------

$objClientes = new Clientes($conn);

$rsAgregar = $objClientes->agregarRegistro($nombre, $direccion, $direccionComplemento, $codigoPostal, $telefono, $correo, $usuarioId);

if (!$rsAgregar)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objClientes->mensajeError;
}
else
{
    $resultado["id"] = $objClientes->clienteId;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------