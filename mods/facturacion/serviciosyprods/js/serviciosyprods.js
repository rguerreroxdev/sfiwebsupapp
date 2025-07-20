//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=serviciosyprods&subopc=crearservicioprod";
});

//-----------------------------------------------

document.getElementById("marca").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("btnreset").addEventListener("click", () => {
    document.getElementById("marca").value = "";

    actualizarTablaDeDatos();
});

//-----------------------------------------------

function actualizarTablaDeDatos()
{
    $("#tabledatos").bootstrapTable("refresh");
}

//-----------------------------------------------

function operateFormatter(value, row, index) {
    return [
        '<a href="javascript:verDatos(' + row.OTROSERVICIOPRODUCTOID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function customParams(p)
{
    return {
        marca: $("#marca").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

function verDatos(spId)
{
    window.location.href="?mod=facturacion&opc=serviciosyprods&subopc=verservicioprod&spid=" + spId;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------