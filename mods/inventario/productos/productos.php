<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.01.01");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("02.01.01");
        $accesoCrear = in_array("02.01.01.01", $accesos) ? "" : " disabled";

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
        foreach ($listaDeMarcas as $marca)
        {
            $texto = $marca["NOMBRE"];
            $valor = $marca["MARCAID"] == -1 ? "" : $marca["MARCAID"];
            $listaDeMarcasOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }
?>

<h3>Products</h3>

<div class="p-3 bg-body rounded shadow-sm">

<div class="col-lg-10">
    <button class="btn btn-sm btn-success" id="btncrear"<?= $accesoCrear ?>><i class="bi bi-plus-circle"></i> Create new</button>
    <div class="toolbar">
        <span class="label-text">Search</span>
        <div class="row">
            <div class="col">
                <div class="input-group input-group-sm min-width-300px">
                    <label class="input-group-text width-95px" for="categoria">Category</label>
                    <select class="form-select" id="categoria" name="categoria">
                        <!-- Categorías -->
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
            <div class="col">
                <button id="btnreset" class="btn btn-sm btn-secondary"><i class="bi bi-eraser"></i> Reset</button>
            </div>
        </div>
    </div>

    <table
            id="tabledatos"
            data-toggle="table"
            data-url="./mods/inventario/productos/procs/getproductos.php"
            data-side-pagination="server"
            data-pagination="true"
            data-search="true"
            data-show-refresh="true"
            data-show-button-text="true"
            data-toolbar=".toolbar"
            data-page-list="[25, 50, 100]"
            data-page-size="25"
            data-query-params="customParams"
            data-icon-size="sm"
            class="table-sm small"
    >
        <thead>
            <tr>
                <th data-field="PRODUCTOID" data-visible="false">ID</th>
                <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                <th data-field="CODIGO" data-width="80">Code</th>
                <th data-field="CATEGORIA">Category</th>
                <th data-field="MARCA">Brand</th>
                <th data-field="MODELO">Model</th>
                <th data-field="DESCRIPCION">Description</th>
                <th data-field="MSRP" data-width="80" data-align="right" data-formatter="msrpFormatter">MSRP</th>
                <th data-field="operate" data-formatter="operateFormatter">Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Registros -->
        </tbody>
    </table>
</div>

</div>

<?php 
    } // else de mostrar contenido por acceso a opción
?>