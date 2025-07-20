<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/RecepcionesDeCarga.php");
require_once("../../../../inc/class/RecepcionesDeCargaDetalle.php");
require_once("../../../../inc/class/RecepcionesDeCargaEstados.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

$error = false;

//-----------------------------------------------

$recepcionDeCargaId = $_POST["rid"];
$usuarioId = $_POST["uid"];

$sucursalId = $_POST["sucursal"];
$proveedorId = $_POST["proveedorid"];
$tipoDeStockOrigenId = $_POST["tipodestockorigen"];
$tipoDeStockDistId = $_POST["tipodestockdist"];
$fechaDeEmision = $_POST["fechadeemision"];
$fechaDeRecepcion = $_POST["fechaderecepcion"];
$correlativo = $_POST["correlativo"];
$numeroDeDocumento = $_POST["numerodedocumento"];
$porcentajeTipoDeStockOrigen = $_POST["porcentajeorigen"];
$porcentajeTipoDeStockDist = $_POST["porcentajedist"];
$tipoDeGarantiaId = $_POST["tipodegarantia"];
$estado = $_POST["estado"];

$arrayDetalleId = $_POST["detalleid"];
$arrayCantidad = $_POST["cantidad"];
$arrayCategoria = $_POST["categoria"];
$arrayModelo = $_POST["modelo"];
$arrayProductoId = $_POST["productoid"];
$arrayTipoDeStockOrigenDetalle = $_POST["tipodestockorigendetalle"];
$arrayTipoDeStockOrigenDetalleId = $_POST["tipodestockorigendetalleid"];
$arrayPorcentajeOrigenDetalle = $_POST["porcentajeorigendetalle"];
$arrayTipoDeStockDistDetalle = $_POST["tipodestockdistdetalle"];
$arrayTipoDeStockDistDetalleId = $_POST["tipodestockdistdetalleid"];
$arrayPorcentajeDistDetalle = $_POST["porcentajedistdetalle"];

$arrayFilasEliminadas = json_decode($_POST["filaseliminadas"], true);

$fechaDeEmision = str_replace("-", "", $fechaDeEmision);
$fechaDeRecepcion = str_replace("-", "", $fechaDeRecepcion);

$accion = $recepcionDeCargaId == -1 ? "Agregar" : "Editar";

//-----------------------------------------------

$objRecepcionDeCarga = new RecepcionesDeCarga($conn);
$objRecepcionDeCargaDetalle = new RecepcionesDeCargaDetalle($conn);
$objRecepcionDeCargaEstado = new RecepcionesDeCargaEstados($conn);

// Se inserta o se actualiza dependiendo de la acción que se está haciendo sobre registros
if ($accion == "Agregar")
{
    $rsAgregar = $objRecepcionDeCarga->agregarRegistro(
        $sucursalId, $proveedorId, $tipoDeStockOrigenId, $tipoDeStockDistId, $fechaDeEmision, $fechaDeRecepcion, $correlativo,
        $numeroDeDocumento, $porcentajeTipoDeStockOrigen, $porcentajeTipoDeStockDist, $tipoDeGarantiaId, $estado, $usuarioId
    );

    if (!$rsAgregar)
    {
        $error = true;
        $resultado["error"] = 1;
        $resultado["mensaje"] = $objRecepcionDeCarga->mensajeError;
    }
    else
    {
        $recepcionDeCargaId = $objRecepcionDeCarga->recepcionDeCargaId;
    }
}
else
{
    $fechaModificacion = date("Ymd H:i:s");

    $rsModificar = $objRecepcionDeCarga->editarRegistro(
        $recepcionDeCargaId,
        [
            "SUCURSALID", $sucursalId,
            "PROVEEDORID", $proveedorId,
            "TIPODESTOCKORIGENID", $tipoDeStockOrigenId,
            "TIPODESTOCKDISTID", $tipoDeStockDistId,
            "FECHADEEMISION", $fechaDeEmision,
            "FECHADERECEPCION", $fechaDeRecepcion,
            "NUMERODEDOCUMENTO", $numeroDeDocumento,
            "PORCENTAJETIPODESTOCKORIGEN", $porcentajeTipoDeStockOrigen,
            "PORCENTAJETIPODESTOCKDIST", $porcentajeTipoDeStockDist,
            "TIPODEGARANTIAID", $tipoDeGarantiaId,
            "USUARIOIDMODIFICACION", $usuarioId,
            "FECHAMODIFICACION", $fechaModificacion
        ]
    );

    if (!$rsModificar)
    {
        $error = true;
        $resultado["error"] = 1;
        $resultado["mensaje"] = $objRecepcionDeCarga->mensajeError;
    }
}

// Si no hay error en la inserción o actualización del registro, se pasa a guardar el detalle
if (!$error)
{
    // Se realizan los DELETE (filas que se eliminaron y ya estaban guardadas)
    foreach($arrayFilasEliminadas as $detalleId)
    {
        $rsEliminar = $objRecepcionDeCargaDetalle->eliminarRegistro($detalleId);

        if (!$rsEliminar)
        {
            $error = true;
            $resultado["error"] = 1;
            $resultado["mensaje"] = $objRecepcionDeCargaDetalle->mensajeError;

            break;
        }
    }

    // los INSERT y los UPDATE
    for ($i=0; $i < count($arrayDetalleId); $i++) { 
        // INSERT si no hay detalleId, y UPDATE si hay detalleId
        if ($arrayDetalleId[$i] == "")
        {
            $rsAgregar = $objRecepcionDeCargaDetalle->agregarRegistro(
                $recepcionDeCargaId, $arrayCantidad[$i], $arrayProductoId[$i],
                $arrayTipoDeStockOrigenDetalleId[$i], $arrayTipoDeStockDistDetalleId[$i],
                $arrayPorcentajeOrigenDetalle[$i], $arrayPorcentajeDistDetalle[$i]
            );

            if (!$rsAgregar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objRecepcionDeCargaDetalle->mensajeError;

                break;
            }
        }
        else
        {
            $rsModificar = $objRecepcionDeCargaDetalle->editarRegistro(
                $arrayDetalleId[$i],
                [
                    "CANTIDAD", $arrayCantidad[$i],
                    "PRODUCTOID", $arrayProductoId[$i],
                    "TIPODESTOCKORIGENID", $arrayTipoDeStockOrigenDetalleId[$i],
                    "TIPODESTOCKDISTID", $arrayTipoDeStockDistDetalleId[$i],
                    "PORCENTAJETIPODESTOCKORIGEN", $arrayPorcentajeOrigenDetalle[$i],
                    "PORCENTAJETIPODESTOCKDIST", $arrayPorcentajeDistDetalle[$i]
                ]
            );

            if (!$rsModificar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objRecepcionDeCargaDetalle->mensajeError;

                break;
            }
        }
    }
}

if (!$error)
{
    // Guardar historial
    $historialDescripcion = $accion == "Agregar" ? "Document creation" : "Document modification";
    $objRecepcionDeCargaEstado->agregarRegistro($recepcionDeCargaId, $estado, $historialDescripcion, $usuarioId);

    $resultado["rid"] = $recepcionDeCargaId;
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------