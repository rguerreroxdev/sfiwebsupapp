<?php
//-----------------------------------------------

function getFilasDetalle($conn, string $devolucionId): string
{
    require_once("inc/class/FacDevolucionesDetalle.php");
    $objDevolucionDetalle = new FacDevolucionesDetalle($conn);

    $detalles = $objDevolucionDetalle->getAll($devolucionId);

    $filas = "";
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
        $filas .= "
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

    return $filas;
}

//-----------------------------------------------