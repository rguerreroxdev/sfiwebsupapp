<?php
//-----------------------------------------------

function getFilasDetalle($conn, string $trasladoId): string
{
    require_once("inc/class/TrasladosDetalle.php");
    $objTrasladoDetalle = new TrasladosDetalle($conn);

    $detalles = $objTrasladoDetalle->getAll($trasladoId);

    $filas = "";
    foreach ($detalles as $detalle)
    {
        $trasladoDetalleID = $detalle["TRASLADODETALLEID"];
        $inventarioId = $detalle["INVENTARIOID"];
        $codigoInventario = $detalle["CODIGOINVENTARIO"];
        $categoria = $detalle["CATEGORIA"];
        $marca = $detalle["MARCA"];
        $modelo = $detalle["MODELO"];
        $descripcion = $detalle["DESCRIPCION"];
        $msrp = $detalle["MSRP"];
        $stockporcdist = $detalle["PORCENTAJETIPODESTOCKDIST"];
        $stockdist = $detalle["TIPODESTOCKDIST"];
        
        $filas .= "
            <tr>
                <td>
                    <div class=\"input-group input-group-sm\">
                        <input type=\"text\" id=\"inventario[]\" name=\"inventario[]\" class=\"form-control form-control-sm\" maxlength=\"9\" oninput=\"buscarItem(this)\" value=\"$codigoInventario\" required>
                        <button class=\"btn btn-outline-secondary\" type=\"button\" id=\"btninventario[]\" onclick=\"seleccionarInventario(this)\"><i class=\"bi bi-search\"></i></button>
                        <input type=\"hidden\" id=\"inventarioid[]\" name=\"inventarioid[]\" value=\"$inventarioId\">
                    </div>                    
                </td>
                <td>
                    <input type=\"text\" id=\"categoria[]\" name=\"categoria[]\" class=\"form-control form-control-sm form-control-readonly\" onfocus\"readonlyPre(event)\" oninput=\"readonly(event)\" value=\"$categoria\" readonly required>
                </td>
                <td>
                    <input type=\"text\" id=\"marca[]\" name=\"marca[]\" class=\"form-control form-control-sm form-control-readonly\" value=\"$marca\" onfocus=\"readonlyPre(event)\" oninput=\"readonly(event)\" readonly required>
                </td>
                <td>
                    <input type=\"text\" id=\"modelo[]\" name=\"modelo[]\" class=\"form-control form-control-sm form-control-readonly\" onfocus=\"readonlyPre(event)\" oninput=\"readonly(event)\" value=\"$modelo\" readonly required>
                </td>
                <td>
                    <input type=\"text\" id=\"descripcion[]\" name=\"descripcion[]\" class=\"form-control form-control-sm form-control-readonly\" value=\"$descripcion\" onfocus=\"readonlyPre(event)\" oninput=\"readonly(event)\" readonly required>
                </td>
                <td>
                    <input type=\"text\" id=\"msrp[]\" name=\"msrp[]\" class=\"form-control form-control-sm form-control-readonly\" value=\"$msrp\" onfocus=\"readonlyPre(event)\" oninput=\"readonly(event)\" readonly required>
                </td>
                <td>
                    <input type=\"text\" id=\"stocktype[]\" name=\"stocktype[]\" class=\"form-control form-control-sm form-control-readonly\" value=\"$stockdist\" onfocus=\"readonlyPre(event)\" oninput=\"readonly(event)\" readonly required>
                </td>
                <td>
                    <input type=\"hidden\" id=\"detalleid[]\" name=\"detalleid[]\" value=\"$trasladoDetalleID\">
                    <button class=\"btn btn-sm btn-outline-danger\" type=\"button\" onclick=\"eliminarFila(this)\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button>
                </td>
            </tr>
        ";
    }

    return $filas;
}

//-----------------------------------------------