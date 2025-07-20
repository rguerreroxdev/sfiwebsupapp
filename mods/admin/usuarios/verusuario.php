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

        // Obtener los datos del usuario recibido en la URL
        $usuarioId = -1;
        if (isset($_GET["uid"]))
        {
            $usuarioId = is_numeric($_GET["uid"]) ? intval($_GET["uid"]) : -1;
        }
        
        require_once("inc/class/Usuario.php");
        $objUsuario = new Usuario($conn);

        $objUsuario->getById($usuarioId);

        // Si el registro no existe, retornar a listado
        if ($objUsuario->usuarioId == -1)
        {
            echo ("<script>window.location.href='?mod=admin&opc=usuarios'</script>");
            exit();
        }

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("01.02.01");
        $accesoEditar = in_array("01.02.01.02", $accesos) ? "" : " disabled";
        $accesoEliminar = in_array("01.02.01.03", $accesos) ? "" : " disabled";
?>

<h3>Users - User data</h3>

<nav>
    <div class="nav nav-tabs small" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-editar-tab" data-bs-toggle="tab" data-bs-target="#nav-editar" type="button" role="tab" aria-controls="nav-editar" aria-selected="true">User data</button>
        <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Save dates</button>
    </div>
</nav>

<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-editar" role="tabpanel" aria-labelledby="nav-editar-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Full name</span>
                        <input type="text" id="nombre" name="nombre" class="form-control form-control-sm" value="<?= $objUsuario->nombreCompleto ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">User</span>
                        <input type="text" id="usuario" name="usuario" class="form-control form-control-sm" value="<?= $objUsuario->usuario ?>" readonly>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Active (Enabled to login)</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="activo" name="activo" class="form-check-input mt-0" value=""<?= $objUsuario->activo == 1 ? " checked" : "" ?> disabled>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Change password at next login</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="cambiarpwd" name="cambiarpwd" class="form-check-input mt-0" value=""<?= $objUsuario->cambiarContrasena == 1 ? " checked" : "" ?> disabled>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Profile</span>
                        <input type="text" id="perfil" name="perfil" class="form-control form-control-sm" value="<?= $objUsuario->perfil ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Initial module</span>
                        <input type="text" id="modulodeinicio" name="modulodeinicio" class="form-control form-control-sm" value="<?= $objUsuario->moduloDeInicio ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="my-3 p-3 bg-body rounded shadow-sm">
            <div class="col-sm-4">
                <span class="fs-6">Access to stores</span>
                <table
                    id="tablesucursales"
                    data-toggle="table"
                    data-url="./mods/admin/usuarios/procs/getsucursalesxusuario.php?uid=<?= $objUsuario->usuarioId ?>"
                    data-pagination="false"
                    class="table-sm small"
                >
                    <thead>
                        <tr>
                            <th data-field="SUCURSAL">Stores</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>
            </div>
        </div>

        <input type="hidden" id="uid" name="uid" value="<?= $objUsuario->usuarioId ?>">
        <input type="hidden" id="loggeduid" name="loggeduid" value="<?= $_SESSION["usuarioId"] ?>">

        <div class="row justify-content-between">
            <div class="col">
                <button class="btn btn-sm btn-secondary" id="btnregresar"><i class="bi bi-arrow-return-left"></i> Go back</button>
            </div>
            <div class="col">
                <button class="btn btn-sm btn-primary" id="btneditar" style="min-width: 75px;"<?= $accesoEditar ?>><i class="bi bi-pencil-square"></i> Edit</button>
                <button class="btn btn-sm btn-danger" id="btneliminar" style="min-width: 75px;"<?= $accesoEliminar ?>><i class="bi bi-trash"></i> Delete</button>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation date</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objUsuario->fechaCreacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objUsuario->usuarioCreo != null ? $objUsuario->usuarioCreo : "-" ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objUsuario->fechaModificacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objUsuario->usuarioModifica != null ? $objUsuario->usuarioModifica : "-" ?>" readonly>
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
                The record was deleted.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="modal fade small" id="modalConfirmar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Delete record</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Confirm that you are going to delete this record
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btnconfirmaeliminar"><i class="bi bi-trash"></i> Yes, delete</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="bi bi-x-octagon"></i> No, cancel</button>
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