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

//-----------------------------------------------

$codigo = isset($_POST["codigo"]) && trim($_POST["codigo"]) != "" ? trim($_POST["codigo"]) : "-1";

//-----------------------------------------------

$objClientes = new Clientes($conn);

$resultadoBusqueda = $objClientes->getWithFilters("C.CODIGO = '$codigo'");

if (count($resultadoBusqueda) > 0)
{
    $resultado["encontrado"] = 1;
    $resultado["clienteid"] = $resultadoBusqueda[0]["CLIENTEID"];
    $resultado["codigo"] = $resultadoBusqueda[0]["CODIGO"];
    $resultado["nombre"] = $resultadoBusqueda[0]["NOMBRE"];
    $resultado["direccion"] = $resultadoBusqueda[0]["DIRECCION"];
    $resultado["direccioncomplemento"] = $resultadoBusqueda[0]["DIRECCIONCOMPLEMENTO"];
    $resultado["codigopostal"] = $resultadoBusqueda[0]["CODIGOPOSTAL"];
    $resultado["telefono"] = $resultadoBusqueda[0]["TELEFONO"];
    $resultado["correo"] = $resultadoBusqueda[0]["CORREOELECTRONICO"];
    $resultado["facturas"] = $resultadoBusqueda[0]["FACTURAS"];
}
else
{
    $resultado["encontrado"] = 0;
    $resultado["clienteid"] = "";
    $resultado["codigo"] = "";
    $resultado["nombre"] = "";
    $resultado["direccion"] = "";
    $resultado["direccioncomplemento"] = "";
    $resultado["codigopostal"] = "";
    $resultado["telefono"] = "";
    $resultado["correo"] = "";
    $resultado["facturas"] = 0;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------