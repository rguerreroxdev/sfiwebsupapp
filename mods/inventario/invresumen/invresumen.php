<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.03.03");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Para crear el combo de Sucursales
        require_once("inc/class/Sucursales.php");
        $objSucursales = new Sucursales($conn);
        $listaDeSucursales = $objSucursales->getListaParaComboDeUsuario($_SESSION["usuarioId"], "All that I have access to");
        $listaDeSucursalesOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"];

            if ($valor == -1)
            {
                continue;
            }

            $selected = "";
            if ($sucursal["ESCASAMATRIZ"] == 1)
            {
                $selected = " selected";
            }

            $listaDeSucursalesOptions .= "
                <option value=\"$valor\"$selected>$texto</option>
            ";
        }

        // Para crear el combo de Categorías
        require_once("inc/class/Categorias.php");
        $objCategorias = new Categorias($conn);
        $listaDeCategorias = $objCategorias->getListaParaCombo("ALL");
        $listaDeCategoriasOptions = "";
        foreach ($listaDeCategorias as $categoria)
        {
            $texto = $categoria["NOMBRE"];
            $valor = $categoria["CATEGORIAID"];

            if ($valor == -1)
            {
                continue;
            }

            $listaDeCategoriasOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }
?>

<h3>Summary inventory</h3>

<nav>
    <div class="nav nav-tabs small" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-general-tab" data-bs-toggle="tab" data-bs-target="#nav-general" type="button" role="tab" aria-controls="nav-general" aria-selected="true">Main inventory</button>
        <button class="nav-link" id="nav-categoria-tab" data-bs-toggle="tab" data-bs-target="#nav-categoria" type="button" role="tab" aria-controls="nav-categoria" aria-selected="false">Product inventory by category</button>
    </div>
</nav>

<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="col-lg-4">
                <div class=""><strong>Main inventory</strong></div>
                <div class="toolbargeneral">
                    <span class="label-text">Search</span>
                    <div class="row">
                        <div class="col">
                            <div class="input-group input-group-sm min-width-300px">
                                <label class="input-group-text" for="sucursalgeneral">Store</label>
                                <select class="form-select" id="sucursalgeneral" name="sucursalgeneral" required>
                                    <!-- Sucursales -->
                                    <?= $listaDeSucursalesOptions ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <table
                        id="tablegeneral"
                        data-toggle="table"
                        data-url="./mods/inventario/invresumen/procs/getinvgeneral.php"
                        data-pagination="true"
                        data-toolbar=".toolbargeneral"
                        data-page-list="[25, 50, 100]"
                        data-page-size="25"
                        data-query-params="generalCustomParams"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="CATEGORIA">Category</th>
                            <th data-field="EXISTENCIA" data-align="right">Stock</th>
                            <th data-field="ENTRANSITO" data-align="right">In transit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="nav-categoria" role="tabpanel" aria-labelledby="nav-categoria-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="col-lg-10">
                <div class=""><strong>Product inventory by category</strong></div>
                <div class="toolbarporcategoria">
                    <span class="label-text">Search</span>
                    <div class="row">
                        <div class="col">
                            <div class="input-group input-group-sm min-width-300px">
                                <label class="input-group-text" for="sucursalcategoria">Store</label>
                                <select class="form-select" id="sucursalcategoria" name="sucursalcategoria" required>
                                    <!-- Sucursales -->
                                    <?= $listaDeSucursalesOptions ?>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group input-group-sm width-290px">
                                <label class="input-group-text" for="categoria">Category</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <!-- Sucursales -->
                                    <?= $listaDeCategoriasOptions ?>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Only in stock</span>
                                <div class="input-group-text">
                                    <input type="checkbox" id="solostock" name="solostock" class="form-check-input mt-0" value="" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <table
                        id="tabledatosporcategoria"
                        data-toggle="table"
                        data-url="./mods/inventario/invresumen/procs/getinvporcategoria.php"
                        data-pagination="true"
                        data-toolbar=".toolbarporcategoria"
                        data-search="true"
                        data-show-refresh="true"
                        data-show-button-text="true"
                        data-page-list="[25, 50, 100]"
                        data-page-size="25"
                        data-query-params="customParams"
                        data-icon-size="sm"
                        class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                            <th data-field="MARCA">Brand</th>
                            <th data-field="MODELO">Model</th>
                            <th data-field="COLOR">Color</th>
                            <th data-field="DESCRIPCION">Description</th>
                            <th data-field="EXISTENCIA" data-align="right">Stock</th>
                            <th data-field="ENTRANSITO" data-align="right">In transit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
    } // else de mostrar contenido por acceso a opción
?>