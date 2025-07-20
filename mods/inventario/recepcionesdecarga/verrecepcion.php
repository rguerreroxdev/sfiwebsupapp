<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.02.01");

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

        // Si el registro no existe, retornar a listado
        if ($objRecepcion->recepcionDeCargaId == -1)
        {
            echo ("<script>window.location.href='?mod=inventario&opc=recepcionesdecarga'</script>");
            exit();
        }

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("02.02.01");
        $accesoEditar = in_array("02.02.01.02", $accesos) ? "" : " disabled";
        $accesoEliminar = in_array("02.02.01.03", $accesos) ? "" : " disabled";
        $accesoCerrar = in_array("02.02.01.04", $accesos) ? "" : " disabled";
        $accesoProcesar = in_array("02.02.01.05", $accesos) ? "" : " disabled";

        $textoCerrarAbrir = "";
        $textoEliminarAnular = "";
        $mostrarInventario = false;
        switch (strtoupper($objRecepcion->estado)) {
            case 'FOR':
                $accesoProcesar = "disabled";
                $textoCerrarAbrir = "Close";
                $textoEliminarAnular = $objRecepcion->correlativo == -1 ? "Delete" : "Cancel";
                break;
            case 'CER':
                $accesoEditar = "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = $objRecepcion->correlativo == -1 ? "Delete" : "Cancel";
                break;
            case 'ANU':
                $accesoEditar = "disabled";
                $accesoEliminar = "disabled";
                $accesoCerrar = "disabled";
                $accesoProcesar = "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = "Cancel";
                break;
            case 'PRO':
                $accesoEditar = "disabled";
                $accesoEliminar = "disabled";
                $accesoCerrar = "disabled";
                $accesoProcesar = "disabled";
                $textoCerrarAbrir = "Open";
                $mostrarInventario = true;
                $textoEliminarAnular = "Cancel";
                break;
        }
?>

<h3>Bills of lading - Document data</h3>

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
                        <label class="input-group-text width-95px" for="sucursal">Store</label>
                        <input type="text" id="sucursal" name="sucursal" class="form-control form-control-sm" value="<?= $objRecepcion->sucursal ?>" readonly>
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
                        <span class="input-group-text width-95px">Supplier</span>            
                        <input type="text" id="codigoproveedor" name="codigoproveedor" class="form-control form-control-sm" style="max-width: 70px;" value="<?= $objRecepcion->codigoProveedor ?>" readonly>
                        <input type="text" id="proveedor" name="proveedor" class="form-control form-control-sm" value="<?= $objRecepcion->proveedor ?>" readonly>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Load ID</span>
                        <input type="text" id="numerodedocumento" name="numerodedocumento" class="form-control form-control-sm" value="<?= $objRecepcion->numeroDeDocumento ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Document date</span>
                        <input type="text" id="fechadeemision" name="fechadeemision" class="form-control form-control-sm" value="<?= $objRecepcion->fechaDeEmision->format("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Reception date</span>
                        <input type="text" id="fechaderecepcion" name="fechaderecepcion" class="form-control form-control-sm" value="<?= $objRecepcion->fechaDeRecepcion->format("m/d/Y") ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="tipodestockorigen">Stock type origin</label>
                        <input type="text" id="tipodestockorigen" name="tipodestockorigen" class="form-control form-control-sm" value="<?= $objRecepcion->tipoDeStockOrigen ?>" readonly>
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
                        <label class="input-group-text width-130px" for="tipodestockdist">Stock type distr.</label>
                        <input type="text" id="tipodestockdist" name="tipodestockdist" class="form-control form-control-sm" value="<?= $objRecepcion->tipoDeStockDist ?>" readonly>
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
                        <label class="input-group-text width-130px" for="tipodestockdist">Warranty</label>
                        <input type="text" id="tipodegarantia" name="tipodegarantia" class="form-control form-control-sm" value="<?= $objRecepcion->tipoDeGarantia ?>" readonly>
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
                            <button class="btn btn-sm btn-danger" id="btneliminar" style="min-width: 75px;"<?= $accesoEliminar ?>>
                                <?php if ($textoEliminarAnular == "Delete"): ?>
                                    <i class="bi bi-trash"></i>
                                <?php else: ?>
                                    <i class="bi bi-file-earmark-break"></i>
                                <?php endif; ?>
                                <?= $textoEliminarAnular ?>
                            </button>
                        </div>
                        <div class="col-auto"<?php if ($objRecepcion->estado == "PRO"): ?> style="display: none;"<?php endif; ?>>
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
                        <div class="col-auto"<?php if ($objRecepcion->estado != "PRO"): ?> style="display: none;"<?php endif; ?>>
                            <div class="col-auto">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-secondary" id="btnetiquetas" style="min-width: 75px;"><i class="bi bi-credit-card-2-front"></i> Print labels</button>
                            </div>
                        </div>
                    </div>
                </div><!-- botones -->
            </div>
        </div>

        <div class="p-3 mt-2 bg-body rounded shadow-sm">
            <div class="col">
                <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/inventario/recepcionesdecarga/procs/getrecepciondetalle.php?rid=<?= $objRecepcion->recepcionDeCargaId ?>"
                    data-pagination="false"
                    class="table-sm small col-9"
                >
                    <thead>
                        <tr>
                            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="CANTIDAD" data-align="right">Quantity</th>
                            <th data-field="CODIGOPRODUCTO">Product</th>
                            <th data-field="CATEGORIA">Category</th>
                            <th data-field="MARCA">Brand</th>
                            <th data-field="PRODUCTO">Model</th>
                            <th data-field="DESCRIPCION">Description</th>
                            <th data-field="TIPODESTOCKORIGEN">Stock type origin</th>
                            <th data-field="PORCENTAJETIPODESTOCKORIGEN" data-align="right">% origin</th>
                            <th data-field="COSTOORIGEN" data-align="right" data-formatter="costoOrigenFormatter">Cost origin ($)</th>
                            <th data-field="TIPODESTOCKDIST">Stock type distr.</th>
                            <th data-field="PORCENTAJETIPODESTOCKDIST" data-align="right">% distr</th>
                            <th data-field="COSTODIST" data-align="right" data-formatter="costoDistFormatter">Cost distr. ($)</th>
                            <th data-field="MSRP" data-align="right" data-formatter="msrpFormatter">MSRP ($)</th>
                            <?php if ($mostrarInventario): ?>
                            <th data-field="inventario" data-align="center" data-formatter="inventarioFormatter">Inventory</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>

                <div class="mt-2">Total items: <span id="totalitems"></span></div>
            </div>
        </div>

        <!-- Botones estaban aquí -->

        <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
        <input type="hidden" id="rid" name="rid" value="<?= $objRecepcion->recepcionDeCargaId ?>">
        <input type="hidden" id="estadoActual" name="estadoActual" value="<?= $objRecepcion->estado ?>">
    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation date</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objRecepcion->fechaCreacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objRecepcion->usuarioCreo ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objRecepcion->fechaModificacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objRecepcion->usuarioModifica ?>" readonly>
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

<div class="toast-container p-5 position-absolute top-0 start-50 translate-middle-x" id="toastPlacement">
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
                Confirm that you are going to <?= strtolower($textoEliminarAnular) ?> this document
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
                <?= $objRecepcion->estado == "FOR" ? "<br>A closed document can not be edited." : "" ?>
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
                Confirm that you are going to post this document.
                <br>When posting, the items will be converted to inventory and the action cannot be reversed.
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
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Error when deleting</h1>
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


<div class="modal fade small" id="modalEmitirEtiquetas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Print labels - Bill of lading <?= $objRecepcion->correlativo ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="frmprint">
                <div class="row mt-1 mb-1">
                    <div class="col">
                        <div class="container pt-1 pb-1 border" style="width: 200px;">
                            <div class="row">
                                <div class="col-6">
                                    <a href="#abc" class="text-decoration-none text-secondary">
                                        <div class="text-center border pos-etiqueta p-2" onclick="setPos(1);">1</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#abc" class="text-decoration-none text-secondary">
                                        <div class="text-center border pos-etiqueta p-2" onclick="setPos(2);">2</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#abc" class="text-decoration-none text-secondary">
                                        <div class="text-center border pos-etiqueta p-2" onclick="setPos(3);">3</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#abc" class="text-decoration-none text-secondary">
                                        <div class="text-center border pos-etiqueta p-2" onclick="setPos(4);">4</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#abc" class="text-decoration-none text-secondary">
                                        <div class="text-center border pos-etiqueta p-2" onclick="setPos(5);">5</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#abc" class="text-decoration-none text-secondary">
                                        <div class="text-center border pos-etiqueta p-2" onclick="setPos(6);">6</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#abc" class="text-decoration-none text-secondary">
                                        <div class="text-center border pos-etiqueta p-2" onclick="setPos(7);">7</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#abc" class="text-decoration-none text-secondary">
                                        <div class="text-center border pos-etiqueta p-2" onclick="setPos(8);">8</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#abc" class="text-decoration-none text-secondary">
                                        <div class="text-center border pos-etiqueta p-2" onclick="setPos(9);">9</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#abc" class="text-decoration-none text-secondary">
                                        <div class="text-center border pos-etiqueta p-2" onclick="setPos(10);">10</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <span>Label sheet</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mt-5" style="width: 225px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text width-160px">Start in position #</span>
                                <input type="number" id="ubicacioninicial" name="ubicacioninicial" class="form-control form-control-sm" value="1" step="1" min="1" max="10">
                            </div>
                        </div>
                        <div class="mt-3">
                            
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary" id="btnimprimiretiquetas" style="min-width: 75px;"><i class="bi bi-printer"></i> Print</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x"></i> Close</button>
                </div>
            </form>
        </div>
    </div>
</div>



<?php 
    } // else de mostrar contenido por acceso a opción
?>