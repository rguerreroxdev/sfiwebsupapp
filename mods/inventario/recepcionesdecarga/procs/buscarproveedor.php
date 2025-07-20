<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Proveedores.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$codigo = isset($_POST["codigo"]) && trim($_POST["codigo"]) != "" ? trim($_POST["codigo"]) : "-1";

//-----------------------------------------------

$objProveedores = new Proveedores($conn);

$resulstadoBusqueda = $objProveedores->getWithFilters("P.CODIGO = '$codigo'");

if (count($resulstadoBusqueda) > 0)
{
    $resultado["encontrado"] = 1;
    $resultado["proveedorid"] = $resulstadoBusqueda[0]["PROVEEDORID"];
    $resultado["codigo"] = $resulstadoBusqueda[0]["CODIGO"];
    $resultado["nombre"] = $resulstadoBusqueda[0]["NOMBRE"];
}
else
{
    $resultado["encontrado"] = 0;
    $resultado["proveedorid"] = "";
    $resultado["codigo"] = "";
    $resultado["nombre"] = "";
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------