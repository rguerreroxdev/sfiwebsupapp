<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("FAC", "03.03.01");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        $facturaId = isset($_GET["fid"]) ? $_GET["fid"] : -1;
        $facturaId = is_numeric($facturaId) ? $facturaId : -1;

        require_once("inc/class/Facturas.php");
        $objFactura = new Facturas($conn);
        $objFactura->getById($facturaId);

        // Si el registro no existe, retornar a listado
        if ($objFactura->facturaId == -1)
        {
            echo ("<script>window.location.href='?mod=facturacion&opc=facturacion'</script>");
            exit();
        }

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("03.03.01");
        $accesoEditar = in_array("03.03.01.02", $accesos) ? "" : " disabled";
        $accesoEliminar = in_array("03.03.01.03", $accesos) ? "" : " disabled";
        $accesoCerrar = in_array("03.03.01.04", $accesos) ? "" : " disabled";
        $accesoProcesar = in_array("03.03.01.05", $accesos) ? "" : " disabled";
        $accesoAnular = in_array("03.03.01.06", $accesos) ? "" : " disabled";

        $textoCerrarAbrir = "";
        $textoEliminarAnular = "";
        switch (strtoupper($objFactura->estado))
        {
            case 'FOR':
                $accesoProcesar = "disabled";
                $textoCerrarAbrir = "Close";
                $textoEliminarAnular = $objFactura->correlativo == -1 ? "Delete" : "Cancel";
                break;
            case 'CER':
                $accesoEditar = "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = $objFactura->correlativo == -1 ? "Delete" : "Cancel";
                break;
            case 'PRO':
                $accesoEditar = "disabled";
                $accesoEliminar = $accesoAnular;
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
            case 'DEV':
                $accesoEditar = "disabled";
                $accesoEliminar = "disabled";
                $accesoCerrar = "disabled";
                $accesoProcesar = "disabled";
                $textoCerrarAbrir = "Open";
                $textoEliminarAnular = "Cancel";
                break;
        }

        // Para obtener el total de pagos
        require_once("inc/class/FacturasPagos.php");
        $objFacturaPagos = new FacturasPagos($conn);
        $totalPagos = $objFacturaPagos->getTotalDeMontosPorFactura($facturaId);
?>

<h3>Invoices - Document data</h3>

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
                        <label class="input-group-text width-95px" for="sucursalnombre">Store</label>
                        <input type="text" id="sucursalnombre" name="sucursalnombre" class="form-control form-control-sm text-center" value="<?= $objFactura->sucursalNombre ?>" readonly>
                        <input type="hidden" id="sucursalid" name="sucursalid" class="form-control form-control-sm text-center" value="<?= $objFactura->sucursalId ?>">
                        <input type="hidden" id="sucursaldireccion" name="sucursaldireccion" class="form-control form-control-sm text-center" value="<?= $objFactura->sucursalDireccion ?>">
                        <input type="hidden" id="sucursaldireccioncomplemento" name="sucursaldireccioncomplemento" class="form-control form-control-sm text-center" value="<?= $objFactura->sucursalDireccionComplemento ?>">
                        <input type="hidden" id="sucursalcodigopostal" name="sucursalcodigopostal" class="form-control form-control-sm text-center" value="<?= $objFactura->sucursalCodigoPostal ?>">
                        <input type="hidden" id="sucursaltelefono" name="sucursaltelefono" class="form-control form-control-sm text-center" value="<?= $objFactura->sucursalTelefono ?>">
                        <input type="hidden" id="sucursaltelefonoservicio" name="sucursaltelefonoservicio" class="form-control form-control-sm text-center" value="<?= $objFactura->sucursalTelefonoServicio ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Correlative</span>
                        <input type="text" id="correlativocompuesto" name="correlativocompuesto" class="form-control form-control-sm text-center" value="<?= $objFactura->prefijoDeCorrelativo . "-" . $objFactura->correlativo ?>" readonly>
                        <input type="hidden" id="prefijodecorrelativo" name="prefijodecorrelativo" class="form-control form-control-sm text-center" value="<?= $objFactura->prefijoDeCorrelativo ?>">
                        <input type="hidden" id="correlativo" name="correlativo" class="form-control form-control-sm text-center" value="<?= $objFactura->correlativo ?>">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Status</span>
                        <input type="text" id="nombredeestado" name="nombredeestado" class="form-control form-control-sm text-center" value="<?= $objFactura->nombreDeEstado ?>" readonly>
                        <input type="hidden" id="estado" name="estado" value="<?= $objFactura->estado ?>">
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-8 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Customer<span class="text-danger">&nbsp;*</span></span>
                        <input type="text" id="cliente" name="cliente" class="form-control form-control-sm" value="<?= $objFactura->clienteCodigo . " - " . $objFactura->clienteNombre ?>" readonly>
                        <input type="hidden" id="clienteid" name="clienteid" value="<?= $objFactura->clienteId ?>">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-170px">Is previous customer</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="esclienteprevio" name="esclienteprevio" class="form-check-input mt-0" value=""<?= $objFactura->esClientePrevio == 1 ? " checked" : "" ?> disabled>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Address</span>
                        <input type="text" id="clientedireccion" name="clientedireccion" class="form-control form-control-sm" value="<?= $objFactura->clienteDireccion ?>" maxlength="100" readonly>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Address cont.</span>
                        <input type="text" id="clientedireccioncomplemento" name="clientedireccioncomplemento" class="form-control form-control-sm" value="<?= $objFactura->clienteDireccionComplemento ?>" maxlength="100" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-3 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-90px">ZIP code</span>
                        <input type="text" id="clientecodigopostal" name="clientecodigopostal" class="form-control form-control-sm" value="<?= $objFactura->clienteCodigoPostal ?>" maxlength="5" readonly>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-90px">Phone</span>
                        <input type="text" id="clientetelefono" name="clientetelefono" class="form-control form-control-sm" value="<?= $objFactura->clienteTelefono ?>" maxlength="50" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Email</span>
                        <input type="email" id="clientecorreo" name="clientecorreo" class="form-control form-control-sm" value="<?= $objFactura->clienteCorreoElectronico ?>" maxlength="100" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-8 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Referral person</span>
                        <input type="text" id="personadereferencia" name="personadereferencia" class="form-control form-control-sm" value="<?= $objFactura->personaDeReferencia ?>" maxlength="100" readonly>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-90px" for="plataformadereferencia">Platform</label>
                        <input type="text" id="plataformadereferencia" name="plataformadereferencia" class="form-control form-control-sm" value="<?= $objFactura->plataformaDeReferencia ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Document date</span>
                        <input type="text" id="fechadeemision" name="fechadeemision" class="form-control form-control-sm" value="<?= $objFactura->fecha ?>" readonly>
                    </div>
                </div>
                <div class="col-md-7 col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Salesperson</span>
                        <input type="text" id="vendedor" name="vendedor" class="form-control form-control-sm" value="<?= $objFactura->usuarioVendedorNombre ?>" readonly>
                        <input type="hidden" id="vendedorid" name="vendedorid" class="form-control form-control-sm" value="<?= $objFactura->usuarioIdVendedor ?>">
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="formaderetiro">Pickup type<span class="text-danger">&nbsp;*</span></label>
                        <input type="text" id="formaderetiro" name="formaderetiro" class="form-control form-control-sm" value="<?= $objFactura->formaDeRetiro ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Pickup date<span class="text-danger">&nbsp;*</span></span>
                        <input type="text" id="fechaderetiro" name="fechaderetiro" class="form-control form-control-sm" value="<?= $objFactura->fechaDeRetiro ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-170px">Installation needed</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="agregarinstalacion" name="agregarinstalacion" class="form-check-input mt-0" value=""<?= $objFactura->agregarInstalacion == 1 ? " checked" : "" ?> disabled>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-170px">Accesories needed</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="agregaraccesorios" name="agregaraccesorios" class="form-check-input mt-0" value=""<?= $objFactura->agregarAccesorios == 1 ? " checked" : "" ?> disabled>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-170px">Don't calculate taxes</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="nocalcularimpuesto" name="nocalcularimpuesto" class="form-check-input mt-0" value=""<?= $objFactura->noCalcularImpuesto == 1 ? " checked" : "" ?> disabled>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-11 col-lg-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Account notes</span>
                        <textarea id="notas" name="notas" class="form-control form-control-sm" maxlength="200" value="" readonly><?= $objFactura->notas ?></textarea>
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
                            <button class="btn btn-sm btn-secondary" id="btnimprimirfactura" style="min-width: 75px;"><i class="bi bi-printer"></i> Print invoice</button>
                            <button class="btn btn-sm btn-secondary" id="btnimprimirhold" style="min-width: 75px;"><i class="bi bi-printer"></i> Print hold</button>
                            <button class="btn btn-sm btn-danger" id="btneliminar" style="min-width: 75px;"<?= $accesoEliminar ?>>
                                <?php if ($textoEliminarAnular == "Delete"): ?>
                                    <i class="bi bi-trash"></i>
                                <?php else: ?>
                                    <i class="bi bi-file-earmark-break"></i>
                                <?php endif; ?>
                                <?= $textoEliminarAnular ?>
                            </button>
                        </div>
                        <div class="col-auto"<?php if ($objFactura->estado == "PRO" || $objFactura->estado == "ANU" || $objFactura->estado == "DEV"): ?> style="display: none;"<?php endif; ?>>
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

        <div class="px-3 pb-3 mt-2 bg-body rounded shadow-sm">
            <span class="small">Inventory items</span>
            <div class="col">
                <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/facturacion/facturacion/procs/getfacturadetalle.php?fid=<?= $objFactura->facturaId ?>"
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
                    data-url="./mods/facturacion/facturacion/procs/getfacturaotrodetalle.php?fid=<?= $objFactura->facturaId ?>"
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
                        <input type="text" id="totalantesdeimpuesto" name="totalantesdeimpuesto" class="form-control form-control-sm text-end" value="<?= "$ " . number_format($objFactura->totalAntesDeImpuesto, 2) ?>" readonly>
                    </div>
                    <div class="mt-1 input-group input-group-sm">
                        <span class="input-group-text width-140px">+ Sales taxes <?= number_format($objFactura->impuestoPorcentaje, 2) ?>%</span>
                        <input type="text" id="impuesto" name="impuesto" class="form-control form-control-sm text-end" value="<?= "$ " . number_format($objFactura->impuesto, 2) ?>" readonly>
                        <input type="hidden" id="impuestoporcentaje" name="impuestoporcentaje" value="<?= number_format($objFactura->impuestoPorcentaje, 2) ?>">
                    </div>
                    <div class="mt-1 input-group input-group-sm">
                        <span class="input-group-text width-140px">Total + Taxes</span>
                        <input type="text" id="totalmasimpuesto" name="totalmasimpuesto" class="form-control form-control-sm text-end" value="<?= "$ " . number_format($objFactura->totalConImpuesto, 2) ?>" readonly>
                    </div>
                    <div class="mt-1 input-group input-group-sm">
                        <span class="input-group-text width-140px">- Finance taxes</span>
                        <input type="text" id="impuestofinanciera" name="impuestofinanciera" class="form-control form-control-sm text-end" value="<?= "$ " . number_format($objFactura->impuestoFinanciera, 2) ?>" readonly>
                    </div>
                    <div class="mt-1 input-group input-group-sm">
                        <span class="input-group-text width-140px">TOTAL</span>
                        <input type="text" id="totalFinal" name="totalFinal" class="form-control form-control-sm text-end" value="<?= "$ " . number_format($objFactura->totalFinal, 2) ?>" readonly>
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
                    data-url="./mods/facturacion/facturacion/procs/getfacturapagos.php?fid=<?= $objFactura->facturaId ?>"
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

            <input type="hidden" id="totalpagos" name="totalpagos" class="form-control form-control-sm text-end" value="<?= "$ " . number_format($totalPagos, 2) ?>" readonly>

        </div>
    
        <div class="px-3 py-1 mt-2 mb-5">
        </div>

        <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
        <input type="hidden" id="fid" name="fid" value="<?= $objFactura->facturaId ?>">
        <input type="hidden" id="estadoActual" name="estadoActual" value="<?= $objFactura->estado ?>">
    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation date</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objFactura->fechaCreacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objFactura->usuarioCreo ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objFactura->fechaModificacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objFactura->usuarioModifica ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 mt-2 bg-body rounded shadow-sm">
            <table
                id="tabledatos"
                data-toggle="table"
                data-url="./mods/facturacion/facturacion/procs/getfacturacionestados.php?fid=<?= $objFactura->facturaId ?>"
                class="table-sm small"
            >
                <thead>
                    <tr>
                        <th data-field="FACTURAESTADOID" data-visible="false">ID</th>
                        <th data-field="FACTURAID" data-visible="false">FID</th>
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
                <div<?php if ($objFactura->estado != "PRO") { echo ' style="display: none;"'; } ?>>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-80px">Reason</span>
                        <textarea id="razondeanulacion" name="razondeanulacion" class="form-control form-control-sm" maxlength="200" value=""></textarea>
                    </div>                    
                </div>
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
                <?= $objFactura->estado == "FOR" ? "<br>A closed document can not be edited." : "" ?>
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
                Stocks will be reduced to zero.
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