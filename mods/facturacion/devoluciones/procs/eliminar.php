<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Accesos.php");
require_once("../../../../inc/class/FacDevoluciones.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$devolucionId = $_POST["did"];
$usuarioId = $_POST["uid"];

//-----------------------------------------------

$objAccesos = new Accesos($conn, $usuarioId);
$accesoEliminar = $objAccesos->getAccesoAOpcion("03.03.02.03");

if ($accesoEliminar)
{
    $objDevoluciones = new FacDevoluciones($conn);

    $rsEliminacion = $objDevoluciones->eliminarRegistro($devolucionId);
    
    if (!$rsEliminacion)
    {
        $resultado["error"] = 1;
        $resultado["mensaje"] = $objDevoluciones->mensajeError;
    }
}
else
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = "You do not have access to delete documents.";
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------