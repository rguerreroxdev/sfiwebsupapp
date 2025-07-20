<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("FAC", "03.03.02");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        $devolucionId = isset($_GET["did"]) ? $_GET["did"] : -1;
        $devolucionId = is_numeric($devolucionId) ? $devolucionId : -1;

        require_once("inc/class/FacDevoluciones.php");
        $objDevolucion = new FacDevoluciones($conn);
        $objDevolucion->getById($devolucionId);

        // Si el registro no existe, retornar a listado
        if ($objDevolucion->devolucionId == -1)
        {
            echo ("<script>window.location.href='?mod=facturacion&opc=devoluciones'</script>");
            exit();
        }

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("03.03.02");
        $accesoEditar = in_array("03.03.02.02", $accesos) ? "" : " disabled";
        $accesoEliminar = in_array("03.03.02.03", $accesos) ? "" : " disabled";
        $accesoCerrar = in_array("03.03.02.04", $accesos) ? "" : " disabled";
        $accesoProcesar = in_array("03.03.02.05", $accesos) ? "" : " disabled";
        $accesoFSustituta = " disabled";

        $textoCerrarAbrir = "";
        $textoEliminarAnular = "";
        switch (strtoupper($objDevolucion->estado))
        {
            case 'FOR':
                $accesoProcesar = "disabled";
                $textoCerrarAbrir = "Close";
                $textoEliminarAnular = $objDevolucion->correlativoDevolucion == -1 ? "Delete" : "Delete";
                break;
            case 'CER':
                $accesoEditar = "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = $objDevolucion->correlativoDeFactura == -1 ? "Delete" : "Delete";
                break;
            case 'PRO':
                $accesoEliminar = "disabled";
                $accesoEditar = "disabled";
                $accesoCerrar = "disabled";
                $accesoProcesar = "disabled";
                $accesoFSustituta = $objDevolucion->facturaSustituyeId > 0 ? " disabled" : "";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = "Delete";
                break;
        }

        // Para fecha modales de búsquedas
        $fechaDesde = new DateTime();
        $fechaDesde->sub(new DateInterval('P3M'));
        $fechaDesde->modify('first day of this month');
        $fechaDesdeString = $fechaDesde->format('Y-m-d');
?>

<h3>Returns - Credit memo - Document data</h3>

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
                        <label class="input-group-text width-95px" for="sucursal">Store<span class="text-danger">&nbsp;*</span></label>
                        <input type="text" id="sucursalnombre" name="sucursalnombre" class="form-control form-control-sm text-center" value="<?= $objDevolucion->sucursalNombre ?>" readonly>
                        <input type="hidden" id="sucursal" name="sucursal" value="<?= $objDevolucion->sucursalId ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Correlative</span>
                        <input type="text" id="correlativocompuesto" name="correlativocompuesto" class="form-control form-control-sm text-center" value="<?= $objDevolucion->prefijoCorrelativoDevolucion . "-" . $objDevolucion->correlativoDevolucion ?>" readonly>
                        <input type="hidden" id="prefijodecorrelativo" name="prefijodecorrelativo" class="form-control form-control-sm text-center" value="<?= $objDevolucion->prefijoCorrelativoDevolucion ?>">
                        <input type="hidden" id="correlativo" name="correlativo" class="form-control form-control-sm text-center" value="<?= $objDevolucion->correlativoDevolucion ?>">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Status</span>
                        <input type="text" id="nombredeestado" name="nombredeestado" class="form-control form-control-sm text-center" value="<?= $objDevolucion->nombreDeEstado ?>" readonly>
                        <input type="hidden" id="estado" name="estado" value="<?= $objDevolucion->estado ?>">
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Returned invoice<span class="text-danger">&nbsp;*</span></span>            
                        <input type="text" id="facturadevuelta" name="facturadevuelta" class="form-control form-control-sm"  value="<?= $objDevolucion->prefijoDeCorrelativoDeFactura . "-" . $objDevolucion->correlativoDeFactura ?>" readonly>
                        <input type="hidden" id="facturadevueltaid" name="facturadevueltaid" value="<?= $objDevolucion->facturaDevueltaId ?>">
                    </div>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Returned invoice date</span>
                        <input type="text" id="facturadevueltafecha" name="facturadevueltafecha" class="form-control form-control-sm" value="<?= $objDevolucion->fechaDeFactura ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-8 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Customer</span>            
                        <input type="text" id="codigocliente" name="codigocliente" class="form-control form-control-sm" style="max-width: 70px;" value="<?= $objDevolucion->clienteCodigo ?>" readonly>
                        <input type="text" id="cliente" name="cliente" class="form-control form-control-sm" value="<?= $objDevolucion->clienteNombre ?>" readonly>
                        <input type="hidden" id="clienteid" name="clienteid" value="<?= $objDevolucion->clienteId ?>">
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Address</span>
                        <input type="text" id="clientedireccion" name="clientedireccion" class="form-control form-control-sm" value="<?= $objDevolucion->clienteDireccion ?>" maxlength="100" readonly>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Address cont.</span>
                        <input type="text" id="clientedireccioncomplemento" name="clientedireccioncomplemento" class="form-control form-control-sm" value="<?= $objDevolucion->clienteDireccionComplemento ?>" maxlength="100" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-3 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-90px">ZIP code</span>
                        <input type="text" id="clientecodigopostal" name="clientecodigopostal" class="form-control form-control-sm" value="<?= $objDevolucion->clienteCodigoPostal ?>" maxlength="5" readonly>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-90px">Phone</span>
                        <input type="text" id="clientetelefono" name="clientetelefono" class="form-control form-control-sm" value="<?= $objDevolucion->clienteTelefono ?>" maxlength="50" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Email</span>
                        <input type="email" id="clientecorreo" name="clientecorreo" class="form-control form-control-sm" value="<?= $objDevolucion->clienteCorreoElectronico ?>" maxlength="100" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Document date<span class="text-danger">&nbsp;*</span></span>
                        <input type="text" id="fechadeemision" name="fechadeemision" class="form-control form-control-sm" value="<?= $objDevolucion->fechaDevolucion ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-170px">Don't calculate taxes</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="nocalcularimpuesto" name="nocalcularimpuesto" class="form-check-input mt-0" value=""<?= $objDevolucion->noCalcularImpuesto == 1 ? " checked" : "" ?> disabled>
                            <input type="hidden" id="nocalcularimpuestovalor" name="nocalcularimpuestovalor" value="<?= $objDevolucion->noCalcularImpuesto ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-11 col-lg-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Return notes</span>
                        <textarea id="notas" name="notas" class="form-control form-control-sm" readonly><?= $objDevolucion->concepto ?></textarea>
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
                            <button class="btn btn-sm btn-secondary" id="btnimprimirdevolucion" style="min-width: 75px;"><i class="bi bi-printer"></i> Print credit memo</button>
                            <button class="btn btn-sm btn-danger" id="btneliminar" style="min-width: 75px;"<?= $accesoEliminar ?>>
                                <?php if ($textoEliminarAnular == "Delete"): ?>
                                    <i class="bi bi-trash"></i>
                                <?php else: ?>
                                    <i class="bi bi-file-earmark-break"></i>
                                <?php endif; ?>
                                <?= $textoEliminarAnular ?>
                            </button>
                        </div>
                        <div class="col-auto"<?php if ($objDevolucion->estado == "PRO" || $objDevolucion->estado == "ANU"): ?> style="display: none;"<?php endif; ?>>
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

        <div class="px-3 pb-3 mt-2 bg-body rounded shadow-sm"<?php if ($objDevolucion->estado != "PRO"): ?> style="display: none;"<?php endif; ?>>
            <span class="small">Substitute invoice (when applicable)</span>

            <div class="row mt-2">
                <div class="col-md-4 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Substitute invoice</span>            
                        <input type="text" id="facturasustituta" name="facturasustituta" class="form-control form-control-sm"  value="<?= $objDevolucion->prefijoDeCorrelativoSustituye . "-" . $objDevolucion->CorrelativoFacturaSustituye ?>" readonly>
                        <input type="hidden" id="facturasustituyeid" name="facturasustituyeid" value="<?= $objDevolucion->facturaSustituyeId ?>">
                    </div>
                </div>
                <div class="col-md-4 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Substitute invoice date</span>
                        <input type="text" id="facturasustituyefecha" name="facturasustituyefecha" class="form-control form-control-sm" value="<?= $objDevolucion->fechaFacturaSustituye ?>" readonly>
                    </div>
                </div>
                <div class="col-md-4 col-lg-4">
                    <button class="btn btn-sm btn-primary" id="btnfsustituta" style="min-width: 75px;"<?= $accesoFSustituta ?>><i class="bi bi-pencil-square"></i> Set substitute invoice</button>
                </div>
            </div>
        </div>

        <div class="px-3 pb-3 mt-2 bg-body rounded shadow-sm">
            <span class="small">Inventory items</span>
            <div class="col">
                <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/facturacion/devoluciones/procs/getdevoluciondetalle.php?did=<?= $objDevolucion->devolucionId ?>"
                    data-pagination="false"
                    class="table-sm small col-9"
                >
                    <thead>
                        <tr>
                        <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="CODIGOINVENTARIO">Inventory item</th>
                            <th data-field="MARCA">Brand</th>
                            <th data-field="MODELO">Model</th>
                            <th data-field="MSRP" data-width="80" data-align="right" data-formatter="msrpFormatter">MSRP</th>
                            <th data-field="DESCRIPCION" data-formatter="detDescripcionFormatter">Description</th>
                            <th data-field="TIPODEGARANTIA">Warranty</th>
                            <th data-field="PRECIO" data-width="80" data-align="right" data-formatter="detPrecioFormatter">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-3 pb-3 mt-2 bg-body rounded shadow-sm">
            <span class="small">Services and other products</span>
            <div class="col">
                <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/facturacion/devoluciones/procs/getdevolucionotrodetalle.php?did=<?= $objDevolucion->devolucionId ?>"
                    data-pagination="false"
                    class="table-sm small col-9"
                >
                    <thead>
                        <tr>
                        <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="PRODUCTOCODIGO">Code</th>
                            <th data-field="MARCA">Brand</th>
                            <th data-field="MODELO">Model</th>
                            <th data-field="DESCRIPCION">Description</th>
                            <th data-field="PRECIO" data-width="80" data-align="right" data-formatter="detPrecioFormatter">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-3 py-1 mt-2 bg-body rounded shadow-sm">
            <div class="row justify-content-end">
                <div class="col-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-140px">Total before taxes</span>
                        <input type="text" id="totalantesdeimpuesto" name="totalantesdeimpuesto" class="form-control form-control-sm text-end" value="<?= "$ " . number_format($objDevolucion->totalAntesDeImpuesto, 2) ?>" readonly>
                    </div>
                    <div class="mt-1 input-group input-group-sm">
                        <span class="input-group-text width-140px">+ Sales taxes <?= number_format($objDevolucion->impuestoPorcentaje, 2) ?>%</span>
                        <input type="text" id="impuesto" name="impuesto" class="form-control form-control-sm text-end" value="<?= "$ " . number_format($objDevolucion->impuesto, 2) ?>" readonly>
                        <input type="hidden" id="impuestoporcentaje" name="impuestoporcentaje" value="<?= number_format($objDevolucion->impuestoPorcentaje, 2) ?>">
                    </div>
                    <div class="mt-1 input-group input-group-sm">
                        <span class="input-group-text width-140px">Total + Taxes</span>
                        <input type="text" id="totalmasimpuesto" name="totalmasimpuesto" class="form-control form-control-sm text-end" value="<?= "$ " . number_format($objDevolucion->totalConImpuesto, 2) ?>" readonly>
                    </div>
                    <div class="mt-1 input-group input-group-sm">
                        <span class="input-group-text width-140px">- Finance taxes</span>
                        <input type="text" id="impuestofinanciera" name="impuestofinanciera" class="form-control form-control-sm text-end" value="<?= "$ " . number_format($objDevolucion->impuestoFinanciera, 2) ?>" readonly>
                    </div>
                    <div class="mt-1 input-group input-group-sm">
                        <span class="input-group-text width-140px">TOTAL</span>
                        <input type="text" id="totalFinal" name="totalFinal" class="form-control form-control-sm text-end" value="<?= "$ " . number_format($objDevolucion->totalFinal, 2) ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-3 pb-3 mt-2 bg-body rounded shadow-sm">
            <span class="small">Payment</span>
            <div class="col">
                <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/facturacion/devoluciones/procs/getdevolucionpagos.php?did=<?= $objDevolucion->devolucionId ?>"
                    data-pagination="false"
                    data-show-footer="true"
                    class="table-sm small col-9"
                >
                    <thead>
                        <tr>
                        <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="TIPODEPAGO">Form of payment</th>
                            <th data-field="FINANCIERA">Financial entity</th>
                            <th data-field="CONTRATOFINANCIERA">Contract number</th>
                            <th data-field="NUMERORECIBOCHEQUE" data-footer-formatter="pagFooterLabelFormatter">Card Receipt/Check #</th>
                            <th data-field="MONTO" data-width="80" data-align="right" data-formatter="pagMontoFormatter" data-footer-formatter="pagTotalMontoFormatter">Amount</th>
                            <th data-field="IMPUESTO" data-width="80" data-align="right" data-formatter="pagImpuestoFormatter" data-footer-formatter="pagTotalImpuestoFormatter">Taxes</th>
                            <th data-field="TOTAL" data-width="80" data-align="right" data-formatter="pagTotalFormatter" data-footer-formatter="pagTotalFinalFormatter">Amount+Taxes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>
            </div>
        </div>
    
        <div class="px-3 py-1 mt-2 mb-5">
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
                data-url="./mods/facturacion/devoluciones/procs/getdevolucionestados.php?did=<?= $objDevolucion->devolucionId ?>"
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

<div class="modal fade small" id="modalConfirmarEliminar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $textoEliminarAnular ?> document</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Confirm that you are going to <?= strtolower($textoEliminarAnular) ?> this document.
                <?php if (strtolower($textoEliminarAnular) == "cancel" ): ?>
                    <br>The stock will be increased for each inventory item.
                <?php endif ?>
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
                The document will take a correlative number.<br>
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

<div class="modal fade small" id="modalSeleccionarFacturaSustituta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select substitute invoice - <?= $objDevolucion->sucursalNombre ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="facturasSustitutasToolbar">
                    <span class="label-text">Search</span>
                    <div>
                        <div class="row">
                            <div class="col-auto">
                                <div class="input-group input-group-sm" style="width: 180px;">
                                    <span class="input-group-text width-90px">Correlative</span>
                                    <input type="text" id="bfscorrelativo" name="bfscorrelativo" class="form-control form-control-sm" maxlength="7">
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="input-group input-group-sm" style="width: 210px;">
                                    <span class="input-group-text width-90px">Date from</span>
                                    <input type="date" id="bfsfechadesde" name="bfsfechadesde" class="form-control form-control-sm" value="<?= $fechaDesdeString ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-auto">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text width-90px">Customer</span>
                                    <input type="text" id="bfscliente" name="bfscliente" class="form-control form-control-sm" maxlength="100">
                                </div>
                            </div>
                            <div class="col-auto">
                                <div>
                                    <button id="bfsbtnreset" class="btn btn-sm btn-secondary"><i class="bi bi-eraser"></i> Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <table
                        id="tablefacturassustitutas"
                        data-toggle="table"
                        data-url="./mods/facturacion/devoluciones/procs/getfacturassustitutas.php"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-search="false"
                        data-search-align="left"
                        data-show-refresh="false"
                        data-show-button-text="true"
                        data-toolbar=".facturasSustitutasToolbar"
                        data-page-list="[10, 25, 50, 100]"
                        data-page-size="10"
                        data-query-params="facturasSustitutasCustomParams"
                        data-icon-size="sm"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="FACTURAID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="FECHA">Document date</th>
                            <th data-field="NUMEROFACTURA">Correlative</th>
                            <th data-field="CLIENTE">Customer</th>
                            <th data-field="TOTALFINAL" data-width="80" data-align="right" data-formatter="totalFormatter">Total</th>
                            <th data-field="NOMBREDEESTADO" data-formatter="estadoFormatter" data-align="center">Status</th>
                            <th data-field="operate" data-formatter="facturasSustitutasOperateFormatter" data-events="facturasSustitutasOperateEvents">Actions</th>
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

<div class="modal fade small" id="modalConfirmarFSustituta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Save sustitue invoice data</h1>
            </div>
            <div class="modal-body">
                Confirm that you are saving the sustitute invoice data.
                <br>It is not possible to undo this operation after saving it.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btnconfirmafsustituta"><i class="bi bi-floppy2"></i> Yes, save</button>
                <button type="button" class="btn btn-secondary btn-sm" id="btncancelafsustituta"><i class="bi bi-x-octagon"></i> No, return</button>
            </div>
        </div>
    </div>
</div>

<?php 
    } // else de mostrar contenido por acceso a opción
?>