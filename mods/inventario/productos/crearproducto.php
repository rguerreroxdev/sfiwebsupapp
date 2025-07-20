<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.01.01.01");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Para crear el combo de Categorías
        require_once("inc/class/Categorias.php");
        $objCategoria = new Categorias($conn);
        $listaDeCategorias = $objCategoria->getListaParaCombo();
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

<h3>Products - Create new</h3>

<form id="frm">

<nav>
    <div class="nav nav-tabs small" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-editar-tab" data-bs-toggle="tab" data-bs-target="#nav-editar" type="button" role="tab" aria-controls="nav-editar" aria-selected="true">Product data</button>
        <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Save dates</button>
    </div>
</nav>

<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-editar" role="tabpanel" aria-labelledby="nav-editar-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-95px" for="categoria">Category<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="categoria" name="categoria" required>
                            <!-- Categorías -->
                            <?= $listaDeCategoriasOptions ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Brand<span class="text-danger">&nbsp;*</span></span>
                        <input type="text" id="marcanombre" name="marcanombre" class="form-control form-control-sm" readonly required>
                        <input type="hidden" id="marca" name="marca" class="form-control form-control-sm" readonly required>
                        <button class="btn btn-outline-secondary" type="button" id="btnmarca"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Model<span class="text-danger">&nbsp;*</span></span>
                        <input type="text" id="modelo" name="modelo" class="form-control form-control-sm" maxlength="100" required>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Color<span class="text-danger">&nbsp;*</span></span>
                        <input type="text" id="colornombre" name="colornombre" class="form-control form-control-sm" readonly required>
                        <input type="hidden" id="color" name="color" class="form-control form-control-sm" readonly required>
                        <button class="btn btn-outline-secondary" type="button" id="btncolor"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-10 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Description<span class="text-danger">&nbsp;*</span></span>
                        <input type="text" id="descripcion" name="descripcion" class="form-control form-control-sm" maxlength="250" required>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">MSRP<span class="text-danger">&nbsp;*</span></span>
                        <input type="number" id="msrp" name="msrp" class="form-control form-control-sm text-end" step="0.01" min="0.00" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2 justify-content-between">
            <div class="col">
                <span class="fst-italic small"><span class="text-danger">*</span> -> Required data</span>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-sm btn-primary" id="btnguardar" style="min-width: 75px;">
                    <i class="bi bi-floppy2"></i> Save
                    <span class="spinner-border spinner-border-sm visually-hidden" id="btnguardarspinner" role="status" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-sm btn-secondary" id="btncancelar" style="min-width: 75px;"><i class="bi bi-x-octagon"></i> Cancel</button>
            </div>
        </div>

    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation date</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= date("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $_SESSION["usuario"] ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= date("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $_SESSION["usuario"] ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
    </div>
</div>

</form>

<div class="toast-container p-5 position-fixed top-0 start-50 translate-middle-x" id="toastPlacement">
    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastMensaje">
        <div class="d-flex">
            <div class="toast-body">
                The data was saved.
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

<div class="modal fade small" id="modalSeleccionarMarca" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select brand</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="toolbarmarcas">
                    <span class="label-text">Search</span>
                </div>
                <table
                        id="tablemarcas"
                        data-toggle="table"
                        data-url="./mods/inventario/productos/procs/getmarcas.php"
                        data-toolbar=".toolbarmarcas"
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
                            <th data-field="MARCAID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowMarcaIndexFormatter">#</th>
                            <th data-field="NOMBRE">Name</th>
                            <th data-field="operate" data-formatter="marcasOperateFormatter" data-events="marcasOperateEvents">Actions</th>
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

<div class="modal fade small" id="modalSeleccionarColor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select color</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="toolbarcolores">
                    <span class="label-text">Search</span>
                </div>
                <table
                        id="tablecolores"
                        data-toggle="table"
                        data-url="./mods/inventario/productos/procs/getcolores.php"
                        data-toolbar=".toolbarcolores"
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
                            <th data-field="COLORID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowColorIndexFormatter">#</th>
                            <th data-field="NOMBRE">Name</th>
                            <th data-field="operate" data-formatter="coloresOperateFormatter" data-events="coloresOperateEvents">Actions</th>
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