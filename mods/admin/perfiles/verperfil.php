<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("ADM", "01.02.02");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Obtener los datos del perfil recibido en la URL
        $perfilId = -1;
        if (isset($_GET["pid"]))
        {
            $perfilId = is_numeric($_GET["pid"]) ? intval($_GET["pid"]) : -1;
        }
        
        require_once("inc/class/Perfiles.php");
        $objPerfil = new Perfiles($conn);

        $objPerfil->getById($perfilId);

        // Si el registro no existe, retornar a listado
        if ($objPerfil->perfilId == -1)
        {
            echo ("<script>window.location.href='?mod=admin&opc=perfiles'</script>");
            exit();
        }

        // Verificar si el perfil es de administrador para restringir eliminar y modificar accesos
        $esPerfilAdministrador = $objPerfil->perfilId == 1;

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("01.02.02");
        $accesoEditar = in_array("01.02.02.02", $accesos) && !$esPerfilAdministrador;
        $accesoEliminar = in_array("01.02.02.03", $accesos) && !$esPerfilAdministrador ? "" : " disabled";

        // Para crear el combo de Módulos
        $listaDeModulos = $objPerfil->getModulosParaCombo();
        $listaDeModulosOptions = "";
        $moduloInicial = "";
        foreach ($listaDeModulos as $modulo)
        {
            $texto = $modulo["NOMBRE"];
            $valor = $modulo["MODULOID"];
            $listaDeModulosOptions .= "
                <option value=\"$valor\">$texto</option>
            ";

            $moduloInicial = $moduloInicial == "" ? $valor : $moduloInicial;
        }

        // Para crear el combo de opciones de menú
        $listaDeMenu = $objPerfil->getMenusPrincipalesParaCombo($moduloInicial);
        $listaDeMenuOptions = "";
        foreach ($listaDeMenu as $menu)
        {
            $texto = $menu["DESCRIPCION"];
            $valor = $menu["CODIGO"];
            $listaDeMenuOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }
?>

<h3>Profiles - Profile data</h3>

<nav>
    <div class="nav nav-tabs small" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-editar-tab" data-bs-toggle="tab" data-bs-target="#nav-editar" type="button" role="tab" aria-controls="nav-editar" aria-selected="true">Profile data</button>
        <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Save dates</button>
    </div>
</nav>

<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-editar" role="tabpanel" aria-labelledby="nav-editar-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-90px">Name</span>
                        <input type="text" id="nombre" name="nombre" class="form-control form-control-sm" value="<?= $objPerfil->nombre ?>" readonly>
                        <?php if ($accesoEditar): ?>
                        <button class="btn btn-sm btn-primary" id="btneditar" style="min-width: 75px;"><i class="bi bi-pencil-square"></i> Change profile name</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col">
                    <button class="btn btn-sm btn-danger" id="btneliminar" style="min-width: 75px;"<?= $accesoEliminar ?>><i class="bi bi-trash"></i> Delete profile</button>
                </div>
            </div>
            <?php if ($esPerfilAdministrador): ?>
            <span class="text-danger small">This profile can not be edited or deleted.</span>
            <?php endif; ?>
        </div>

        <div class="p-3 mt-2 bg-body rounded shadow-sm">
            <div class="row">
                <strong>Profile access detail</strong>
                <div class="col-lg-9">
                    <div class="toolbar">
                        <div class="row">
                            <div class="col">
                                <div class="input-group input-group-sm">
                                    <label class="input-group-text" for="sucursal">Module</label>
                                    <select class="form-select" id="modulo" name="modulo" required>
                                        <!-- Módulos -->
                                        <?= $listaDeModulosOptions ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group input-group-sm">
                                    <label class="input-group-text" for="sucursal">Menu</label>
                                    <select class="form-select" id="menu" name="menu" required>
                                        <!-- Menús -->
                                        <?= $listaDeMenuOptions ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table
                            id="tabledatos"
                            data-toggle="table"
                            data-url="./mods/admin/perfiles/procs/getaccesos.php"
                            data-toolbar=".toolbar"
                            data-search="true"
                            data-height="400"
                            data-icon-size="sm"
                            data-query-params="customParams"
                            class="table-sm small"
                    >
                        <thead>
                            <tr>
                                <th data-field="PERFILDETALLEID" data-visible="false">DetID</th>
                                <th data-field="PERFILOPCIONID" data-visible="false">OptID</th>
                                <th data-field="MODULOID" data-visible="false">ModID</th>
                                <th data-field="MODULO">Module</th>
                                <th data-field="CODIGO" data-visible="false">Acces code</th>
                                <th data-field="DESCRIPCION" data-formatter="descripcionFormatter">Acces description</th>
                                <?php if($accesoEditar && !$esPerfilAdministrador): ?>
                                <th data-field="operate" data-formatter="accesoEditFormatter">Access status</th>
                                <?php else: ?>
                                <th data-field="operate" data-formatter="accesoFormatter">Access status</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Registros -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
        <input type="hidden" id="pid" name="pid" value="<?= $objPerfil->perfilId ?>">

        <div class="row mt-2 justify-content-between">
            <div class="col">
                <button class="btn btn-sm btn-secondary" id="btnregresar"><i class="bi bi-arrow-return-left"></i> Go back</button>
            </div>
            <div class="col">
                
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation date</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objPerfil->fechaCreacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objPerfil->usuarioCreo ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objPerfil->fechaModificacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objPerfil->usuarioModifica ?>" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="toast-container p-5 position-fixed top-0 start-50 translate-middle-x" id="toastPlacement">
    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastMensaje">
        <div class="d-flex">
            <div class="toast-body">
                <span id="textodemensaje"></span>
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

<div class="modal fade small" id="modalConfirmar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Delete profile</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Confirm that you are going to delete this profile
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btnconfirmaeliminar"><i class="bi bi-trash"></i> Yes, delete</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-octagon"></i> No, cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade small" id="modalEditar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Update profile name</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group input-group-sm">
                    <span class="input-group-text width-90px">Name</span>
                    <input type="text" id="editnombre" name="editnombre" class="form-control form-control-sm" value="<?= $objPerfil->nombre ?>" maxlength="100" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btnconfirmaguardar"><i class="bi bi-floppy2"></i> Save</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-octagon"></i> Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade small" id="modalMensaje" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Error when deleting</h1>
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

<?php 
    } // else de mostrar contenido por acceso a opción
?>