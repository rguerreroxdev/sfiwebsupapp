<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);

    if (isset($_GET["tid"]))
    {
        // Se está editando
        $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.02.02.02");
    }
    else
    {
        // Se está creando
        $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.02.02.01");
    }

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        $trasladoId = isset($_GET["tid"]) ? $_GET["tid"] : -1;
        $trasladoId = is_numeric($trasladoId) ? $trasladoId : -1;

        require_once("inc/class/Traslados.php");
        $objTraslado = new Traslados($conn);
        $objTraslado->getById($trasladoId);

        // Antes de continuar, se verifica que un documento existente solo se puede editar en estado formulado
        if ($objTraslado->trasladoId != -1 && $objTraslado->estado != "FOR")
        {
            echo('<script>window.location.href="?mod=inventario&opc=traslados&subopc=vertraslado&tid=' . $objTraslado->trasladoId . '";</script>');
            exit();
        }
        
        $filasDetalle = "";
        if ($objTraslado->trasladoId == -1)
        {
            $objTraslado->iniciarDatosParaNuevoRegistro();
        }
        else
        {
            require_once("mods/inventario/traslados/procs/getfilasdetalle.php");
            $filasDetalle = getFilasDetalle($conn, $objTraslado->trasladoId);
        }

        // Para crear el combo de Sucursales de origen (a las que tiene acceso el usuario)
        require_once("inc/class/Sucursales.php");
        $objSucursales = new Sucursales($conn);
        $listaDeSucursales = $objSucursales->getListaParaComboDeUsuario($_SESSION["usuarioId"]);
        $listaDeSucursalesOrigenOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"] == -1 ? "" : $sucursal["SUCURSALID"];

            $selected = "";
            if ($objTraslado->sucursalOrigenId != -1)
            {
                $selected = $objTraslado->sucursalOrigenId == $valor ? " selected" : "";
            }
            
            $listaDeSucursalesOrigenOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        // Para crear el combo de Sucursales de destino (todas las sucursales)
        $listaDeSucursales = $objSucursales->getListaParaCombo();
        $listaDeSucursalesDestinoOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"] == -1 ? "" : $sucursal["SUCURSALID"];

            $selected = "";
            if ($objTraslado->sucursalDestinoId != -1)
            {
                $selected = $objTraslado->sucursalDestinoId == $valor ? " selected" : "";
            }
            
            $listaDeSucursalesDestinoOptions .= "
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
?>

<h3>Inventory transfers - Document registration</h3>

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
                        <span class="input-group-text width-130px">Creation date</span>
                        <input type="text" id="fechadecreacionver" name="fechadecreacionver" class="form-control form-control-sm text-center" value="<?= $objTraslado->fechaCreacion == null ? date("m/d/Y") : $objTraslado->fechaCreacion->format("m/d/Y") ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Correlative</span>
                        <input type="text" id="correlativo" name="correlativo" class="form-control form-control-sm text-center" value="<?= $objTraslado->correlativo ?>" readonly>
                    </div>
                </div>
                <div class="col-sm-1">
                    <!-- vacío -->
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Status</span>
                        <input type="text" id="nombredeestado" name="nombredeestado" class="form-control form-control-sm text-center" value="<?= $objTraslado->nombreDeEstado ?>" readonly>
                        <input type="hidden" id="estado" name="estado" value="<?= $objTraslado->estado ?>">
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="sucursalorigen">Origin store<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="sucursalorigen" name="sucursalorigen" required>
                            <!-- Sucursales -->
                            <?= $listaDeSucursalesOrigenOptions ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="sucursaldestino">Destination store<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="sucursaldestino" name="sucursaldestino" required>
                            <!-- Sucursales -->
                            <?= $listaDeSucursalesDestinoOptions ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12 col-lg-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Notes</span>
                        <textarea id="observaciones" name="observaciones" class="form-control form-control-sm" maxlength="260"><?= $objTraslado->observaciones ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col justify-content-end">
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
                        <th class="width-130px">Inventory item</th>
                        <th class="width-130px">Category</th>
                        <th class="width-130px">Brand</th>
                        <th class="width-130px">Model</th>
                        <th class="width-300px">Description</th>
                        <th class="width-130px">MSRP $</th>
                        <th class="width-130px">Stock type distr.</th>
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

        <input type="hidden" id="tid" name="tid" value="<?= $objTraslado->trasladoId ?>">
    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation date</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objTraslado->fechaCreacion == null ? date("m/d/Y") : $objTraslado->fechaCreacion->format("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objTraslado->usuarioCreo == null ? $_SESSION["usuario"] : $objTraslado->usuarioCreo ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objTraslado->fechaModificacion == null ? date("m/d/Y") : $objTraslado->fechaModificacion->format("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objTraslado->usuarioModifica == null ? $_SESSION["usuario"] : $objTraslado->usuarioModifica ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 mt-2 bg-body rounded shadow-sm">
            <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/inventario/traslados/procs/gettrasladoestados.php?tid=<?= $objTraslado->trasladoId ?>"
                    class="table-sm small"
            >
                <thead>
                    <tr>
                        <th data-field="TRASLADOESTADOID" data-visible="false">ID</th>
                        <th data-field="TRASLADOID" data-visible="false">TID</th>
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

<div class="modal fade small" id="modalSeleccionarInventario" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select inventory item - <span id="nombresucursal"></span></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="inventarioToolbar">
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
                    </div>
                </div>
                <table
                        id="tableinventario"
                        data-toggle="table"
                        data-url="./mods/inventario/traslados/procs/getinventario.php"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-search="true"
                        data-show-refresh="true"
                        data-show-button-text="true"
                        data-toolbar=".inventarioToolbar"
                        data-page-list="[10, 25, 50, 100]"
                        data-page-size="10"
                        data-query-params="inventarioCustomParams"
                        data-icon-size="sm"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="INVENTARIOID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowInventarioIndexFormatter">#</th>
                            <th data-field="CODIGOINVENTARIO">Code</th>
                            <th data-field="CATEGORIA">Category</th>
                            <th data-field="MARCA">Brand</th>
                            <th data-field="MODELO">Model</th>
                            <th data-field="DESCRIPCION">Description</th>
                            <th data-field="MSRP" data-visible="false">MSRP</th>
                            <th data-field="PORCENTAJETIPODESTOCKDIST" data-visible="false">Stock type distr. %</th>
                            <th data-field="TIPODESTOCKDIST" data-visible="false">Stock type distr.</th>
                            <th data-field="operate" data-formatter="inventarioOperateFormatter" data-events="inventarioOperateEvents">Actions</th>
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