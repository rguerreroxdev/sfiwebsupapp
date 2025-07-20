<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.02.05");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

    // Para crear el combo de Categorías
    require_once("inc/class/Categorias.php");
    $objCategorias = new Categorias($conn);
    $listaDeCategorias = $objCategorias->getListaParaCombo("ALL");
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

<h3>Labels</h3>

<div class="p-3 bg-body rounded shadow-sm">
    <div class="toolbar">
        <span class="label-text">Search</span>
        <div class="row">
            <div class="col">
                <div class="input-group input-group-sm width-250px me-2">
                    <label class="input-group-text" for="categoria">Category</label>
                    <select class="form-select" id="categoria" name="categoria" required>
                        <!-- Sucursales -->
                        <?= $listaDeCategoriasOptions ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <table
            id="tabledatos"
            data-toggle="table"
            data-url="./mods/inventario/etiquetas/procs/getinvgeneral.php"
            data-unique-id="INVENTARIOID"
            data-side-pagination="server"
            data-pagination="true"
            data-search="true"
            data-search-align="left"
            data-show-refresh="true"
            data-show-button-text="true"
            data-toolbar=".toolbar"
            data-page-list="[25, 50, 100]"
            data-page-size="25"
            data-query-params="customParams"
            data-icon-size="sm"
            data-height="400"
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
                <th data-field="operate" data-formatter="operateFormatter" data-events="operateEvents">Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Registros -->
        </tbody>
    </table>
</div>

<div class="row" style="margin-left: 1px; margin-right: 1px;">
    <div class="col-6 p-3 mt-2 bg-body rounded shadow-sm">
        <span class="small">Selected inventory items (<span id="numFilas">0</span> items selected)</span>
        <table id="tablaDetalle" style="width: 100%;">
            <thead>
                <tr class="small border-bottom">
                    <th style="width: 20%;">Code</th>
                    <th style="width: 30%;">Category</th>
                    <th style="width: 30%;">Model</th>
                    <th style="width: 20%;">Actions</th>
                </tr>
            </thead>
            <tbody class="small">
                <!-- Filas de ítems -->
            </tbody>
        </table>
    </div>

    <div class="col-6 p-3 mt-2 bg-body rounded shadow-sm text-center">
        <button class="btn btn-sm btn-primary" id="btnetiquetas" style="min-width: 75px;"><i class="bi bi-credit-card-2-front"></i> Print labels</button>
    </div>
</div>

<div class="modal fade small" id="modalEmitirEtiquetas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Print labels</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="frmprint">
                <div class="row">
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

<?php 
    } // else de mostrar contenido por acceso a opción
?>