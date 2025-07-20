//-----------------------------------------------

let modalSeleccionarMarca = new bootstrap.Modal(document.getElementById('modalSeleccionarMarca'));

document.getElementById("btnmarca").addEventListener("click", () => {
    modalSeleccionarMarca.show();
});

//-----------------------------------------------

document.getElementById("btncancelar").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=serviciosyprods&subopc=verservicioprod&spid=" + document.getElementById("spid").value;
});

document.getElementById("btneliminamarca").addEventListener("click", () => {
    document.getElementById("marca").value = "";
    document.getElementById("marcanombre").value = "";    
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
        "./mods/facturacion/serviciosyprods/procs/guardaredit.php",
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
            window.location.href="?mod=facturacion&opc=serviciosyprods&subopc=verservicioprod&spid=" + document.getElementById("spid").value;
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
    let modelo = document.getElementById("modelo");
    let descripcion = document.getElementById("descripcion");
    
    modelo.value = modelo.value.trim();
    descripcion.value = descripcion.value.trim();
}

//-----------------------------------------------

function marcasOperateFormatter(value, row, index) {
    return [
        '<a class="sel-marca" href="javascript:void(0)" title="Aplicar">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.marcasOperateEvents = {
    "click .sel-marca": function(e, value, row, index) {
        document.getElementById("marca").value = row.MARCAID;
        document.getElementById("marcanombre").value = row.NOMBRE;

        modalSeleccionarMarca.hide();
        document.getElementById("modelo").focus();
    }
}

//-----------------------------------------------

function rowMarcaIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------

document.getElementById("descripcion").focus();

//-----------------------------------------------