//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));
let modalConfirmarEliminar = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
let modalConfirmarCerrarAbrir = new bootstrap.Modal(document.getElementById('modalConfirmarCerrarAbrir'));
let modalConfirmarProcesar = new bootstrap.Modal(document.getElementById('modalConfirmarProcesar'));

let estadoDisabledBtnEditar = document.getElementById("btneditar").getAttribute("disabled");
let estadoDisabledBtnEliminar = document.getElementById("btneliminar").getAttribute("disabled");
let estadoDisabledBtnCerrarAbrir = document.getElementById("btncerrarabrir").getAttribute("disabled");
let estadoDisabledBtnProcesar = document.getElementById("btnprocesar").getAttribute("disabled");

//-----------------------------------------------

document.getElementById("btnregresar").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=salidas";
});

document.getElementById("btneditar").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=salidas&subopc=registrosalida&sid=" + document.getElementById("sid").value;
});

document.getElementById("btnimprimir").addEventListener("click", () => {
    imprimirSalida();
});

document.getElementById("btnexcel").addEventListener("click", () => {
    let estadoActual = document.getElementById("estadoActual").value;
    if (estadoActual == "FOR" || estadoActual == "CER")
    {
        document.getElementById("mensajetoast").innerHTML = `The document must have been posted in order to generate an Excel file`;
        toastMensaje.show();
        return;
    }
    
    generarExcel();
});

document.getElementById("btneliminar").addEventListener("click", () => {
    modalConfirmarEliminar.show();
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

document.getElementById("btncerrarabrir").addEventListener("click", () => {
    modalConfirmarCerrarAbrir.show();
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

document.getElementById("btnprocesar").addEventListener("click", () => {
    modalConfirmarProcesar.show();
});

document.getElementById("btnconfirmaprocesar").addEventListener("click", () => {
    cambiarEstado("PRO");
});

//-----------------------------------------------

function eliminar()
{
    document.getElementById("btneditar").setAttribute("disabled", "true");
    document.getElementById("btneliminar").setAttribute("disabled", "true");
    document.getElementById("btncerrarabrir").setAttribute("disabled", "true");
    document.getElementById("btnprocesar").setAttribute("disabled", "true");

    modalConfirmarEliminar.hide();

    let datos = new FormData();
    datos.append("sid", document.getElementById("sid").value);
    datos.append("uid", document.getElementById("uid").value);

    fetch(
        "./mods/inventario/salidas/procs/eliminar.php",
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

function cambiarEstado(estado)
{
    let datos = new FormData();
    datos.append("sid", document.getElementById("sid").value);
    datos.append("uid", document.getElementById("uid").value);
    datos.append("estado", estado);

    fetch(
        "./mods/inventario/salidas/procs/cambiarestado.php",
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
    modalConfirmarEliminar.hide();
}

//-----------------------------------------------

function finalizar(data, accion)
{
    if (data.error == 0)
    {
        let mensaje = "";
        let url = "?mod=inventario&opc=salidas&subopc=versalida&sid=" + document.getElementById("sid").value;
        switch (accion) {
            case "eliminar":
                mensaje = "The document was deleted.";
                url = "?mod=inventario&opc=salidas"
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

function imprimirSalida()
{
    let url = 'mods/inventario/salidas/procs/imprimirsalida.php?sid=' + document.getElementById("sid").value;
    window.open(url, "_blank");
}

function generarExcel()
{
    let url = 'mods/inventario/salidas/procs/generarexcel.php?sid=' + document.getElementById("sid").value;
    window.open(url, "_blank");
}

//-----------------------------------------------

$('#tabledatos').on('post-body.bs.table', function () {
    let data = $('#tabledatos').bootstrapTable('getData');
    let rowCount = data.length;
    
    document.getElementById("totalitems").innerHTML = rowCount;
});

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

function msrpFormatter(value, row, index) {
    return "$ " + Number(row.MSRP).toFixed(2);
}

function costOFormatter(value, row, index) {
    return "$ " + Number(row.COSTOORIGEN).toFixed(2);
}

function costDFormatter(value, row, index) {
    return "$ " + Number(row.COSTODIST).toFixed(2);
}
//-----------------------------------------------