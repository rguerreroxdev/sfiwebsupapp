<?php
//-----------------------------------------------

function getFilasDetalle($conn, string $recepcionDeCargaId): string
{
    require_once("inc/class/RecepcionesDeCargaDetalle.php");
    $objRecepcionDetalles = new RecepcionesDeCargaDetalle($conn);

    $detalles = $objRecepcionDetalles->getAll($recepcionDeCargaId);

    $filas = "";
    foreach ($detalles as $detalle)
    {
        $recepcionDeCargaDetalleId = $detalle["RECEPCIONDECARGADETALLEID"];
        $cantidad = $detalle["CANTIDAD"];
        $codigoProducto = $detalle["CODIGOPRODUCTO"];
        $modelo = $detalle["PRODUCTO"];
        $categoria = $detalle["CATEGORIA"];
        $marca = $detalle["MARCA"];
        $descripcion = $detalle["DESCRIPCION"];
        $productoId = $detalle["PRODUCTOID"];
        $tipoDeStockOrigen = $detalle["TIPODESTOCKORIGEN"];
        $tipoDeStockOrigenId = $detalle["TIPODESTOCKORIGENID"];
        $porcentajeOrigen = $detalle["PORCENTAJETIPODESTOCKORIGEN"];
        $tipoDeStockDist = $detalle["TIPODESTOCKDIST"];
        $tipoDeStockDistId = $detalle["TIPODESTOCKDISTID"];
        $porcentajeDist = $detalle["PORCENTAJETIPODESTOCKDIST"];
        $msrp = $detalle["MSRP"];

        $filas .= "
            <tr>
                <td>
                    <input type=\"number\" class=\"form-control form-control-sm\" id=\"cantidad[]\" name=\"cantidad[]\" maxlength=\"4\" step=\"1\" min=\"1\" value=\"$cantidad\" oninput=\"mostrarCantidadItems(this)\" required>
                </td>
                <td>
                    <div class=\"input-group input-group-sm\">
                        <input type=\"text\" id=\"producto[]\" name=\"producto[]\" class=\"form-control form-control-sm\" maxlength=\"5\" onfocus=\"readonlyPre(event)\" oninput=\"buscarProducto(this)\" value=\"$codigoProducto\" required>
                        <button class=\"btn btn-outline-secondary\" type=\"button\" id=\"btnproducto[]\" onclick=\"seleccionarProducto(this)\"><i class=\"bi bi-search\"></i></button>
                        <input type=\"hidden\" id=\"productoid[]\" name=\"productoid[]\" value=\"$productoId\">
                    </div>
                </td>
                <td>
                    <input type=\"text\" id=\"categoria[]\" name=\"categoria[]\" class=\"form-control form-control-sm form-control-readonly width\" value=\"$categoria\" onfocus=\"readonlyPre(event)\" oninput=\"readonly(event)\" readonly required>
                </td>
                <td>
                    <input type=\"text\" id=\"marca[]\" name=\"marca[]\" class=\"form-control form-control-sm form-control-readonly\" value=\"$marca\" onfocus=\"readonlyPre(event)\" oninput=\"readonly(event)\" readonly required>
                </td>
                <td>
                    <input type=\"text\" id=\"modelo[]\" name=\"modelo[]\" class=\"form-control form-control-sm form-control-readonly\" value=\"$modelo\" onfocus=\"readonlyPre(event)\" oninput=\"readonly(event)\" readonly required>
                </td>
                <td>
                    <input type=\"text\" id=\"descripcion[]\" name=\"descripcion[]\" class=\"form-control form-control-sm form-control-readonly\" value=\"$descripcion\" onfocus=\"readonlyPre(event)\" oninput=\"readonly(event)\" readonly required>
                </td>
                <td>
                    <div class=\"input-group input-group-sm\">
                        <input type=\"text\" id=\"tipodestockorigendetalle[]\" name=\"tipodestockorigendetalle[]\" class=\"form-control form-control-sm form-control-readonly\" value=\"$tipoDeStockOrigen\" onfocus=\"readonlyPre(event)\" oninput=\"readonly(event)\" readonly required>
                        <button class=\"btn btn-outline-secondary\" type=\"button\" id=\"btntipodestockorigendetalle[]\" onclick=\"seleccionarTipoDeStockDetalle(this, 'O')\"><i class=\"bi bi-search\"></i></button>
                        <input type=\"hidden\" id=\"tipodestockorigendetalleid[]\" name=\"tipodestockorigendetalleid[]\" value=\"$tipoDeStockOrigenId\">
                    </div>
                </td>
                <td>
                    <input type=\"text\" class=\"form-control form-control-sm text-end\" id=\"porcentajeorigendetalle[]\" name=\"porcentajeorigendetalle[]\" step=\"0.01\" min=\"0.00\" value=\"$porcentajeOrigen\" readonly required>
                </td>
                <td>
                    <div class=\"input-group input-group-sm\">
                        <input type=\"text\" id=\"tipodestockdistdetalle[]\" name=\"tipodestockdistdetalle[]\" class=\"form-control form-control-sm form-control-readonly\" value=\"$tipoDeStockDist\" onfocus=\"readonlyPre(event)\" oninput=\"readonly(event)\" readonly required>
                        <button class=\"btn btn-outline-secondary\" type=\"button\" id=\"btntipodestockdistdetalle[]\" onclick=\"seleccionarTipoDeStockDetalle(this, 'D')\"><i class=\"bi bi-search\"></i></button>
                        <input type=\"hidden\" id=\"tipodestockdistdetalleid[]\" name=\"tipodestockdistdetalleid[]\" value=\"$tipoDeStockDistId\">
                    </div>
                </td>
                <td>
                    <input type=\"text\" class=\"form-control form-control-sm text-end\" id=\"porcentajedistdetalle[]\" name=\"porcentajedistdetalle[]\" step=\"0.01\" min=\"0.00\" value=\"$porcentajeDist\" readonly required>
                </td>
                <td>
                    <input type=\"text\" class=\"form-control form-control-sm text-end\" id=\"msrp[]\" name=\"msrp[]\" value=\"$msrp\" readonly required>
                </td>
                <td>
                    <input type=\"hidden\" id=\"detalleid[]\" name=\"detalleid[]\" value=\"$recepcionDeCargaDetalleId\">
                    <button class=\"btn btn-sm btn-outline-danger\" type=\"button\" onclick=\"eliminarFila(this)\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button>
                </td>
            </tr>
        ";
    }

    return $filas;
}

//-----------------------------------------------