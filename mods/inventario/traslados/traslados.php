<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.02.02");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("02.02.02");
        $accesoCrear = in_array("02.02.02.01", $accesos) ? "" : " disabled";

        // Fecha por defecto para mostrar datos: 6 meses anterior a la actual
        $fechaDesde = new DateTime();
        $fechaDesde->sub(new DateInterval('P6M'));
        $fechaDesde->modify('first day of this month');
        $fechaDesdeString = $fechaDesde->format('Y-m-d');

        // Para crear combos de Sucursales
        require_once("inc/class/Sucursales.php");
        $objSucursales = new Sucursales($conn);
        $listaDeSucursales = $objSucursales->getListaParaCombo("ALL");
        $listaDeSucursalesOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"];
            
            $listaDeSucursalesOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Para crear el combo estados de traslados
        require_once("inc/class/Traslados.php");
        $objTraslados = new Traslados($conn);
        $listaDeEstados = $objTraslados->getListaDeEstadosParaCombo("ALL");
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

<h3>Inventory transfers</h3>

<div class="p-3 bg-body rounded shadow-sm">

<button class="btn btn-sm btn-success" id="btncrear"<?= $accesoCrear ?>><i class="bi bi-plus-circle"></i> Create new</button>


<div class="toolbar">
    <span class="label-text">Search</span>
    <div class="row">
        <div class="col-6">
            <div class="input-group input-group-sm">
                <label class="input-group-text width-130px" for="sucursalorigen">Store origin</label>
                <select class="form-select" id="sucursalorigen" name="sucursalorigen" required>
                    <!-- Sucursales -->
                    <?= $listaDeSucursalesOptions ?>
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="input-group input-group-sm">
                <label class="input-group-text width-130px" for="sucursaldestino">Store destination</label>
                <select class="form-select" id="sucursaldestino" name="sucursaldestino" required>
                    <!-- Sucursales -->
                    <?= $listaDeSucursalesOptions ?>
                </select>
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
        data-url="./mods/inventario/traslados/procs/gettraslados.php"
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
            <th data-field="TRASLADOID" data-visible="false">ID</th>
            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
            <th data-field="FECHACREACION">Document date</th>
            <th data-field="CORRELATIVO">Correlative</th>
            <th data-field="SUCURSALORIGEN">Origin store</th>
            <th data-field="SUCURSALDESTINO">Destination store</th>
            <th data-field="PIEZAS" data-align="right">Pcs.</th>
            <th data-field="NOMBREDEESTADO" data-formatter="estadoFormatter" data-align="center">Status</th>
            <th data-field="USUARIOCREO">Created by</th>
            <th data-field="operate" data-formatter="operateFormatter">Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Registros -->
    </tbody>
</table>

<input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">

</div>

<?php 
    } // else de mostrar contenido por acceso a opción
?>