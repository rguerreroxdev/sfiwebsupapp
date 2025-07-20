<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);

    if (isset($_GET["did"]))
    {
        // Se está editando
        $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("FAC", "03.03.02.02");
    }
    else
    {
        // Se está creando
        $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("FAC", "03.03.02.01");
    }

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

        // Antes de continuar, se verifica que un documento existente solo se puede editar en estado formulado
        if ($objDevolucion->devolucionId != -1 && $objDevolucion->estado != "FOR")
        {
            echo('<script>window.location.href="?mod=facturacion&opc=devoluciones&subopc=verdevolucion&did=' . $objDevolucion->devolucionId . '";</script>');
            exit();
        }

        $filasDetalle = "";
        $filasServiciosOtrosProductos = "";
        $filasPagos = "";
        if ($objDevolucion->devolucionId == -1)
        {
            $objDevolucion->iniciarDatosParaNuevoRegistro();
        }
        else
        {
            require_once("mods/facturacion/devoluciones/procs/getfilasdetalle.php");
            $filasDetalle = getFilasDetalle($conn, $objDevolucion->devolucionId);

            require_once("mods/facturacion/devoluciones/procs/getfilasservicios.php");
            $filasServiciosOtrosProductos = getFilasServiciosOtrosProductos($conn, $objDevolucion->devolucionId);

            require_once("mods/facturacion/devoluciones/procs/getfilaspagos.php");
            $filasPagos = getFilasPagos($conn, $objDevolucion->devolucionId);
        }

        // Para obtener el total de pagos
        require_once("inc/class/FacturasPagos.php");
        $objFacturaPagos = new FacturasPagos($conn);
        $totalPagos = $objFacturaPagos->getTotalDePagosPorFactura($devolucionId);

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

        // Para fecha modales de búsquedas
        $fechaDesde = new DateTime();
        $fechaDesde->sub(new DateInterval('P3M'));
        $fechaDesde->modify('first day of this month');
        $fechaDesdeString = $fechaDesde->format('Y-m-d');

        // Para obtener el total de pagos
        require_once("inc/class/FacDevolucionesPagos.php");
        $objDevolucionPagos = new FacDevolucionesPagos($conn);
        $totalPagosMontos = $objDevolucionPagos->getTotalDeMontosPorDevolucion($devolucionId);
        $totalPagosImpuestos = $objDevolucionPagos->getTotalDeImpuestosPorDevolucion($devolucionId);
        $totalPagosTotales = $objDevolucionPagos->getTotalDePagosPorDevolucion($devolucionId);
?>

<h3>Returns - Credit memo - Document registration</h3>

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
                        <button class="btn btn-outline-secondary" type="button" id="btnfacturadevuelta"><i class="bi bi-search"></i></button>
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
                        <input type="date" id="fechadeemision" name="fechadeemision" class="form-control form-control-sm" value="<?= $objDevolucion->fechaDevoluciondt->format("Y-m-d") ?>" required>
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
                        <textarea id="notas" name="notas" class="form-control form-control-sm" maxlength="200"><?= $objDevolucion->concepto ?></textarea>
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

        <div class="px-3 pb-3 mt-2 bg-body rounded shadow-sm">
            <span class="small">Inventory items</span>
            <table id="tablaDetalle">
                <thead>
                    <tr class="small border-bottom">
                        <th class="width-130px">Inventory number</th>
                        <th class="width-130px">Brand</th>
                        <th class="width-130px">Model</th>
                        <th class="width-130px">MSRP</th>
                        <th class="width-300px">Description</th>
                        <th class="width-130px">Warranty</th>
                        <th class="width-130px">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Filas de ítems -->
                    <?= $filasDetalle ?>
                </tbody>
            </table>
        </div>

        <div class="px-3 pb-3 mt-2 bg-body rounded shadow-sm">
            <span class="small">Services and other products</span>
            <table id="tablaServiciosOtrosProductos">
                <thead>
                    <tr class="small border-bottom">
                        <th class="width-130px">Code</th>
                        <th class="width-130px">Brand</th>
                        <th class="width-260px">Model</th>
                        <th class="width-430px">Description</th>
                        <th class="width-130px">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Filas de ítems -->
                    <?= $filasServiciosOtrosProductos ?>
                </tbody>
            </table>
        </div>

        <div class="px-3 py-1 mt-2 bg-body rounded shadow-sm">
            <table id="tablaFormato">
                <thead>
                    <tr class="small">
                        <th class="width-130px"></th>
                        <th class="width-130px"></th>
                        <th class="width-130px"></th>
                        <th class="width-130px"></th>
                        <th class="width-300px"></th>
                        <th class="width-130px"></th>
                        <th class="width-130px"></th>
                        <th class="width-90px"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7">
                            <div class="row justify-content-end">
                                <div class="col-auto">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text width-140px">Total before taxes</span>
                                        <input type="text" id="totalantesdeimpuesto" name="totalantesdeimpuesto" class="form-control form-control-sm text-end" value="<?= number_format($objDevolucion->totalAntesDeImpuesto, 2, ".", "") ?>" readonly>
                                    </div>
                                    <div class="mt-1 input-group input-group-sm">
                                        <span class="input-group-text width-140px">+ Sales taxes&nbsp;<span id="porcentajevisto"><?= number_format($objDevolucion->impuestoPorcentaje, 2, ".", "") ?></span>%</span>
                                        <input type="text" id="impuesto" name="impuesto" class="form-control form-control-sm text-end" value="<?= number_format($objDevolucion->impuesto, 2, ".", "") ?>" readonly>
                                        <input type="hidden" id="impuestoporcentaje" name="impuestoporcentaje" value="<?= number_format($objDevolucion->impuestoPorcentaje, 2, ".", "") ?>">
                                    </div>
                                    <div class="mt-1 input-group input-group-sm">
                                        <span class="input-group-text width-140px">Total + Taxes</span>
                                        <input type="text" id="totalconimpuesto" name="totalconimpuesto" class="form-control form-control-sm text-end" value="<?= number_format($objDevolucion->totalConImpuesto, 2, ".", "") ?>" readonly>
                                    </div>
                                    <div class="mt-1 input-group input-group-sm">
                                        <span class="input-group-text width-140px">- Finance taxes</span>
                                        <input type="text" id="impuestofinanciera" name="impuestofinanciera" class="form-control form-control-sm text-end" value="<?= number_format($objDevolucion->impuestoFinanciera, 2, ".", "") ?>" readonly>
                                        <input type="hidden" id="impuestofinancierareal" name="impuestofinancierareal" value="<?= number_format($objDevolucion->impuestoFinanciera, 3, ".", "") ?>">
                                    </div>
                                    <div class="mt-1 input-group input-group-sm">
                                        <span class="input-group-text width-140px">TOTAL</span>
                                        <input type="text" id="totalfinal" name="totalfinal" class="form-control form-control-sm text-end" value="<?= number_format($objDevolucion->totalFinal, 2, ".", "") ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="px-3 pb-3 mt-2 bg-body rounded shadow-sm">
            <span class="small">Payment</span>
            <div class="pt-4">
                <table class="" id="tablaPagos">
                    <thead>
                        <tr class="small border-bottom">
                            <th class="width-130px">Form of payment</th>
                            <th class="width-170px">Financial entity</th>
                            <th class="width-130px">Contract number</th>
                            <th class="width-170px">Card Receipt/Check #</th>
                            <th class="width-130px">Amount</th>
                            <th class="width-130px">Taxes</th>
                            <th class="width-130px">Amount+Taxes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filas de pagos -->
                        <?= $filasPagos ?>
                    </tbody>
                </table>
                <table id="tablaFormatoPagos">
                    <thead>
                        <tr class="small border-bottom">
                            <th class="width-130px"></th>
                            <th class="width-170px"></th>
                            <th class="width-130px"></th>
                            <th class="width-170px"></th>
                            <th class="width-130px"></th>
                            <th class="width-130px"></th>
                            <th class="width-130px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <div class="row mt-1 justify-content-end">
                                    <div class="col-auto">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">TOTALS</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mt-1 input-group input-group-sm">
                                    <input type="text" id="totalpagosmonto" name="totalpagosmonto" class="form-control form-control-sm text-end" value="<?= number_format($totalPagosMontos, 2, ".", "") ?>" readonly>
                                </div>
                            </td>
                            <td>
                                <div class="mt-1 input-group input-group-sm">
                                    <input type="text" id="totalpagosimpuesto" name="totalpagosimpuesto" class="form-control form-control-sm text-end" value="<?= number_format($totalPagosImpuestos, 2, ".", "") ?>" readonly>
                                </div>
                            </td>
                            <td>
                                <div class="mt-1 input-group input-group-sm">
                                    <input type="text" id="totalpagos" name="totalpagos" class="form-control form-control-sm text-end" value="<?= number_format($totalPagosTotales, 2, ".", "") ?>" readonly>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
        <input type="hidden" id="did" name="did" value="<?= $objDevolucion->devolucionId ?>">
        
        <div class="row mt-2 justify-content-between">
            <div class="col">
                <span class="fst-italic small"><span class="text-danger">*</span> -> Required data</span>
            </div>
        </div>

        <div class="mt-4"></div>
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
                    data-url="./mods/facturacion/devoluciones/procs/getdevolucionestados.php?fid=<?= $objDevolucion->devolucionId ?>"
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

<div class="modal fade small" id="modalNoConfig" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Credit memo registration</h1>
            </div>
            <div class="modal-body">
                This store has no settings defined for creating invoices, so credit memos cannot be created.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade small" id="modalSeleccionarFacturaDevuelta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select returned invoice - <span id="fdevueltanombresucursal"></span></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="facturasDevueltasToolbar">
                    <span class="label-text">Search</span>
                    <div>
                        <div class="row">
                            <div class="col-auto">
                                <div class="input-group input-group-sm" style="width: 180px;">
                                    <span class="input-group-text width-90px">Correlative</span>
                                    <input type="text" id="bfdcorrelativo" name="bfdcorrelativo" class="form-control form-control-sm" maxlength="7">
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="input-group input-group-sm" style="width: 210px;">
                                    <span class="input-group-text width-90px">Date from</span>
                                    <input type="date" id="bfdfechadesde" name="bfdfechadesde" class="form-control form-control-sm" value="<?= $fechaDesdeString ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-auto">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text width-90px">Customer</span>
                                    <input type="text" id="bfdcliente" name="bfdcliente" class="form-control form-control-sm" maxlength="100">
                                </div>
                            </div>
                            <div class="col-auto">
                                <div>
                                    <button id="bfdbtnreset" class="btn btn-sm btn-secondary"><i class="bi bi-eraser"></i> Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <table
                        id="tablefacturasdevueltas"
                        data-toggle="table"
                        data-url="./mods/facturacion/devoluciones/procs/getfacturasdevueltas.php"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-search="false"
                        data-search-align="left"
                        data-show-refresh="false"
                        data-show-button-text="true"
                        data-toolbar=".facturasDevueltasToolbar"
                        data-page-list="[10, 25, 50, 100]"
                        data-page-size="10"
                        data-query-params="facturasDevueltasCustomParams"
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
                            <th data-field="operate" data-formatter="facturasDevueltasOperateFormatter" data-events="facturasDevueltasOperateEvents">Actions</th>
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