<?php
//-----------------------------------------------

function getFilasServiciosOtrosProductos($conn, string $facturaId): string
{
    require_once("inc/class/FacturasOtrosDetalles.php");
    $objFacturaOtroDetalle = new FacturasOtrosDetalles($conn);

    $detalles = $objFacturaOtroDetalle->getAll($facturaId);

    $filas = "";
    foreach ($detalles as $detalle)
    {
        $facturaOtroDetalleID = $detalle["FACTURAOTRODETALLEID"];
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
                        <input type=\"text\" id=\"servicio[]\" name=\"servicio[]\" class=\"form-control form-control-sm\" maxlength=\"5\" oninput=\"buscarItemServicio(this)\" value=\"$codigo\" required>
                        <button class=\"btn btn-outline-secondary\" type=\"button\" id=\"btnservicio[]\" onclick=\"seleccionarServicio(this)\"><i class=\"bi bi-search\"></i></button>
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
                    <input type=\"number\" id=\"servprecio[]\" name=\"servprecio[]\" class=\"form-control form-control-sm text-end\" value=\"$precio\" min=\"0.01\" max=\"99999.99\" step=\"0.01\" oninput=\"calcularTotales()\" required>
                </td>
                <td>
                    <input type=\"hidden\" id=\"servdetalleid[]\" name=\"servdetalleid[]\" value=\"$facturaOtroDetalleID\">
                    <button class=\"btn btn-sm btn-outline-danger\" type=\"button\" onclick=\"eliminarFilaServicio(this)\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button>
                </td>
            </tr>
        ";
    }

    return $filas;
}

//-----------------------------------------------