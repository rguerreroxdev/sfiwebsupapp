//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalConfirmarEliminar = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
let modalConfirmarCerrarAbrir = new bootstrap.Modal(document.getElementById('modalConfirmarCerrarAbrir'));
let modalConfirmarProcesar = new bootstrap.Modal(document.getElementById('modalConfirmarProcesar'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));
let modalEmitirEtiquetas = new bootstrap.Modal(document.getElementById('modalEmitirEtiquetas'));

let estadoDisabledBtnEditar = document.getElementById("btneditar").getAttribute("disabled");
let estadoDisabledBtnEliminar = document.getElementById("btneliminar").getAttribute("disabled");
let estadoDisabledBtnCerrarAbrir = document.getElementById("btncerrarabrir").getAttribute("disabled");
let estadoDisabledBtnProcesar = document.getElementById("btnprocesar").getAttribute("disabled");

//-----------------------------------------------

document.getElementById("btnetiquetas").addEventListener('click', function() {
    modalEmitirEtiquetas.show();
});

//-----------------------------------------------

document.getElementById("btnregresar").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=recepcionesdecarga";
});

document.getElementById("btneditar").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=recepcionesdecarga&subopc=registrorecepcion&rid=" + document.getElementById("rid").value;
});

document.getElementById("btnimprimir").addEventListener("click", () => {
    imprimirRecepcion();
});

document.getElementById("btneliminar").addEventListener("click", () => {
    modalConfirmarEliminar.show();
});

document.getElementById("btncerrarabrir").addEventListener("click", () => {
    modalConfirmarCerrarAbrir.show();
});

document.getElementById("btnprocesar").addEventListener("click", () => {
    modalConfirmarProcesar.show();
});

document.getElementById("btnconfirmaeliminar").addEventListener("click", () => {
    let textoDeBoton = document.getElementById("btneliminar").innerText.trim();
    
    if (textoDeBoton.toLowerCase() == "delete")
    {
        eliminar();
    }
    else if (textoDeBoton.toLowerCase() == "cancel")
    {
        cambiarEstado("ANU");
    }
});

document.getElementById("btnconfirmacerrarabrir").addEventListener("click", () => {
    let estadoActual = document.getElementById("estadoActual").value;
    if (estadoActual == "FOR")
    {
        cambiarEstado("CER");
    }
    if (estadoActual == "CER")
    {
        cambiarEstado("FOR");
    }
});

document.getElementById("btnconfirmaprocesar").addEventListener("click", () => {
    cambiarEstado("PRO");
});

//-----------------------------------------------

function msrpFormatter(value, row, index) {
    return (Number(row.MSRP).toFixed(2));
}

function costoOrigenFormatter(value, row, index) {
    return (Number(row.COSTOORIGEN).toFixed(2));
}

function costoDistFormatter(value, row, index) {
    return (Number(row.COSTODIST).toFixed(2));
}

function inventarioFormatter(value, row, index) {
    return [
        '<a href="javascript:verInventario(' + row.RECEPCIONDECARGAID + ', ' + row.RECEPCIONDECARGADETALLEID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function eliminar()
{
    document.getElementById("btneditar").setAttribute("disabled", "true");
    document.getElementById("btneliminar").setAttribute("disabled", "true");
    document.getElementById("btncerrarabrir").setAttribute("disabled", "true");
    document.getElementById("btnprocesar").setAttribute("disabled", "true");
    modalConfirmarEliminar.hide();

    let datos = new FormData();
    datos.append("rid", document.getElementById("rid").value);
    datos.append("uid", document.getElementById("uid").value);

    fetch(
        "./mods/inventario/recepcionesdecarga/procs/eliminar.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizar(data, "eliminar"))
    .catch(error => console.warn(error));
}

//-----------------------------------------------

function finalizar(data, accion)
{
    if (data.error == 0)
    {
        let mensaje = "";
        let url = "?mod=inventario&opc=recepcionesdecarga&subopc=verrecepcion&rid=" + document.getElementById("rid").value;
        switch (accion) {
            case "eliminar":
                mensaje = "The document was deleted.";
                url = "?mod=inventario&opc=recepcionesdecarga"
                break;
        
            case "CER":
                mensaje = "The document was closed.";
                break;

            case "FOR":
                mensaje = "The document was opened.";
                break;

            case "PRO":
                mensaje = "The document was posted.";
                break;

            case "ANU":
                mensaje = "The document was canceled.";
                break;
        }

        document.getElementById("mensajetoast").innerHTML = mensaje;
        toastMensaje.show();
        setTimeout(() => {
            window.location.href = url;
        }, 2000);
    }
    else
    {
        document.getElementById("mensajedeerror").innerHTML = data.mensaje;
        modalMensaje.show();

        if(estadoDisabledBtnEditar == null) document.getElementById("btneditar").removeAttribute("disabled");
        if(estadoDisabledBtnEliminar == null) document.getElementById("btneliminar").removeAttribute("disabled");
        if(estadoDisabledBtnCerrarAbrir == null) document.getElementById("btncerrarabrir").removeAttribute("disabled");
        if(estadoDisabledBtnProcesar == null) document.getElementById("btnprocesar").removeAttribute("disabled");
    }
}

//-----------------------------------------------

function imprimirRecepcion()
{
    let url = 'mods/inventario/recepcionesdecarga/procs/imprimirrecepcion.php?rid=' + document.getElementById("rid").value;
    window.open(url, "_blank");
}

//-----------------------------------------------

function verInventario(rId, rdId)
{
    window.location.href="?mod=inventario&opc=recepcionesdecarga&subopc=verinventario&rid=" + rId + "&rdid=" + rdId;
}

//-----------------------------------------------

function cambiarEstado(estado)
{
    let datos = new FormData();
    datos.append("rid", document.getElementById("rid").value);
    datos.append("uid", document.getElementById("uid").value);
    datos.append("estado", estado);

    fetch(
        "./mods/inventario/recepcionesdecarga/procs/cambiarestado.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizar(data, estado))
    .catch(error => console.warn(error));

    modalConfirmarCerrarAbrir.hide();
    modalConfirmarProcesar.hide();
}

//-----------------------------------------------

document.getElementById("frmprint").addEventListener("submit", (event) => {
    event.preventDefault();

    const rid = document.getElementById("rid").value;
    const ubi = document.getElementById("ubicacioninicial").value;

    let url = `./mods/inventario/etiquetas/procs/imprimiretiquetas.php?tip=R&rid=${rid}&ubi=${ubi}`;
    window.open(url, "_blank");
});

//-----------------------------------------------

function setPos(pos)
{
    document.getElementById("ubicacioninicial").value = pos;
}

//-----------------------------------------------

$('#tabledatos').on('post-body.bs.table', function () {
    let table = document.getElementById('tabledatos');
    let sum = 0;
    
    for (let i = 1; i < table.rows.length; i++) {
        if (table.rows[i].cells.length <= 1)
            return;

        let cellValue = parseInt(table.rows[i].cells[1].innerText);
        if (!isNaN(cellValue))
        {
            sum += cellValue;
        }
    }
    
    document.getElementById("totalitems").innerHTML = sum;
});

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------