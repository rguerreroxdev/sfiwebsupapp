<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);

    if (isset($_GET["did"]))
    {
        // Se está editando
        $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.02.04.02");
    }
    else
    {
        // Se está creando
        $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.02.04.01");
    }

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        $devolucionId = isset($_GET["did"]) ? $_GET["did"] : -1;
        $devolucionId = is_numeric($devolucionId) ? $devolucionId : -1;

        require_once("inc/class/DevolucionesInv.php");
        $objDevolucion = new DevolucionesInv($conn);
        $objDevolucion->getById($devolucionId);

        // Antes de continuar, se verifica que un documento existente solo se puede editar en estado formulado
        if ($objDevolucion->devolucionId != -1 && $objDevolucion->estado != "FOR")
        {
            echo('<script>window.location.href="?mod=inventario&opc=devoluciones&subopc=verdevolucion&did=' . $objDevolucion->devolucionId . '";</script>');
            exit();
        }
        
        $filasDetalle = "";
        if ($objDevolucion->devolucionId == -1)
        {
            $objDevolucion->iniciarDatosParaNuevoRegistro();
        }
        else
        {
            require_once("mods/inventario/devoluciones/procs/getfilasdetalle.php");
            $filasDetalle = getFilasDetalle($conn, $objDevolucion->devolucionId);
        }

        // Para crear el combo de Sucursales (a las que tiene acceso el usuario)
        require_once("inc/class/Sucursales.php");
        $objSucursales = new Sucursales($conn);
        $listaDeSucursales = $objSucursales->getListaParaComboDeUsuario($_SESSION["usuarioId"]);
        $listaDeSucursalesOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"] == -1 ? "" : $sucursal["SUCURSALID"];

            $selected = "";
            if ($objDevolucion->sucursalId != -1)
            {
                $selected = $objDevolucion->sucursalId == $valor ? " selected" : "";
            }
            
            $listaDeSucursalesOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        // Para crear el combo de tipos de devolución
        require_once("inc/class/TiposDeDevolucionInv.php");
        $objTiposDeDevolucion = new TiposDeDevolucionInv($conn);
        $listaDeTiposDeDevolucion = $objTiposDeDevolucion->getListaParaCombo("SELECT");
        $listaDeTiposDeDevolucionOptions = "";
        foreach ($listaDeTiposDeDevolucion as $tipoDeDevolucion)
        {
            $texto = $tipoDeDevolucion["NOMBRE"];
            $valor = $tipoDeDevolucion["TIPODEDEVOLUCIONID"] == -1 ? "" : $tipoDeDevolucion["TIPODEDEVOLUCIONID"];

            $selected = "";
            if ($objDevolucion->tipoDeDevolucion != -1)
            {
                $selected = $objDevolucion->tipoDeDevolucionId == $valor ? " selected" : "";
            }

            $listaDeTiposDeDevolucionOptions .= "
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

<h3>Inventory returns - Document registration</h3>

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
                        <span class="input-group-text width-130px">Correlative</span>
                        <input type="text" id="correlativo" name="correlativo" class="form-control form-control-sm text-center" value="<?= $objDevolucion->correlativo ?>" readonly>
                    </div>
                </div>
                <div class="col-sm-1">
                    <!-- vacío -->
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Status</span>
                        <input type="text" id="nombredeestado" name="nombredeestado" class="form-control form-control-sm text-center" value="<?= $objDevolucion->nombreDeEstado ?>" readonly>
                        <input type="hidden" id="estado" name="estado" value="<?= $objDevolucion->estado ?>">
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-7 col-lg-4">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="sucursal">Store<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="sucursal" name="sucursal" required>
                            <!-- Sucursales -->
                            <?= $listaDeSucursalesOptions ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="sucursal">Type<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="tipodedevolucion" name="tipodedevolucion" required>
                            <!-- Tipos de salida -->
                            <?= $listaDeTiposDeDevolucionOptions ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Return date<span class="text-danger">&nbsp;*</span></span>
                        <input type="date" id="fechadedevolucion" name="fechadedevolucion" class="form-control form-control-sm" value="<?= $objDevolucion->fechadt->format("Y-m-d") ?>" required>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-11 col-lg-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Notes</span>
                        <textarea id="concepto" name="concepto" class="form-control form-control-sm" maxlength="250"><?= $objDevolucion->concepto ?></textarea>
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
                        <th class="width-170px">Description</th>
                        <th class="width-90px">Discharge #</th>
                        <th class="width-130px">Discharge date</th>
                        <th class="width-130px">Discharge type</th>
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

        <input type="hidden" id="did" name="did" value="<?= $objDevolucion->devolucionId ?>">
    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation date</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objDevolucion->fechaCreacion == null ? date("m/d/Y") : $objDevolucion->fechaCreacion->format("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objDevolucion->usuarioCreo == null ? $_SESSION["usuario"] : $objDevolucion->usuarioCreo ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objDevolucion->fechaModificacion == null ? date("m/d/Y") : $objDevolucion->fechaModificacion->format("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objDevolucion->usuarioModifica == null ? $_SESSION["usuario"] : $objDevolucion->usuarioModifica ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 mt-2 bg-body rounded shadow-sm">
            <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/inventario/devoluciones/procs/getdevolucionestados.php?did=<?= $objDevolucion->devolucionId ?>"
                    class="table-sm small"
            >
                <thead>
                    <tr>
                        <th data-field="DEVOLUCIONESTADOID" data-visible="false">ID</th>
                        <th data-field="DEVOLUCIONID" data-visible="false">DID</th>
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
    <div class="modal-dialog modal-xl modal-dialog-centered">
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
                        data-url="./mods/inventario/devoluciones/procs/getinventario.php"
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
                            <th data-field="CORRELATIVOSALIDA">Discharge #</th>
                            <th data-field="FECHASALIDA">Discharge date</th>
                            <th data-field="TIPODESALIDA">Discharge type</th>
                            <th data-field="CONCEPTO">Discharge concept</th>
                            <th data-field="SALIDADETALLEID" data-visible="false">SDID</th>
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