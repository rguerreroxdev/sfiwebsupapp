<?php
//-----------------------------------------------

function getFilasServiciosOtrosProductos($conn, string $devolucionId): string
{
    require_once("inc/class/FacDevolucionesOtrosDetalles.php");
    $objDevolucionOtroDetalle = new FacDevolucionesOtrosDetalles($conn);

    $detalles = $objDevolucionOtroDetalle->getAll($devolucionId);

    $filas = "";
    foreach ($detalles as $detalle)
    {
        $otroServicioProductoId = $detalle["OTROSERVICIOPRODUCTOID"];
        $codigo = $detalle["PRODUCTOCODIGO"];
        $marca = $detalle["MARCA"];
        $modelo = $detalle["MODELO"];
        $descripcion = $detalle["DESCRIPCION"];
        $precio = $detalle["PRECIO"];
        $filas .= "
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

    return $filas;
}

//-----------------------------------------------