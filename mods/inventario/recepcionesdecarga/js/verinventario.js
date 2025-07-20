//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let toastError = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastError'));
let modalConfirmarActualizar = new bootstrap.Modal(document.getElementById('modalConfirmarActualizar'));

//-----------------------------------------------

document.getElementById("btnregresar").addEventListener("click", () => {
    let rId = document.getElementById("rid").value;
    window.location.href="?mod=inventario&opc=recepcionesdecarga&subopc=verrecepcion&rid=" + rId;
});

document.getElementById("btnconfirmaactualizar").addEventListener("click", () => {
    guardar();
});

//-----------------------------------------------

document.getElementById("frm").addEventListener("submit", (event) => {
    event.preventDefault();
    aplicarTrimAElementos();
    modalConfirmarActualizar.show();
});

function guardar()
{
    let form = document.getElementById('frm');
    let datos = new FormData(form);

    fetch(
        "./mods/inventario/recepcionesdecarga/procs/actualizarseries.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizar(data))
    .catch(error => console.warn(error));
}

function finalizar(data)
{
    if (data.error == 1)
    {
        document.getElementById("textodeerror").innerHTML = data.mensaje;
        toastError.show();
        return;
    }
    
    toastMensaje.show();
    modalConfirmarActualizar.hide();
}

//-----------------------------------------------

function aplicarTrimAElementos()
{
    let inputs = document.getElementsByName('serie[]');
    inputs.forEach(input => {
        input.value = input.value.trim();
    });
}

//-----------------------------------------------