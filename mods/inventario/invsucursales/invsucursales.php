<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.03.02");

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

        // Para crear el combo de Marcas
        require_once("inc/class/Marcas.php");
        $objMarca = new Marcas($conn);
        $listaDeMarcas = $objMarca->getListaParaCombo("ALL");
        $listaDeMarcasOptions = "";
        foreach ($listaDeMarcas as $marca)
        {
            $texto = $marca["NOMBRE"];
            $valor = $marca["MARCAID"] == -1 ? "" : $marca["MARCAID"];
            $listaDeMarcasOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }
?>

<h3>Stock in stores</h3>

<div class="p-3 bg-body rounded shadow-sm">
    <div class="col">
        <div class="toolbar">
            <span class="label-text">Search</span>
            <div class="row">
                <div class="col">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text" for="categoria">Category</label>
                        <select class="form-select" id="categoria" name="categoria" required>
                            <!-- Sucursales -->
                            <?= $listaDeCategoriasOptions ?>
                        </select>
                    </div>
                </div>
                <div class="col">
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
                id="tableproductos"
                data-url="./mods/inventario/invsucursales/procs/getproductos.php"
                data-side-pagination="server"
                data-pagination="true"
                data-search="true"
                data-show-refresh="true"
                data-show-button-text="true"
                data-toolbar=".toolbar"
                data-page-list="[25, 50, 100]"
                data-page-size="25"
                data-query-params="productosCustomParams"
                data-height="400"
                data-click-to-select="true"
                data-single-select="true"
                data-icon-size="sm"
                class="table-sm small"
        >
            <thead>
                <tr>
                    <th data-field="PRODUCTOID" data-visible="false">ID</th>
                    <th data-field="state" data-checkbox="true"></th>
                    <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                    <th data-field="CATEGORIA">Category</th>
                    <th data-field="MARCA">Brand</th>
                    <th data-field="MODELO">Model</th>
                    <th data-field="DESCRIPCION">Description</th>
                </tr>
            </thead>
            <tbody>
                <!-- Registros -->
            </tbody>
        </table>
    </div>
</div>

<div class="p-3 mt-2 bg-body rounded shadow-sm">
    <div class="col-md-4">
        <div class="">
            Stock of: <span id="detproducto"></span>
        </div>
        <table
                id="tableexistencias"
                data-toggle="table"
                data-url="./mods/inventario/invsucursales/procs/getexistencias.php"
                data-query-params="existenciasCustomParams"
                class="table-sm small"
        >
            <thead>
                <tr>
                    <th data-field="SUCURSAL">Store</th>
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

<input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
<input type="hidden" id="pid" name="pid" value="">

<?php 
    } // else de mostrar contenido por acceso a opción
?>