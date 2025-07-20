//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));
let modalConfirmarEliminar = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
let modalConfirmarCerrarAbrir = new bootstrap.Modal(document.getElementById('modalConfirmarCerrarAbrir'));
let modalConfirmarProcesarOrigen = new bootstrap.Modal(document.getElementById('modalConfirmarProcesarOrigen'));
let modalConfirmarProcesarDestino = new bootstrap.Modal(document.getElementById('modalConfirmarProcesarDestino'));
let modalConfirmarRechazarDestino = new bootstrap.Modal(document.getElementById('modalConfirmarRechazarDestino'));

let estadoDisabledBtnEditar = document.getElementById("btneditar").getAttribute("disabled");
let estadoDisabledBtnEliminar = document.getElementById("btneliminar").getAttribute("disabled");
let estadoDisabledBtnCerrarAbrir = document.getElementById("btncerrarabrir").getAttribute("disabled");
let estadoDisabledBtnProcesarOrigen = document.getElementById("btnprocesarorigen").getAttribute("disabled");
let estadoDisabledBtnProcesarDestino = document.getElementById("btnprocesardestino").getAttribute("disabled");
let estadoDisabledBtnRechazarDestino = document.getElementById("btnrechazardestino").getAttribute("disabled");

//-----------------------------------------------

document.getElementById("btnregresar").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=traslados";
});

document.getElementById("btneditar").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=traslados&subopc=registrotraslado&tid=" + document.getElementById("tid").value;
});

document.getElementById("btnimprimir").addEventListener("click", () => {
    let estadoActual = document.getElementById("estadoActual").value;
    if (estadoActual == "FOR" || estadoActual == "CER")
    {
        document.getElementById("mensajetoast").innerHTML = `The document must have been posted at origin in order to be printed`;
        toastMensaje.show();
        return;
    }
    
    imprimirTraslado();
});

document.getElementById("btnexcel").addEventListener("click", () => {
    let estadoActual = document.getElementById("estadoActual").value;
    if (estadoActual == "FOR" || estadoActual == "CER")
    {
        document.getElementById("mensajetoast").innerHTML = `The document must have been posted at origin in order to generate an Excel file`;
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

document.getElementById("btnprocesarorigen").addEventListener("click", () => {
    modalConfirmarProcesarOrigen.show();
});

document.getElementById("btnconfirmaprocesarorigen").addEventListener("click", () => {
    cambiarEstado("PRO");
});

document.getElementById("btnprocesardestino").addEventListener("click", () => {
    modalConfirmarProcesarDestino.show();
});

document.getElementById("btnconfirmaprocesardestino").addEventListener("click", () => {
    cambiarEstado("PRD");
});

document.getElementById("btnrechazardestino").addEventListener("click", () => {
    modalConfirmarRechazarDestino.show();
});

document.getElementById("btnconfirmarechazardestino").addEventListener("click", () => {
    cambiarEstado("LIB");
});

//-----------------------------------------------

function eliminar()
{
    document.getElementById("btneditar").setAttribute("disabled", "true");
    document.getElementById("btneliminar").setAttribute("disabled", "true");
    document.getElementById("btncerrarabrir").setAttribute("disabled", "true");
    document.getElementById("btnprocesarorigen").setAttribute("disabled", "true");
    document.getElementById("btnprocesardestino").setAttribute("disabled", "true");
    document.getElementById("btnrechazardestino").setAttribute("disabled", "true");

    modalConfirmarEliminar.hide();

    let datos = new FormData();
    datos.append("tid", document.getElementById("tid").value);
    datos.append("uid", document.getElementById("uid").value);

    fetch(
        "./mods/inventario/traslados/procs/eliminar.php",
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
    datos.append("tid", document.getElementById("tid").value);
    datos.append("uid", document.getElementById("uid").value);
    datos.append("estado", estado);

    fetch(
        "./mods/inventario/traslados/procs/cambiarestado.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizar(data, estado))
    .catch(error => console.warn(error));

    modalConfirmarCerrarAbrir.hide();
    modalConfirmarProcesarOrigen.hide();
    modalConfirmarProcesarDestino.hide();
    modalConfirmarRechazarDestino.hide();
    modalConfirmarEliminar.hide();
}

//-----------------------------------------------

function finalizar(data, accion)
{
    if (data.error == 0)
    {
        let mensaje = "";
        let url = "?mod=inventario&opc=traslados&subopc=vertraslado&tid=" + document.getElementById("tid").value;
        switch (accion) {
            case "eliminar":
                mensaje = "The document was deleted.";
                url = "?mod=inventario&opc=traslados"
                break;
        
            case "CER":
                mensaje = "The document was closed.";
                break;

            case "FOR":
                mensaje = "The document was opened.";
                break;

            case "PRO":
                mensaje = "The document was posted at the origin.";
                break;

            case "PRD":
                mensaje = "The document was posted at the destination.";
                break;

            case "LIB":
                mensaje = "The document was rejected at the destination.";
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
        if(estadoDisabledBtnProcesarOrigen == null) document.getElementById("btnprocesarorigen").removeAttribute("disabled");
        if(estadoDisabledBtnProcesarDestino == null) document.getElementById("btnprocesardestino").removeAttribute("disabled");
        if(estadoDisabledBtnRechazarDestino == null) document.getElementById("btnrechazardestino").removeAttribute("disabled");
    }
}

//-----------------------------------------------

function imprimirTraslado()
{
    let url = 'mods/inventario/traslados/procs/imprimirtraslado.php?tid=' + document.getElementById("tid").value;
    window.open(url, "_blank");
}

function generarExcel()
{
    let url = 'mods/inventario/traslados/procs/generarexcel.php?tid=' + document.getElementById("tid").value;
    window.open(url, "_blank");
}

//-----------------------------------------------

function porcentajeFormatter(value, row, index) {
    return Number(row.PORCENTAJETIPODESTOCKDIST).toFixed(2) + " %";
}

function msrpFormatter(value, row, index) {
    return "$ " + Number(row.MSRP).toFixed(2);
}

$('#tabledatos').on('post-body.bs.table', function () {
    let data = $('#tabledatos').bootstrapTable('getData');
    let rowCount = data.length;
    
    document.getElementById("totalitems").innerHTML = rowCount;
});

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------