<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Financieras.php");

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

//-----------------------------------------------

$objEntidadesFinancieras = new Financieras($conn);

$rsAgregar = $objEntidadesFinancieras->agregarRegistro($nombre, $usuarioId);

if (!$rsAgregar)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objEntidadesFinancieras->mensajeError;
}
else
{
    $resultado["id"] = $objEntidadesFinancieras->financieraId;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------