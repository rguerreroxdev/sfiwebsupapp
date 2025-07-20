//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let toastError = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastError'));

let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));
let modalConfirmarEliminar = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
let modalConfirmarCerrarAbrir = new bootstrap.Modal(document.getElementById('modalConfirmarCerrarAbrir'));
let modalConfirmarProcesar = new bootstrap.Modal(document.getElementById('modalConfirmarProcesar'));

let modalSeleccionarFSustituta = new bootstrap.Modal(document.getElementById('modalSeleccionarFacturaSustituta'));
let modalConfirmarFSustituta = new bootstrap.Modal(document.getElementById('modalConfirmarFSustituta'));

let estadoDisabledBtnEditar = document.getElementById("btneditar").getAttribute("disabled");
let estadoDisabledBtnEliminar = document.getElementById("btneliminar").getAttribute("disabled");
let estadoDisabledBtnCerrarAbrir = document.getElementById("btncerrarabrir").getAttribute("disabled");
let estadoDisabledBtnProcesar = document.getElementById("btnprocesar").getAttribute("disabled");

//-----------------------------------------------

document.getElementById("btnregresar").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=devoluciones";
});

document.getElementById("btneditar").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=devoluciones&subopc=registrodevolucion&did=" + document.getElementById("did").value;
});

document.getElementById("btnimprimirdevolucion").addEventListener("click", () => {
    imprimirDevolucion();
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
    // else if (textoDeBoton.toLowerCase() == "cancel")
    // {
    //     cambiarEstado("ANU");
    // }
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
    datos.append("did", document.getElementById("did").value);
    datos.append("uid", document.getElementById("uid").value);

    fetch(
        "./mods/facturacion/devoluciones/procs/eliminar.php",
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
    datos.append("did", document.getElementById("did").value);
    datos.append("uid", document.getElementById("uid").value);
    datos.append("estado", estado);

    fetch(
        "./mods/facturacion/devoluciones/procs/cambiarestado.php",
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
        let url = "?mod=facturacion&opc=devoluciones&subopc=verdevolucion&did=" + document.getElementById("did").value;
        switch (accion) {
            case "eliminar":
                mensaje = "The document was deleted.";
                url = "?mod=facturacion&opc=devoluciones"
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

function imprimirDevolucion()
{
    let url = 'mods/facturacion/devoluciones/procs/imprimirdevolucion.php?did=' + document.getElementById("did").value;
    window.open(url, "_blank");
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

function msrpFormatter(value, row, index) {
    return "$ " + Number(row.MSRP).toFixed(2);
}

function detDescripcionFormatter(value, row, index) {
    return row.CATEGORIA + " - " + row.DESCRIPCION;
}

function detPrecioFormatter(value, row, index) {
    return "$ " + Number(row.PRECIO).toFixed(2);
}

function pagMontoFormatter(value, row, index) {
    return "$ " + Number(row.MONTO).toFixed(2);
}

function pagImpuestoFormatter(value, row, index) {
    return "$ " + Number(row.IMPUESTO).toFixed(2);
}

function pagTotalFormatter(value, row, index) {
    return "$ " + Number(row.TOTAL).toFixed(2);
}

function pagTotalMontoFormatter(data)
{
    let total = 0;
    data.forEach(row => {
        total += parseFloat(row.MONTO) || 0;
    });
    return '$ ' + total.toFixed(2);
}

function pagTotalImpuestoFormatter(data)
{
    let total = 0;
    data.forEach(row => {
        total += parseFloat(row.IMPUESTO) || 0;
    });
    return '$ ' + total.toFixed(2);
}

function pagTotalFinalFormatter(data)
{
    let total = 0;
    data.forEach(row => {
        total += parseFloat(row.TOTAL) || 0;
    });
    return '$ ' + total.toFixed(2);
}

function pagFooterLabelFormatter(data)
{
    return 'TOTALS:';
}

//-----------------------------------------------

function totalFormatter(value, row, index) {
    return ('$ ' + parseFloat(row.TOTALFINAL).toFixed(2));
}

function estadoFormatter(value, row, index) {
    let elemento = "";

    switch (row.ESTADO) {
        case "FOR":
            elemento = `<span class="text-danger">${row.NOMBREDEESTADO.toLowerCase()}</span>`;
            break;
        case "CER":
            elemento = `<span class="text-success">${row.NOMBREDEESTADO.toLowerCase()}</span>`;
            break;
        case "PRO":
            elemento = `<span class="text-success fw-bold">${row.NOMBREDEESTADO.toLowerCase()}</span>`;
            break;
                                            
        default:
            elemento = `<span class="text-secondary">${row.NOMBREDEESTADO.toLowerCase()}</span>`;
            break;
    }

    return elemento;
}

//-----------------------------------------------

document.getElementById("bfsfechadesde").addEventListener("change", () => {
    actualizarTablaDeFacturasSustitutas();
});

const inputBFSCorrelativo = document.getElementById('bfscorrelativo');
const inputBFSCliente = document.getElementById('bfscliente');
let typingTimer;
const typingInterval = 500;

function bfsBusqueda() {
    actualizarTablaDeFacturasSustitutas();
}

inputBFSCorrelativo.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(bfsBusqueda, typingInterval);
});

inputBFSCliente.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(bfsBusqueda, typingInterval);
});

document.getElementById("bfsbtnreset").addEventListener("click", () => {
    document.getElementById("bfscliente").value = "";
    document.getElementById("bfscorrelativo").value = "";

    actualizarTablaDeFacturasSustitutas();
});

document.getElementById('bfscorrelativo').addEventListener('input', function (event) {
    // Permite un "-" solo al inicio y dígitos del 0 al 9
    this.value = this.value.replace(/(?!^-)[^0-9]/g, ''); 
    
    // Asegura que el "-" solo esté una vez al inicio
    if (this.value.indexOf('-') > 0) {
        this.value = this.value.replace('-', '');
    }
});

function actualizarTablaDeFacturasSustitutas()
{
    $("#tablefacturassustitutas").bootstrapTable("refresh");
}

document.getElementById("btnfsustituta").addEventListener("click", () => {
    if (document.getElementById('facturasustituyeid').value != "")
    {
        document.getElementById("textodeerror").innerHTML = "The substitute invoice has already been chosen.";
        toastError.show();
        return;
    }

    document.getElementById("bfscorrelativo").value = "";
    document.getElementById("bfscliente").value = "";

    actualizarTablaDeFacturasSustitutas();
    modalSeleccionarFSustituta.show();
});

function facturasSustitutasOperateFormatter(value, row, index) {
    if (row.ESTADO == "PRO")
    {
        return [
            '<a class="sel-fsustituta" href="javascript:void(0)" title="Aplicar">',
            '<i class="bi bi-red bi-check-circle"></i>',
            '</a>'
        ].join('');
    }
    else
    {
        return "-";
    }
}

window.facturasSustitutasOperateEvents = {
    "click .sel-fsustituta": function(e, value, row, index) {
        validarFacturaSustituta(row.FACTURAID);
    }
}

function validarFacturaSustituta(fId)
{
    let datos = new FormData();
    datos.append("fid", fId);

    fetch(
        "./mods/facturacion/devoluciones/procs/validarfsustituta.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizarValidacionFSustituta(data, fId))
    .catch(error => console.warn(error));    
}

function finalizarValidacionFSustituta(data, fId)
{
    if (data.facturayautilizada == 0)
    {
        buscarFacturaSustituta(fId);
    }
    else
    {
        document.getElementById("mensajetoast").innerHTML = "The selected invoice is already a substitute in the Credit Memo " + data.correlativocompuesto +".";
        toastMensaje.show();
    }
}

function buscarFacturaSustituta(fId)
{
    let datos = new FormData();
    datos.append("fid", fId);

    fetch(
        "./mods/facturacion/devoluciones/procs/getfactura.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => ubicarDatosFSustituta(data))
    .catch(error => console.warn(error));
}

function ubicarDatosFSustituta(data)
{
    document.getElementById("facturasustituta").value = data.correlativoCompuesto;
    document.getElementById("facturasustituyeid").value = data.facturaId;
    document.getElementById("facturasustituyefecha").value = data.fecha;

    modalSeleccionarFSustituta.hide();
    modalConfirmarFSustituta.show();
}

function facturasSustitutasCustomParams(p)
{
    return {
        sid: $("#sucursal").val(),
        cliente: $("#bfscliente").val(),
        correlativo: $("#bfscorrelativo").val(), 
        fechadesde: $("#bfsfechadesde").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

document.getElementById("btncancelafsustituta").addEventListener("click", () => {
    document.getElementById("facturasustituta").value = "-";
    document.getElementById("facturasustituyeid").value = "";
    document.getElementById("facturasustituyefecha").value = "";

    modalConfirmarFSustituta.hide();
});

document.getElementById("btnconfirmafsustituta").addEventListener("click", () => {
    let datos = new FormData();
    datos.append("did", document.getElementById("did").value);
    datos.append("fid", document.getElementById("facturasustituyeid").value);
    datos.append("uid", document.getElementById("uid").value);

    fetch(
        "./mods/facturacion/devoluciones/procs/setfacturasustituta.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizarFSustituta(data))
    .catch(error => console.warn(error));
    
    modalConfirmarFSustituta.hide();
});

function finalizarFSustituta(data)
{
    document.getElementById("mensajetoast").innerHTML = "The data was saved";
    toastMensaje.show();
    modalConfirmarFSustituta.hide();

    const did = document.getElementById("did").value;
    setTimeout(() => {
        window.location.href="?mod=facturacion&opc=devoluciones&subopc=verdevolucion&did=" + did;
    }, 1000);
}

//-----------------------------------------------