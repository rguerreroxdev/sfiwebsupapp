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
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$productoId = $_POST["pid"];
$usuarioId = $_POST["uid"];
$categoriaId = $_POST["categoria"];
$marcaId = $_POST["marca"];
$colorId = $_POST["color"];
$modelo = $_POST["modelo"];
$descripcion = $_POST["descripcion"];
$msrp = $_POST["msrp"];

//-----------------------------------------------

$objProductos = new Productos($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objProductos->editarRegistro($productoId,
    ["CATEGORIAID", $categoriaId, "MARCAID", $marcaId, "COLORID", $colorId,
    "MODELO", $modelo, "DESCRIPCION", $descripcion, "MSRP", $msrp,
    "USUARIOIDMODIFICACION", $usuarioId, "FECHAMODIFICACION", $fechaModificacion]
);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objProductos->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------