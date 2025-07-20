<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "03.01.01.02");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Obtener los datos del cliente recibido en la URL
        $clienteId = -1;
        if (isset($_GET["cid"]))
        {
            $clienteId = is_numeric($_GET["cid"]) ? intval($_GET["cid"]) : -1;
        }

        require_once("inc/class/Clientes.php");
        $objClientes = new Clientes($conn);

        $objClientes->getById($clienteId);

        // Si el registro no existe, retornar a listado
        if ($objClientes->clienteId == -1)
        {
            echo ("<script>window.location.href='?mod=facturacion&opc=clientes'</script>");
            exit();
        }
?>

<h3>Customers - Edit data</h3>

<form id="frm">
<nav>
    <div class="nav nav-tabs small" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-editar-tab" data-bs-toggle="tab" data-bs-target="#nav-editar" type="button" role="tab" aria-controls="nav-editar" aria-selected="true">Product data</button>
        <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Save dates</button>
    </div>
</nav>

<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-editar" role="tabpanel" aria-labelledby="nav-editar-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-90px">Code</span>
                        <input type="text" id="codigo" name="codigo" class="form-control form-control-sm" value="<?= $objClientes->codigo ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-10 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-95px">Name<span class="text-danger">&nbsp;*</span></span>
                        <input type="text" id="nombre" name="nombre" class="form-control form-control-sm" maxlength="200" value="<?= $objClientes->nombre ?>" required>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-10 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-170px">Address</span>
                        <input type="text" id="direccion" name="direccion" class="form-control form-control-sm" maxlength="100" value="<?= $objClientes->direccion ?>">
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-10 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-170px">Address complement</span>
                        <input type="text" id="direccioncomplemento" name="direccioncomplemento" class="form-control form-control-sm" maxlength="100" value="<?= $objClientes->direccionComplemento ?>">
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">ZIP code</span>
                        <input type="text" id="codigopostal" name="codigopostal" class="form-control form-control-sm" minlength="5" maxlength="5" value="<?= $objClientes->codigoPostal ?>">
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Phone number</span>
                        <input type="text" id="telefono" name="telefono" class="form-control form-control-sm" maxlength="50" value="<?= $objClientes->telefono ?>">
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-10 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Email</span>
                        <input type="email" id="correoelectronico" name="correoelectronico" class="form-control form-control-sm" maxlength="100" value="<?= $objClientes->correoElectronico ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2 justify-content-between">
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
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation data</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objClientes->fechaCreacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objClientes->usuarioCreo ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification data</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= date("m/d/Y") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $_SESSION["usuario"] ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="cid" name="cid" value="<?= $objClientes->clienteId ?>">
        <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">

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