//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=recepcionesdecarga&subopc=registrorecepcion";
});

//-----------------------------------------------

let modalEmitirEtiquetas = new bootstrap.Modal(document.getElementById('modalEmitirEtiquetas'));
let modalSeleccionarProveedor = new bootstrap.Modal(document.getElementById('modalSeleccionarProveedor'));

//-----------------------------------------------

document.getElementById("btnproveedor").addEventListener("click", () => {
    modalSeleccionarProveedor.show();
});

document.getElementById("fechadesde").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("sucursal").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("estado").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("proveedor").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("btnreset").addEventListener("click", () => {
    document.getElementById("sucursal").value = "-1";
    document.getElementById("estado").value = "";
    document.getElementById("correlativo").value = "";
    document.getElementById("loadid").value = "";
    document.getElementById("codigoproveedor").value="";
    document.getElementById("proveedor").value="";
    document.getElementById("proveedorid").value="";

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

//-----------------------------------------------

function rowProveedorIndexFormatter(value, row, index) {
    return index + 1;
}

function proveedoresOperateFormatter(value, row, index) {
    return [
        '<a class="sel-proveedor" href="javascript:void(0)" title="Aplicar">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.proveedoresOperateEvents = {
    "click .sel-proveedor": function(e, value, row, index) {
        document.getElementById("proveedorid").value = row.PROVEEDORID;
        document.getElementById("codigoproveedor").value = row.CODIGO;
        document.getElementById("proveedor").value = row.NOMBRE;

        actualizarTablaDeDatos();
        modalSeleccionarProveedor.hide();
    }
}

//-----------------------------------------------

function buscarProveedor(event)
{
    if(event.target.value.length == 4)
    {
        let datos = new FormData();
        datos.append("codigo", document.getElementById("codigoproveedor").value);
    
        fetch(
            "./mods/inventario/recepcionesdecarga/procs/buscarproveedor.php",
            {
                method: "POST",
                body: datos
            }
        )
        .then(response => response.json())
        .then(data => ubicarDatosProvedor(data))
        .catch(error => console.warn(error)); 
    }
    else
    {
        let refrescarTiposDeStock = document.getElementById("proveedorid").value != "";

        document.getElementById("proveedor").value = "";
        document.getElementById("proveedorid").value = "";

        actualizarTablaDeDatos();
    }
}

function ubicarDatosProvedor(data)
{
    document.getElementById("proveedor").value = data.nombre;
    document.getElementById("proveedorid").value = data.proveedorid;

    actualizarTablaDeDatos();
}

//-----------------------------------------------

const inputCorrelativo = document.getElementById('correlativo');
let typingTimer;
const typingInterval = 500;

function busqueda() {
    actualizarTablaDeDatos();
}

inputCorrelativo.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(busqueda, typingInterval);
});

//-----------------------------------------------

const inputLoadId = document.getElementById('loadid');

inputLoadId.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(busqueda, typingInterval);
});

//-----------------------------------------------

function actualizarTablaDeDatos()
{
    $("#tabledatos").bootstrapTable("refresh");
}

//-----------------------------------------------

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
            elemento = `<span class="text-primary">${row.NOMBREDEESTADO.toLowerCase()}</span>`;
            break;
                
        default:
            elemento = `<span class="text-secondary">${row.NOMBREDEESTADO.toLowerCase()}</span>`;
            break;
    }

    return elemento;
}

function porcentajeOrigenFormatter(value, row, index) {
    return (row.PORCENTAJETIPODESTOCKORIGEN + ' %');
}

function porcentajeDistFormatter(value, row, index) {
    return (row.PORCENTAJETIPODESTOCKDIST + ' %');
}

//-----------------------------------------------

function operateFormatter(value, row, index) {
    let etiqueta = "";
    if (row.ESTADO == "PRO")
    {
        etiqueta = '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="javascript:emitirEtiquetas(' + row.RECEPCIONDECARGAID + ', ' + row.CORRELATIVO + ')" title="Print labels"><i class="bi bi-red bi-credit-card-2-front"></i></a>';
    }
    return [
        '<a href="javascript:verDatos(' + row.RECEPCIONDECARGAID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>',
        etiqueta
    ].join('');
}

//-----------------------------------------------

function customParams(p)
{
    return {
        uid: $("#uid").val(),
        sid: $("#sucursal").val(),
        correlativo: $("#correlativo").val(), 
        provid: $("#proveedorid").val(),
        loadid: $("#loadid").val(),
        fechadesde: $("#fechadesde").val(),
        estado: $("#estado").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

function verDatos(rId)
{
    window.location.href="?mod=inventario&opc=recepcionesdecarga&subopc=verrecepcion&rid=" + rId;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------

function emitirEtiquetas(rid, corr)
{
    document.getElementById("billcorrelativo").innerHTML = corr;
    document.getElementById("rid").value = rid;
    document.getElementById("ubicacioninicial").value = 1;

    modalEmitirEtiquetas.show();
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