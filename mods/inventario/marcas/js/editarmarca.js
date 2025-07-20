//-----------------------------------------------

document.getElementById("nombre").focus();

document.getElementById("btncancelar").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=marcas&subopc=vermarca&mid=" + document.getElementById("mid").value;
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

//-----------------------------------------------

function guardar(datos)
{
    document.getElementById("btnguardar").setAttribute("disabled", "true");
    document.getElementById("btnguardarspinner").classList.remove("visually-hidden");

    fetch(
        "./mods/inventario/marcas/procs/guardaredit.php",
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
            window.location.href="?mod=inventario&opc=marcas&subopc=vermarca&mid=" + document.getElementById("mid").value;
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
    nombre.value = nombre.value.trim();
}

//-----------------------------------------------