<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.04.04");

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

            $listaDeCategoriasOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Para crear el combo tipos de salida
        require_once("inc/class/TiposDeSalida.php");
        $objTiposDeSalida = new TiposDeSalidas($conn);
        $listaDeTiposDeSalida = $objTiposDeSalida->getListaParaCombo("ALL");
        $listaDeTiposDeSalidaOptions = "";
        foreach ($listaDeTiposDeSalida as $tipoDeSalida)
        {
            $texto = $tipoDeSalida["NOMBRE"];
            $valor = $tipoDeSalida["TIPODESALIDAID"];
            $listaDeTiposDeSalidaOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }
?>

<h3>Report: Discharged items</h3>

<div class="p-3 bg-body rounded shadow-sm">
    <div class="row">
        <div class="col-auto">
            <div class="input-group input-group-sm min-width-300px">
                <label class="input-group-text" for="sucursal">Store</label>
                <select class="form-select" id="sucursal" name="sucursal" required>
                    <!-- Sucursales -->
                    <?= $listaDeSucursalesOptions ?>
                </select>
            </div>
        </div>
        <div class="col-auto">
            <div class="input-group input-group-sm width-250px">
                <label class="input-group-text" for="categoria">Category</label>
                <select class="form-select" id="categoria" name="categoria" required>
                    <!-- Sucursales -->
                    <?= $listaDeCategoriasOptions ?>
                </select>
            </div>
        </div>
        <div class="col-auto">
            <div class="input-group input-group-sm">
                <label class="input-group-text width-90px" for="tipo">Type</label>
                <select class="form-select" id="tipo" name="tipo" required>
                    <!-- Tipos de salida -->
                    <?= $listaDeTiposDeSalidaOptions ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-auto">
            <div class="input-group input-group-sm width-250px">
                <label class="input-group-text" for="fechainicial">From</label>
                <input type="date" id="fechainicial" name="fechainicial" class="form-control form-control-sm">
            </div>
        </div>
        <div class="col-auto">
            <div class="input-group input-group-sm width-250px">
                <label class="input-group-text" for="fechafinal">To</label>
                <input type="date" id="fechafinal" name="fechafinal" class="form-control form-control-sm">
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-auto">
            <button type="button" id="btnpdf" name="btnpdf" class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-pdf"></i> Generate PDF report</button>
            <button type="button" id="btnexcel" name="btnexcel" class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-excel"></i> Generate Excel report</button>
        </div>
    </div>
</div>

<input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">

<?php 
    } // else de mostrar contenido por acceso a opción
?>