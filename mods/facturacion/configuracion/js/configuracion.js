//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=configuracion&subopc=crearconfiguracion";
});

//-----------------------------------------------

document.getElementById("sucursal").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("btnreset").addEventListener("click", () => {
    document.getElementById("sucursal").value = "-1";

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
        '<a href="javascript:verDatos(' + row.CONFIGURACIONPORSUCURSALID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function customParams(p)
{
    return {
        sucursal: $("#sucursal").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

function verDatos(csId)
{
    window.location.href="?mod=facturacion&opc=configuracion&subopc=verconfiguracion&csid=" + csId;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------