//-----------------------------------------------

document.getElementById("sucursalgeneral").addEventListener("change", () => {
    actualizarTablaGeneral();
});

document.getElementById("sucursalcategoria").addEventListener("change", () => {
    actualizarTablaPorCategoria();
});

document.getElementById("categoria").addEventListener("change", () => {
    actualizarTablaPorCategoria();
});

document.getElementById("solostock").addEventListener("change", () => {
    actualizarTablaPorCategoria();
});

//-----------------------------------------------

function actualizarTablaGeneral()
{
    $("#tablegeneral").bootstrapTable("refresh");
}

function actualizarTablaPorCategoria()
{
    $("#tabledatosporcategoria").bootstrapTable("refresh");
}

//-----------------------------------------------

function generalCustomParams(p)
{
    return {
        sid: $("#sucursalgeneral").val()
    };
}

function customParams(p)
{
    let solostock = document.getElementById("solostock").checked ? 1 : 0;
    return {
        sid: $("#sucursalcategoria").val(),
        cid: $("#categoria").val(),
        stock: solostock
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

    let prov = document.getElementById("detalleProveedor");
    if (prov)
    {
        document.getElementById("detalleProveedor").innerHTML = data.item.PROVEEDOR;
    }


    $("#tablehistorial").bootstrapTable("destroy");
    $("#tablehistorial").bootstrapTable({data: data.historial});
    modalHistorial.show();
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------