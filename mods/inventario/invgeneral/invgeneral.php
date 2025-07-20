<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.03.01");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("02.03.01");
        $accesoEditarSerie = in_array("02.03.01.01", $accesos);
        $accesoVerHistorial = in_array("02.03.01.02", $accesos);
        $accesoVerCostoYProveedor = in_array("02.03.01.03", $accesos);

        // Para crear el combo de Sucursales
        require_once("inc/class/Sucursales.php");
        $objSucursales = new Sucursales($conn);
        $listaDeSucursales = $objSucursales->getListaParaComboDeUsuario($_SESSION["usuarioId"], "All that I have access to");
        $listaDeSucursalesOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"];

            $selected = "";
            if ($sucursal["ESCASAMATRIZ"] == 1)
            {
                $selected = " selected";
            }

            $listaDeSucursalesOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        // Para crear el combo de Categorías
        require_once("inc/class/Categorias.php");
        $objCategorias = new Categorias($conn);
        $listaDeCategorias = $objCategorias->getListaParaCombo("ALL");
        $listaDeCategoriasOptions = "";
        foreach ($listaDeCategorias as $categoria)
        {
            $texto = $categoria["NOMBRE"];
            $valor = $categoria["CATEGORIAID"] == -1 ? "" : $categoria["CATEGORIAID"];
            $listaDeCategoriasOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Para crear el combo de Colores
        require_once("inc/class/Colores.php");
        $objColores = new Colores($conn);
        $listaDeColores = $objColores->getListaParaCombo("ALL");
        $listaDeColoresOptions = "";
        foreach ($listaDeColores as $color)
        {
            $texto = $color["NOMBRE"];
            $valor = $color["COLORID"] == -1 ? "" : $color["COLORID"];
            $listaDeColoresOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Para crear el combo de Stock Types
        require_once("inc/class/TiposDeStock.php");
        $objStockTypes = new TiposDeStock($conn);
        $listaDeStockTypes = $objStockTypes->getListaSinFiltroParaCombo("ALL");
        $listaDeStockTypesOptions = "";
        foreach ($listaDeStockTypes as $stockType)
        {
            $texto = $stockType["NOMBRECORTO"];
            $valor = $stockType["TIPODESTOCKID"] == -1 ? "" : $stockType["TIPODESTOCKID"];
            $listaDeStockTypesOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Fecha por defecto para mostrar datos: 6 meses anterior a la actual
        $fechaDesde = new DateTime();
        $fechaDesde->sub(new DateInterval('P6M'));
        $fechaDesde->modify('first day of this month');
        $fechaDesdeString = $fechaDesde->format('Y-m-d');
?>

<h3>General inventory</h3>

<div class="p-3 bg-body rounded shadow-sm">

<div class="toolbar">
    <span class="label-text">Search</span>
    <div class="row">
        <div class="col-md-5 col-lg-5">
            <div class="input-group input-group-sm min-width-300px">
                <label class="input-group-text" for="sucursal">Store</label>
                <select class="form-select" id="sucursal" name="sucursal" required>
                    <!-- Sucursales -->
                    <?= $listaDeSucursalesOptions ?>
                </select>
            </div>
        </div>
        <div class="col">
            <div class="input-group input-group-sm width-250px">
                <label class="input-group-text" for="categoria">Category</label>
                <select class="form-select" id="categoria" name="categoria" required>
                    <!-- Sucursales -->
                    <?= $listaDeCategoriasOptions ?>
                </select>
            </div>
        </div>
        <div class="col">
            <div class="input-group input-group-sm">
                <span class="input-group-text">Only in stock</span>
                <div class="input-group-text">
                    <input type="checkbox" id="solostock" name="solostock" class="form-check-input mt-0" value="" checked>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-1">
        <div class="col-md-5 col-lg-5">
            <div class="input-group input-group-sm min-width-300px">
                <label class="input-group-text" for="color">Color</label>
                <select class="form-select" id="color" name="color" required>
                    <!-- Colores -->
                    <?= $listaDeColoresOptions ?>
                </select>
            </div>
        </div>
        <div class="col-md-5 col-lg-4">
            <div class="input-group input-group-sm width-250px">
                <label class="input-group-text" for="stocktype">Stock type</label>
                <select class="form-select" id="stocktype" name="stocktype" required>
                    <!-- Stock type -->
                    <?= $listaDeStockTypesOptions ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row mt-1">
        <div class="col-md-5 col-lg-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text width-160px">Bill of lading number</span>            
                <input type="text" id="numerorecepcion" name="numerorecepcion" class="form-control form-control-sm" style="max-width: 70px;" maxlength="5">
                <button class="btn btn-outline-secondary" type="button" id="btnrecepcion"><i class="bi bi-search"></i></button>
            </div>
        </div>
        <div class="col-auto mt-1">
            <div>
                <button id="btnresetinvgeneral" class="btn btn-sm btn-secondary"><i class="bi bi-eraser"></i> Reset</button>
            </div>
        </div>
    </div>
</div>

<table
        id="tabledatos"
        data-toggle="table"
        data-url="./mods/inventario/invgeneral/procs/getinvgeneral.php"
        data-unique-id="INVENTARIOID"
        data-side-pagination="server"
        data-pagination="true"
        data-search="true"
        data-show-refresh="true"
        data-show-button-text="true"
        data-toolbar=".toolbar"
        data-page-list="[25, 50, 100, 1000]"
        data-page-size="25"
        data-query-params="customParams"
        data-icon-size="sm"
        class="table-sm small"
>
    <thead>
        <tr>
            <th data-field="INVENTARIOID" data-visible="false">ID</th>
            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
            <th data-field="SUCURSAL">Store</th>
            <th data-field="CODIGOINVENTARIO">Code</th>
            <th data-field="CATEGORIA">Category</th>
            <th data-field="MARCA">Brand</th>
            <th data-field="MODELO">Model</th>
            <th data-field="COLOR">Color</th>
            <th data-field="DESCRIPCION">Description</th>
            <th data-field="MSRP" data-width="80" data-align="right" data-formatter="msrpFormatter">MSRP</th>
            <th data-field="RECEPCIONCORRELATIVO">B.O.L.</th>
            <th data-field="TIPODESTOCKDIST" data-width="80">Stock type distr.</th>
            <th data-field="EXISTENCIA">Stock</th>
            <th data-field="ENTRANSITO">In transit</th>
            <?php if ($accesoEditarSerie): ?>
            <th data-field="editarserie" data-formatter="editarSerieFormatter">Edit</th>
            <?php endif; ?>
            <?php if ($accesoVerHistorial): ?>
            <th data-field="verhistorial" data-formatter="verHistorialFormatter">History</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <!-- Registros -->
    </tbody>
</table>

</div>

<div class="modal fade small" id="modalEditarSerie" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select Stock type</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
                <input type="hidden" id="itemid" name="itemid" value="">
                <div class="input-group input-group-sm">
                    <span class="input-group-text width-130px">Code</span>
                    <input type="text" id="itemcodigo" name="itemcodigo" class="form-control form-control-sm" value="" readonly>
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text width-130px">Category</span>
                    <input type="text" id="itemcategoria" name="itemcategoria" class="form-control form-control-sm" value="" readonly>
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text width-130px">Brand</span>
                    <input type="text" id="itemmarca" name="itemmarca" class="form-control form-control-sm" value="" readonly>
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text width-130px">Model</span>
                    <input type="text" id="itemmodelo" name="itemmodelo" class="form-control form-control-sm" value="" readonly>
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text width-130px">Serial number</span>
                    <input type="text" id="itemserie" name="itemserie" class="form-control form-control-sm" maxlength="50" value="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-primary" id="btnguardar" style="min-width: 75px;">
                    <i class="bi bi-floppy2"></i> Save
                    <span class="spinner-border spinner-border-sm visually-hidden" id="btnguardarspinner" role="status" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-octagon"></i> Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container p-5 position-fixed top-0 start-50 translate-middle-x" id="toastPlacement">
    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastMensaje">
        <div class="d-flex">
            <div class="toast-body">
                The serial number was updated.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<div class="toast-container p-5 position-fixed top-0 start-50 translate-middle-x" id="toastPlacement">
    <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastError">
        <div class="d-flex">
            <div class="toast-body">
                <span id="textodeerror"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="modal fade small" id="modalHistorial" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Item history</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        Inventory code: <span id="detalleInvCodigo"></span>
                    </div>
                    <div class="col">
                        Category: <span id="detalleCategoria"></span>
                    </div>
                    <div class="col">
                    </div>
                </div>
                <?php if ($accesoVerCostoYProveedor): ?>
                <div class="row">
                    <div class="col">
                        Supplier: <span id="detalleProveedor"></span>
                    </div>
                </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col">
                        Brand: <span id="detalleBrand"></span>
                    </div>
                    <div class="col">
                        Model: <span id="detalleModelo"></span>
                    </div>
                    <div class="col">
                        Serial number: <span id="detalleSerie"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        MSRP: $<span id="detalleMsrp"></span>
                    </div>
                </div>
                <?php if ($accesoVerCostoYProveedor): ?>
                <div class="row">
                    <div class="col">
                        Stock type origin: <span id="detalleStockOrigen"></span>
                    </div>
                    <div class="col">
                        % origin: <span id="detallePorcentajeOrigen"></span>
                    </div>
                    <div class="col">
                        Cost origin: <span id="detalleCostoOrigin"></span>
                    </div>
                </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col">
                        Stock type distr.: <span id="detalleStockDist"></span>
                    </div>
                    <div class="col">
                        % distr.: <span id="detallePorcentajeDist"></span>
                    </div>
                    <div class="col">
                        Cost distr.: <span id="detalleCostoDist"></span>
                    </div>
                </div>

                <table
                        id="tablehistorial"
                        data-toggle="table"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="FECHA">Date</th>
                            <th data-field="SUCURSAL">Store</th>
                            <th data-field="MOVIMIENTO">Movement type</th>
                            <th data-field="CORRELATIVO">Document number</th>
                            <th data-field="ENTRADA">Inbound</th>
                            <th data-field="SALIDA">Outbound</th>
                            <th data-field="EXISTENCIA">Stock</th>
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

<div class="modal fade small" id="modalBuscarRecepcion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Bill of lading search</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="toolbar2">
                    <span class="label-text">Search</span>
                    <div class="row">
                        <div class="col-auto mt-1">
                            <div class="input-group input-group-sm" style="width: 220px;">
                                <span class="input-group-text width-90px">Correlative</span>
                                <input type="text" id="correlativo" name="correlativo" class="form-control form-control-sm" maxlength="7">
                            </div>
                        </div>
                        <div class="col-auto mt-1">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text width-95px">Supplier</span>
                                <input type="text" id="codigoproveedor" name="codigoproveedor" class="form-control form-control-sm" oninput="buscarProveedor(event)" style="max-width: 70px;" maxlength="4" value="">
                                <input type="text" id="proveedor" name="proveedor" class="form-control form-control-sm" value="" placeholder="ALL" readonly>
                                <button class="btn btn-outline-secondary" type="button" id="btnproveedor"><i class="bi bi-search"></i></button>
                                <input type="hidden" id="proveedorid" name="proveedorid" value="">
                            </div>
                        </div>
                        <div class="col-auto mt-1">
                            <div class="input-group input-group-sm" style="width: 220px;">
                                <span class="input-group-text width-90px">Load ID</span>
                                <input type="text" id="loadid" name="loadid" class="form-control form-control-sm" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-auto mt-1">
                            <div class="input-group input-group-sm" style="width: 220px;">
                                <span class="input-group-text width-90px">Date from</span>
                                <input type="date" id="fechadesde" name="fechadesde" class="form-control form-control-sm" value="<?= $fechaDesdeString ?>">
                            </div>
                        </div>
                        <div class="col-auto mt-1">
                            <div class="input-group input-group-sm min-width-300px">
                                <label class="input-group-text width-90px" for="sucursal">Store</label>
                                <select class="form-select" id="recepcionsucursal" name="recepcionsucursal" required>
                                    <!-- Sucursales -->
                                    <?= $listaDeSucursalesOptions ?>
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
                        id="tablerecepciones"
                        data-toggle="table"
                        data-toolbar=".toolbar2"
                        data-url="./mods/inventario/invgeneral/procs/getrecepciones.php"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-page-list="[10, 25, 50, 100]"
                        data-page-size="10"
                        data-query-params="recepcionesCustomParams"
                        data-icon-size="sm"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="RECEPCIONDECARGAID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="FECHADEEMISION">Document date</th>
                            <th data-field="FECHADERECEPCION">Reception date</th>
                            <th data-field="SUCURSAL">Store</th>
                            <th data-field="CORRELATIVO">Correlative</th>
                            <th data-field="PROVEEDOR">Supplier</th>
                            <th data-field="NUMERODEDOCUMENTO">Load ID</th>
                            <th data-field="PIEZAS" data-align="right">Pcs.</th>
                            <th data-field="USUARIOCREO">Created by</th>
                            <th data-field="operate" data-formatter="recepcionesOperateFormatter" data-events="recepcionesOperateEvents">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-octagon"></i> Close</button>
            </div>
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
                <div class="toolbarproveedor">
                    <span class="label-text">Search</span>
                </div>
                <table
                        id="tableproveedores"
                        data-toggle="table"
                        data-url="./mods/inventario/invgeneral/procs/getproveedores.php"
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
                            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
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