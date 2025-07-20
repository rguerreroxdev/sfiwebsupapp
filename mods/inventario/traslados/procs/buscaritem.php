<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Inventario.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$codigo = isset($_POST["codigo"]) && trim($_POST["codigo"]) != "" ? trim($_POST["codigo"]) : "-1";

//-----------------------------------------------

$objInventario = new Inventario($conn);

$resulstadoBusqueda = $objInventario->getByCodigo($codigo);

if (count($resulstadoBusqueda) > 0)
{
    $resultado["encontrado"] = 1;
    $resultado["inventarioid"] = $resulstadoBusqueda[0]["INVENTARIOID"];
    $resultado["sucursalid"] = $resulstadoBusqueda[0]["SUCURSALID"];
    $resultado["existencia"] = $resulstadoBusqueda[0]["EXISTENCIA"];
    $resultado["codigo"] = $resulstadoBusqueda[0]["CODIGOINVENTARIO"];
    $resultado["categoria"] = $resulstadoBusqueda[0]["CATEGORIA"];
    $resultado["modelo"] = $resulstadoBusqueda[0]["MODELO"];
    $resultado["marca"] = $resulstadoBusqueda[0]["MARCA"];
    $resultado["descripcion"] = $resulstadoBusqueda[0]["DESCRIPCION"];
    $resultado["msrp"] = $resulstadoBusqueda[0]["MSRP"];
    $resultado["porcentajetipodestockdist"] = $resulstadoBusqueda[0]["PORCENTAJETIPODESTOCKDIST"];
    $resultado["tipodestockdist"] = $resulstadoBusqueda[0]["TIPODESTOCKDIST"];
}
else
{
    $resultado["encontrado"] = 0;
    $resultado["inventarioid"] = "";
    $resultado["sucursalid"] = "";
    $resultado["existencia"] = "";
    $resultado["codigo"] = "";
    $resultado["categoria"] = "";
    $resultado["modelo"] = "";
    $resultado["marca"] = "";
    $resultado["color"] = "";
    $resultado["descripcion"] = "";
    $resultado["msrp"] = "";
    $resultado["porcentajetipodestockdist"] = "";
    $resultado["tipodestockdist"] = "";
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------