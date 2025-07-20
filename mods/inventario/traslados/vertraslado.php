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

        $trasladoId = isset($_GET["tid"]) ? $_GET["tid"] : -1;
        $trasladoId = is_numeric($trasladoId) ? $trasladoId : -1;

        require_once("inc/class/Traslados.php");
        $objTraslado = new Traslados($conn);
        $objTraslado->getById($trasladoId);

        // Si el registro no existe, retornar a listado
        if ($objTraslado->trasladoId == -1)
        {
            echo ("<script>window.location.href='?mod=inventario&opc=traslados'</script>");
            exit();
        }

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("02.02.02");
        $accesoEditar = in_array("02.02.02.02", $accesos) ? "" : " disabled";
        $accesoEliminar = in_array("02.02.02.03", $accesos) ? "" : " disabled";
        $accesoCerrar = in_array("02.02.02.04", $accesos) ? "" : " disabled";
        $accesoProcesarOrigen = in_array("02.02.02.05", $accesos) ? "" : " disabled";
        $accesoProcesarDestino = in_array("02.02.02.06", $accesos) ? "" : " disabled";
        $accesoRechazar = in_array("02.02.02.07", $accesos) ? "" : " disabled";
        $accesoAnular = in_array("02.02.02.08", $accesos) ? "" : " disabled";

        // Obtener accesos a sucursales
        $sucursalesAcceso = $usuarioAccesos->getListaDeSucursalesDeUsuario();
        $accesoASucursalOrigen = in_array($objTraslado->sucursalOrigenId, $sucursalesAcceso);
        $accesoASucursalDestino = in_array($objTraslado->sucursalDestinoId, $sucursalesAcceso);

        $textoCerrarAbrir = "";
        $textoEliminarAnular = $objTraslado->correlativo == -1 ? "Delete" : "Cancel";
        switch (strtoupper($objTraslado->estado))
        {
            case 'FOR':
                    $accesoEditar = $accesoASucursalOrigen ? $accesoEditar : "disabled";
                $accesoEliminar = $accesoASucursalOrigen ? $accesoEliminar : "disabled";
                $accesoProcesarOrigen = "disabled";
                $accesoProcesarDestino = "disabled";
                $accesoRechazar = "disabled";
                $accesoCerrar = $accesoASucursalOrigen ? $accesoCerrar : "disabled";
                $textoCerrarAbrir = "Close";
                break;
            case 'CER':
                $accesoEditar = "disabled";
                $accesoCerrar = $accesoASucursalOrigen ? $accesoCerrar : "disabled";
                $accesoProcesarOrigen = $accesoASucursalOrigen ? $accesoProcesarOrigen : "disabled";
                $accesoEliminar = $accesoASucursalOrigen ? $accesoEliminar : "disabled";
                $accesoProcesarDestino = "disabled";
                $accesoRechazar = "disabled";
                $textoCerrarAbrir = "Open";
                break;
            case 'PRO':
                $accesoEditar = "disabled";
                $accesoEliminar = "disabled";
                $accesoAnular = "disabled";
                $accesoCerrar = "disabled";
                $accesoProcesarOrigen = "disabled";
                $accesoProcesarDestino = $accesoASucursalDestino ? $accesoProcesarDestino : "disabled";
                $accesoRechazar = $accesoASucursalDestino ? $accesoRechazar : "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = "Cancel";
                break;
            case 'PRD':
                $accesoEditar = "disabled";
                $accesoEliminar = "disabled";
                $accesoAnular = "disabled";
                $accesoCerrar = "disabled";
                $accesoProcesarOrigen = "disabled";
                $accesoProcesarDestino = "disabled";
                $accesoRechazar = "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = "Cancel";
                break;
            case 'LIB':
                $accesoEditar = "disabled";
                $accesoEliminar = $accesoASucursalOrigen ? $accesoAnular : "disabled";
                $accesoCerrar = "disabled";
                $accesoProcesarOrigen = "disabled";
                $accesoProcesarDestino = "disabled";
                $accesoRechazar = "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = "Cancel";
                break;
            case 'ANU':
                $accesoEditar = "disabled";
                $accesoEliminar = "disabled";
                $accesoCerrar = "disabled";
                $accesoProcesarOrigen = "disabled";
                $accesoProcesarDestino = "disabled";
                $accesoRechazar = "disabled";
                $accesoAnular = "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = "Cancel";
                break;
            }
?>

<h3>Inventory Transfers - Document data</h3>

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
                        <span class="input-group-text width-130px">Creation date</span>
                        <input type="text" id="fechadecreacionver" name="fechadecreacionver" class="form-control form-control-sm text-center" value="<?= $objTraslado->fechaCreacion->format("m/d/Y") ?>" readonly>
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
                        <label class="input-group-text width-130px" for="sucursalorigen">Origin Store</label>
                        <input type="text" id="sucursalorigen" name="sucursalorigen" class="form-control form-control-sm" value="<?= $objTraslado->sucursalOrigen ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="sucursaldestino">Destination Store</label>
                        <input type="text" id="sucursaldestino" name="sucursaldestino" class="form-control form-control-sm" value="<?= $objTraslado->sucursalDestino ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12 col-lg-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Notes</span>
                        <textarea id="observaciones" name="observaciones" class="form-control form-control-sm" maxlength="500" rows="2" value="" readonly><?= $objTraslado->observaciones ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row mt-2 justify-content-end">
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

                <div class="col-auto"<?php if ($objTraslado->estado == "PRD" || $objTraslado->estado == "ANU"): ?> style="display: none;"<?php endif; ?>>
                    <button class="btn btn-sm btn-success" id="btncerrarabrir" style="min-width: 75px;"<?= $accesoCerrar ?>>
                        <?php if ($textoCerrarAbrir == "Open"): ?>
                            <i class="bi bi-folder2-open"></i>
                        <?php else: ?>
                            <i class="bi bi-folder-x"></i>
                        <?php endif; ?>
                        <?= $textoCerrarAbrir ?>
                    </button>
                    <button class="btn btn-sm btn-success" id="btnprocesarorigen" style="min-width: 75px;"<?= $accesoProcesarOrigen ?>><i class="bi bi-gear"></i> Post origin</button>
                    <button class="btn btn-sm btn-success" id="btnprocesardestino" style="min-width: 75px;"<?= $accesoProcesarDestino ?>><i class="bi bi-gear"></i> Post dest.</button>
                    <button class="btn btn-sm btn-success" id="btnrechazardestino" style="min-width: 75px;"<?= $accesoRechazar ?>><i class="bi bi-backspace"></i> Reject dest.</button>
                </div>
            </div>

        </div>

        <div class="p-3 mt-2 bg-body rounded shadow-sm">
            <div class="col">
                <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/inventario/traslados/procs/gettrasladodetalle.php?tid=<?= $objTraslado->trasladoId ?>"
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
                            <th data-field="MSRP" data-width="80" data-align="right" data-formatter="msrpFormatter">MSRP</th>
                            <th data-field="TIPODESTOCKDIST" data-width="80">Stock type distr.</th>
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
        <input type="hidden" id="tid" name="tid" value="<?= $objTraslado->trasladoId ?>">
        <input type="hidden" id="estadoActual" name="estadoActual" value="<?= $objTraslado->estado ?>">
    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation date</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objTraslado->fechaCreacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objTraslado->usuarioCreo ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objTraslado->fechaModificacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objTraslado->usuarioModifica ?>" readonly>
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
                        <th data-field="TRASLADOID" data-visible="false">RID</th>
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
                <?php if (strtolower($textoEliminarAnular) == "cancel"): ?><br>Stocks will return to origin.<?php endif; ?>
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
                <?= $objTraslado->estado == "FOR" ? "<br>A closed document can not be edited." : "" ?>
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

<div class="modal fade small" id="modalConfirmarProcesarOrigen" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Post origin</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Confirm that you are going to post this document.<br>
                Stocks will be reduced to zero at origin to be in transit to the destination.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btnconfirmaprocesarorigen"><i class="bi bi-gear"></i> Yes, post</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-octagon"></i> No, cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade small" id="modalConfirmarProcesarDestino" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Post destination</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Confirm that you are going to post (receive) this document.<br>
                Stocks will increase at the destination.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btnconfirmaprocesardestino"><i class="bi bi-gear"></i> Yes, post</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-octagon"></i> No, cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade small" id="modalConfirmarRechazarDestino" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Reject transfer at destination</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Confirm that you are going to reject this document.<br>
                With this, the origin will be able to cancel the document.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btnconfirmarechazardestino"><i class="bi bi-gear"></i> Yes, reject</button>
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