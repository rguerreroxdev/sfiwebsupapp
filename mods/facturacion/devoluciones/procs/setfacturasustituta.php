<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/FacDevoluciones.php");
require_once("../../../../inc/class/FacDevolucionesEstados.php");
require_once("../../../../inc/class/FacturasEstados.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

$error = false;

//-----------------------------------------------

$devolucionID = $_POST["did"];
$usuarioId = $_POST["uid"];
$facturaId = $_POST["fid"];

//-----------------------------------------------

$objDevolucion = new FacDevoluciones($conn);
$objDevolucionEstado = new FacDevolucionesEstados($conn);

//-----------------------------------------------

$fechaModificacion = date("Ymd H:i:s");

$rsModificar = $objDevolucion->editarRegistro(
    $devolucionID,
    [
        "FACTURASUSTITUYEID", $facturaId,
        "USUARIOIDMODIFICACION", $usuarioId,
        "FECHAMODIFICACION", $fechaModificacion
    ]
);

if (!$rsModificar)
{
    $error = true;
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objDevolucion->mensajeError;
}

if (!$error)
{
    // Guardar historial en devolución
    $historialDescripcion = "Set substitute invoice data";
    $objDevolucionEstado->agregarRegistro($devolucionID, "PRO", $historialDescripcion, $usuarioId);

    // Guardar historial en factura sustituta
    $objFacturaEstados = new FacturasEstados($conn);
    $objDevolucion->getById($devolucionID);
    $correlativoDevolucion = $objDevolucion->prefijoCorrelativoDevolucion . "-" . $objDevolucion->correlativoDevolucion;
    $correlativoSustituida = $objDevolucion->prefijoDeCorrelativoDeFactura. "-" . $objDevolucion->correlativoDeFactura;
    $descripcion = "Set as substitute for Invoice " . $correlativoSustituida . " in Credit Memo " . $correlativoDevolucion;
    $objFacturaEstados->agregarRegistro($facturaId, "PRO", $descripcion, $usuarioId);
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------