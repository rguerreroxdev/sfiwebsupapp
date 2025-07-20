//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let toastError = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastError'));

let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));
let modalSeleccionarFDevuelta = new bootstrap.Modal(document.getElementById('modalSeleccionarFacturaDevuelta'));
let modalNoConfig = new bootstrap.Modal(document.getElementById('modalNoConfig'));

//-----------------------------------------------

document.getElementById("btncancelar").addEventListener("click", () => {
    let dId = document.getElementById("did").value;
    if (dId == -1)
    {
        window.location.href="?mod=facturacion&opc=devoluciones";
    }
    else
    {
        window.location.href="?mod=facturacion&opc=devoluciones&subopc=verdevolucion&did=" + dId;
    }
});

//-----------------------------------------------

const selectSucursal = document.getElementById('sucursal');
const spanNombreSucursalFDevuelta = document.getElementById('fdevueltanombresucursal');
selectSucursal.addEventListener('change', function() {
    const textoSeleccionado = selectSucursal.options[selectSucursal.selectedIndex].text;
    spanNombreSucursalFDevuelta.textContent = textoSeleccionado;

    if (selectSucursal.value == "")
        return;

    let datos = new FormData();
    datos.append("sid", selectSucursal.value);

    fetch(
        "./mods/facturacion/devoluciones/procs/seleccionarsucursal.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizarSucursal(data))
    .catch(error => console.warn(error));

});

function finalizarSucursal(data)
{
    if (data.configuracionid == -1)
    {
        document.getElementById("prefijodecorrelativo").value = "";
        document.getElementById("correlativocompuesto").value = document.getElementById("prefijodecorrelativo").value + "-" + document.getElementById("correlativo").value;

        modalNoConfig.show();
        selectSucursal.value = "";
    }
    else
    {
        document.getElementById("prefijodecorrelativo").value = data.prefijodecorrelativo;
        document.getElementById("correlativocompuesto").value = data.prefijodecorrelativo + "-" + document.getElementById("correlativo").value;
    }
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

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

document.getElementById("bfdfechadesde").addEventListener("change", () => {
    actualizarTablaDeFacturasDevueltas();
});

const inputBFDCorrelativo = document.getElementById('bfdcorrelativo');
const inputBFDCliente = document.getElementById('bfdcliente');
let typingTimer;
const typingInterval = 500;

function bfdBusqueda() {
    actualizarTablaDeFacturasDevueltas();
}

inputBFDCorrelativo.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(bfdBusqueda, typingInterval);
});

inputBFDCliente.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(bfdBusqueda, typingInterval);
});

document.getElementById("bfdbtnreset").addEventListener("click", () => {
    document.getElementById("bfdcliente").value = "";
    document.getElementById("bfdcorrelativo").value = "";

    actualizarTablaDeFacturasDevueltas();
});

document.getElementById('bfdcorrelativo').addEventListener('input', function (event) {
    // Permite un "-" solo al inicio y dígitos del 0 al 9
    this.value = this.value.replace(/(?!^-)[^0-9]/g, ''); 
    
    // Asegura que el "-" solo esté una vez al inicio
    if (this.value.indexOf('-') > 0) {
        this.value = this.value.replace('-', '');
    }
});

function actualizarTablaDeFacturasDevueltas()
{
    $("#tablefacturasdevueltas").bootstrapTable("refresh");
}

document.getElementById("btnfacturadevuelta").addEventListener("click", () => {
    if (document.getElementById('sucursal').value == "")
    {
        document.getElementById("textodeerror").innerHTML = "You must select a store.";
        toastError.show();
        return;
    }

    document.getElementById("bfdcorrelativo").value = "";
    document.getElementById("bfdcliente").value = "";

    actualizarTablaDeFacturasDevueltas();
    modalSeleccionarFDevuelta.show();

});

function facturasDevueltasOperateFormatter(value, row, index) {
    if (row.ESTADO == "PRO")
    {
        return [
            '<a class="sel-fdevuelta" href="javascript:void(0)" title="Aplicar">',
            '<i class="bi bi-red bi-check-circle"></i>',
            '</a>'
        ].join('');
    }
    else
    {
        return "-";
    }
}

window.facturasDevueltasOperateEvents = {
    "click .sel-fdevuelta": function(e, value, row, index) {
        
        buscarFacturaDevuelta(row.FACTURAID);

        modalSeleccionarFDevuelta.hide();
    }
}

function buscarFacturaDevuelta(fId)
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
    .then(data => ubicarDatosFDevuelta(data))
    .catch(error => console.warn(error));
}

function ubicarDatosFDevuelta(data)
{
    document.getElementById("facturadevuelta").value = data.correlativoCompuesto;
    document.getElementById("facturadevueltaid").value = data.facturaId;
    document.getElementById("facturadevueltafecha").value = data.fecha;
    document.getElementById("codigocliente").value = data.clienteCodigo;
    document.getElementById("cliente").value = data.clienteNombre;
    document.getElementById("clienteid").value = data.clienteId;
    document.getElementById("clientedireccion").value = data.clienteDireccion;
    document.getElementById("clientedireccioncomplemento").value = data.clienteDireccionComplemento;
    document.getElementById("clientecodigopostal").value = data.clienteCodigoPostal;
    document.getElementById("clientetelefono").value = data.clienteTelefono;
    document.getElementById("clientecorreo").value = data.clienteCorreo;

    document.getElementById("nocalcularimpuesto").checked = data.noCalcularImpuesto == 1;
    document.getElementById("nocalcularimpuestovalor").value = data.noCalcularImpuesto;

    document.getElementById("totalantesdeimpuesto").value = parseFloat(data.totalAntesDeImpuesto).toFixed(2);
    document.getElementById("porcentajevisto").innerHTML = parseFloat(data.impuestoPorcentaje).toFixed(2);
    document.getElementById("impuestoporcentaje").value = data.impuestoPorcentaje;
    document.getElementById("impuesto").value = parseFloat(data.impuesto).toFixed(2);
    document.getElementById("totalconimpuesto").value = parseFloat(data.totalConImpuesto).toFixed(2);
    document.getElementById("impuestofinanciera").value = parseFloat(data.impuestoFinanciera).toFixed(2);
    document.getElementById("impuestofinancierareal").value = data.impuestoFinanciera;
    document.getElementById("totalfinal").value = parseFloat(data.totalFinal).toFixed(2);

    // 1. Agregar detalles
    let tbodyDetalle = document.querySelector('#tablaDetalle tbody');
    tbodyDetalle.innerHTML = '';
    tbodyDetalle.innerHTML = data.tbodyDetalle;

    // 2. Agregar otros detalles
    let tbodyOtroDetalle = document.querySelector('#tablaServiciosOtrosProductos tbody');
    tbodyOtroDetalle.innerHTML = '';
    tbodyOtroDetalle.innerHTML = data.tbodyOtrosDetalles;

    // 3. Agregar pagos
    let tbodyPagos = document.querySelector('#tablaPagos tbody');
    tbodyPagos.innerHTML = '';
    tbodyPagos.innerHTML = data.tbodyPagos;
    
    // 4. Agretar totales de pagos
    document.getElementById("totalpagosmonto").value = parseFloat(data.pagoTotalMonto).toFixed(2);
    document.getElementById("totalpagosimpuesto").value = parseFloat(data.pagoTotalImpuesto).toFixed(2);
    document.getElementById("totalpagos").value = parseFloat(data.pagoTotalFinal).toFixed(2);

    // No se puede cambiar de sucursal
    document.getElementById('sucursal').style.pointerEvents = 'none';
    document.getElementById('sucursal').style.backgroundColor = '#e9ecef';
}

function facturasDevueltasCustomParams(p)
{
    return {
        sid: $("#sucursal").val(),
        cliente: $("#bfdcliente").val(),
        correlativo: $("#bfdcorrelativo").val(), 
        fechadesde: $("#bfdfechadesde").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

document.getElementById("frm").addEventListener("submit", (event) => {
    event.preventDefault();

    const facturaDevueltaId = document.getElementById("facturadevueltaid").value;

    if (facturaDevueltaId == -1)
    {
        document.getElementById("textodeerror").innerHTML = "You have not selected an invoice to return.";
        toastError.show();
        return;
    }

    if (event.target.checkValidity())
    {
        let datos = new FormData(event.target);
        guardar(datos);
    }
});

function guardar(datos)
{
    document.getElementById("btnguardar").setAttribute("disabled", "true");
    document.getElementById("btnguardarspinner").classList.remove("visually-hidden");

    fetch(
        "./mods/facturacion/devoluciones/procs/guardardevolucion.php",
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
    document.getElementById("btnguardarspinner").classList.add("visually-hidden");

    if (data.error == 0)
    {
        toastMensaje.show();
        setTimeout(() => {
            window.location.href="?mod=facturacion&opc=devoluciones&subopc=verdevolucion&did=" + data.did;
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

function evaluarHabilitarSucursal()
{
    if (document.getElementById("facturadevueltaid").value == -1)
    {
        document.getElementById('sucursal').style.pointerEvents = 'auto';
        document.getElementById('sucursal').style.backgroundColor = '';
    }

}

// Al cargar se pone en readonly la sucursal y luego se evalúa si se activa
document.getElementById('sucursal').style.pointerEvents = 'none';
document.getElementById('sucursal').style.backgroundColor = '#e9ecef';
evaluarHabilitarSucursal();