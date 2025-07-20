//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let toastError = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastError'));
let modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmar'));
let modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));

//-----------------------------------------------

document.getElementById("btnregresar").addEventListener("click", () => {
    window.location.href="?mod=admin&opc=perfiles";
});

if (document.getElementById("btneditar"))
{
    document.getElementById("btneditar").addEventListener("click", () => {
        let esAdmin = document.getElementById("pid").value == 1;

        if (esAdmin)
        {
            document.getElementById("textodeerror").innerHTML = "You can not change the administrator profile.";
            toastError.show();
            return;
        }

        document.getElementById("editnombre").value = document.getElementById("nombre").value;
        modalEditar.show();
        setTimeout(() => {
            document.getElementById("editnombre").focus();
        }, 500);
    });
}

document.getElementById("btneliminar").addEventListener("click", () => {
    let esAdmin = document.getElementById("pid").value == 1;

    if (esAdmin)
    {
        document.getElementById("textodeerror").innerHTML = "You can not delete the administrator profile.";
        toastError.show();
        return;
    }

    modalConfirmar.show();
});

document.getElementById("btnconfirmaguardar").addEventListener("click", () => {
    guardar();
});

document.getElementById("btnconfirmaeliminar").addEventListener("click", () => {
    eliminar();
});

document.getElementById("modulo").addEventListener("change", () => {
    actualizarComboMenus();
});

document.getElementById("menu").addEventListener("change", () => {
    actualizarTablaDeAccesos();
});

//-----------------------------------------------

function actualizarComboMenus()
{
    let datos = new FormData();
    datos.append("mid", document.getElementById("modulo").value);

    fetch(
        "./mods/admin/perfiles/procs/getmenus.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => {
        document.getElementById("menu").innerHTML = data;
        actualizarTablaDeAccesos()
    })
    .catch(error => console.warn(error));    
}

//-----------------------------------------------

function actualizarTablaDeAccesos()
{
    $("#tabledatos").bootstrapTable("refresh");
}

//-----------------------------------------------

function eliminar()
{
    document.getElementById("btneliminar").setAttribute("disabled", "true");
    modalConfirmar.hide();

    let datos = new FormData();
    datos.append("pid", document.getElementById("pid").value);
    datos.append("uid", document.getElementById("uid").value);

    fetch(
        "./mods/admin/perfiles/procs/eliminar.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizarEliminacion(data))
    .catch(error => console.warn(error));
}

//-----------------------------------------------

function finalizarEliminacion(data)
{
    if (data.error == 0)
    {
        document.getElementById("textodemensaje").innerHTML = "The profile was deleted.";
        toastMensaje.show();
        setTimeout(() => {
            window.location.href="?mod=admin&opc=perfiles";
        }, 2000);
    }
    else
    {
        document.getElementById("mensajedeerror").innerHTML = data.mensaje;
        modalMensaje.show();

        document.getElementById("btneliminar").removeAttribute("disabled");
    }
}

//-----------------------------------------------

function guardar()
{
    let nombre = document.getElementById("editnombre");
    nombre.value = nombre.value.trim();

    if (nombre.value == "")
    {
        document.getElementById("textodeerror").innerHTML = "The name can not be empty.";
        toastError.show();
        return;
    }
    
    modalEditar.hide();

    let datos = new FormData();
    datos.append("nombre", document.getElementById("editnombre").value);
    datos.append("pid", document.getElementById("pid").value);
    datos.append("uid", document.getElementById("uid").value);

    fetch(
        "./mods/admin/perfiles/procs/guardaredit.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizarGuardar(data))
    .catch(error => console.warn(error));
}

function finalizarGuardar(data)
{
    if (data.error == 0)
    {
        document.getElementById("textodemensaje").innerHTML = "The profile was updated.";
        toastMensaje.show();
        setTimeout(() => {
            window.location.href="?mod=admin&opc=perfiles&subopc=verperfil&pid=" + document.getElementById("pid").value;
        }, 2000);
    }
    else
    {
        document.getElementById("mensajedeerror").innerHTML = data.mensaje;
        modalMensaje.show();
    }
}

//-----------------------------------------------

function accesoEditFormatter(value, row, index) {
    let isChecked = row.ESTADO == 1 ? "checked" : "";
    return (
        `<div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" ${isChecked} onclick="cambiarAcceso(${row.PERFILDETALLEID}, this)">
        </div>`
    );
}

function descripcionFormatter(value, row, index) {
    let cantidadPuntos = row.CODIGO.split('.').length - 1;
    let tabulacion = cantidadPuntos >= 3 ? "&nbsp;&nbsp;&nbsp;&nbsp;" : "";
    return (
        tabulacion + row.DESCRIPCION
    );
}

function accesoFormatter(value, row, index) {
    let isChecked = row.ESTADO == 1 ? "checked" : "";
    return (
        `<div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" ${isChecked} disabled>
        </div>`
    );
}

//-----------------------------------------------

function cambiarAcceso(id, elemento)
{
    let estado = elemento.checked ? 1 : 0;

    let datos = new FormData();
    datos.append("pid", document.getElementById("pid").value);
    datos.append("uid", document.getElementById("uid").value);
    datos.append("pdid", id);
    datos.append("estado", estado);

    fetch(
        "./mods/admin/perfiles/procs/guardarestado.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => {
        document.getElementById("textodemensaje").innerHTML = "Access was updated.";
        toastMensaje.show();    
    })
    .catch(error => console.warn(error));
}

//-----------------------------------------------

function customParams(p)
{
    let pid = document.getElementById("pid").value;
    let modid = document.getElementById("modulo").value;
    let menu = document.getElementById("menu").value;
    return {
        pid: pid,
        modid: modid,
        menu: menu
    };
}

//-----------------------------------------------