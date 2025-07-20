<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);

    if (isset($_GET["rid"]))
    {
        // Se está editando
        $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.02.01.02");
    }
    else
    {
        // Se está creando
        $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.02.01.01");
    }

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        $recepcionId = isset($_GET["rid"]) ? $_GET["rid"] : -1;
        $recepcionId = is_numeric($recepcionId) ? $recepcionId : -1;

        require_once("inc/class/RecepcionesDeCarga.php");
        $objRecepcion = new RecepcionesDeCarga($conn);
        $objRecepcion->getById($recepcionId);

        // Antes de continuar, se verifica que un documento existente solo se puede editar en estado formulado
        if ($objRecepcion->recepcionDeCargaId != -1 && $objRecepcion->estado != "FOR")
        {
            echo('<script>window.location.href="?mod=inventario&opc=recepcionesdecarga&subopc=verrecepcion&rid=' . $objRecepcion->recepcionDeCargaId . '";</script>');
            exit();
        }
        
        $filasDetalle = "";
        if ($objRecepcion->recepcionDeCargaId == -1)
        {
            $objRecepcion->iniciarDatosParaNuevoRegistro();
        }
        else
        {
            require_once("mods/inventario/recepcionesdecarga/procs/getfilasdetalle.php");
            $filasDetalle = getFilasDetalle($conn, $objRecepcion->recepcionDeCargaId);
        }

        // Para crear el combo de Sucursales
        require_once("inc/class/Sucursales.php");
        $objSucursales = new Sucursales($conn);
        $listaDeSucursales = $objSucursales->getListaParaComboDeUsuario($_SESSION["usuarioId"]);
        $listaDeSucursalesOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"] == -1 ? "" : $sucursal["SUCURSALID"];
            $esCasaMatriz = $sucursal["ESCASAMATRIZ"];
            
            $selected = "";
            if ($objRecepcion->recepcionDeCargaId != -1)
            {
                $selected = $objRecepcion->sucursalId == $valor ? " selected" : "";
            }
            else
            {
                $selected = $esCasaMatriz == 1 ? " selected" : "";
            }

            $listaDeSucursalesOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        // Para crear el combo de Stock Type de origen y distribucióon
        require_once("inc/class/TiposDeStock.php");
        $objTiposDeStock = new TiposDeStock($conn);

        $proveedorIdParaCombo = $objRecepcion->proveedorId == null ? "-1" : $objRecepcion->proveedorId;
        $textoParaCombo = $proveedorIdParaCombo != -1 ? "SELECT" : "SELECT PROVIDER";
        $listaDeTiposDeStock = $objTiposDeStock->getListaParaCombo($proveedorIdParaCombo, $textoParaCombo);

        $listaDeTiposDeStockOrigenOptions = "";
        foreach ($listaDeTiposDeStock as $tipoDeStock)
        {
            $texto = $tipoDeStock["NOMBRECORTO"];
            $valor = $tipoDeStock["TIPODESTOCKID"] == -1 ? "" : $tipoDeStock["TIPODESTOCKID"];
            
            $selected = $objRecepcion->tipoDeStockOrigenId == $valor ? " selected" : "";

            $listaDeTiposDeStockOrigenOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        $listaDeTiposDeStockDistOptions = "";
        foreach ($listaDeTiposDeStock as $tipoDeStock)
        {
            $texto = $tipoDeStock["NOMBRECORTO"];
            $valor = $tipoDeStock["TIPODESTOCKID"] == -1 ? "" : $tipoDeStock["TIPODESTOCKID"];
            
            $selected = $objRecepcion->tipoDeStockDistId == $valor ? " selected" : "";

            $listaDeTiposDeStockDistOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        // Para crear el combo de tipos de garantía
        require_once("inc/class/TiposDeGarantia.php");
        $objTiposDeGarantia = new TiposDeGarantias($conn);

        $listaDeTiposDeGarantia = $objTiposDeGarantia->getListaParaCombo();

        $listaDeTiposDeGarantiaOptions = "";
        foreach ($listaDeTiposDeGarantia as $tipoDeGarantia)
        {
            $texto = $tipoDeGarantia["NOMBRE"];
            $valor = $tipoDeGarantia["TIPODEGARANTIAID"] == -1 ? "" : $tipoDeGarantia["TIPODEGARANTIAID"];
            
            $selected = $objRecepcion->tipoDeGarantiaId == $valor ? " selected" : "";

            $listaDeTiposDeGarantiaOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        // Para crear el combo de Categorías
        require_once("inc/class/Categorias.php");
        $objCategoria = new Categorias($conn);
        $listaDeCategorias = $objCategoria->getListaParaCombo("ALL");
        $listaDeCategoriasOptions = "";
        foreach ($listaDeCategorias as $categoria)
        {
            $texto = $categoria["NOMBRE"];
            $valor = $categoria["CATEGORIAID"] == -1 ? "" : $categoria["CATEGORIAID"];
            $listaDeCategoriasOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Para crear el combo de Marcas
        require_once("inc/class/Marcas.php");
        $objMarca = new Marcas($conn);
        $listaDeMarcas = $objMarca->getListaParaCombo("ALL");
        $listaDeMarcasOptions = "";
        foreach ($listaDeMarcas as $marca)
        {
            $texto = $marca["NOMBRE"];
            $valor = $marca["MARCAID"] == -1 ? "" : $marca["MARCAID"];
            $listaDeMarcasOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }
?>

<h3>Bills of lading - Document registration</h3>

<nav>
    <div class="nav nav-tabs small" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-editar-tab" data-bs-toggle="tab" data-bs-target="#nav-editar" type="button" role="tab" aria-controls="nav-editar" aria-selected="true">Document data</button>
        <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Status history</button>
    </div>
</nav>

<form id="frm">
<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-editar" role="tabpanel" aria-labelledby="nav-editar-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-95px" for="sucursal">Store<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="sucursal" name="sucursal" required>
                            <!-- Sucursales -->
                            <?= $listaDeSucursalesOptions ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Correlative</span>
                        <input type="text" id="correlativo" name="correlativo" class="form-control form-control-sm text-center" value="<?= $objRecepcion->correlativo ?>" readonly>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Status</span>
                        <input type="text" id="nombredeestado" name="nombredeestado" class="form-control form-control-sm text-center" value="<?= $objRecepcion->nombreDeEstado ?>" readonly>
                        <input type="hidden" id="estado" name="estado" value="<?= $objRecepcion->estado ?>">
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-8 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Supplier<span class="text-danger">&nbsp;*</span></span>            
                        <input type="text" id="codigoproveedor" name="codigoproveedor" class="form-control form-control-sm" onfocus="readonlyPre(event)" oninput="buscarProveedor(event)" style="max-width: 70px;" maxlength="4" value="<?= $objRecepcion->codigoProveedor ?>" required>
                        <input type="text" id="proveedor" name="proveedor" class="form-control form-control-sm" value="<?= $objRecepcion->proveedor ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="btnproveedor"><i class="bi bi-search"></i></button>
                        <input type="hidden" id="proveedorid" name="proveedorid" value="<?= $objRecepcion->proveedorId ?>">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Load ID<span class="text-danger">&nbsp;*</span></span>
                        <input type="text" id="numerodedocumento" name="numerodedocumento" class="form-control form-control-sm" maxlength="100" value="<?= $objRecepcion->numeroDeDocumento ?>" required>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Document date<span class="text-danger">&nbsp;*</span></span>
                        <input type="date" id="fechadeemision" name="fechadeemision" class="form-control form-control-sm" value="<?= $objRecepcion->fechaDeEmision->format("Y-m-d") ?>" required>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Reception date<span class="text-danger">&nbsp;*</span></span>
                        <input type="date" id="fechaderecepcion" name="fechaderecepcion" class="form-control form-control-sm" value="<?= $objRecepcion->fechaDeRecepcion->format("Y-m-d") ?>" required>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="tipodestockorigen">Stock type origin<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="tipodestockorigen" name="tipodestockorigen" required>
                            <!-- Tipos de stock -->
                            <?= $listaDeTiposDeStockOrigenOptions ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">% origin</span>
                        <input type="text" id="porcentajeorigen" name="porcentajeorigen" class="form-control form-control-sm text-end" value="<?= number_format($objRecepcion->porcentajeTipoDeStockOrigen, 2) ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="tipodestockdist">Stock type distr.<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="tipodestockdist" name="tipodestockdist" required>
                            <!-- Tipos de stock -->
                            <?= $listaDeTiposDeStockDistOptions ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">% distr.</span>
                        <input type="text" id="porcentajedist" name="porcentajedist" class="form-control form-control-sm text-end" value="<?= number_format($objRecepcion->porcentajeTipoDeStockDist, 2) ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="tipodestockdist">Warranty <span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="tipodegarantia" name="tipodegarantia" required>
                            <!-- Tipos de garantía -->
                            <?= $listaDeTiposDeGarantiaOptions ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-12 justify-content-end">
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary" id="btnguardar" style="min-width: 75px;">
                                <i class="bi bi-floppy2"></i> Save
                                <span class="spinner-border spinner-border-sm visually-hidden" id="btnguardarspinner" role="status" aria-hidden="true"></span>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" id="btncancelar" style="min-width: 75px;"><i class="bi bi-x-octagon"></i> Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 mt-2 bg-body rounded shadow-sm">
            <table id="tablaDetalle">
                <thead>
                    <tr class="small border-bottom">
                        <th class="width-80px">Quantity</th>
                        <th class="width-100px">Product</th>
                        <th class="width-130px">Category</th>
                        <th class="width-130px">Brand</th>
                        <th class="width-130px">Model</th>
                        <th class="width-300px">Description</th>
                        <th class="width-130px">Stock type o.</th>
                        <th class="width-90px">% origin</th>
                        <th class="width-130px">Stock type d.</th>
                        <th class="width-90px">% distr.</th>
                        <th class="width-90px">MSRP ($)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Filas de ítems -->
                    <?= $filasDetalle ?>
                </tbody>
            </table>
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-success" type="button" id="btnagregarfila" onclick="agregarFila()"><i class="bi bi-plus-circle"></i> Add item</button>
            </div>

            <div class="mt-2">Total items: <span id="totalitems"></span></div>
        </div>

        <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">

        <div class="row mt-2 justify-content-between">
            <div class="col">
                <span class="fst-italic small"><span class="text-danger">*</span> -> Required data</span>
            </div>
        </div>

        <div class="mt-4"></div>

        <input type="hidden" id="rid" name="rid" value="<?= $objRecepcion->recepcionDeCargaId ?>">
    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation date</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objRecepcion->fechaCreacion == null ? date("m/d/Y") : $objRecepcion->fechaCreacion->format("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objRecepcion->usuarioCreo == null ? $_SESSION["usuario"] : $objRecepcion->usuarioCreo ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objRecepcion->fechaModificacion == null ? date("m/d/Y") : $objRecepcion->fechaModificacion->format("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objRecepcion->usuarioModifica == null ? $_SESSION["usuario"] : $objRecepcion->usuarioModifica ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 mt-2 bg-body rounded shadow-sm">
            <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/inventario/recepcionesdecarga/procs/getrecepcionestados.php?rid=<?= $objRecepcion->recepcionDeCargaId ?>"
                    class="table-sm small"
            >
                <thead>
                    <tr>
                        <th data-field="RECEPCIONDECARGAESTADOID" data-visible="false">ID</th>
                        <th data-field="RECEPCIONDECARGAID" data-visible="false">RID</th>
                        <th data-field="FECHA">Date</th>
                        <th data-field="USUARIO">User</th>
                        <th data-field="NOMBREDEESTADO">Status</th>
                        <th data-field="DESCRIPCION">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Registros -->
                </tbody>
            </table>
        </div>
    </div>
</div>
</form>



<div class="toast-container p-5 position-absolute top-0 start-50 translate-middle-x" id="toastPlacement">
    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastMensaje">
        <div class="d-flex">
            <div class="toast-body">
                The data was saved.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<div class="toast-container p-5 position-absolute top-0 start-50 translate-middle-x" id="toastPlacement">
    <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastError">
        <div class="d-flex">
            <div class="toast-body">
                <span id="textodeerror"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="modal fade small" id="modalMensaje" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Error when saving</h1>
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
                        data-url="./mods/inventario/recepcionesdecarga/procs/getproveedores.php"
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

<div class="modal fade small" id="modalSeleccionarProducto" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select product</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="productosToolbar">
                    <span class="label-text">Search</span>
                    <div>
                        <div>
                            <div class="input-group input-group-sm min-width-300px">
                                <label class="input-group-text width-95px" for="categoria">Category</label>
                                <select class="form-select" id="categoria" name="categoria">
                                    <!-- Categorías -->
                                    <?= $listaDeCategoriasOptions ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <div class="input-group input-group-sm min-width-300px">
                                <label class="input-group-text width-95px" for="marca">Brand</label>
                                <select class="form-select" id="marca" name="marca">
                                    <!-- Marcas -->
                                    <?= $listaDeMarcasOptions ?>                    
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <table
                        id="tableproductos"
                        data-toggle="table"
                        data-url="./mods/inventario/recepcionesdecarga/procs/getproductos.php"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-search="true"
                        data-show-refresh="true"
                        data-show-button-text="true"
                        data-toolbar=".productosToolbar"
                        data-page-list="[10, 25, 50, 100]"
                        data-page-size="10"
                        data-query-params="productosCustomParams"
                        data-icon-size="sm"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="PRODUCTOID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowProductoIndexFormatter">#</th>
                            <th data-field="CODIGO">Code</th>
                            <th data-field="CATEGORIA">Category</th>
                            <th data-field="MARCA">Brand</th>
                            <th data-field="MODELO">Model</th>
                            <th data-field="DESCRIPCION">Description</th>
                            <th data-field="operate" data-formatter="productosOperateFormatter" data-events="productosOperateEvents">Actions</th>
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

<div class="modal fade small" id="modalSeleccionarTipoDeStockDetalle" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select Stock type</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="tiposdestockdetalletoolbar">
                <span class="label-text">Search</span>
                <span id="proveedormodaltiposdestock"></span>
            </div>            
            <div class="modal-body">
                <table
                        id="tabletipodestockdetalle"
                        data-toggle="table"
                        data-url="./mods/inventario/recepcionesdecarga/procs/gettiposdestock.php"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-toolbar=".tiposdestockdetalletoolbar"
                        data-search="true"
                        data-show-refresh="true"
                        data-show-button-text="true"
                        data-page-list="[10, 25, 50, 100]"
                        data-page-size="10"
                        data-query-params="tiposDeStockDetalleCustomParams"
                        data-icon-size="sm"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="TIPODESTOCKID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowTipoStockIndexFormatter">#</th>
                            <th data-field="NOMBRECORTO">Short name</th>
                            <th data-field="PORCENTAJE">Percentage</th>
                            <th data-field="operate" data-formatter="tiposDeStockDetalleOperateFormatter" data-events="tiposDeStockDetalleOperateEvents">Actions</th>
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