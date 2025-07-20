<?php
    // $conn -> ya fue definido en encabezado.inc.php

    $usuario = $_SESSION["usuario"];
?>

<h3>Change password</h3>

<hr>

<form id="frm">
<div class="p-3 bg-body rounded shadow-sm">
    <div class="col-sm-6 col-md-4">
        Change the password of user: <strong><?= $usuario ?></strong>
        <div class="input-group input-group-sm mt-2">
            <span class="input-group-text width-170px">Current password<span class="text-danger">&nbsp;*</span></span>
            <input type="password" id="currentpassword" name="currentpassword" class="form-control form-control-sm" value="" maxlength="50" required>
            <button class="btn btn-outline-secondary" type="button" onmousedown="showPassword(this)" onmouseup="hidePassword(this)" onmouseout="hidePassword(this)"><i class="bi bi-eye"></i></button>
        </div>
    </div>

    <div class="col-sm-6 col-md-4 mt-2">
        <div class="input-group input-group-sm">
            <span class="input-group-text width-170px">New password<span class="text-danger">&nbsp;*</span></span>
            <input type="password" id="newpassword" name="newpassword" class="form-control form-control-sm" value="" maxlength="50" required>
            <button class="btn btn-outline-secondary" type="button" onmousedown="showPassword(this)" onmouseup="hidePassword(this)" onmouseout="hidePassword(this)"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="input-group input-group-sm">
            <span class="input-group-text width-170px">Confirm new password<span class="text-danger">&nbsp;*</span></span>
            <input type="password" id="newpasswordconfirm" name="newpasswordconfirm" class="form-control form-control-sm" value="" maxlength="50" required>
            <button class="btn btn-outline-secondary" type="button" onmousedown="showPassword(this)" onmouseup="hidePassword(this)" onmouseout="hidePassword(this)"><i class="bi bi-eye"></i></button>
        </div>
    </div>
</div>

    <div class="col-sm-6 col-md-4 mt-2">
        <div class="row justify-content-between">
            <div class="col-auto">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary" id="btnguardar" style="min-width: 75px;">
                    Save
                    <span class="spinner-border spinner-border-sm visually-hidden" id="btnguardarspinner" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>

    <input type="hidden" id="uid" name="uid" value="<?= $_SESSION["usuarioId"] ?>">
</form>

<div class="toast-container p-5 position-fixed top-0 start-50 translate-middle-x" id="toastPlacement">
    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastMensaje">
        <div class="d-flex">
            <div class="toast-body">
                The new password has been saved.
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