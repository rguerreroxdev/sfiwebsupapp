<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Facturas.php");
require_once("../../../../inc/class/FacturasDetalle.php");
require_once("../../../../inc/class/FacturasOtrosDetalles.php");
require_once("../../../../inc/class/FacturasPagos.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$facturaId = isset($_POST["fid"]) && trim($_POST["fid"]) != "" ? $_POST["fid"] : -1;

//-----------------------------------------------

$objFactura = new Facturas($conn);

$objFactura->getById($facturaId);

$resultado["facturaId"] = $objFactura->facturaId;
$resultado["correlativoCompuesto"] = $objFactura->prefijoDeCorrelativo . "-" . $objFactura->correlativo;
$resultado["fecha"] = $objFactura->fecha;
$resultado["clienteId"] = $objFactura->clienteId;
$resultado["clienteCodigo"] = $objFactura->clienteCodigo;
$resultado["clienteNombre"] = $objFactura->clienteNombre;
$resultado["clienteDireccion"] = $objFactura->clienteDireccion;
$resultado["clienteDireccionComplemento"] = $objFactura->clienteDireccionComplemento;
$resultado["clienteCodigoPostal"] = $objFactura->clienteCodigoPostal;
$resultado["clienteTelefono"] = $objFactura->clienteTelefono;
$resultado["clienteCorreo"] = $objFactura->clienteCorreoElectronico;
$resultado["noCalcularImpuesto"] = $objFactura->noCalcularImpuesto;
$resultado["totalAntesDeImpuesto"] = $objFactura->totalAntesDeImpuesto;
$resultado["impuestoPorcentaje"] = $objFactura->impuestoPorcentaje;
$resultado["impuesto"] = $objFactura->impuesto;
$resultado["totalConImpuesto"] = $objFactura->totalConImpuesto;
$resultado["impuestoFinanciera"] = $objFactura->impuestoFinanciera;
$resultado["totalFinal"] = $objFactura->totalFinal;

//-----------------------------------------------

$objFacturaDetalles = new FacturasDetalle($conn);

$detalles = $objFacturaDetalles->getAll($facturaId);

$tbodyDetalle = "";
foreach ($detalles as $detalle) {
        $inventarioId = $detalle["INVENTARIOID"];
        $codigoInventario = $detalle["CODIGOINVENTARIO"];
        $marca = $detalle["MARCA"];
        $modelo = $detalle["MODELO"];
        $msrp = $detalle["MSRP"];
        $descripcion = $detalle["CATEGORIA"] . " - " . $detalle["DESCRIPCION"];
        $garantia = $detalle["TIPODEGARANTIA"];
        $garantiaId = $detalle["TIPODEGARANTIAID"];
        $precio = $detalle["PRECIO"];
        $tbodyDetalle .= "
            <tr>
                <td>
                    <div class=\"input-group input-group-sm\">
                        <input type=\"text\" id=\"inventario[]\" name=\"inventario[]\" class=\"form-control form-control-sm\" value=\"$codigoInventario\" readonly>
                        <input type=\"hidden\" id=\"inventarioid[]\" name=\"inventarioid[]\" value=\"$inventarioId\">
                    </div>
                </td>
                <td>
                    <input type=\"text\" id=\"marca[]\" name=\"marca[]\" class=\"form-control form-control-sm\" value=\"$marca\" readonly>
                </td>
                <td>
                    <input type=\"text\" id=\"modelo[]\" name=\"modelo[]\" class=\"form-control form-control-sm\" value=\"$modelo\" readonly>
                </td>
                <td>
                    <input type=\"text\" id=\"msrp[]\" name=\"msrp[]\" class=\"form-control form-control-sm text-end\" value=\"$msrp\" readonly>
                </td>
                <td>
                    <input type=\"text\" id=\"descripcion[]\" name=\"descripcion[]\" class=\"form-control form-control-sm\" value=\"$descripcion\" readonly>
                </td>
                <td>
                    <input type=\"text\" id=\"garantia[]\" name=\"garantia[]\" class=\"form-control form-control-sm\" value=\"$garantia\" readonly>
                    <input type=\"hidden\" id=garantiaid[] name=garantiaid[] value=\"$garantiaId\">
                </td>
                <td>
                    <input type=\"number\" id=\"precio[]\" name=\"precio[]\" class=\"form-control form-control-sm text-end\" value=\"$precio\" readonly>
                </td>
            </tr>
        ";
}

$resultado["tbodyDetalle"] = $tbodyDetalle;

//-----------------------------------------------

$objFacturaOtrosDetalles = new FacturasOtrosDetalles($conn);

$otrosDetalles = $objFacturaOtrosDetalles->getAll($facturaId);

$tbodyOtrosDetalles = "";
foreach ($otrosDetalles as $detalle)
{
    $facturaOtroDetalleID = $detalle["FACTURAOTRODETALLEID"];
    $otroServicioProductoId = $detalle["OTROSERVICIOPRODUCTOID"];
    $codigo = $detalle["PRODUCTOCODIGO"];
    $marca = $detalle["MARCA"];
    $modelo = $detalle["MODELO"];
    $descripcion = $detalle["DESCRIPCION"];
    $precio = $detalle["PRECIO"];
    $tbodyOtrosDetalles .= "
        <tr>
            <td>
                <div class=\"input-group input-group-sm\">
                    <input type=\"text\" id=\"servicio[]\" name=\"servicio[]\" class=\"form-control form-control-sm\" value=\"$codigo\" readonly>
                    <input type=\"hidden\" id=\"servicioid[]\" name=\"servicioid[]\" value=\"$otroServicioProductoId\">
                </div>
            </td>
            <td>
                <input type=\"text\" id=\"servmarca[]\" name=\"servmarca[]\" class=\"form-control form-control-sm\" value=\"$marca\" readonly>
            </td>
            <td>
                <input type=\"text\" id=\"servmodelo[]\" name=\"servmodelo[]\" class=\"form-control form-control-sm\" value=\"$modelo\" readonly>
            </td>
            <td>
                <input type=\"text\" id=\"servdescripcion[]\" name=\"servdescripcion[]\" class=\"form-control form-control-sm\" value=\"$descripcion\" readonly>
            </td>
            <td>
                <input type=\"number\" id=\"servprecio[]\" name=\"servprecio[]\" class=\"form-control form-control-sm text-end\" value=\"$precio\" readonly>
            </td>
        </tr>
    ";
}

$resultado["tbodyOtrosDetalles"] = $tbodyOtrosDetalles;

//-----------------------------------------------

$objFacturaPagos = new FacturasPagos($conn);

$pagos = $objFacturaPagos->getAll($facturaId);

$tbodyPagos = "";
$pagoTotalMonto = 0.00;
$pagoTotalImpuesto = 0.00;
$pagoTotalFinal = 0.00;
foreach ($pagos as $detalle)
{
    $facturaPagoID = $detalle["FACTURAPAGOID"];
    $tipoDePagoId = $detalle["TIPODEPAGOID"];
    $tipoDePago = $detalle["TIPODEPAGO"];
    $financieraId = $detalle["FINANCIERAID"];
    $financiera = $detalle["FINANCIERA"];
    $contratoFinanciera = $detalle["CONTRATOFINANCIERA"];
    $numeroReciboCheque = $detalle["NUMERORECIBOCHEQUE"];
    $monto = $detalle["MONTO"];
    $impuesto = number_format($detalle["IMPUESTO"], 2);
    $impuestoReal = $detalle["IMPUESTO"];
    $total = number_format($detalle["TOTAL"], 2);
    $totalReal = $detalle["TOTAL"];

    $financieraId = $financieraId == null ? -1 : $financieraId;

    $tbodyPagos .= "
        <tr>
            <td>
                <input type=\"text\" id=\"tipopago[]\" name=\"tipopago[]\" class=\"form-control form-control-sm\" value=\"$tipoDePago\" readonly>
                <input type=\"hidden\" id=\"tipopagoid[]\" name=\"tipopagoid[]\" value=\"$tipoDePagoId\">
            </td>
            <td>
                <input type=\"text\" id=\"financiera[]\" name=\"financiera[]\" class=\"form-control form-control-sm\" value=\"$financiera\" readonly>
                <input type=\"hidden\" id=\"financieraid[]\" name=\"financieraid[]\" value=\"$financieraId\">
            </td>
            <td>
                <input type=\"text\" id=\"contrato[]\" name=\"contrato[]\" class=\"form-control form-control-sm\" value=\"$contratoFinanciera\" readonly>
            </td>
            <td>
                <input type=\"text\" id=\"recibocheque[]\" name=\"recibocheque[]\" class=\"form-control form-control-sm\" value=\"$numeroReciboCheque\" readonly>
            </td>
            <td>
                <input type=\"text\" id=\"pagomonto[]\" name=\"pagomonto[]\" class=\"form-control form-control-sm text-end\" value=\"$monto\" readonly>
            </td>
            <td>
                <input type=\"text\" id=\"pagoimpuesto[]\" name=\"pagoimpuesto[]\" class=\"form-control form-control-sm text-end\" value=\"$impuesto\" readonly>
                <input type=\"hidden\" id=\"pagoimpuestoreal[]\" name=\"pagoimpuestoreal[]\" class=\"form-control form-control-sm text-end\" value=\"$impuestoReal\">
            </td>
            <td>
                <input type=\"text\" id=\"pagofilatotal[]\" name=\"pagofilatotal[]\" class=\"form-control form-control-sm text-end\" value=\"$total\" readonly>
                <input type=\"hidden\" id=\"pagofilatotalreal[]\" name=\"pagofilatotalreal[]\" class=\"form-control form-control-sm text-end\" value=\"$totalReal\" readonly>
            </td>
        </tr>
    ";

    $pagoTotalMonto += $monto;
    $pagoTotalImpuesto += $impuestoReal;
    $pagoTotalFinal += $totalReal;
}

$resultado["tbodyPagos"] = $tbodyPagos;
$resultado["pagoTotalMonto"] = $pagoTotalMonto;
$resultado["pagoTotalImpuesto"] = $pagoTotalImpuesto;
$resultado["pagoTotalFinal"] = $pagoTotalFinal;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------