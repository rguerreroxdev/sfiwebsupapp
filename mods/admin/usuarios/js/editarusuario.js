//-----------------------------------------------

let filasEliminadas = [];

//-----------------------------------------------

document.getElementById("nombre").focus();

document.getElementById("btncancelar").addEventListener("click", () => {
    window.location.href="?mod=admin&opc=usuarios&subopc=verusuario&uid=" + document.getElementById("uid").value;
});

document.getElementById("frm").addEventListener("submit", (event) => {
    event.preventDefault();

    aplicarTrimAElementos();
    if (event.target.checkValidity())
    {
        let datos = new FormData(event.target);
        let jsonFilasEliminadas = JSON.stringify(filasEliminadas);
        datos.append("filaseliminadas", jsonFilasEliminadas);

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
        "./mods/admin/usuarios/procs/guardaredit.php",
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
            window.location.href="?mod=admin&opc=usuarios&subopc=verusuario&uid=" + document.getElementById("uid").value;
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
    let usuario = document.getElementById("usuario");
    let password = document.getElementById("password");
    
    nombre.value = nombre.value.trim();
    usuario.value = usuario.value.trim();
    password.value = password.value.trim() == "" ? "" : password.value;
}

//-----------------------------------------------

function showPassword() {
    let passwordInput = document.getElementById("password");
    passwordInput.type = "text";
}

function hidePassword() {
    let passwordInput = document.getElementById("password");
    passwordInput.type = "password";
}

//-----------------------------------------------

function eliminarFila(boton)
{
    let fila = boton.parentNode.parentNode;

    sid = fila.cells[1].getElementsByTagName("input")[0].value;
    if (sid != "")
    {
        filasEliminadas.push(sid);
    }
    
    fila.parentNode.removeChild(fila);
}

//-----------------------------------------------

function agregarFila()
{
    if (document.getElementById("sucursal").value == -1)
    {
        document.getElementById("textodeerror").innerHTML = "You must select a Store to add rows.";
        toastError.show();
        return;
    }

    if (!validarSucursal())
    {
        document.getElementById("textodeerror").innerHTML = "The store is already selected.";
        toastError.show();
        return;
    }

    let selectSucursal = document.getElementById("sucursal");
    let sucursalId = selectSucursal.value;
    let sucursal = selectSucursal.options[selectSucursal.selectedIndex].text;

    let tabla = document.getElementById("tablaSucursales").getElementsByTagName('tbody')[0];
    let newRow = tabla.insertRow(tabla.rows.length);

    let celda1 = newRow.insertCell(0);
    let celda2 = newRow.insertCell(1);

    celda1.innerHTML = `${sucursal}
                        <input type="hidden" id="sucursalid[]" name="sucursalid[]" value="${sucursalId}">`;
    celda2.innerHTML = `<input type="hidden" id="sucursalxusuarioid[]" name="sucursalxusuarioid[]" value="">
                        <button class="btn btn-sm btn-outline-danger" type="button" onclick="eliminarFila(this)" title="Delete"><i class="bi bi-trash"></i></button>`;
    
    setTimeout(function() {
        celda1.getElementsByTagName("input")[0].focus();
    }, 0);
}

function validarSucursal()
{
    valor = document.getElementById("sucursal").value;

    const inputs = document.querySelectorAll('input[id="sucursalid[]"]');

    for (let input of inputs) {
        if (input.value === valor) {
            return false;
        }
    }

    return true;
}

//-----------------------------------------------