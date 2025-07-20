//-----------------------------------------------

document.getElementById("sucursal").focus();

document.getElementById("btncancelar").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=configuracion";
});

const inputimpuesto = document.getElementById('impuesto');
// Limitar a un máximo de 5 caracteres
inputimpuesto.addEventListener('input', function() {
    if (inputimpuesto.value.length > 5) {
        inputimpuesto.value = inputimpuesto.value.slice(0, 5);
    }
});

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
        "./mods/facturacion/configuracion/procs/guardarnuevo.php",
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
            window.location.href="?mod=facturacion&opc=configuracion&subopc=verconfiguracion&csid=" + data.id;
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
    let prefijo = document.getElementById("prefijo");
    
    prefijo.value = prefijo.value.trim();
}

//-----------------------------------------------

let elementNoConfig = document.getElementById('modalNoConfig');
if (elementNoConfig)
{
    let modalNoConfig = new bootstrap.Modal(elementNoConfig);
    document.getElementById("btnRetornarALista").addEventListener("click", () => {
        window.location.href="?mod=facturacion&opc=configuracion";
    });

    modalNoConfig.show();
}

//-----------------------------------------------