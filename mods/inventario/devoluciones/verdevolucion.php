<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.02.04");

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

        // Si el registro no existe, retornar a listado
        if ($objDevolucion->devolucionId == -1)
        {
            echo ("<script>window.location.href='?mod=inventario&opc=devoluciones'</script>");
            exit();
        }

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("02.02.04");
        $accesoEditar = in_array("02.02.04.02", $accesos) ? "" : " disabled";
        $accesoEliminar = in_array("02.02.04.03", $accesos) ? "" : " disabled";
        $accesoCerrar = in_array("02.02.04.04", $accesos) ? "" : " disabled";
        $accesoProcesar = in_array("02.02.04.05", $accesos) ? "" : " disabled";

        $textoCerrarAbrir = "";
        $textoEliminarAnular = "";
        switch (strtoupper($objDevolucion->estado))
        {
            case 'FOR':
                $accesoProcesar = "disabled";
                $textoCerrarAbrir = "Close";
                $textoEliminarAnular = $objDevolucion->correlativo == -1 ? "Delete" : "Cancel";
                break;
            case 'CER':
                $accesoEditar = "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = $objDevolucion->correlativo == -1 ? "Delete" : "Cancel";
                break;
            case 'PRO':
                $accesoEditar = "disabled";
                $accesoEliminar = "disabled";
                $accesoCerrar = "disabled";
                $accesoProcesar = "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = "Cancel";
                break;
            case 'ANU':
                $accesoEditar = "disabled";
                $accesoEliminar = "disabled";
                $accesoCerrar = "disabled";
                $accesoProcesar = "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = "Cancel";
                break;
        }
?>

<h3>Inventory returns - Document data</h3>

<nav>
    <div class="nav nav-tabs small" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-editar-tab" data-bs-toggle="tab" data-bs-target="#nav-editar" type="button" role="tab" aria-controls="nav-editar" aria-selected="true">Document data</button>
        <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Status history</button>
    </div>
</nav>

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
                        <label class="input-group-text width-130px" for="sucursal">Store</label>
                        <input type="text" id="sucursal" name="sucursal" class="form-control form-control-sm" value="<?= $objDevolucion->sucursal ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="sucursal">Type</label>
                        <input type="text" id="tipodedevolucion" name="tipodedevolucion" class="form-control form-control-sm" value="<?= $objDevolucion->tipoDeDevolucion ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Return date</span>
                        <input type="text" id="fechadedevolucion" name="fechadedevolucion" class="form-control form-control-sm text-center" value="<?= $objDevolucion->fecha ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-11 col-lg-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Notes</span>
                        <textarea id="concepto" name="concepto" class="form-control form-control-sm" maxlength="500" value="" readonly><?= $objDevolucion->concepto ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-12">
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <button class="btn btn-sm btn-secondary" id="btnregresar"><i class="bi bi-arrow-return-left"></i> Go back</button>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-primary" id="btneditar" style="min-width: 75px;"<?= $accesoEditar ?>><i class="bi bi-pencil-square"></i> Edit</button>
                            <button class="btn btn-sm btn-secondary" id="btnimprimir" style="min-width: 75px;"><i class="bi bi-printer"></i> Print</button>
                            <button class="btn btn-sm btn-secondary" id="btnexcel" style="min-width: 75px;"><i class="bi bi-file-earmark-excel"></i> Excel</button>
                            <button class="btn btn-sm btn-danger" id="btneliminar" style="min-width: 75px;"<?= $accesoEliminar ?>>
                                <?php if ($textoEliminarAnular == "Delete"): ?>
                                    <i class="bi bi-trash"></i>
                                <?php else: ?>
                                    <i class="bi bi-file-earmark-break"></i>
                                <?php endif; ?>
                                <?= $textoEliminarAnular ?>
                            </button>
                        </div>
                        <div class="col-auto"<?php if ($objDevolucion->estado == "PRO"): ?> style="display: none;"<?php endif; ?>>
                            <button class="btn btn-sm btn-success" id="btncerrarabrir" style="min-width: 75px;"<?= $accesoCerrar ?>>
                                <?php if ($textoCerrarAbrir == "Open"): ?>
                                    <i class="bi bi-folder2-open"></i>
                                <?php else: ?>
                                    <i class="bi bi-folder-x"></i>
                                <?php endif; ?>
                                <?= $textoCerrarAbrir ?>
                            </button>
                            <button class="btn btn-sm btn-success" id="btnprocesar" style="min-width: 75px;"<?= $accesoProcesar ?>><i class="bi bi-gear"></i> Post</button>
                        </div>
                    </div>
                </div><!-- Botones -->

            </div>

        </div>

        <div class="p-3 mt-2 bg-body rounded shadow-sm">
            <div class="col">
                <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/inventario/devoluciones/procs/getdevolucionesdetalle.php?did=<?= $objDevolucion->devolucionId ?>"
                    data-pagination="false"
                    class="table-sm small col-9"
                >
                    <thead>
                        <tr>
                        <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="CODIGOINVENTARIO">Inventory item</th>
                            <th data-field="CATEGORIA">Category</th>
                            <th data-field="MARCA">Brand</th>
                            <th data-field="MODELO">Model</th>
                            <th data-field="DESCRIPCION">Description</th>
                            <th data-field="CORRELATIVOSALIDA">Disch. #</th>
                            <th data-field="FECHASALIDA">Disch. date</th>
                            <th data-field="TIPODESALIDA">Disch. type</th>
                            <th data-field="MSRP" data-width="80" data-align="right" data-formatter="msrpFormatter">MSRP</th>
                            <th data-field="TIPODESTOCK" data-width="80">Stock type distr.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>

                <div class="mt-2">Total items: <span id="totalitems"></span></div>
            </div>
        </div>

        <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
        <input type="hidden" id="did" name="did" value="<?= $objDevolucion->devolucionId ?>">
        <input type="hidden" id="estadoActual" name="estadoActual" value="<?= $objDevolucion->estado ?>">
    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation date</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objDevolucion->fechaCreacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objDevolucion->usuarioCreo ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objDevolucion->fechaModificacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objDevolucion->usuarioModifica ?>" readonly>
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
                        <th data-field="DEVOLUCIONID" data-visible="false">RID</th>
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

<div class="modal fade small" id="modalConfirmarEliminar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $textoEliminarAnular ?> document</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Confirm that you are going to <?= strtolower($textoEliminarAnular) ?> this document.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btnconfirmaeliminar"><i class="bi bi-trash"></i> Yes, <?= strtolower($textoEliminarAnular) ?></button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-octagon"></i> No, return</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade small" id="modalConfirmarCerrarAbrir" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $textoCerrarAbrir ?> document</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Confirm that you are going to <?= strtolower($textoCerrarAbrir) ?> this document.
                <?= $objDevolucion->estado == "FOR" ? "<br>A closed document can not be edited." : "" ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btnconfirmacerrarabrir">
                    <?php if ($textoCerrarAbrir == "Open"): ?>
                        <i class="bi bi-folder2-open"></i>
                    <?php else: ?>
                        <i class="bi bi-folder-x"></i>
                    <?php endif; ?>
                    Yes, <?= strtolower($textoCerrarAbrir) ?>
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-octagon"></i> No, cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade small" id="modalConfirmarProcesar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Post document</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Confirm that you are going to post this document.<br>
                Stocks will increase to one.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btnconfirmaprocesar"><i class="bi bi-gear"></i> Yes, post</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-octagon"></i> No, cancel</button>
            </div>
        </div>
    </div>
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