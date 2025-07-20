<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("FAC", "03.03.01");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("03.03.01");
        $accesoCrear = in_array("03.03.01.01", $accesos) ? "" : " disabled";

        // Fecha por defecto para mostrar datos: 3 meses anterior a la actual
        $fechaDesde = new DateTime();
        $fechaDesde->sub(new DateInterval('P3M'));
        $fechaDesde->modify('first day of this month');
        $fechaDesdeString = $fechaDesde->format('Y-m-d');

        // Para crear combos de Sucursales
        $sucursalDeTrabajo = isset($_SESSION["sucursalDeTrabajo"]) ? $_SESSION["sucursalDeTrabajo"] : -1;

        require_once("inc/class/Sucursales.php");
        $objSucursales = new Sucursales($conn);
        $listaDeSucursales = $objSucursales->getListaParaComboDeUsuario($_SESSION["usuarioId"], "SELECT");
        $listaDeSucursalesOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"];

            $selected = $sucursalDeTrabajo == $valor ? " selected" : "";
            
            $listaDeSucursalesOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        // Para crear el combo estados de facturas
        require_once("inc/class/Facturas.php");
        $objFacturas = new Facturas($conn);
        $listaDeEstados = $objFacturas->getListaDeEstadosParaCombo("ALL");
        $listaDeEstadosOptions = "";
        foreach ($listaDeEstados as $estado)
        {
            $texto = $estado["NOMBRE"];
            $valor = $estado["ESTADO"];
            $listaDeEstadosOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }
?>

<h3>Invoices</h3>

<div class="p-3 bg-body rounded shadow-sm">

<button class="btn btn-sm btn-success" id="btncrear"<?= $accesoCrear ?>><i class="bi bi-plus-circle"></i> Create new</button>


<div class="toolbar">
    <span class="label-text">Search</span>
    <div class="row">
        <div class="col-6">
            <div class="input-group input-group-sm">
                <label class="input-group-text width-90px" for="sucursal">Store</label>
                <select class="form-select" id="sucursal" name="sucursal" required>
                    <!-- Sucursales -->
                    <?= $listaDeSucursalesOptions ?>
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text width-90px">Customer</span>
                <input type="text" id="cliente" name="cliente" class="form-control form-control-sm" maxlength="100">
            </div>
        </div>
    </div>
    <div class="row mt-1">
        <div class="col-auto mt-1">
            <div class="input-group input-group-sm" style="width: 180px;">
                <span class="input-group-text width-90px">Correlative</span>
                <input type="text" id="correlativo" name="correlativo" class="form-control form-control-sm" maxlength="7">
            </div>
        </div>
        <div class="col-auto mt-1">
            <div class="input-group input-group-sm" style="width: 210px;">
                <span class="input-group-text width-90px">Date from</span>
                <input type="date" id="fechadesde" name="fechadesde" class="form-control form-control-sm" value="<?= $fechaDesdeString ?>">
            </div>
        </div>
        <div class="col-auto mt-1">
            <div class="input-group input-group-sm" style="width: 320px;">
                <label class="input-group-text width-90px" for="estado">Status</label>
                <select class="form-select" id="estado" name="estado">
                    <!-- Estados -->
                    <?= $listaDeEstadosOptions ?>
                </select>
            </div>
        </div>
        <div class="col-auto mt-1">
            <div>
                <button id="btnreset" class="btn btn-sm btn-secondary"><i class="bi bi-eraser"></i> Reset</button>
            </div>
        </div>
    </div>
</div>

<table
        id="tabledatos"
        data-toggle="table"
        data-toolbar=".toolbar"
        data-url="./mods/facturacion/facturacion/procs/getfacturas.php"
        data-side-pagination="server"
        data-pagination="true"
        data-page-list="[25, 50, 100]"
        data-page-size="25"
        data-query-params="customParams"
        data-icon-size="sm"
        class="table-sm small"
>
    <thead>
        <tr>
            <th data-field="FACTURAID" data-visible="false">ID</th>
            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
            <th data-field="FECHA">Document date</th>
            <th data-field="NUMEROFACTURA">Correlative</th>
            <th data-field="CLIENTE">Customer</th>
            <th data-field="TOTALFINAL" data-width="80" data-align="right" data-formatter="totalFormatter">Total</th>
            <th data-field="VENDEDOR">Sales person</th>
            <th data-field="NOMBREDEESTADO" data-formatter="estadoFormatter" data-align="center">Status</th>
            <th data-field="operate" data-formatter="operateFormatter">Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Registros -->
    </tbody>
</table>

<input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">

</div>

<div class="modal fade small" id="modalMensaje" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Error</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <span id="mensajedeerror"></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<?php 
    } // else de mostrar contenido por acceso a opción
?>