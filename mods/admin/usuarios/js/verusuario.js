//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmar'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));

//-----------------------------------------------

document.getElementById("btnregresar").addEventListener("click", () => {
    window.location.href="?mod=admin&opc=usuarios";
});

document.getElementById("btneditar").addEventListener("click", () => {
    window.location.href="?mod=admin&opc=usuarios&subopc=editarusuario&uid=" + document.getElementById("uid").value;
});

document.getElementById("btneliminar").addEventListener("click", () => {
    let uid = document.getElementById("uid").value;
    let loggeduid = document.getElementById("loggeduid").value;

    if (uid == 1)
    {
        document.getElementById("mensajedeerror").innerHTML = "You can not delete the administrator user.";
        modalMensaje.show();
        return;
    }

    if (uid == loggeduid)
    {
        document.getElementById("mensajedeerror").innerHTML = "You can not delete your own user.";
        modalMensaje.show();
        return;
    }

    modalConfirmar.show();
});

document.getElementById("btnconfirmaeliminar").addEventListener("click", () => {
    eliminar();
});

//-----------------------------------------------

function eliminar()
{
    document.getElementById("btneditar").setAttribute("disabled", "true");
    document.getElementById("btneliminar").setAttribute("disabled", "true");
    modalConfirmar.hide();

    let datos = new FormData();
    datos.append("uid", document.getElementById("uid").value);
    datos.append("loggeduid", document.getElementById("loggeduid").value);

    fetch(
        "./mods/admin/usuarios/procs/eliminar.php",
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
    if (data.error == 0)
    {
        toastMensaje.show();
        setTimeout(() => {
            window.location.href="?mod=admin&opc=usuarios";
        }, 2000);
    }
    else
    {
        document.getElementById("mensajedeerror").innerHTML = data.mensaje;
        modalMensaje.show();

        document.getElementById("btneditar").removeAttribute("disabled");
        document.getElementById("btneliminar").removeAttribute("disabled");
    }
}

//-----------------------------------------------