//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmar'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));

//-----------------------------------------------

document.getElementById("btnregresar").addEventListener("click", () => {
    window.location.href="?mod=admin&opc=sucursales";
});

document.getElementById("btneditar").addEventListener("click", () => {
    window.location.href="?mod=admin&opc=sucursales&subopc=editarsucursal&sid=" + document.getElementById("sid").value;
});

document.getElementById("btneliminar").addEventListener("click", () => {
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
    datos.append("sid", document.getElementById("sid").value);
    datos.append("uid", document.getElementById("uid").value);

    fetch(
        "./mods/admin/sucursales/procs/eliminar.php",
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
            window.location.href="?mod=admin&opc=sucursales";
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