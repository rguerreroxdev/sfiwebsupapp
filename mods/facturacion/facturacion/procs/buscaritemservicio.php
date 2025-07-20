<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/OtrosServiciosProductos.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$codigo = isset($_POST["codigo"]) && trim($_POST["codigo"]) != "" ? trim($_POST["codigo"]) : "-1";

//-----------------------------------------------

$objOtroServicioProducto = new OtrosServiciosProductos($conn);

$resultadoBusqueda = $objOtroServicioProducto->getByCodigo($codigo);

if (count($resultadoBusqueda) > 0)
{
    $resultado["encontrado"] = 1;
    $resultado["servicioid"] = $resultadoBusqueda[0]["OTROSERVICIOPRODUCTOID"];
    $resultado["codigo"] = $resultadoBusqueda[0]["CODIGO"];
    $resultado["modelo"] = $resultadoBusqueda[0]["MODELO"];
    $resultado["marca"] = $resultadoBusqueda[0]["MARCA"];
    $resultado["descripcion"] = $resultadoBusqueda[0]["DESCRIPCION"];
}
else
{
    $resultado["encontrado"] = 0;
    $resultado["servicioid"] = "";
    $resultado["codigo"] = "";
    $resultado["modelo"] = "";
    $resultado["marca"] = "";
    $resultado["descripcion"] = "";
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------
