<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Facturas.php");
require_once("../../../../inc/class/FacturasDetalle.php");
require_once("../../../../inc/class/FacturasOtrosDetalles.php");
require_once("../../../../inc/class/FacturasPagos.php");
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

$facturaId = $_POST["fid"];
$usuarioId = $_POST["uid"];
$vendedorId = $_POST["vendedorid"];

$sucursalId = $_POST["sucursalid"];
$sucursalNombre = $_POST["sucursalnombre"];
$sucursalDireccion = $_POST["sucursaldireccion"];
$sucursalDireccionComplemento = $_POST["sucursaldireccioncomplemento"];
$sucursalCodigoPostal = $_POST["sucursalcodigopostal"];
$sucursalTelefono = $_POST["sucursaltelefono"];
$sucursalTelefonoServicio = $_POST["sucursaltelefonoservicio"];

$prefijoDeCorrelativo = $_POST["prefijodecorrelativo"];
$correlativo = $_POST["correlativo"];
$estado = $_POST["estado"];

$clienteid = $_POST["clienteid"];
$clienteNombre = $_POST["cliente"];
$clienteDireccion = $_POST["clientedireccion"];
$clienteDireccionComplemento = $_POST["clientedireccioncomplemento"];
$clienteCodigoPostal = $_POST["clientecodigopostal"];
$clienteTelefono = $_POST["clientetelefono"];
$clienteCorreo = $_POST["clientecorreo"];
$esClientePrevio = isset($_POST["esclienteprevio"]) ? 1 : 0;

$personaDeReferencia = $_POST["personadereferencia"];
$plataformaDeReferenciaId = $_POST["plataformadereferencia"];

$fechaDeEmision = $_POST["fechadeemision"];
$fechaDeEmision = str_replace("-", "", $fechaDeEmision);

$formaDeRetiroId = $_POST["formaderetiro"];
$fechaDeRetiro = $_POST["fechaderetiro"];
$fechaDeRetiro = str_replace("-", "", $fechaDeRetiro);

$agregarInstalacion = isset($_POST["agregarinstalacion"]) ? 1 : 0;
$agregarAccesorios = isset($_POST["agregaraccesorios"]) ? 1 : 0;
$noCalcularImpuesto = isset($_POST["nocalcularimpuesto"]) ? 1 : 0;

$totalAntesDeImpuesto = $_POST["totalantesdeimpuesto"];
$impuestoPorcentaje = $_POST["impuestoporcentaje"];
$impuesto = $_POST["impuesto"];
$totalConImpuesto = $_POST["totalconimpuesto"];
$impuestoFinancieraReal = $_POST["impuestofinancierareal"];
$totalFinal = $_POST["totalfinal"];

$notas = $_POST["notas"];

$arrayDetalleId = isset($_POST["detalleid"]) ? $_POST["detalleid"] : [];
$arrayInventarioId = isset($_POST["inventarioid"]) ? $_POST["inventarioid"] : [];
$arrayTipoDeGarantiaId = isset($_POST["garantiaid"]) ? $_POST["garantiaid"] : [];
$arrayDetallePrecio = isset($_POST["precio"]) ? $_POST["precio"] : [];

$arrayServicioId = isset($_POST["servdetalleid"]) ? $_POST["servdetalleid"] : [];
$arrayOtroServicioId = isset($_POST["servicioid"]) ? $_POST["servicioid"] : [];
$arrayServicioPrecio = isset($_POST["servprecio"]) ? $_POST["servprecio"] : [];

$arrayPagoId = isset($_POST["facpagoid"]) ? $_POST["facpagoid"] : [];
$arrayTipoPagoId = isset($_POST["tipopagoid"]) ? $_POST["tipopagoid"] : [];
$arrayFinancieraId = isset($_POST["financieraid"]) ? $_POST["financieraid"] : [];
$arrayContrato = isset($_POST["contrato"]) ? $_POST["contrato"] : [];
$arrayReciboCheque = isset($_POST["recibocheque"]) ? $_POST["recibocheque"] : [];
$arrayPagoMonto = isset($_POST["pagomonto"]) ? $_POST["pagomonto"] : [];
$arrayPagoImpuesto = isset($_POST["pagoimpuestoreal"]) ? $_POST["pagoimpuestoreal"] : [];
$arrayPagoTotal = isset($_POST["pagofilatotalreal"]) ? $_POST["pagofilatotalreal"] : [];

$arrayFilasDetalleEliminadas = json_decode($_POST["filasdetalleeliminadas"], true);
$arrayFilasOtroDetalleEliminadas = json_decode($_POST["filasservicioeliminadas"], true);
$arrayFilasPagoEliminadas = json_decode($_POST["filaspagoeliminadas"], true);

$accion = $facturaId == -1 ? "Agregar" : "Editar";

//-----------------------------------------------

$objFactura = new Facturas($conn);
$objFacturaDetalle = new FacturasDetalle($conn);
$objFacturaOtroDetalle = new FacturasOtrosDetalles($conn);
$objFacturaPagos = new FacturasPagos($conn);
$objFacturaEstado = new FacturasEstados($conn);

// Se inserta o se actualiza dependiendo de la acción que se está haciendo sobre registros
if ($accion == "Agregar")
{
    $rsAgregar = $objFactura->agregarRegistro(
        $sucursalId, $clienteid, $vendedorId, $plataformaDeReferenciaId, $formaDeRetiroId,
        $fechaDeEmision, $prefijoDeCorrelativo, $correlativo,
        $sucursalNombre, $sucursalDireccion, $sucursalDireccionComplemento, $sucursalCodigoPostal, $sucursalTelefono, $sucursalTelefonoServicio,
        $clienteNombre, $clienteDireccion, $clienteDireccionComplemento, $clienteCodigoPostal, $clienteTelefono, $clienteCorreo,
        $personaDeReferencia, $esClientePrevio, $fechaDeRetiro, $agregarInstalacion, $agregarAccesorios, $noCalcularImpuesto,
        $totalAntesDeImpuesto, $impuestoPorcentaje, $impuesto, $totalConImpuesto, $impuestoFinancieraReal, $totalFinal, $notas,
        $estado, $usuarioId 
    );

    if (!$rsAgregar)
    {
        $error = true;
        $resultado["error"] = 1;
        $resultado["mensaje"] = $objFactura->mensajeError;
    }
    else
    {
        $facturaId = $objFactura->facturaId;
    }
}
else
{
    $fechaModificacion = date("Ymd H:i:s");

    $rsModificar = $objFactura->editarRegistro(
        $facturaId,
        [
            "CLIENTEID", $clienteid,
            "USUARIOIDVENDEDOR", $vendedorId,
            "PLATAFORMADEREFERENCIAID", $plataformaDeReferenciaId,
            "FORMADERETIROID", $formaDeRetiroId,
            "FECHA", $fechaDeEmision,
            "PREFIJODECORRELATIVO", $prefijoDeCorrelativo,
            "CORRELATIVO", $correlativo,
            "SUCURSALID", $sucursalId,
            "SUCURSALNOMBRE", $sucursalNombre,
            "SUCURSALDIRECCION", $sucursalDireccion,
            "SUCURSALDIRECCIONCOMPLEMENTO", $sucursalDireccionComplemento,
            "SUCURSALCODIGOPOSTAL", $sucursalCodigoPostal,
            "SUCURSALTELEFONO", $sucursalTelefono,
            "SUCURSALTELEFONOSERVICIO", $sucursalTelefonoServicio,
            "CLIENTENOMBRE", $clienteNombre,
            "CLIENTEDIRECCION", $clienteDireccion,
            "CLIENTEDIRECCIONCOMPLEMENTO", $clienteDireccionComplemento,
            "CLIENTECODIGOPOSTAL", $clienteCodigoPostal,
            "CLIENTETELEFONO", $clienteTelefono,
            "CLIENTECORREOELECTRONICO", $clienteCorreo,
            "PERSONADEREFERENCIA", $personaDeReferencia,
            "ESCLIENTEPREVIO", $esClientePrevio,
            "FECHADERETIRO", $fechaDeRetiro,
            "AGREGARINSTALACION", $agregarInstalacion,
            "AGREGARACCESORIOS", $agregarAccesorios,
            "NOCALCULARIMPUESTO", $noCalcularImpuesto,
            "TOTALANTESDEIMPUESTO", $totalAntesDeImpuesto,
            "IMPUESTOPORCENTAJE", $impuestoPorcentaje,
            "IMPUESTO", $impuesto,
            "TOTALCONIMPUESTO", $totalConImpuesto,
            "IMPUESTOFINANCIERA", $impuestoFinancieraReal,
            "TOTALFINAL", $totalFinal,
            "NOTAS", $notas,
            "USUARIOIDMODIFICACION", $usuarioId,
            "FECHAMODIFICACION", $fechaModificacion
        ]
    );

    if (!$rsModificar)
    {
        $error = true;
        $resultado["error"] = 1;
        $resultado["mensaje"] = $objFactura->mensajeError;
    }
}

// Si no hay error en la inserción o actualización del registro, se pasa a guardar el detalle
if (!$error)
{
    // Se realizan los DELETE (filas que se eliminaron y ya estaban guardadas)
    //***** Los DELETE de Detalle *****
    foreach($arrayFilasDetalleEliminadas as $id)
    {
        $rsEliminar = $objFacturaDetalle->eliminarRegistro($id);

        if (!$rsEliminar)
        {
            $error = true;
            $resultado["error"] = 1;
            $resultado["mensaje"] = $objFacturaDetalle->mensajeError;

            break;
        }
    }
    //***** Los DELETE de OtroDetalle *****
    foreach($arrayFilasOtroDetalleEliminadas as $id)
    {
        $rsEliminar = $objFacturaOtroDetalle->eliminarRegistro($id);

        if (!$rsEliminar)
        {
            $error = true;
            $resultado["error"] = 1;
            $resultado["mensaje"] = $objFacturaOtroDetalle->mensajeError;

            break;
        }
    }
    //***** Los DELETE de Pagos *****
    foreach($arrayFilasPagoEliminadas as $id)
    {
        $rsEliminar = $objFacturaPagos->eliminarRegistro($id);

        if (!$rsEliminar)
        {
            $error = true;
            $resultado["error"] = 1;
            $resultado["mensaje"] = $objFacturaPagos->mensajeError;

            break;
        }
    }

    //***** Los INSERT y los UPDATE de Detalle *****
    for ($i=0; $i < count($arrayDetalleId); $i++)
    { 
        // INSERT si no hay detalleId, y UPDATE si hay detalleId
        if ($arrayDetalleId[$i] == "")
        {
            $rsAgregar = $objFacturaDetalle->agregarRegistro(
                $facturaId,
                $arrayInventarioId[$i],
                $arrayTipoDeGarantiaId[$i],
                $arrayDetallePrecio[$i]
            );

            if (!$rsAgregar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objFacturaDetalle->mensajeError;

                break;
            }
        }
        else
        {
            $rsModificar = $objFacturaDetalle->editarRegistro(
                $arrayDetalleId[$i],
                [
                    "INVENTARIOID", $arrayInventarioId[$i],
                    "TIPODEGARANTIAID", $arrayTipoDeGarantiaId[$i],
                    "PRECIO", $arrayDetallePrecio[$i]
                ]
            );

            if (!$rsModificar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objFacturaDetalle->mensajeError;

                break;
            }
        }
    }

    //***** Los INSERT y los UPDATE de OtroDetalle *****
    for ($i=0; $i < count($arrayServicioId); $i++)
    { 
        // INSERT si no hay servicioId, y UPDATE si hay servicioId
        if ($arrayServicioId[$i] == "")
        {
            $rsAgregar = $objFacturaOtroDetalle->agregarRegistro(
                $facturaId,
                $arrayOtroServicioId[$i],
                $arrayServicioPrecio[$i]
            );

            if (!$rsAgregar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objFacturaOtroDetalle->mensajeError;

                break;
            }
        }
        else
        {
            $rsModificar = $objFacturaOtroDetalle->editarRegistro(
                $arrayServicioId[$i],
                [
                    "OTROSERVICIOPRODUCTOID", $arrayOtroServicioId[$i],
                    "PRECIO", $arrayServicioPrecio[$i]
                ]
            );

            if (!$rsModificar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objFacturaOtroDetalle->mensajeError;

                break;
            }
        }
    }

    //***** Los INSERT y los UPDATE de Pagos *****
    for ($i=0; $i < count($arrayPagoId); $i++)
    { 
        // INSERT si no hay pagoId, y UPDATE si hay pagoId
        if ($arrayPagoId[$i] == "")
        {
            $rsAgregar = $objFacturaPagos->agregarRegistro(
                $facturaId,
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
                $resultado["mensaje"] = $objFacturaPagos->mensajeError;

                break;
            }
        }
        //****** Por el momento no hay modificación de pagos */
        else
        {
            $rsModificar = $objFacturaPagos->editarRegistro(
                $arrayPagoId[$i],
                [
                    "TIPODEPAGOID", $arrayTipoPagoId[$i],
                    "FINANCIERAID", $arrayFinancieraId[$i],
                    "CONTRATOFINANCIERA", $arrayContrato[$i],
                    "NUMERORECIBOCHEQUE", $arrayReciboCheque[$i],
                    "MONTO", $arrayPagoMonto[$i],
                    "IMPUESTO", $arrayPagoImpuesto[$i],
                    "TOTAL", $arrayPagoTotal[$i]
                ]
            );

            if (!$rsModificar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objFacturaPagos->mensajeError;

                break;
            }
        }
    }
}

if (!$error)
{
    // Guardar historial
    $historialDescripcion = $accion == "Agregar" ? "Document creation" : "Document modification";
    $objFacturaEstado->agregarRegistro($facturaId, $estado, $historialDescripcion, $usuarioId);

    $resultado["fid"] = $facturaId;
}

//-----------------------------------------------

// Poner en sesión la sucursal para filtrar en búsqueda
Sesion::setVariableDeSesion("sucursalDeTrabajo", $sucursalId);

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------