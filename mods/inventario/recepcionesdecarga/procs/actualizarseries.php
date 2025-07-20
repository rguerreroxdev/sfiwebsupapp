<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Accesos.php");
require_once("../../../../inc/class/Inventario.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$usuarioId = $_POST["uid"];
$arrayInventarioId = $_POST["iid"];
$arraySeries = $_POST["serie"];

//-----------------------------------------------

$objAccesos = new Accesos($conn, $usuarioId);
$accesoEditar = $objAccesos->getAccesoAOpcion("02.03.01.01");

if ($accesoEditar)
{
    $objInventario = new Inventario($conn);

    for ($i=0; $i < count($arraySeries); $i++) 
    {
        $objInventario->editarSerie($arrayInventarioId[$i], $arraySeries[$i]);
    }
}
else
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = "You do not have access to update serial numbers.";
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------