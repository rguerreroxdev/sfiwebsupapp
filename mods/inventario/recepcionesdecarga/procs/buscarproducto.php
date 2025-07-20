<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Productos.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$codigo = isset($_POST["codigo"]) && trim($_POST["codigo"]) != "" ? trim($_POST["codigo"]) : "-1";

//-----------------------------------------------

$objProductos = new Productos($conn);

$resulstadoBusqueda = $objProductos->getWithFilters("P.CODIGO = '$codigo'");

if (count($resulstadoBusqueda) > 0)
{
    $resultado["encontrado"] = 1;
    $resultado["productoid"] = $resulstadoBusqueda[0]["PRODUCTOID"];
    $resultado["codigo"] = $resulstadoBusqueda[0]["CODIGO"];
    $resultado["categoria"] = $resulstadoBusqueda[0]["CATEGORIA"];
    $resultado["modelo"] = $resulstadoBusqueda[0]["MODELO"];
    $resultado["marca"] = $resulstadoBusqueda[0]["MARCA"];
    $resultado["color"] = $resulstadoBusqueda[0]["COLOR"];
    $resultado["descripcion"] = $resulstadoBusqueda[0]["DESCRIPCION"];
    $resultado["msrp"] = $resulstadoBusqueda[0]["MSRP"];
}
else
{
    $resultado["encontrado"] = 0;
    $resultado["productoid"] = "";
    $resultado["codigo"] = "";
    $resultado["categoria"] = "";
    $resultado["modelo"] = "";
    $resultado["marca"] = "";
    $resultado["color"] = "";
    $resultado["descripcion"] = "";
    $resultado["msrp"] = "";
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------