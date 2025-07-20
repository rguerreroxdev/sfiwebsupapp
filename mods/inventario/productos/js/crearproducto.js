//-----------------------------------------------

let modalSeleccionarMarca = new bootstrap.Modal(document.getElementById('modalSeleccionarMarca'));
let modalSeleccionarColor = new bootstrap.Modal(document.getElementById('modalSeleccionarColor'));

document.getElementById("btnmarca").addEventListener("click", () => {
    modalSeleccionarMarca.show();
});

document.getElementById("btncolor").addEventListener("click", () => {
    modalSeleccionarColor.show();
});

//-----------------------------------------------

document.getElementById("categoria").focus();

document.getElementById("btncancelar").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=productos";
});

const inputmsrp = document.getElementById('msrp');
// Limitar a un máximo de 18 caracteres
inputmsrp.addEventListener('input', function() {
    if (inputmsrp.value.length > 16) {
        inputmsrp.value = inputmsrp.value.slice(0, 16);
    }
});

document.getElementById("frm").addEventListener("submit", (event) => {
    event.preventDefault();

    aplicarTrimAElementos();

    if (!validarMarcaYColor())
    {
        document.getElementById("textodeerror").innerHTML = "Brand and color are required";
        toastError.show();
        return;
    }

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
        "./mods/inventario/productos/procs/guardarnuevo.php",
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
            window.location.href="?mod=inventario&opc=productos&subopc=verproducto&pid=" + data.id;
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

function validarMarcaYColor()
{
    let marcaNombre = document.getElementById("marcanombre").value;
    let colorNombre = document.getElementById("colornombre").value;

    return marcaNombre.length > 0 && colorNombre.length > 0;
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

function coloresOperateFormatter(value, row, index) {
    return [
        '<a class="sel-color" href="javascript:void(0)" title="Aplicar">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.coloresOperateEvents = {
    "click .sel-color": function(e, value, row, index) {
        document.getElementById("color").value = row.COLORID;
        document.getElementById("colornombre").value = row.NOMBRE;

        modalSeleccionarColor.hide();
        document.getElementById("descripcion").focus();
    }
}

//-----------------------------------------------

function rowMarcaIndexFormatter(value, row, index) {
    return index + 1;
}

function rowColorIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------