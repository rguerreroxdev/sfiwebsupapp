//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmar'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));

//-----------------------------------------------

document.getElementById("btnregresar").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=tiposdepago";
});

document.getElementById("btneditar").addEventListener("click", () => {
    if (document.getElementById("tpid").value == 1)
    {
        document.getElementById("mensajedeerror").innerHTML = "You can not edit this form of payment (FINANCE).";
        modalMensaje.show();

        return;
    }

    window.location.href="?mod=facturacion&opc=tiposdepago&subopc=editartipodepago&tpid=" + document.getElementById("tpid").value;
});

document.getElementById("btneliminar").addEventListener("click", () => {
    if (document.getElementById("tpid").value == 1)
    {
        document.getElementById("mensajedeerror").innerHTML = "You can not delete this form of payment (FINANCE).";
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
    datos.append("tpid", document.getElementById("tpid").value);
    datos.append("uid", document.getElementById("uid").value);

    fetch(
        "./mods/facturacion/tiposdepago/procs/eliminar.php",
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
            window.location.href="?mod=facturacion&opc=tiposdepago";
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