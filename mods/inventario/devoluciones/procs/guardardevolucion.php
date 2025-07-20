<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/DevolucionesInv.php");
require_once("../../../../inc/class/DevolucionesDetalleInv.php");
require_once("../../../../inc/class/DevolucionesEstadosInv.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

$error = false;

//-----------------------------------------------

$devolucionId = $_POST["did"];
$usuarioId = $_POST["uid"];

$correlativo = $_POST["correlativo"];
$estado = $_POST["estado"];
$fecha = $_POST["fechadedevolucion"];
$sucursalId = $_POST["sucursal"];
$tipoDedevolucionId = $_POST["tipodedevolucion"];
$concepto = $_POST["concepto"];

$fecha = str_replace("-", "", $fecha);

$arrayDetalleId = $_POST["detalleid"];
$arrayInventarioId = $_POST["inventarioid"];
$arraySalidaDetalleId = $_POST["salidadetalleid"];

$arrayFilasEliminadas = json_decode($_POST["filaseliminadas"], true);

$accion = $devolucionId == -1 ? "Agregar" : "Editar";

//-----------------------------------------------

$objDevolucion = new DevolucionesInv($conn);
$objDevolucionDetalle = new DevolucionesDetalleInv($conn);
$objDevolucionEstado = new DevolucionesEstadosInv($conn);

// Se inserta o se actualiza dependiendo de la acción que se está haciendo sobre registros
if ($accion == "Agregar")
{
    $rsAgregar = $objDevolucion->agregarRegistro(
        $sucursalId, $tipoDedevolucionId, $fecha, $correlativo, $estado, $concepto, $usuarioId
    );

    if (!$rsAgregar)
    {
        $error = true;
        $resultado["error"] = 1;
        $resultado["mensaje"] = $objDevolucion->mensajeError;
    }
    else
    {
        $devolucionId = $objDevolucion->devolucionId;
    }
}
else
{
    $fechaModificacion = date("Ymd H:i:s");

    $rsModificar = $objDevolucion->editarRegistro(
        $devolucionId,
        [
            "SUCURSALID", $sucursalId,
            "TIPODEDEVOLUCIONID", $tipoDedevolucionId,
            "FECHA", $fecha,
            "CONCEPTO", $concepto,
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
}

// Si no hay error en la inserción o actualización del registro, se pasa a guardar el detalle
if (!$error)
{
    // Se realizan los DELETE (filas que se eliminaron y ya estaban guardadas)
    foreach($arrayFilasEliminadas as $detalleId)
    {
        $rsEliminar = $objDevolucionDetalle->eliminarRegistro($detalleId);

        if (!$rsEliminar)
        {
            $error = true;
            $resultado["error"] = 1;
            $resultado["mensaje"] = $objDevolucionDetalle->mensajeError;

            break;
        }
    }
    
    // los INSERT y los UPDATE
    for ($i=0; $i < count($arrayDetalleId); $i++) { 
        // INSERT si no hay detalleId, y UPDATE si hay detalleId
        if ($arrayDetalleId[$i] == "")
        {
            $rsAgregar = $objDevolucionDetalle->agregarRegistro(
                $devolucionId,
                $arrayInventarioId[$i],
                $arraySalidaDetalleId[$i]
            );

            if (!$rsAgregar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objDevolucionDetalle->mensajeError;

                break;
            }
        }
        else
        {
            $rsModificar = $objDevolucionDetalle->editarRegistro(
                $arrayDetalleId[$i],
                [
                    "INVENTARIOID", $arrayInventarioId[$i],
                    "SALIDADETALLEID", $arraySalidaDetalleId[$i]
                ]
            );

            if (!$rsModificar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objDevolucionDetalle->mensajeError;

                break;
            }
        }
    }
}

if (!$error)
{
    // Guardar historial
    $historialDescripcion = $accion == "Agregar" ? "Document creation" : "Document modification";
    $objDevolucionEstado->agregarRegistro($devolucionId, $estado, $historialDescripcion, $usuarioId);

    $resultado["did"] = $devolucionId;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------