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

$resultadoBusqueda = $objInventario->getInventarioEnSalidaByCodigo($codigo);

if (count($resultadoBusqueda) > 0)
{
    $resultado["encontrado"] = 1;
    $resultado["inventarioid"] = $resultadoBusqueda[0]["INVENTARIOID"];
    $resultado["sucursalid"] = $resultadoBusqueda[0]["SUCURSALID"];
    $resultado["existencia"] = $resultadoBusqueda[0]["EXISTENCIA"];
    $resultado["codigo"] = $resultadoBusqueda[0]["CODIGOINVENTARIO"];
    $resultado["categoria"] = $resultadoBusqueda[0]["CATEGORIA"];
    $resultado["modelo"] = $resultadoBusqueda[0]["MODELO"];
    $resultado["marca"] = $resultadoBusqueda[0]["MARCA"];
    $resultado["descripcion"] = $resultadoBusqueda[0]["DESCRIPCION"];
    $resultado["correlativosalida"] = $resultadoBusqueda[0]["CORRELATIVOSALIDA"];
    $resultado["fechasalida"] = $resultadoBusqueda[0]["FECHASALIDA"];
    $resultado["salidadetalleid"] = $resultadoBusqueda[0]["SALIDADETALLEID"];
    $resultado["tipodesalida"] = $resultadoBusqueda[0]["TIPODESALIDA"];
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
    $resultado["correlativosalida"] = "";
    $resultado["fechasalida"] = "";
    $resultado["salidadetalleid"] = "";
    $resultado["tipodesalida"] = "";
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------