<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("FAC", "03.04.08");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Para crear el combo de Sucursales
        require_once("inc/class/Sucursales.php");
        $objSucursales = new Sucursales($conn);
        $listaDeSucursales = $objSucursales->getListaParaComboDeUsuario($_SESSION["usuarioId"], "SELECT A STORE");
        $listaDeSucursalesOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"];

            $valor = $valor == -1 ? "" : $valor;

            $listaDeSucursalesOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }
?>

<h3>Report: Sales by supplier</h3>

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
    </div>
    <div class="row mt-3">
        <div class="col-auto">
            <div class="input-group input-group-sm">
                <span class="input-group-text width-100px">Supplier</span>
                <input type="text" id="proveedor" name="proveedor" class="form-control form-control-sm" readonly>
                <input type="hidden" id="proveedorid" name="proveedorid" class="form-control form-control-sm">
                <button class="btn btn-outline-secondary" type="button" id="btnproveedor"><i class="bi bi-search"></i></button>
            </div>
        </div>
        <div class="col-auto">
            <button type="button" id="btntodos" name="btntodos" class="btn btn-sm btn-primary">All suppliers</button>
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

<div class="toast-container p-5 position-fixed top-0 start-50 translate-middle-x" id="toastPlacement">
    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastMensaje">
        <div class="d-flex">
            <div class="toast-body" id="mensajetoast">
                <!-- mensaje -->
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="modal fade small" id="modalSeleccionarProveedor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select supplier</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="proveedoresToolbar">
                    <span class="label-text">Search</span>
                    <div>
                        <div>
                            <!-- Filtros -->
                        </div>
                    </div>
                </div>
                <table
                        id="tableproveedores"
                        data-toggle="table"
                        data-url="./mods/facturacion/rptventasporproveedor/procs/getproveedores.php"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-search="true"
                        data-search-align="left"
                        data-show-refresh="true"
                        data-show-button-text="true"
                        data-toolbar=".proveedoresToolbar"
                        data-page-list="[10, 25, 50, 100]"
                        data-page-size="10"
                        data-query-params="proveedoresCustomParams"
                        data-icon-size="sm"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="PROVEEDORID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="NOMBRE">Full name</th>
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