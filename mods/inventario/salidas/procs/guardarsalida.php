<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Salidas.php");
require_once("../../../../inc/class/SalidasDetalle.php");
require_once("../../../../inc/class/SalidasEstados.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

$error = false;

//-----------------------------------------------

$salidaId = $_POST["sid"];
$usuarioId = $_POST["uid"];

$correlativo = $_POST["correlativo"];
$estado = $_POST["estado"];
$fecha = $_POST["fechadesalida"];
$sucursalId = $_POST["sucursal"];
$tipoDeSalidaId = $_POST["tipodesalida"];
$concepto = $_POST["concepto"];

$fecha = str_replace("-", "", $fecha);

$arrayDetalleId = $_POST["detalleid"];
$arrayInventarioId = $_POST["inventarioid"];

$arrayFilasEliminadas = json_decode($_POST["filaseliminadas"], true);

$accion = $salidaId == -1 ? "Agregar" : "Editar";

//-----------------------------------------------

$objSalida = new Salidas($conn);
$objSalidaDetalle = new SalidasDetalle($conn);
$objSalidaEstado = new SalidasEstados($conn);

// Se inserta o se actualiza dependiendo de la acción que se está haciendo sobre registros
if ($accion == "Agregar")
{
    $rsAgregar = $objSalida->agregarRegistro(
        $sucursalId, $tipoDeSalidaId, $fecha, $correlativo, $estado, $concepto, $usuarioId
    );

    if (!$rsAgregar)
    {
        $error = true;
        $resultado["error"] = 1;
        $resultado["mensaje"] = $objSalida->mensajeError;
    }
    else
    {
        $salidaId = $objSalida->salidaId;
    }
}
else
{
    $fechaModificacion = date("Ymd H:i:s");

    $rsModificar = $objSalida->editarRegistro(
        $salidaId,
        [
            "SUCURSALID", $sucursalId,
            "TIPODESALIDAID", $tipoDeSalidaId,
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
        $resultado["mensaje"] = $objSalida->mensajeError;
    }
}

// Si no hay error en la inserción o actualización del registro, se pasa a guardar el detalle
if (!$error)
{
    // Se realizan los DELETE (filas que se eliminaron y ya estaban guardadas)
    foreach($arrayFilasEliminadas as $detalleId)
    {
        $rsEliminar = $objSalidaDetalle->eliminarRegistro($detalleId);

        if (!$rsEliminar)
        {
            $error = true;
            $resultado["error"] = 1;
            $resultado["mensaje"] = $objSalidaDetalle->mensajeError;

            break;
        }
    }
    
    // los INSERT y los UPDATE
    for ($i=0; $i < count($arrayDetalleId); $i++) { 
        // INSERT si no hay detalleId, y UPDATE si hay detalleId
        if ($arrayDetalleId[$i] == "")
        {
            $rsAgregar = $objSalidaDetalle->agregarRegistro(
                $salidaId,
                $arrayInventarioId[$i]
            );

            if (!$rsAgregar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objSalidaDetalle->mensajeError;

                break;
            }
        }
        else
        {
            $rsModificar = $objSalidaDetalle->editarRegistro(
                $arrayDetalleId[$i],
                [
                    "INVENTARIOID", $arrayInventarioId[$i],
                ]
            );

            if (!$rsModificar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objSalidaDetalle->mensajeError;

                break;
            }
        }
    }
}

if (!$error)
{
    // Guardar historial
    $historialDescripcion = $accion == "Agregar" ? "Document creation" : "Document modification";
    $objSalidaEstado->agregarRegistro($salidaId, $estado, $historialDescripcion, $usuarioId);

    $resultado["sid"] = $salidaId;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------