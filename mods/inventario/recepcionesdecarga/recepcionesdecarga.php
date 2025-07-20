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

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("02.02.01");
        $accesoCrear = in_array("02.02.01.01", $accesos) ? "" : " disabled";

        // Para crear el combo de Sucursales
        require_once("inc/class/Sucursales.php");
        $objSucursales = new Sucursales($conn);
        $listaDeSucursales = $objSucursales->getListaParaComboDeUsuario($_SESSION["usuarioId"], "All that I have access to");
        $listaDeSucursalesOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"];
            
            $listaDeSucursalesOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Para crear el combo estados de recepciones
        require_once("inc/class/RecepcionesDeCarga.php");
        $objRecepciones = new RecepcionesDeCarga($conn);
        $listaDeEstados = $objRecepciones->getListaDeEstadosParaCombo("ALL");
        $listaDeEstadosOptions = "";
        foreach ($listaDeEstados as $estado)
        {
            $texto = $estado["NOMBRE"];
            $valor = $estado["ESTADO"];
            $listaDeEstadosOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Fecha por defecto para mostrar datos: 6 meses anterior a la actual
        $fechaDesde = new DateTime();
        $fechaDesde->sub(new DateInterval('P6M'));
        $fechaDesde->modify('first day of this month');
        $fechaDesdeString = $fechaDesde->format('Y-m-d');
?>

<h3>Bills of lading</h3>

<div class="p-3 bg-body rounded shadow-sm">

<button class="btn btn-sm btn-success" id="btncrear"<?= $accesoCrear ?>><i class="bi bi-plus-circle"></i> Create new</button>


<div class="toolbar">
    <span class="label-text">Search</span>
    <div class="row">
        <div class="col-auto mt-1">
            <div class="input-group input-group-sm" style="width: 220px;">
                <span class="input-group-text width-90px">Correlative</span>
                <input type="text" id="correlativo" name="correlativo" class="form-control form-control-sm" maxlength="7">
            </div>
        </div>
        <div class="col-auto mt-1">
            <div class="input-group input-group-sm">
                <span class="input-group-text width-95px">Supplier</span>
                <input type="text" id="codigoproveedor" name="codigoproveedor" class="form-control form-control-sm" oninput="buscarProveedor(event)" style="max-width: 70px;" maxlength="4" value="">
                <input type="text" id="proveedor" name="proveedor" class="form-control form-control-sm" value="" placeholder="ALL" readonly>
                <button class="btn btn-outline-secondary" type="button" id="btnproveedor"><i class="bi bi-search"></i></button>
                <input type="hidden" id="proveedorid" name="proveedorid" value="">
            </div>
        </div>
        <div class="col-auto mt-1">
            <div class="input-group input-group-sm" style="width: 220px;">
                <span class="input-group-text width-90px">Load ID</span>
                <input type="text" id="loadid" name="loadid" class="form-control form-control-sm" maxlength="100">
            </div>
        </div>
    </div>
    <div class="row mt-1">
        <div class="col-auto mt-1">
            <div class="input-group input-group-sm" style="width: 220px;">
                <span class="input-group-text width-90px">Date from</span>
                <input type="date" id="fechadesde" name="fechadesde" class="form-control form-control-sm" value="<?= $fechaDesdeString ?>">
            </div>
        </div>
        <div class="col-auto mt-1">
            <div class="input-group input-group-sm min-width-300px">
                <label class="input-group-text width-90px" for="sucursal">Store</label>
                <select class="form-select" id="sucursal" name="sucursal" required>
                    <!-- Sucursales -->
                    <?= $listaDeSucursalesOptions ?>
                </select>
            </div>
        </div>
        <div class="col-auto mt-1">
            <div class="input-group input-group-sm" style="width: 220px;">
                <label class="input-group-text width-90px" for="estado">Status</label>
                <select class="form-select" id="estado" name="estado">
                    <!-- Estados -->
                    <?= $listaDeEstadosOptions ?>
                </select>
            </div>
        </div>
        <div class="col-auto mt-1">
            <div>
                <button id="btnreset" class="btn btn-sm btn-secondary"><i class="bi bi-eraser"></i> Reset</button>
            </div>
        </div>
    </div>
</div>

<table
        id="tabledatos"
        data-toggle="table"
        data-toolbar=".toolbar"
        data-url="./mods/inventario/recepcionesdecarga/procs/getrecepciones.php"
        data-side-pagination="server"
        data-pagination="true"
        data-page-list="[25, 50, 100]"
        data-page-size="25"
        data-query-params="customParams"
        data-icon-size="sm"
        class="table-sm small"
>
    <thead>
        <tr>
            <th data-field="RECEPCIONDECARGAID" data-visible="false">ID</th>
            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
            <th data-field="FECHADEEMISION">Document date</th>
            <th data-field="FECHADERECEPCION">Reception date</th>
            <th data-field="SUCURSAL">Store</th>
            <th data-field="CORRELATIVO">Correlative</th>
            <th data-field="PROVEEDOR">Supplier</th>
            <th data-field="NUMERODEDOCUMENTO">Load ID</th>
            <th data-field="TIPODESTOCKORIGEN">Stock type origin</th>
            <th data-field="PORCENTAJETIPODESTOCKORIGEN" data-formatter="porcentajeOrigenFormatter" data-align="right">% origin</th>
            <th data-field="TIPODESTOCKDIST">Stock type distr.</th>
            <th data-field="PORCENTAJETIPODESTOCKDIST" data-formatter="porcentajeDistFormatter" data-align="right">% distr.</th>
            <th data-field="PIEZAS" data-align="right">Pcs.</th>
            <th data-field="NOMBREDEESTADO" data-formatter="estadoFormatter" data-align="center">Status</th>
            <th data-field="USUARIOCREO">Created by</th>
            <th data-field="operate" data-formatter="operateFormatter">Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Registros -->
    </tbody>
</table>

<input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">

</div>


<div class="modal fade small" id="modalEmitirEtiquetas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Print labels - Bill of lading <span id="billcorrelativo"></span></h1>
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
                            <input type="hidden" id="rid" name="rid" value="">
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

<div class="modal fade small" id="modalSeleccionarProveedor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Select supplier</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="toolbarproveedor">
                    <span class="label-text">Search</span>
                </div>
                <table
                        id="tableproveedores"
                        data-toggle="table"
                        data-url="./mods/inventario/rptrecepproveedor/procs/getproveedores.php"
                        data-toolbar=".toolbarproveedor"
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
                            <th data-field="PROVEEDORID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowProveedorIndexFormatter">#</th>
                            <th data-field="CODIGO">Code</th>
                            <th data-field="NOMBRE">Name</th>
                            <th data-field="operate" data-formatter="proveedoresOperateFormatter" data-events="proveedoresOperateEvents">Actions</th>
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