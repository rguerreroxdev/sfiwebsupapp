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
    window.location.href="?mod=facturacion&opc=facturacion";
});

document.getElementById("btneditar").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=facturacion&subopc=registrofactura&fid=" + document.getElementById("fid").value;
});

document.getElementById("btnimprimirfactura").addEventListener("click", () => {
    imprimirFactura();
});

document.getElementById("btnimprimirhold").addEventListener("click", () => {
    let estadoActual = document.getElementById("estadoActual").value;
    if (estadoActual == "FOR" || estadoActual == "CER")
    {
        document.getElementById("mensajetoast").innerHTML = `The document must have been posted in order to print the hold for customer`;
        toastMensaje.show();
        return;
    }
    
    imprimirHold();
});

document.getElementById("btneliminar").addEventListener("click", () => {
    document.getElementById("razondeanulacion").value = "";
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
        document.getElementById("razondeanulacion").value = document.getElementById("razondeanulacion").value.trim();
        if (document.getElementById("razondeanulacion").value == "")
        {
            document.getElementById("mensajetoast").innerHTML = "You must enter a reason for cancellation.";
            toastMensaje.show();
            return;
        }

        cambiarEstado("ANU");
    }
});

document.getElementById("btncerrarabrir").addEventListener("click", () => {
    let estadoActual = document.getElementById("estadoActual").value;
    if (estadoActual == "FOR")
    {
        if (!validarCierre())
            return;
    }

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
    validarProcesar();
    // En la función validarProcesar se muestra la pantalla de confirmar procesar
    // si la validación es correcta.
});

document.getElementById("btnconfirmaprocesar").addEventListener("click", () => {
    cambiarEstado("PRO");
});

function validarCierre()
{
    const totalFactura = document.getElementById("totalantesdeimpuesto").value;
    const totalPagos = document.getElementById("totalpagos").value;

    if (totalFactura != totalPagos)
    {
        document.getElementById("mensajedeerror").innerHTML = "You cannot close this document because the Invoice Total before taxes and the Payment amount total are different.";
        modalMensaje.show();
        return false;
    }

    return true;
}

function validarProcesar()
{
    let datos = new FormData();
    datos.append("sid", document.getElementById("sucursalid").value);

    fetch(
        "./mods/facturacion/facturacion/procs/validarprocesar.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizarValidarProcesar(data))
    .catch(error => console.warn(error));    
}

function finalizarValidarProcesar(data)
{
    if (data.error == 1)
    {
        document.getElementById("mensajedeerror").innerHTML = data.mensaje;
        modalMensaje.show();
    }
    else
    {
        modalConfirmarProcesar.show();
    }
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
    datos.append("fid", document.getElementById("fid").value);
    datos.append("uid", document.getElementById("uid").value);

    fetch(
        "./mods/facturacion/facturacion/procs/eliminar.php",
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
    datos.append("fid", document.getElementById("fid").value);
    datos.append("uid", document.getElementById("uid").value);
    datos.append("estado", estado);
    // Cuando es anulación
    datos.append("razondeanulacion", document.getElementById("razondeanulacion").value);

    fetch(
        "./mods/facturacion/facturacion/procs/cambiarestado.php",
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
        let url = "?mod=facturacion&opc=facturacion&subopc=verfactura&fid=" + document.getElementById("fid").value;
        switch (accion) {
            case "eliminar":
                mensaje = "The document was deleted.";
                url = "?mod=facturacion&opc=facturacion"
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

function imprimirFactura()
{
    let url = 'mods/facturacion/facturacion/procs/imprimirfactura.php?fid=' + document.getElementById("fid").value;
    window.open(url, "_blank");
}

function imprimirHold()
{
    let url = 'mods/facturacion/facturacion/procs/imprimirhold.php?fid=' + document.getElementById("fid").value;
    window.open(url, "_blank");
}

//-----------------------------------------------

// $('#tabledatos').on('post-body.bs.table', function () {
//     let data = $('#tabledatos').bootstrapTable('getData');
//     let rowCount = data.length;
    
//     document.getElementById("totalitems").innerHTML = rowCount;
// });

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