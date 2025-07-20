<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("FAC", "03.01.03");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("03.01.03");
        $accesoCrear = in_array("03.01.03.01", $accesos) ? "" : " disabled";
?>

<h3>Financial entities</h3>

<div class="p-3 bg-body rounded shadow-sm">

<div class="col-lg-9">
    <button class="btn btn-sm btn-success" id="btncrear"<?= $accesoCrear ?>><i class="bi bi-plus-circle"></i> Create new</button>
    <div class="toolbar">
        <span class="label-text">Search</span>
    </div>

    <table
    id="tabledatos"
            data-toggle="table"
            data-url="./mods/facturacion/entidadesfinanc/procs/getentidades.php"
            data-side-pagination="server"
            data-pagination="true"
            data-search="true"
            data-search-align="left"
            data-show-refresh="true"
            data-show-button-text="true"
            data-toolbar=".toolbar"
            data-page-list="[25, 50, 100]"
            data-page-size="25"
            data-icon-size="sm"
            class="table-sm small"
    >
        <thead>
            <tr>
                <th data-field="FINANCIERAID" data-visible="false">ID</th>
                <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
                <th data-field="NOMBRE">Name</th>
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