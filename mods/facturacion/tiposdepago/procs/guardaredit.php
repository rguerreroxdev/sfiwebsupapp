<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/TiposDePago.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$tipoDePagoId = $_POST["tpid"];
$usuarioId = $_POST["uid"];
$nombre = $_POST["nombre"];
$sumaImpuesto = isset($_POST["sumaimpuesto"]) ? 1 : 0;
$pagoSinImpuesto = isset($_POST["pagosinimpuesto"]) ? 1 : 0;

//-----------------------------------------------

$objTiposDePago = new TiposDePago($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objTiposDePago->editarRegistro(
    $tipoDePagoId,
    [
        "NOMBRE", $nombre,
        "SUMAIMPUESTO", $sumaImpuesto,
        "PERMITEPAGOSINIMPUESTO", $pagoSinImpuesto,
        "USUARIOIDMODIFICACION", $usuarioId,
        "FECHAMODIFICACION", $fechaModificacion
    ]
);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objTiposDePago->mensajeError;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------