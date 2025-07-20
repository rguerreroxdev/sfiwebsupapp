<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("ADM", "01.02.01.01");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Para crear el combo de módulos
        require_once("inc/class/Usuario.php");
        $objUsuarios = new Usuario($conn);
        $listaDeModulos = $objUsuarios->getListaDeModulos();
        $listaDeModulosOptions = "";
        foreach ($listaDeModulos as $modulo)
        {
            $texto = $modulo["NOMBRE"];
            $valor = $modulo["MODULOID"] == "AAA" ? "" : $modulo["MODULOID"];
            $listaDeModulosOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Para crear el combo de Perfiles
        require_once("inc/class/Perfiles.php");
        $objPerfiles = new Perfiles($conn);
        $listaDePerfiles = $objPerfiles->getListaParaCombo();
        $listaDePerfilesOptions = "";
        foreach ($listaDePerfiles as $perfil)
        {
            $texto = $perfil["NOMBRE"];
            $valor = $perfil["PERFILID"] == -1 ? "" : $perfil["PERFILID"];
            $listaDePerfilesOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }

        // Para crear el combo de Sucursales
        require_once("inc/class/Sucursales.php");
        $objSucursales = new Sucursales($conn);
        $listaDeSucursales = $objSucursales->getListaParaCombo();
        $listaDeSucursalesOptions = "";
        foreach ($listaDeSucursales as $sucursal)
        {
            $texto = $sucursal["NOMBRE"];
            $valor = $sucursal["SUCURSALID"];
            $listaDeSucursalesOptions .= "
                <option value=\"$valor\">$texto</option>
            ";
        }
?>

<h3>Users - Create new</h3>

<nav>
    <div class="nav nav-tabs small" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-editar-tab" data-bs-toggle="tab" data-bs-target="#nav-editar" type="button" role="tab" aria-controls="nav-editar" aria-selected="true">User data</button>
        <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Save dates</button>
    </div>
</nav>

<form id="frm">
<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-editar" role="tabpanel" aria-labelledby="nav-editar-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Full name<span class="text-danger">&nbsp;*</span></span>
                        <input type="text" id="nombre" name="nombre" class="form-control form-control-sm" value="" maxlength="150" required>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">User<span class="text-danger">&nbsp;*</span></span>
                        <input type="text" id="usuario" name="usuario" class="form-control form-control-sm" value="" maxlength="50" required>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Active (Enabled to login)</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="activo" name="activo" class="form-check-input mt-0" value="" checked>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Password<span class="text-danger">&nbsp;*</span></span>
                        <input type="password" id="password" name="password" class="form-control form-control-sm" value="" maxlength="50" required>
                        <button class="btn btn-outline-secondary" type="button" id="btnverpassword" onmousedown="showPassword()" onmouseup="hidePassword()" onmouseout="hidePassword()"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Change password at next login</span>
                        <div class="input-group-text">
                            <input type="checkbox" id="cambiarpwd" name="cambiarpwd" class="form-check-input mt-0" value="" checked>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="perfil">Profile<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="perfil" name="perfil" required>
                            <!-- Perfiles -->
                            <?= $listaDePerfilesOptions ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-130px" for="moduloinicial">Initial module<span class="text-danger">&nbsp;*</span></label>
                        <select class="form-select" id="moduloinicial" name="moduloinicial" required>
                            <!-- Modulos -->
                            <?= $listaDeModulosOptions ?>
                        </select>
                    </div>
                </div>
            </div>            
        </div>

        <div class="my-3 p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-sm-4">
                    <span class="fs-6">Access to stores</span>
                    <div class="input-group input-group-sm">
                        <label class="input-group-text width-95px" for="perfil">Store</label>
                        <select class="form-select" id="sucursal" name="sucursal" required>
                            <!-- Sucursales -->
                            <?= $listaDeSucursalesOptions ?>
                        </select>
                        <button class="btn btn-outline-success" type="button" id="agregarsucursal" onclick="agregarFila()">Add</button>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-4">
                    <table id="tablaSucursales" class="table table-hover table-sm small">
                        <thead>
                            <tr class="border-bottom">
                                <th style="width: 70%;">Store</th>
                                <th style="width: 30%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Filas de ítems -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row justify-content-between">
            <div class="col">
                <span class="fst-italic small"><span class="text-danger">*</span> -> Required data</span>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-sm btn-primary" id="btnguardar" style="min-width: 75px;">
                    <i class="bi bi-floppy2"></i> Save
                    <span class="spinner-border spinner-border-sm visually-hidden" id="btnguardarspinner" role="status" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-sm btn-secondary" id="btncancelar" style="min-width: 75px;"><i class="bi bi-x-octagon"></i> Cancel</button>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation data</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= date("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $_SESSION["usuario"] ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification data</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= date("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $_SESSION["usuario"] ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="loggeduid" name="loggeduid" value="<?= $_SESSION["usuarioId"] ?>">

    </div>

</div>
</form>

<div class="toast-container p-5 position-fixed top-0 start-50 translate-middle-x" id="toastPlacement">
    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastMensaje">
        <div class="d-flex">
            <div class="toast-body">
                The data was saved.
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

<div class="modal fade small" id="modalMensaje" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-danger" id="staticBackdropLabel">Error when saving</h1>
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