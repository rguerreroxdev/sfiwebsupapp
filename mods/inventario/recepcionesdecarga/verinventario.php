<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Verificar acceso a módulo y opción de menú
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesoAOpcion = $usuarioAccesos->validarAccesoAOpcionDeMenu("INV", "02.02.01");

    if (!$accesoAOpcion)
    {
        require_once("inc/errordeacceso.inc.php");
    }
    else
    {   // Se cierra al final del archivo

        $recepcionId = isset($_GET["rid"]) ? $_GET["rid"] : -1;
        $recepcionId = is_numeric($recepcionId) ? $recepcionId : -1;

        $recepcionDetalleId = isset($_GET["rdid"]) ? $_GET["rdid"] : -1;
        $recepcionDetalleId = is_numeric($recepcionDetalleId) ? $recepcionDetalleId : -1;

        require_once("inc/class/RecepcionesDeCarga.php");
        $objRecepcion = new RecepcionesDeCarga($conn);
        $objRecepcion->getById($recepcionId);

        // Si el registro de la tabla maestra no existe, retornar a listado
        if ($objRecepcion->recepcionDeCargaId == -1)
        {
            echo ("<script>window.location.href='?mod=inventario&opc=recepcionesdecarga'</script>");
            exit();
        }

        require_once("inc/class/RecepcionesDeCargaDetalle.php");
        $objRecepcionDetalle = new RecepcionesDeCargaDetalle($conn);
        $objRecepcionDetalle->getById($recepcionDetalleId);

        // Si el registro de la tabla maestra no existe, retornar a listado
        if ($objRecepcionDetalle->recepcionDeCargaDetalleId == -1)
        {
            echo ("<script>window.location.href='?mod=inventario&opc=recepcionesdecarga&subopc=verrecepcion&rid=$recepcionId'</script>");
            exit();
        }

        // Obtener lista de inventario
        require_once("inc/class/Inventario.php");
        $objInventario = new Inventario($conn);
        $listaDeInventario = $objInventario->itemsDeRecepcionDetalleId($recepcionDetalleId);

        // Obtener accesos a opciones sobre registros
        $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("02.03.01");
        $accesoEditarSerie = in_array("02.03.01.01", $accesos);
?>

<h3>Bills of lading - Inventory detail</h3>

<div class="p-3 bg-body rounded shadow-sm">
    <div class="row">
        <div class="col-sm-3">
            <div class="input-group input-group-sm">
                <label class="input-group-text width-95px" for="sucursal">Store</label>
                <input type="text" id="sucursal" name="sucursal" class="form-control form-control-sm" value="<?= $objRecepcion->sucursal ?>" readonly>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text width-95px">Correlative</span>
                <input type="text" id="correlativo" name="correlativo" class="form-control form-control-sm text-center" value="<?= $objRecepcion->correlativo ?>" readonly>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-sm-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text width-95px">Supplier</span>            
                <input type="text" id="codigoproveedor" name="codigoproveedor" class="form-control form-control-sm" style="max-width: 70px;" value="<?= $objRecepcion->codigoProveedor ?>" readonly>
                <input type="text" id="proveedor" name="proveedor" class="form-control form-control-sm" value="<?= $objRecepcion->proveedor ?>" readonly>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text width-95px">Load ID</span>
                <input type="text" id="numerodedocumento" name="numerodedocumento" class="form-control form-control-sm" value="<?= $objRecepcion->numeroDeDocumento ?>" readonly>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-sm-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text width-130px">Document date</span>
                <input type="text" id="fechadeemision" name="fechadeemision" class="form-control form-control-sm" value="<?= $objRecepcion->fechaDeEmision->format("m/d/Y") ?>" readonly>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text width-130px">Reception date</span>
                <input type="text" id="fechaderecepcion" name="fechaderecepcion" class="form-control form-control-sm" value="<?= $objRecepcion->fechaDeRecepcion->format("m/d/Y") ?>" readonly>
            </div>
        </div>
    </div>
</div>

<form id="frm">

<div class="p-3 mt-2 bg-body rounded shadow-sm">
    <div class="col-sm-9">
        Selected row
        <table class="table table-sm small col-9">
            <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $objRecepcionDetalle->cantidad ?></td>
                    <td><?= $objRecepcionDetalle->codigoProducto ?></td>
                    <td><?= $objRecepcionDetalle->categoria ?></td>
                    <td><?= $objRecepcionDetalle->marca ?></td>
                    <td><?= $objRecepcionDetalle->producto ?></td>
                    <td><?= $objRecepcionDetalle->descripcion ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <div class="row justify-content-end">
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-secondary" id="btnregresar"><i class="bi bi-arrow-return-left"></i> Go back</button>
                </div>
                <div class="col-auto">
                    <?php if($accesoEditarSerie): ?>
                    <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
                    <button type="submit" class="btn btn-sm btn-primary" id="btnguardar"><i class="bi bi-floppy2"></i> Update serial numbers</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="p-3 mt-2 bg-body rounded shadow-sm">
    <div class="col-sm-6">
        Inventory from selected row
        <table class="table table-sm small">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Serial number</th>
                </tr>
            </thead>
            <tbody>
                <?php $conteo = 1 ?>
                <?php foreach($listaDeInventario as $item): ?>
                <tr>
                    <td><?php echo $conteo; $conteo++; ?></td>
                    <td><?= $item["CODIGOINVENTARIO"] ?></td>
                    <?php if($accesoEditarSerie): ?>
                    <td>
                        <input type="hidden" id="iid[]" name="iid[]" value="<?= $item["INVENTARIOID"] ?>">
                        <input type="text" class="form-control form-control-sm" id="serie[]" name="serie[]" maxlength="50" value="<?= $item["SERIE"] ?>">
                    </td>
                    <?php else: ?>
                    <td><?= $item["SERIE"] ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</form>

<input type="hidden" id="rid" name="rid" value="<?= $objRecepcion->recepcionDeCargaId ?>">

<div class="toast-container p-5 position-fixed top-0 start-50 translate-middle-x" id="toastPlacement">
    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastMensaje">
        <div class="d-flex">
            <div class="toast-body" id="mensajetoast">
                The serial numbers were updated.
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

<div class="modal fade small" id="modalConfirmarActualizar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Update serial numbers</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Confirm that you are going to update the serial numbers.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btnconfirmaactualizar">Yes, update</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">No, cancel</button>
            </div>
        </div>
    </div>
</div>

<?php 
    } // else de mostrar contenido por acceso a opción
?>