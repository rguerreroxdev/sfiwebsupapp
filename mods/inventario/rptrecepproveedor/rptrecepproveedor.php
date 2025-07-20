<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.04.05");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Para crear el combo de Sucursales
        require_once("inc/class/Sucursales.php");
        $objSucursales = new Sucursales($conn);
        $listaDeSucursales = $objSucursales->getListaParaComboDeUsuario($_SESSION["usuarioId"], "All that I have access to");
        $listaDeSucursalesOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"];

            $listaDeSucursalesOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }
?>

<h3>Report: Purchasing by supplier</h3>

<div class="p-3 bg-body rounded shadow-sm">
    <div class="row">
        <div class="col-auto">
            <div class="input-group input-group-sm min-width-300px">
                <label class="input-group-text" for="sucursal">Store</label>
                <select class="form-select" id="sucursal" name="sucursal" required>
                    <!-- Sucursales -->
                    <?= $listaDeSucursalesOptions ?>
                </select>
            </div>
        </div>
        <div class="col-auto">
            <div class="input-group input-group-sm">
                <span class="input-group-text width-95px">Supplier</span>
                <input type="text" id="codigoproveedor" name="codigoproveedor" class="form-control form-control-sm" oninput="buscarProveedor(event)" style="max-width: 70px;" maxlength="4" value="">
                <input type="text" id="proveedor" name="proveedor" class="form-control form-control-sm" value="" placeholder="ALL" readonly>
                <button class="btn btn-outline-secondary" type="button" id="btnproveedor"><i class="bi bi-search"></i></button>
                <input type="hidden" id="proveedorid" name="proveedorid" value="">
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-auto">
            <div class="input-group input-group-sm width-250px">
                <label class="input-group-text" for="fechainicial">From</label>
                <input type="date" id="fechainicial" name="fechainicial" class="form-control form-control-sm">
            </div>
        </div>
        <div class="col-auto">
            <div class="input-group input-group-sm width-250px">
                <label class="input-group-text" for="fechafinal">To</label>
                <input type="date" id="fechafinal" name="fechafinal" class="form-control form-control-sm">
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-auto">
            <button type="button" id="btnpdf" name="btnpdf" class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-pdf"></i> Generate PDF report</button>
            <button type="button" id="btnexcel" name="btnexcel" class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-excel"></i> Generate Excel report</button>
        </div>
    </div>
</div>

<input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">

<div class="modal fade small" id="modalSeleccionarProveedor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select supplier</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="toolbarproveedor">
                    <span class="label-text">Search</span>
                </div>
                <table
                        id="tableproveedores"
                        data-toggle="table"
                        data-url="./mods/inventario/rptrecepproveedor/procs/getproveedores.php"
                        data-toolbar=".toolbarproveedor"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-search="true"
                        data-search-align="left"
                        data-show-refresh="true"
                        data-show-button-text="true"
                        data-page-list="[10, 25, 50, 100]"
                        data-page-size="10"
                        data-icon-size="sm"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="PROVEEDORID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowProveedorIndexFormatter">#</th>
                            <th data-field="CODIGO">Code</th>
                            <th data-field="NOMBRE">Name</th>
                            <th data-field="operate" data-formatter="proveedoresOperateFormatter" data-events="proveedoresOperateEvents">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>                
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