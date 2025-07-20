<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("ADM", "01.02.01");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("01.02.01");
        $accesoCrear = in_array("01.02.01.01", $accesos) ? "" : " disabled";

        // Para crear el combo de estado (activo, desactivado, todos)
        require_once("inc/class/Usuario.php");
        $objUsuarios = new Usuario($conn);
        $listaDeEstados = $objUsuarios->getEstadoParaCombo();
        $listaDeEstadosOptions = "";
        foreach ($listaDeEstados as $estado)
        {
            $texto = $estado["NOMBRE"];
            $valor = $estado["ESTADO"];
            $listaDeEstadosOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Para crear el combo de perfiles
        require_once("inc/class/Perfiles.php");
        $objPerfiles = new Perfiles($conn);
        $listaDePerfiles = $objPerfiles->getListaParaCombo("ALL");
        $listaDePerfilesOptions = "";
        foreach ($listaDePerfiles as $perfil)
        {
            $texto = $perfil["NOMBRE"];
            $valor = $perfil["PERFILID"];
            $listaDePerfilesOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }
?>

<h3>Users</h3>

<div class="p-3 bg-body rounded shadow-sm">

<button class="btn btn-sm btn-success" id="btncrear"<?= $accesoCrear ?>><i class="bi bi-plus-circle"></i> Create new</button>

<div class="toolbar">
    <span class="label-text">Search</span>
    <div class="row">
        <div class="col">
            <div class="input-group input-group-sm min-width-300px">
                <label class="input-group-text width-95px" for="perfil">Profile</label>
                <select class="form-select" id="perfil" name="perfil">
                    <!-- Perfiles -->
                    <?= $listaDePerfilesOptions ?>
                </select>
            </div>
        </div>
        <div class="col">
            <div class="input-group input-group-sm min-width-300px">
                <label class="input-group-text width-95px" for="categoria">Active status</label>
                <select class="form-select" id="activo" name="activo">
                    <!-- Estados -->
                    <?= $listaDeEstadosOptions ?>
                </select>
            </div>
        </div>
    </div>
</div>

<table
id="tabledatos"
        data-toggle="table"
        data-url="./mods/admin/usuarios/procs/getusuarios.php"
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
            <th data-field="USUARIOID" data-visible="false">ID</th>
            <th data-field="Index" data-formatter="rowIndexFormatter">#</th>
            <th data-field="NOMBRECOMPLETO">Full name</th>
            <th data-field="USUARIO">User</th>
            <th data-field="ACTIVO" data-formatter="activeFormatter">Active</th>
            <th data-field="PERFIL">Profile</th>
            <th data-field="operate" data-formatter="operateFormatter">Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Registros -->
    </tbody>
</table>

</div>

<?php 
    } // else de mostrar contenido por acceso a opción
?>