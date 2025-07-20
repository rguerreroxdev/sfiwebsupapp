//-----------------------------------------------

document.getElementById("currentpassword").focus();

document.getElementById("frm").addEventListener("submit", (event) => {
    event.preventDefault();

    aplicarTrimAElementos();
    if (!validarNuevoPassword())
    {
        document.getElementById("textodeerror").innerHTML = "The new password and its confirmation do not match.";
        toastError.show();
        return;
    }

    if (event.target.checkValidity())
    {
        let datos = new FormData(event.target);
        guardar(datos);
    }
});

//-----------------------------------------------

// Definir elementos para mostrar mensajes
let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));

let toastErrorElement = document.getElementById('toastError');
let toastError = bootstrap.Toast.getOrCreateInstance(toastErrorElement);

//-----------------------------------------------

function guardar(datos)
{
    document.getElementById("btnguardar").setAttribute("disabled", "true");
    document.getElementById("btnguardarspinner").classList.remove("visually-hidden");

    fetch(
        "./mods/admin/cambiarcontrasena/procs/guardar.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizar(data))
    .catch(error => console.warn(error));
}

//-----------------------------------------------

function finalizar(data)
{
    document.getElementById("btnguardarspinner").classList.add("visually-hidden");

    if (data.error == 0)
    {
        toastMensaje.show();
        setTimeout(() => {
            window.location.href="?mod=inicio";
        }, 2000);
    }
    else
    {
        document.getElementById("textodeerror").innerHTML = data.mensaje;
        toastError.show();

        document.getElementById("btnguardar").removeAttribute("disabled");
    }
}

//-----------------------------------------------

function aplicarTrimAElementos()
{
    let currentPassword = document.getElementById("currentpassword");
    let newPassword = document.getElementById("newpassword");
    let newPasswordConfirm = document.getElementById("newpasswordconfirm");
    
    currentPassword.value = currentPassword.value.trim() == "" ? "" : currentPassword.value;
    newPassword.value = newPassword.value.trim() == "" ? "" : newPassword.value;
    newPasswordConfirm.value = newPasswordConfirm.value.trim() == "" ? "" : newPasswordConfirm.value;
}

//-----------------------------------------------

function validarNuevoPassword()
{
    let newPassword = document.getElementById("newpassword").value;
    let newPasswordConfirm = document.getElementById("newpasswordconfirm").value;

    return newPassword == newPasswordConfirm;
}

//-----------------------------------------------

function showPassword(event) {
    let passwordInput = event.parentNode.getElementsByTagName("input")[0];
    passwordInput.type = "text";
}

function hidePassword(event) {
    let passwordInput = event.parentNode.getElementsByTagName("input")[0];
    passwordInput.type = "password";
}

//-----------------------------------------------