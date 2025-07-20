<?php
//-----------------------------------------------

function getFilasDetalle($conn, string $facturaId): string
{
    require_once("inc/class/FacturasDetalle.php");
    $objFacturaDetalle = new FacturasDetalle($conn);

    $detalles = $objFacturaDetalle->getAll($facturaId);

    $filas = "";
    foreach ($detalles as $detalle)
    {
        $facturaDetalleID = $detalle["FACTURADETALLEID"];
        $inventarioId = $detalle["INVENTARIOID"];
        $codigoInventario = $detalle["CODIGOINVENTARIO"];
        $marca = $detalle["MARCA"];
        $modelo = $detalle["MODELO"];
        $msrp = $detalle["MSRP"];
        $descripcion = $detalle["CATEGORIA"] . " - " . $detalle["DESCRIPCION"];
        $garantia = $detalle["TIPODEGARANTIA"];
        $garantiaId = $detalle["TIPODEGARANTIAID"];
        $precio = $detalle["PRECIO"];
        $filas .= "
            <tr>
                <td>
                    <div class=\"input-group input-group-sm\">
                        <input type=\"text\" id=\"inventario[]\" name=\"inventario[]\" class=\"form-control form-control-sm\" maxlength=\"9\" oninput=\"buscarItemInventario(this)\" value=\"$codigoInventario\" required>
                        <button class=\"btn btn-outline-secondary\" type=\"button\" id=\"btninventario[]\" onclick=\"seleccionarInventario(this)\"><i class=\"bi bi-search\"></i></button>
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
                    <input type=\"number\" id=\"precio[]\" name=\"precio[]\" class=\"form-control form-control-sm text-end\" value=\"$precio\" min=\"0.01\" max=\"99999.99\" step=\"0.01\" oninput=\"calcularTotales()\" required>
                </td>
                <td>
                    <input type=\"hidden\" id=\"detalleid[]\" name=\"detalleid[]\" value=\"$facturaDetalleID\">
                    <button class=\"btn btn-sm btn-outline-danger\" type=\"button\" onclick=\"eliminarFilaDetalle(this)\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button>
                </td>
            </tr>
        ";
    }

    return $filas;
}

//-----------------------------------------------