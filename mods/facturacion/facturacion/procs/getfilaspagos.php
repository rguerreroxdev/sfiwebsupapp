<?php
//-----------------------------------------------

function getFilasPagos($conn, string $facturaId): string
{
    require_once("inc/class/FacturasPagos.php");
    $objFacturaPagos = new FacturasPagos($conn);

    $detalles = $objFacturaPagos->getAll($facturaId);

    $filas = "";
    foreach ($detalles as $detalle)
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

        $filas .= "
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
                <td>
                    <input type=\"hidden\" id=\"facpagoid[]\" name=\"facpagoid[]\" value=\"$facturaPagoID\">
                    <button class=\"btn btn-sm btn-outline-danger\" type=\"button\" onclick=\"eliminarFilaPago(this)\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button>
                </td>
            </tr>
        ";
    }

    return $filas;
}

//-----------------------------------------------