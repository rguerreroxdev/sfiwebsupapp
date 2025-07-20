<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/FacDevoluciones.php");
require_once("../../../../inc/class/FacDevolucionesDetalle.php");
require_once("../../../../inc/class/FacDevolucionesOtrosDetalles.php");
require_once("../../../../inc/class/FacDevolucionesPagos.php");
require_once("../../../../inc/class/FacDevolucionesEstados.php");

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

$sucursalId = $_POST["sucursal"];
$facturaDevueltaId = $_POST["facturadevueltaid"];

$prefijoDeCorrelativo = $_POST["prefijodecorrelativo"];
$correlativo = $_POST["correlativo"];
$estado = $_POST["estado"];

$fechaDeEmision = $_POST["fechadeemision"];
$fechaDeEmision = str_replace("-", "", $fechaDeEmision);

$noCalcularImpuesto = $_POST["nocalcularimpuestovalor"];

$totalAntesDeImpuesto = $_POST["totalantesdeimpuesto"];
$impuestoPorcentaje = $_POST["impuestoporcentaje"];
$impuesto = $_POST["impuesto"];
$totalConImpuesto = $_POST["totalconimpuesto"];
$impuestoFinancieraReal = $_POST["impuestofinancierareal"];
$totalFinal = $_POST["totalfinal"];

$concepto = $_POST["notas"];

$arrayInventarioId = isset($_POST["inventarioid"]) ? $_POST["inventarioid"] : [];
$arrayTipoDeGarantiaId = isset($_POST["garantiaid"]) ? $_POST["garantiaid"] : [];
$arrayDetallePrecio = isset($_POST["precio"]) ? $_POST["precio"] : [];

$arrayOtroServicioId = isset($_POST["servicioid"]) ? $_POST["servicioid"] : [];
$arrayServicioPrecio = isset($_POST["servprecio"]) ? $_POST["servprecio"] : [];

$arrayTipoPagoId = isset($_POST["tipopagoid"]) ? $_POST["tipopagoid"] : [];
$arrayFinancieraId = isset($_POST["financieraid"]) ? $_POST["financieraid"] : [];
$arrayContrato = isset($_POST["contrato"]) ? $_POST["contrato"] : [];
$arrayReciboCheque = isset($_POST["recibocheque"]) ? $_POST["recibocheque"] : [];
$arrayPagoMonto = isset($_POST["pagomonto"]) ? $_POST["pagomonto"] : [];
$arrayPagoImpuesto = isset($_POST["pagoimpuestoreal"]) ? $_POST["pagoimpuestoreal"] : [];
$arrayPagoTotal = isset($_POST["pagofilatotalreal"]) ? $_POST["pagofilatotalreal"] : [];

$accion = $devolucionID == -1 ? "Agregar" : "Editar";

//-----------------------------------------------

$objDevolucion = new FacDevoluciones($conn);
$objDevolucionDetalle = new FacDevolucionesDetalle($conn);
$objDevolucionOtroDetalle = new FacDevolucionesOtrosDetalles($conn);
$objDevolucionPagos = new FacDevolucionesPagos($conn);
$objDevolucionEstado = new FacDevolucionesEstados($conn);

//-----------------------------------------------

// Obtener datos de devolución antes de hacerle cambios
$objDevolucionAntesDeCambios = new FacDevoluciones($conn);
$objDevolucionAntesDeCambios->getById($devolucionID);

//-----------------------------------------------

// Se inserta o se actualiza dependiendo de la acción que se está haciendo sobre registros
if ($accion == "Agregar")
{
    $rsAgregar = $objDevolucion->agregarRegistro(
        $sucursalId, $facturaDevueltaId, $fechaDeEmision, $prefijoDeCorrelativo, $correlativo,
        $totalAntesDeImpuesto, $impuestoPorcentaje, $impuesto, $totalConImpuesto, $impuestoFinancieraReal, $totalFinal,
        $concepto, $estado, $usuarioId
    );

    if (!$rsAgregar)
    {
        $error = true;
        $resultado["error"] = 1;
        $resultado["mensaje"] = $objDevolucion->mensajeError;
    }
    else
    {
        $devolucionID = $objDevolucion->devolucionId;
    }
}
else
{
    $fechaModificacion = date("Ymd H:i:s");

    $rsModificar = $objDevolucion->editarRegistro(
        $devolucionID,
        [
            "SUCURSALID", $sucursalId,
            "FACTURADEVUELTAID", $facturaDevueltaId,
            "FECHA", $fechaDeEmision,
            "PREFIJODECORRELATIVO", $prefijoDeCorrelativo,
            "CORRELATIVO", $correlativo,
            "TOTALANTESDEIMPUESTO", $totalAntesDeImpuesto,
            "IMPUESTOPORCENTAJE", $impuestoPorcentaje,
            "IMPUESTO", $impuesto,
            "TOTALCONIMPUESTO", $totalConImpuesto,
            "IMPUESTOFINANCIERA", $impuestoFinancieraReal,
            "TOTALFINAL", $totalFinal,
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
    $insertar = false;
    // Si se está agregando, solamente agregar las filas
    if ($accion == "Agregar")
    {
        $insertar = true;
    }
    else // se está editando
    {
        // Si la factura devuelta ha cambiado: eliminar detalles para agregar los nuevos
        if ($objDevolucionAntesDeCambios->facturaDevueltaId != $facturaDevueltaId)
        {
            $objDevolucionDetalle->eliminarDetallesDeDevolucion($devolucionID);
            $objDevolucionOtroDetalle->eliminarOtrosDetallesDeDevolucion($devolucionID);
            $objDevolucionPagos->eliminarPagosDeDevolucion($devolucionID);

            $insertar = true;
        }
    }

    if ($insertar)
    {
        //***** Los INSERT de Detalle *****
        for ($i=0; $i < count($arrayInventarioId); $i++)
        { 
            $rsAgregar = $objDevolucionDetalle->agregarRegistro(
                $devolucionID,
                $arrayInventarioId[$i],
                $arrayTipoDeGarantiaId[$i],
                $arrayDetallePrecio[$i]
            );

            if (!$rsAgregar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objDevolucionDetalle->mensajeError;

                break;
            }
        }

        //***** Los INSERT de OtroDetalle *****
        for ($i=0; $i < count($arrayOtroServicioId); $i++)
        { 
            $rsAgregar = $objDevolucionOtroDetalle->agregarRegistro(
                $devolucionID,
                $arrayOtroServicioId[$i],
                $arrayServicioPrecio[$i]
            );

            if (!$rsAgregar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objDevolucionOtroDetalle->mensajeError;

                break;
            }
        }

        //***** Los INSERT de Pagos *****
        for ($i=0; $i < count($arrayTipoPagoId); $i++)
        { 
            $rsAgregar = $objDevolucionPagos->agregarRegistro(
                $devolucionID,
                $arrayTipoPagoId[$i],
                $arrayFinancieraId[$i],
                $arrayContrato[$i],
                $arrayReciboCheque[$i],
                $arrayPagoMonto[$i],
                $arrayPagoImpuesto[$i],
                $arrayPagoTotal[$i]
            );

            if (!$rsAgregar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objDevolucionPagos->mensajeError;

                break;
            }
        }
    }
}

if (!$error)
{
    // Guardar historial
    $historialDescripcion = $accion == "Agregar" ? "Document creation" : "Document modification";
    $objDevolucionEstado->agregarRegistro($devolucionID, $estado, $historialDescripcion, $usuarioId);

    $resultado["did"] = $devolucionID;
}

//----d------------------------------------------

// Poner en sesión la sucursal para filtrar en búsqueda
Sesion::setVariableDeSesion("sucursalDeTrabajo", $sucursalId);

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------