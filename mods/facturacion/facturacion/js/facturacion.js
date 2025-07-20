//-----------------------------------------------

let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));

//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=facturacion&subopc=registrofactura";
});

//-----------------------------------------------

document.getElementById("fechadesde").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("sucursal").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("estado").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById('correlativo').addEventListener('input', function (event) {
    // Permite un "-" solo al inicio y dígitos del 0 al 9
    this.value = this.value.replace(/(?!^-)[^0-9]/g, ''); 
    
    // Asegura que el "-" solo esté una vez al inicio
    if (this.value.indexOf('-') > 0) {
        this.value = this.value.replace('-', '');
    }
});


document.getElementById("btnreset").addEventListener("click", () => {
    document.getElementById("sucursal").value = "-1";
    document.getElementById("cliente").value = "";
    document.getElementById("estado").value = "";
    document.getElementById("correlativo").value = "";

    actualizarTablaDeDatos();
});

const inputCorrelativo = document.getElementById('correlativo');
const inputCliente = document.getElementById('cliente');
let typingTimer;
const typingInterval = 500;

function busqueda() {
    actualizarTablaDeDatos();
}

inputCorrelativo.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(busqueda, typingInterval);
});

inputCliente.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(busqueda, typingInterval);
});

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
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

function totalFormatter(value, row, index) {
    return ('$ ' + parseFloat(row.TOTALFINAL).toFixed(2));
}

function operateFormatter(value, row, index) {
    return [
        '<a href="javascript:verDatos(' + row.FACTURAID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function customParams(p)
{
    return {
        uid: $("#uid").val(),
        sid: $("#sucursal").val(),
        cliente: $("#cliente").val(),
        correlativo: $("#correlativo").val(), 
        fechadesde: $("#fechadesde").val(),
        estado: $("#estado").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

function actualizarTablaDeDatos()
{
    $("#tabledatos").bootstrapTable("refresh");
}

//-----------------------------------------------

function verDatos(fId)
{
    window.location.href="?mod=facturacion&opc=facturacion&subopc=verfactura&fid=" + fId;
}

//-----------------------------------------------