<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);

    if (isset($_GET["fid"]))
    {
        // Se está editando
        $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("FAC", "03.03.01.02");
    }
    else
    {
        // Se está creando
        $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("FAC", "03.03.01.01");
    }

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

        // Antes de continuar, se verifica que un documento existente solo se puede editar en estado formulado
        if ($objFactura->facturaId != -1 && $objFactura->estado != "FOR")
        {
            echo('<script>window.location.href="?mod=facturacion&opc=facturacion&subopc=verfactura&fid=' . $objFactura->facturaId . '";</script>');
            exit();
        }

        $filasDetalle = "";
        $filasServiciosOtrosProductos = "";
        $filasPagos = "";
        if ($objFactura->facturaId == -1)
        {
            $objFactura->iniciarDatosParaNuevoRegistro();

            $objFactura->usuarioIdVendedor = $_SESSION["usuarioId"];
            $objFactura->usuarioVendedor = $_SESSION["usuario"];
        }
        else
        {
            require_once("mods/facturacion/facturacion/procs/getfilasdetalle.php");
            $filasDetalle = getFilasDetalle($conn, $objFactura->facturaId);

            require_once("mods/facturacion/facturacion/procs/getfilasservicios.php");
            $filasServiciosOtrosProductos = getFilasServiciosOtrosProductos($conn, $objFactura->facturaId);

            require_once("mods/facturacion/facturacion/procs/getfilaspagos.php");
            $filasPagos = getFilasPagos($conn, $objFactura->facturaId);
        }

        // Para obtener el total de pagos
        require_once("inc/class/FacturasPagos.php");
        $objFacturaPagos = new FacturasPagos($conn);
        $totalPagos = $objFacturaPagos->getTotalDePagosPorFactura($facturaId);

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
            if ($objFactura->sucursalId != -1)
            {
                $selected = $objFactura->sucursalId == $valor ? " selected" : "";
            }
            
            $listaDeSucursalesOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        // Para crear el combo de Plataforma de Referencia
        require_once("inc/class/PlataformasDeReferencia.php");
        $objPlataformasDeReferencia = new PlataformasDeReferencia($conn);
        $listaDePlataformasDeReferencia = $objPlataformasDeReferencia->getListaParaCombo("SELECT");

        $listaDePlataformasOptions = "";
        foreach ($listaDePlataformasDeReferencia as $plataformasDeReferencia)
        {
            $texto = $plataformasDeReferencia["NOMBRE"];
            $valor = $plataformasDeReferencia["PLATAFORMADEREFERENCIAID"] == -1 ? "" : $plataformasDeReferencia["PLATAFORMADEREFERENCIAID"];
            
            $selected = $objFactura->plataformaDeReferenciaId == $valor ? " selected" : "";

            $listaDePlataformasOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        // Para crear el combo de formas de retiro
        $listaDeFormasDeRetiro = $objFactura->getFormasDeRetiroParaCombo("SELECT");

        $listaDeFormasDeRetiroOptions = "";
        foreach ($listaDeFormasDeRetiro as $formaDeRetiro)
        {
            $texto = $formaDeRetiro["NOMBRE"];
            $valor = $formaDeRetiro["FORMADERETIROID"];
            
            $selected = $objFactura->formaDeRetiroId == $valor ? " selected" : "";

            $listaDeFormasDeRetiroOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        // Para crear el combo de tipos de pago
        require_once("inc/class/TiposDePago.php");
        $objTiposDePago = new TiposDePago($conn);
        $listaDeTiposDePago = $objTiposDePago->getListaParaCombo($objFactura->noCalcularImpuesto == 1, "SELECT");
        $listaDeTiposDePagoOptions = "";
        foreach ($listaDeTiposDePago as $tipoDePago)
        {
            $texto = $tipoDePago["NOMBRE"];
            $valor = $tipoDePago["TIPODEPAGOID"] == -1 ? "" : $tipoDePago["TIPODEPAGOID"];
            $listaDeTiposDePagoOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Para crear el combo de financieras
        require_once("inc/class/Financieras.php");
        $objFinancieras = new Financieras($conn);
        $listaDeFinancieras = $objFinancieras->getListaParaCombo("NONE");
        $listaDeFinancierasOptions = "";
        foreach ($listaDeFinancieras as $financiera)
        {
            $texto = $financiera["NOMBRE"];
            $valor = $financiera["FINANCIERAID"];
            $listaDeFinancierasOptions .= "
                <option value=\"$valor\">$texto</option>
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

        $conteoDeMarcas = 0;
        foreach ($listaDeMarcas as $marca)
        {
            if ($conteoDeMarcas == 1)
            {
                $listaDeMarcasOptions .= "
                <option value=\"-2\">- NO BRAND -</option>
            ";
            }

            $texto = $marca["NOMBRE"];
            $valor = $marca["MARCAID"] == -1 ? "" : $marca["MARCAID"];
            $listaDeMarcasOptions .= "
                <option value=\"$valor\">$texto</option>
            ";

            $conteoDeMarcas++;
        }

        // Para obtener el nombre del usuario vendedor
        if ($facturaId == -1)
        {
            require_once("inc/class/Usuario.php");
            $objUsuario = new Usuario($conn);
            $objUsuario->getById($objFactura->usuarioIdVendedor);
            $objFactura->usuarioVendedorNombre = $objUsuario->nombreCompleto;
        }
?>

<h3>Invoices - Document registration</h3>

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
                        <input type="hidden" id="sucursalnombre" name="sucursalnombre" class="form-control form-control-sm text-center" value="<?= $objFactura->sucursalNombre ?>">
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
                        <input type="text" id="codigocliente" name="codigocliente" class="form-control form-control-sm" oninput="buscarCliente(event)" style="max-width: 70px;" maxlength="7" value="<?= $objFactura->clienteCodigo ?>" required>
                        <input type="text" id="cliente" name="cliente" class="form-control form-control-sm" value="<?= $objFactura->clienteNombre ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="btncliente"><i class="bi bi-search"></i></button>
                        <input type="hidden" id="clienteid" name="clienteid" value="<?= $objFactura->clienteId ?>">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-170px">Is previous customer</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="esclienteprevio" name="esclienteprevio" class="form-check-input mt-0" value=""<?= $objFactura->esClientePrevio == 1 ? " checked" : "" ?>>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Address</span>
                        <input type="text" id="clientedireccion" name="clientedireccion" class="form-control form-control-sm" value="<?= $objFactura->clienteDireccion ?>" maxlength="100">
                    </div>
                </div>
                <div class="col-md-6 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Address cont.</span>
                        <input type="text" id="clientedireccioncomplemento" name="clientedireccioncomplemento" class="form-control form-control-sm" value="<?= $objFactura->clienteDireccionComplemento ?>" maxlength="100">
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-3 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-90px">ZIP code</span>
                        <input type="text" id="clientecodigopostal" name="clientecodigopostal" class="form-control form-control-sm" value="<?= $objFactura->clienteCodigoPostal ?>" maxlength="5">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-90px">Phone</span>
                        <input type="text" id="clientetelefono" name="clientetelefono" class="form-control form-control-sm" value="<?= $objFactura->clienteTelefono ?>" maxlength="50">
                    </div>
                </div>
                <div class="col-md-5 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Email</span>
                        <input type="email" id="clientecorreo" name="clientecorreo" class="form-control form-control-sm" value="<?= $objFactura->clienteCorreoElectronico ?>" maxlength="100">
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-8 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Referral person</span>
                        <input type="text" id="personadereferencia" name="personadereferencia" class="form-control form-control-sm" value="<?= $objFactura->personaDeReferencia ?>" maxlength="100">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-90px" for="plataformadereferencia">Platform<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="plataformadereferencia" name="plataformadereferencia" required>
                            <!-- Plataformas de referencia -->
                            <?= $listaDePlataformasOptions ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Document date<span class="text-danger">&nbsp;*</span></span>
                        <input type="date" id="fechadeemision" name="fechadeemision" class="form-control form-control-sm" value="<?= $objFactura->fechadt->format("Y-m-d") ?>" required>
                    </div>
                </div>
                <div class="col-md-7 col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Salesperson</span>
                        <input type="text" id="vendedor" name="vendedor" class="form-control form-control-sm" value="<?= $objFactura->usuarioVendedorNombre ?>" readonly>
                        <input type="hidden" id="vendedorid" name="vendedorid" class="form-control form-control-sm" value="<?= $objFactura->usuarioIdVendedor ?>">
                        <button class="btn btn-outline-secondary" type="button" id="btnvendedor"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="formaderetiro">Pickup type<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="formaderetiro" name="formaderetiro" required>
                            <!-- Formas de retiro -->
                            <?= $listaDeFormasDeRetiroOptions ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Pickup date<span class="text-danger">&nbsp;*</span></span>
                        <input type="date" id="fechaderetiro" name="fechaderetiro" class="form-control form-control-sm" value="<?= $objFactura->fechaDeRetirodt->format("Y-m-d") ?>" required>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-170px">Installation needed</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="agregarinstalacion" name="agregarinstalacion" class="form-check-input mt-0" value=""<?= $objFactura->agregarInstalacion == 1 ? " checked" : "" ?>>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-170px">Accesories needed</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="agregaraccesorios" name="agregaraccesorios" class="form-check-input mt-0" value=""<?= $objFactura->agregarAccesorios == 1 ? " checked" : "" ?>>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-170px">Don't calculate taxes</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="nocalcularimpuesto" name="nocalcularimpuesto" class="form-check-input mt-0" value=""<?= $objFactura->noCalcularImpuesto == 1 ? " checked" : "" ?>>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-11 col-lg-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Account notes</span>
                        <textarea id="notas" name="notas" class="form-control form-control-sm" maxlength="200"><?= $objFactura->notas ?></textarea>
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
                        <th class="width-90px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Filas de ítems -->
                    <?= $filasDetalle ?>
                </tbody>
            </table>
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-success" type="button" id="btnagregarfiladetalle" onclick="agregarFilaInventario()"><i class="bi bi-plus-circle"></i> Add item</button>
            </div>
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
                        <th class="width-90px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Filas de ítems -->
                    <?= $filasServiciosOtrosProductos ?>
                </tbody>
            </table>
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-success" type="button" id="btnagregarfilaservicio" onclick="agregarFilaServicio()"><i class="bi bi-plus-circle"></i> Add item</button>
            </div>
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
                                        <input type="text" id="totalantesdeimpuesto" name="totalantesdeimpuesto" class="form-control form-control-sm text-end" value="<?= number_format($objFactura->totalAntesDeImpuesto, 2, ".", "") ?>" readonly>
                                    </div>
                                    <div class="mt-1 input-group input-group-sm">
                                        <span class="input-group-text width-140px">+ Sales taxes&nbsp;<span id="porcentajevisto"><?= number_format($objFactura->impuestoPorcentaje, 2, ".", "") ?></span>%</span>
                                        <input type="text" id="impuesto" name="impuesto" class="form-control form-control-sm text-end" value="<?= number_format($objFactura->impuesto, 2, ".", "") ?>" readonly>
                                        <input type="hidden" id="impuestoporcentaje" name="impuestoporcentaje" value="<?= number_format($objFactura->impuestoPorcentaje, 2, ".", "") ?>">
                                    </div>
                                    <div class="mt-1 input-group input-group-sm">
                                        <span class="input-group-text width-140px">Total + Taxes</span>
                                        <input type="text" id="totalconimpuesto" name="totalconimpuesto" class="form-control form-control-sm text-end" value="<?= number_format($objFactura->totalConImpuesto, 2, ".", "") ?>" readonly>
                                    </div>
                                    <div class="mt-1 input-group input-group-sm">
                                        <span class="input-group-text width-140px">- Finance taxes</span>
                                        <input type="text" id="impuestofinanciera" name="impuestofinanciera" class="form-control form-control-sm text-end" value="<?= number_format($objFactura->impuestoFinanciera, 2, ".", "") ?>" readonly>
                                        <input type="hidden" id="impuestofinancierareal" name="impuestofinancierareal" value="<?= number_format($objFactura->impuestoFinanciera, 3, ".", "") ?>">
                                    </div>
                                    <div class="mt-1 input-group input-group-sm">
                                        <span class="input-group-text width-140px">TOTAL</span>
                                        <input type="text" id="totalfinal" name="totalfinal" class="form-control form-control-sm text-end" value="<?= number_format($objFactura->totalFinal, 2, ".", "") ?>" readonly>
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
            <table>
                <tr>
                    <td class="width-250">
                        <div class="row mt-1 justify-content-end">
                            <div class="col-auto">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Total before taxes - Payment</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="mt-1 input-group input-group-sm">
                            <input type="text" id="totalmenospago" name="totalmenospago" class="form-control form-control-sm text-end" value="<?= number_format($objFactura->totalConImpuesto - $totalPagos, 2, ".", "") ?>" readonly>
                        </div>
                    </td>
                </tr>
            </table>

            <table>
                <thead>
                    <tr class="small border-bottom">
                        <td class="width-130px">Form of payment</td>
                        <td class="width-170px">Financial entity</td>
                        <td class="width-130px">Contract number</td>
                        <td class="width-170px">Card Receipt/Check #</td>
                        <td class="width-130px">Amount</td>
                        <td class="width-130px">Taxes</td>
                        <td class="width-130px">Amount+Taxes</td>
                        <td class="width-130px"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="input-group input-group-sm">
                                <select class="form-select" id="selecttipodepago" name="selecttipodepago">
                                    <!-- Tipos de pago -->
                                    <?= $listaDeTiposDePagoOptions ?>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <select class="form-select" id="selectfinanciera" name="selectfinanciera">
                                    <!-- Financieras -->
                                    <?= $listaDeFinancierasOptions ?>
                                </select>
                            </div>                            
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="text" id="selectcontrato" name="selectcontrato" class="form-control form-control-sm" maxlength="30">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="text" id="selectrecibocheque" name="selectrecibocheque" class="form-control form-control-sm" maxlength="30">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" id="selectmonto" name="selectmonto" class="form-control form-control-sm text-end" min="0.01" max="99999.99" step="0.01">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" id="selectimpuesto" name="selectimpuesto" class="form-control form-control-sm text-end" readonly>
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" id="selecttotalmasimpuesto" name="selecttotalmasimpuesto" class="form-control form-control-sm text-end" readonly>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-success" type="button" id="btnagregarpago" onclick="agregarFilaPago()"><i class="bi bi-plus-circle"></i> Add payment</button>
                        </td>
                    </tr>
                </tbody>
            </table>

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
                            <th class="width-90px">Actions</th>
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
                            <th class="width-90px"></th>
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
                                    <input type="text" id="totalpagosmonto" name="totalpagosmonto" class="form-control form-control-sm text-end" value="<?= number_format($totalPagos, 2, ".", "") ?>" readonly>
                                </div>
                            </td>
                            <td>
                                <div class="mt-1 input-group input-group-sm">
                                    <input type="text" id="totalpagosimpuesto" name="totalpagosimpuesto" class="form-control form-control-sm text-end" value="<?= number_format($totalPagos, 2, ".", "") ?>" readonly>
                                </div>
                            </td>
                            <td>
                                <div class="mt-1 input-group input-group-sm">
                                    <input type="text" id="totalpagos" name="totalpagos" class="form-control form-control-sm text-end" value="<?= number_format($totalPagos, 2, ".", "") ?>" readonly>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
        <input type="hidden" id="fid" name="fid" value="<?= $objFactura->facturaId ?>">
        
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
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objFactura->fechaCreacion == null ? date("m/d/Y") : $objFactura->fechaCreacion->format("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objFactura->usuarioCreo == null ? $_SESSION["usuario"] : $objFactura->usuarioCreo ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objFactura->fechaModificacion == null ? date("m/d/Y") : $objFactura->fechaModificacion->format("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objFactura->usuarioModifica == null ? $_SESSION["usuario"] : $objFactura->usuarioModifica ?>" readonly>
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

<div class="modal fade small" id="modalSeleccionarCliente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select customer</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="toolbarcliente">
                    <span class="label-text">Search</span>
                </div>
                <table
                        id="tableclientes"
                        data-toggle="table"
                        data-url="./mods/facturacion/facturacion/procs/getclientes.php"
                        data-toolbar=".toolbarcliente"
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
                            <th data-field="CLIENTEID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="CODIGO">Code</th>
                            <th data-field="NOMBRE">Name</th>
                            <th data-field="DIRECCION" data-visible="false">Direcci&oacute;n</th>
                            <th data-field="DIRECCIONCOMPLEMENTO" data-visible="false">Direcci&oacute;n complemento</th>
                            <th data-field="CODIGOPOSTAL" data-visible="false">C&oacute;digo postal</th>
                            <th data-field="TELEFONO" data-visible="false">Tel&eacute;fono</th>
                            <th data-field="CORREOELECTRONICO" data-visible="false">Correo electr&oacute;nico</th>
                            <th data-field="FACTURAS" data-visible="false">Facturas</th>
                            <th data-field="operate" data-formatter="clientesOperateFormatter" data-events="clientesOperateEvents">Actions</th>
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

<div class="modal fade small" id="modalNoConfig" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Invoice registration</h1>
            </div>
            <div class="modal-body">
                This store has no settings defined for creating invoices.
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
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select inventory item - <span id="nombresucursal"></h1>
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
                        data-url="./mods/facturacion/facturacion/procs/getinventario.php"
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
                            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="CODIGOINVENTARIO">Code</th>
                            <th data-field="CATEGORIA">Category</th>
                            <th data-field="MARCA">Brand</th>
                            <th data-field="MODELO">Model</th>
                            <th data-field="DESCRIPCION">Description</th>
                            <th data-field="MSRP" data-width="80" data-align="right">MSRP $</th>
                            <th data-field="GARANTIA">Warranty</th>
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

<div class="modal fade small" id="modalSeleccionarServicio" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select service or non inventory product</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="serviciosToolbar">
                    <span class="label-text">Search</span>
                    <div>
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
                        id="tableservicios"
                        data-toggle="table"
                        data-url="./mods/facturacion/facturacion/procs/getservicios.php"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-search="true"
                        data-show-refresh="true"
                        data-show-button-text="true"
                        data-toolbar=".serviciosToolbar"
                        data-page-list="[10, 25, 50, 100]"
                        data-page-size="10"
                        data-query-params="serviciosCustomParams"
                        data-icon-size="sm"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="OTROSERVICIOPRODUCTOID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="CODIGO">Code</th>
                            <th data-field="MARCA">Brand</th>
                            <th data-field="MODELO">Model</th>
                            <th data-field="DESCRIPCION">Description</th>
                            <th data-field="operate" data-formatter="serviciosOperateFormatter" data-events="serviciosOperateEvents">Actions</th>
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

<div class="modal fade small" id="modalSeleccionarVendedor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select salesperson</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="vendedoresToolbar">
                    <span class="label-text">Search</span>
                    <div>
                        <div>
                            <!-- Filtros -->
                        </div>
                    </div>
                </div>
                <table
                        id="tablevendedores"
                        data-toggle="table"
                        data-url="./mods/facturacion/facturacion/procs/getvendedores.php"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-search="true"
                        data-search-align="left"
                        data-show-refresh="true"
                        data-show-button-text="true"
                        data-toolbar=".vendedoresToolbar"
                        data-page-list="[10, 25, 50, 100]"
                        data-page-size="10"
                        data-query-params="vendedoresCustomParams"
                        data-icon-size="sm"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="USUARIOID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="NOMBRECOMPLETO">Full name</th>
                            <th data-field="USUARIO">User</th>
                            <th data-field="operate" data-formatter="vendedoresOperateFormatter" data-events="vendedoresOperateEvents">Actions</th>
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