//-----------------------------------------------

document.getElementById("nombre").focus();

document.getElementById("btncancelar").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=clientes";
});

document.getElementById('codigopostal').addEventListener('keypress', function(e) {
    if (e.key < '0' || e.key > '9') {
        e.preventDefault();
    }
});

//-----------------------------------------------

document.getElementById("frm").addEventListener("submit", (event) => {
    event.preventDefault();

    aplicarTrimAElementos();

    if (event.target.checkValidity())
    {
        let datos = new FormData(event.target);
        guardar(datos);
    }
});

//-----------------------------------------------

// Definir elementos para mostrar mensajes
let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));

let toastErrorElement = document.getElementById('toastError');
let toastError = bootstrap.Toast.getOrCreateInstance(toastErrorElement);

//-----------------------------------------------

function guardar(datos)
{
    document.getElementById("btnguardar").setAttribute("disabled", "true");
    document.getElementById("btnguardarspinner").classList.remove("visually-hidden");

    fetch(
        "./mods/facturacion/clientes/procs/guardarnuevo.php",
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
            window.location.href="?mod=facturacion&opc=clientes&subopc=vercliente&cid=" + data.id;
        }, 2000);
    }
    else
    {
        document.getElementById("mensajedeerror").innerHTML = data.mensaje;
        modalMensaje.show();

        document.getElementById("btnguardar").removeAttribute("disabled");
    }
}

//-----------------------------------------------

function aplicarTrimAElementos()
{
    let nombre = document.getElementById("nombre");
    let direccion = document.getElementById("direccion");
    let direccionComplemento = document.getElementById("direccioncomplemento");
    let telefono = document.getElementById("telefono");
    let codigoPostal = document.getElementById("codigopostal");
    let correo = document.getElementById("correoelectronico");
    
    nombre.value = nombre.value.trim();
    direccion.value = direccion.value.trim();
    direccionComplemento.value = direccionComplemento.value.trim();
    telefono.value = telefono.value.trim();
    correo.value = correo.value.trim();
    codigoPostal.value = codigopostal.value.replace(/[^0-9]/g, '');
}

//-----------------------------------------------
