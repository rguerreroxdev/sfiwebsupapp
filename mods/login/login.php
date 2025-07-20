        <section class="d-flex justify-content-center align-items-center flex-column" style="min-height: 80vh;">
            <div class="mb-2">
                <img src="./imgs/logo.png" style="width: 300px;">
            </div>
            <div class="card" style="min-width: 300px;">
                <div class="card-body">
                    <h5 class="card-header mb-2">Login</h5>
                    <form id="frmInicioSesion">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control form-control-sm" id="usuario" name="usuario" placeholder="user" required>
                            <label for="usuario">User</label>
                        </div>
                        <div class="form-floating">
                            <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="password" required>
                            <label for="contrasena">Password</label>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2" id="btnSubmit">
                            Login
                            <span class="spinner-border spinner-border-sm visually-hidden" id="btnAceptarSpinner" role="status" aria-hidden="true"></span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
        <div class="toast-container p-5 position-fixed top-0 start-50 translate-middle-x" id="toastPlacement">
            <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastError">
                <div class="d-flex">
                    <div class="toast-body">
                        Incorrect user or password.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
