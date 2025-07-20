<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Accesos.php");
require_once("../../../../inc/class/PlataformasDeReferencia.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$plataformaDeReferenciaId = $_POST["prid"];
$usuarioId = $_POST["uid"];

//-----------------------------------------------

$objAccesos = new Accesos($conn, $usuarioId);
$accesoEliminar = $objAccesos->getAccesoAOpcion("03.01.02.03");

if ($accesoEliminar)
{
    $objPlataformas = new PlataformasDeReferencia($conn);

    $rsEliminacion = $objPlataformas->eliminarRegistro($plataformaDeReferenciaId);
    
    if (!$rsEliminacion)
    {
        $resultado["error"] = 1;
        $resultado["mensaje"] = $objPlataformas->mensajeError;
    }
}
else
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = "You do not have access to delete records.";
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------