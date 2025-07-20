<?php
//-----------------------------------------------

function getFilasPagos($conn, string $devolucionId): string
{
    require_once("inc/class/FacDevolucionesPagos.php");
    $objDevolucionPagos = new FacDevolucionesPagos($conn);

    $detalles = $objDevolucionPagos->getAll($devolucionId);

    $filas = "";
    foreach ($detalles as $detalle)
    {
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
            </tr>
        ";
    }

    return $filas;
}

//-----------------------------------------------