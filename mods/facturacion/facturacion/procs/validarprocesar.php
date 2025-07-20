<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/FacConfiguracionesPorSucursal.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$sucursalId = $_POST["sid"];

$sucursalId = 4;

//-----------------------------------------------

$objConfiguracion = new FacConfiguracionesPorSucursal($conn);

// Validar que existe configuración para la sucursal seleccionada
$objConfiguracion->getBySucursalId($sucursalId);
if ($objConfiguracion->configuracionPorSucursalId == -1)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = "There are no settings for creating invoices in this store.";
}

// Validar que el siguiente correlativo no exista en la sucursal seleccionada
if ($resultado["error"] == 0)
{
    if($objConfiguracion->existeSiguienteCorrelativo($sucursalId))
    {
        $resultado["error"] = 1;
        $resultado["mensaje"] = "The next invoice number to be issued (" . $objConfiguracion->siguienteCorrelativo . ") already exists.<br>Check the settings to define a number that doesn't exist.";        
    }
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------