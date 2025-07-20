<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Traslados.php");
require_once("../../../../inc/class/TrasladosDetalle.php");
require_once("../../../../inc/class/TrasladosEstados.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

$error = false;

//-----------------------------------------------

$trasladoId = $_POST["tid"];
$usuarioId = $_POST["uid"];

$correlativo = $_POST["correlativo"];
$estado = $_POST["estado"];
$sucursalOrigenId = $_POST["sucursalorigen"];
$sucursalDestinoId = $_POST["sucursaldestino"];
$observaciones = $_POST["observaciones"];

$arrayDetalleId = $_POST["detalleid"];
$arrayInventarioId = $_POST["inventarioid"];

$arrayFilasEliminadas = json_decode($_POST["filaseliminadas"], true);

$accion = $trasladoId == -1 ? "Agregar" : "Editar";

//-----------------------------------------------

$objTraslado = new Traslados($conn);
$objTrasladoDetalle = new TrasladosDetalle($conn);
$objTrasladoEstado = new TrasladosEstados($conn);

// Se inserta o se actualiza dependiendo de la acción que se está haciendo sobre registros
if ($accion == "Agregar")
{
    $rsAgregar = $objTraslado->agregarRegistro(
        $sucursalOrigenId, $sucursalDestinoId, $correlativo, $estado, $observaciones, $usuarioId
    );

    if (!$rsAgregar)
    {
        $error = true;
        $resultado["error"] = 1;
        $resultado["mensaje"] = $objTraslado->mensajeError;
    }
    else
    {
        $trasladoId = $objTraslado->trasladoId;
    }
}
else
{
    $fechaModificacion = date("Ymd H:i:s");

    $rsModificar = $objTraslado->editarRegistro(
        $trasladoId,
        [
            "SUCURSALORIGENID", $sucursalOrigenId,
            "SUCURSALDESTINOID", $sucursalDestinoId,
            "OBSERVACIONES", $observaciones,
            "USUARIOIDMODIFICACION", $usuarioId,
            "FECHAMODIFICACION", $fechaModificacion
        ]
    );

    if (!$rsModificar)
    {
        $error = true;
        $resultado["error"] = 1;
        $resultado["mensaje"] = $objTraslado->mensajeError;
    }
}

// Si no hay error en la inserción o actualización del registro, se pasa a guardar el detalle
if (!$error)
{
    // Se realizan los DELETE (filas que se eliminaron y ya estaban guardadas)
    foreach($arrayFilasEliminadas as $detalleId)
    {
        $rsEliminar = $objTrasladoDetalle->eliminarRegistro($detalleId);

        if (!$rsEliminar)
        {
            $error = true;
            $resultado["error"] = 1;
            $resultado["mensaje"] = $objTrasladoDetalle->mensajeError;

            break;
        }
    }
    
    // los INSERT y los UPDATE
    for ($i=0; $i < count($arrayDetalleId); $i++) { 
        // INSERT si no hay detalleId, y UPDATE si hay detalleId
        if ($arrayDetalleId[$i] == "")
        {
            $rsAgregar = $objTrasladoDetalle->agregarRegistro(
                $trasladoId,
                $arrayInventarioId[$i]
            );

            if (!$rsAgregar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objTrasladoDetalle->mensajeError;

                break;
            }
        }
        else
        {
            $rsModificar = $objTrasladoDetalle->editarRegistro(
                $arrayDetalleId[$i],
                [
                    "INVENTARIOID", $arrayInventarioId[$i],
                ]
            );

            if (!$rsModificar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objTrasladoDetalle->mensajeError;

                break;
            }
        }
    }
}

if (!$error)
{
    // Guardar historial
    $historialDescripcion = $accion == "Agregar" ? "Document creation" : "Document modification";
    $objTrasladoEstado->agregarRegistro($trasladoId, $estado, $historialDescripcion, $usuarioId);

    $resultado["tid"] = $trasladoId;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------