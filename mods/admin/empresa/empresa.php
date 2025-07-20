<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("ADM", "01.01.01");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        // Obtener datos de la empresa
        require_once("inc/class/Empresa.php");
        $objEmpresa = new Empresa($conn);

        $objEmpresa->getDatos();

        // Obtener accesos a opciones sobre registro
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("01.01.01");
        $accesoEditar = in_array("01.01.01.01", $accesos) ? "" : " disabled";
?>

<h3>Company</h3>

<nav>
    <div class="nav nav-tabs small" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-editar-tab" data-bs-toggle="tab" data-bs-target="#nav-editar" type="button" role="tab" aria-controls="nav-editar" aria-selected="true">Company data</button>
        <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Save dates</button>
    </div>
</nav>

<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-editar" role="tabpanel" aria-labelledby="nav-editar-tab" tabindex="0">

        <div class="p-3 bg-body rounded shadow-sm">

            <div class="row">
                <div class="col-sm-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Name</span>
                        <input type="text" id="nombre" name="nombre" class="form-control form-control-sm" value="<?= $objEmpresa->nombre ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Address</span>
                        <input type="text" id="direccion" name="direccion" class="form-control form-control-sm" value="<?= $objEmpresa->direccion ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-130px">Phone number</span>
                        <input type="text" id="telefono" name="telefono" class="form-control form-control-sm" value="<?= $objEmpresa->telefono ?>" readonly>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row mt-2">
                <strong>Settings</strong>
            </div>
            <div class="row mt-2">
                <div class="col-3">
                    <div class="text-center small">
                        QR code to display on labels
                    </div>
                    <div class="text-center">
                        <img src="./imgs/QRCode.jpg?t=<?= date("mdYHis"); ?>" style="width: 100px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2 justify-content-between">
            <div class="col">
            </div>
            <div class="col">
                <button class="btn btn-sm btn-primary" id="btneditar" style="min-width: 75px;"<?= $accesoEditar ?>><i class="bi bi-pencil-square"></i> Edit</button>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
        <div class="p-3 bg-body rounded shadow-sm">
            <div class="row">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">Modification date</span>
                        <input type="text" id="fechamodificacion" name="fechamodificacion" class="form-control form-control-sm" value="<?= $objEmpresa->fechaModificacion != null ? $objEmpresa->fechaModificacion->format("m/d/Y H:i") : "-" ?>" readonly>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text width-160px">User who modified</span>
                        <input type="text" id="usuariomodifico" name="usuariomodifico" class="form-control form-control-sm" value="<?= $objEmpresa->usuarioIdModificacion != null ? $objEmpresa->usuarioModifica : "-" ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
    </div>

<?php 
    } // else de mostrar contenido por acceso a opción
?>