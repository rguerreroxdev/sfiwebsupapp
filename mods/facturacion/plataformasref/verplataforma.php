<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("FAC", "03.01.02");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Obtener los datos de la plataforma recibida en la URL
        $plataformaDeReferenciaId = -1;
        if (isset($_GET["prid"]))
        {
            $plataformaDeReferenciaId = is_numeric($_GET["prid"]) ? intval($_GET["prid"]) : -1;
        }


        require_once("inc/class/PlataformasDeReferencia.php");
        $objPlataformas = new PlataformasDeReferencia($conn);

        $objPlataformas->getById($plataformaDeReferenciaId);

        // Si el registro no existe, retornar a listado
        if ($objPlataformas->plataformaDeReferenciaId == -1)
        {
            echo ("<script>window.location.href='?mod=facturacion&opc=plataformasref'</script>");
            exit();
        }

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("03.01.02");
        $accesoEditar = in_array("03.01.02.02", $accesos) ? "" : " disabled";
        $accesoEliminar = in_array("03.01.02.03", $accesos) ? "" : " disabled";
?>

<h3>Referral platforms - Platform data</h3>

<nav>
    <div class="nav nav-tabs small" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-editar-tab" data-bs-toggle="tab" data-bs-target="#nav-editar" type="button" role="tab" aria-controls="nav-editar" aria-selected="true">Category data</button>
        <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Save dates</button>
    </div>
</nav>

<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-editar" role="tabpanel" aria-labelledby="nav-editar-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Name</span>
                        <input type="text" id="nombre" name="nombre" class="form-control form-control-sm" value="<?= $objPlataformas->nombre ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2 justify-content-between">
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
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Creation date</span>
                        <input type="text" id="fechacreacion" name="fechacreacion" class="form-control form-control-sm" value="<?= $objPlataformas->fechaCreacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who created</span>
                        <input type="text" id="usuariocreo" name="usuariocreo" class="form-control form-control-sm" value="<?= $objPlataformas->usuarioCreo ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objPlataformas->fechaModificacion->format("m/d/Y H:i") ?>" readonly>
                    </div>
                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objPlataformas->usuarioModifica ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="prid" name="prid" value="<?= $objPlataformas->plataformaDeReferenciaId ?>">
        <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">

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