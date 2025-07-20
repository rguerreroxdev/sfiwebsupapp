//-----------------------------------------------

let modalEditarSerie = new bootstrap.Modal(document.getElementById('modalEditarSerie'));
let modalHistorial = new bootstrap.Modal(document.getElementById('modalHistorial'));
let modalRecepcion = new bootstrap.Modal(document.getElementById('modalBuscarRecepcion'));
let modalSeleccionarProveedor = new bootstrap.Modal(document.getElementById('modalSeleccionarProveedor'));
let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let toastError = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastError'));

let sucursalPorDefecto = document.getElementById("sucursal").value;

//-----------------------------------------------

document.getElementById("categoria").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("color").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("stocktype").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("sucursal").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("solostock").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("numerorecepcion").addEventListener("input", () => {
    document.getElementById("numerorecepcion").value = document.getElementById("numerorecepcion").value.replace(/[^0-9]/g, '');
    actualizarTablaDeDatos();
});

document.getElementById("btnrecepcion").addEventListener("click", () => {
    resetModalProveedor();
    modalRecepcion.show();
});

document.getElementById("btnresetinvgeneral").addEventListener("click", () => {
    document.getElementById("sucursal").value = sucursalPorDefecto;
    document.getElementById("categoria").value = "";
    document.getElementById("color").value = "";
    document.getElementById("stocktype").value = "";
    document.getElementById("numerorecepcion").value = "";
    document.getElementById("solostock").checked = true;

    actualizarTablaDeDatos();
});

document.getElementById("btnguardar").addEventListener("click", () => {
    guardarSerie();
});

//-----------------------------------------------

document.getElementById("btnproveedor").addEventListener("click", () => {
    $('#tableproveedores').bootstrapTable('resetSearch');
    modalSeleccionarProveedor.show();
});

document.getElementById("fechadesde").addEventListener("change", () => {
    actualizarTablaDeRecepciones();
});

document.getElementById("recepcionsucursal").addEventListener("change", () => {
    actualizarTablaDeRecepciones();
});

document.getElementById("proveedor").addEventListener("change", () => {
    actualizarTablaDeRecepciones();
});

document.getElementById("btnreset").addEventListener("click", () => {
    resetModalProveedor();
});

function resetModalProveedor()
{
    document.getElementById("recepcionsucursal").value = "-1";
    document.getElementById("correlativo").value = "";
    document.getElementById("loadid").value = "";
    document.getElementById("codigoproveedor").value="";
    document.getElementById("proveedor").value="";
    document.getElementById("proveedorid").value="";

    actualizarTablaDeRecepciones();
}

const inputCorrelativo = document.getElementById('correlativo');
let typingTimer;
const typingInterval = 500;
function busquedaRecepcion() {
    actualizarTablaDeRecepciones();
}

inputCorrelativo.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(busquedaRecepcion, typingInterval);
});

const inputLoadId = document.getElementById('loadid');
inputLoadId.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(busquedaRecepcion, typingInterval);
});

function buscarProveedor(event)
{
    if(event.target.value.length == 4)
    {
        let datos = new FormData();
        datos.append("codigo", document.getElementById("codigoproveedor").value);
    
        fetch(
            "./mods/inventario/invgeneral/procs/buscarproveedor.php",
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

        actualizarTablaDeRecepciones();
    }
}

function ubicarDatosProvedor(data)
{
    document.getElementById("proveedor").value = data.nombre;
    document.getElementById("proveedorid").value = data.proveedorid;

    actualizarTablaDeRecepciones();
}

//-----------------------------------------------

function actualizarTablaDeDatos()
{
    $("#tabledatos").bootstrapTable("refresh");
}

function actualizarTablaDeRecepciones()
{
    $("#tablerecepciones").bootstrapTable("refresh");
}

//-----------------------------------------------

function editarSerieFormatter(value, row, index) {
    return [
        '<a href="javascript:editarSerie(' + row.INVENTARIOID + ')" title="Edit serial number">',
        '<i class="bi bi-red bi-pencil-square"></i>',
        '</a>'
    ].join('');
}

function verHistorialFormatter(value, row, index) {
    return [
        '<a href="javascript:verHistorial(' + row.INVENTARIOID + ')" title="Check item history">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

function msrpFormatter(value, row, index) {
    return "$ " + Number(row.MSRP).toFixed(2);
}

//-----------------------------------------------

function customParams(p)
{
    let solostock = document.getElementById("solostock").checked ? 1 : 0;
    return {
        uid: $("#uid").val(),
        sid: $("#sucursal").val(),
        cid: $("#categoria").val(),
        colid: $("#color").val(),
        stid: $("#stocktype").val(),
        stock: solostock,
        nr: $("#numerorecepcion").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

function editarSerie(iId)
{
    let fila = $("#tabledatos").bootstrapTable("getRowByUniqueId", iId);

    document.getElementById("itemcodigo").value= fila.CODIGOINVENTARIO;
    document.getElementById("itemcategoria").value= fila.CATEGORIA;
    document.getElementById("itemmarca").value= fila.MARCA;
    document.getElementById("itemmodelo").value= fila.MODELO;
    document.getElementById("itemserie").value= fila.SERIE;
    document.getElementById("itemid").value= fila.INVENTARIOID;

    modalEditarSerie.show();
    setTimeout(() => {
        document.getElementById("itemserie").focus();
    }, 500);
}

function guardarSerie()
{
    document.getElementById("itemserie").value = document.getElementById("itemserie").value.trim();

    let datos = new FormData();
    datos.append("iid", document.getElementById("itemid").value);
    datos.append("serie", document.getElementById("itemserie").value);
    datos.append("uid", document.getElementById("uid").value);

    fetch(
        "./mods/inventario/invgeneral/procs/guardarserie.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizarSerie(data))
    .catch(error => console.warn(error));
}

function finalizarSerie(data)
{
    if (data.error == 1)
    {
        document.getElementById("textodeerror").innerHTML = data.mensaje;
        toastError.show();
        return;
    }

    let iId = document.getElementById("itemid").value;
    $("#tabledatos").bootstrapTable("updateByUniqueId", {
        id: iId,
        row: {
            SERIE: document.getElementById("itemserie").value
        }
    });
    toastMensaje.show();
    modalEditarSerie.hide();      
}

//-----------------------------------------------

function verHistorial(iId)
{
    let datos = new FormData();
    datos.append("iid", iId);

    fetch(
        "./mods/inventario/invgeneral/procs/gethistorial.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => mostrarHistorial(data))
    .catch(error => console.warn(error));
}

function mostrarHistorial(data)
{
    document.getElementById("detalleInvCodigo").innerHTML = data.item.CODIGOINVENTARIO;
    document.getElementById("detalleCategoria").innerHTML = data.item.CATEGORIA;
    document.getElementById("detalleBrand").innerHTML = data.item.MARCA;
    document.getElementById("detalleModelo").innerHTML = data.item.MODELO;
    document.getElementById("detalleSerie").innerHTML = data.item.SERIE;
    document.getElementById("detalleMsrp").innerHTML = data.item.MSRP;

    let prov = document.getElementById("detalleProveedor");
    if (prov)
    {
        document.getElementById("detalleProveedor").innerHTML = data.item.PROVEEDOR;

        document.getElementById("detalleStockOrigen").innerHTML = data.item.TIPODESTOCKORIGEN;
        document.getElementById("detallePorcentajeOrigen").innerHTML = parseFloat(data.item.PORCENTAJETIPODESTOCKORIGEN).toFixed(2);
        document.getElementById("detalleCostoOrigin").innerHTML = parseFloat(data.item.COSTOORIGEN).toLocaleString('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    document.getElementById("detalleStockDist").innerHTML = data.item.TIPODESTOCKDIST;
    document.getElementById("detallePorcentajeDist").innerHTML = parseFloat(data.item.PORCENTAJETIPODESTOCKDIST).toFixed(2);
    document.getElementById("detalleCostoDist").innerHTML = parseFloat(data.item.COSTODIST).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    $("#tablehistorial").bootstrapTable("destroy");
    $("#tablehistorial").bootstrapTable({data: data.historial});
    modalHistorial.show();
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

function recepcionesOperateFormatter(value, row, index) {
    return [
        '<a class="sel-recepcion" href="javascript:void(0)" title="Aplicar">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.recepcionesOperateEvents = {
    "click .sel-recepcion": function(e, value, row, index) {
        document.getElementById("numerorecepcion").value = row.CORRELATIVO;

        actualizarTablaDeDatos();
        modalRecepcion.hide();
    }
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

        actualizarTablaDeRecepciones();
        modalSeleccionarProveedor.hide();
    }
}

//-----------------------------------------------

function recepcionesCustomParams(p)
{
    return {
        uid: $("#uid").val(),
        sid: $("#recepcionsucursal").val(),
        correlativo: $("#correlativo").val(), 
        provid: $("#proveedorid").val(),
        loadid: $("#loadid").val(),
        fechadesde: $("#fechadesde").val(),
        estado: "PRO",
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------