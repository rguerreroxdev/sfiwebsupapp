<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/FacConfiguracionesPorSucursal.php");
require_once("../../../../inc/class/Sucursales.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$sucursalId = isset($_POST["sid"]) ? $_POST["sid"] : -1;

//-----------------------------------------------

$objConfiguracion = new FacConfiguracionesPorSucursal($conn);
$objConfiguracion->getBySucursalId($sucursalId);

$resultado["configuracionid"] = $objConfiguracion->configuracionPorSucursalId;
$resultado["prefijodecorrelativo"] = $objConfiguracion->prefijoDeCorrelativo;
$resultado["impuestoporcentaje"] = $objConfiguracion->impuestosPorcentaje;

$objSucursal = new Sucursales($conn);
$objSucursal->getById($sucursalId);

$resultado["sucursalnombre"] = $objSucursal->nombre;
$resultado["sucursalid"] = $objSucursal->sucursalId;
$resultado["sucursaldireccion"] = $objSucursal->direccion;
$resultado["sucursaldireccioncomplemento"] = $objSucursal->direccionComplemento;
$resultado["sucursalcodigopostal"] = $objSucursal->codigoPostal;
$resultado["sucursaltelefono"] = $objSucursal->telefono;
$resultado["sucursaltelefonoservicio"] = $objSucursal->telefonoServicio;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------