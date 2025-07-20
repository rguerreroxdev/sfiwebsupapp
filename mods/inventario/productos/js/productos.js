//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=productos&subopc=crearproducto";
});

//-----------------------------------------------

document.getElementById("categoria").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("marca").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("btnreset").addEventListener("click", () => {
    document.getElementById("categoria").value = "";
    document.getElementById("marca").value = "";

    actualizarTablaDeDatos();
});

//-----------------------------------------------

function actualizarTablaDeDatos()
{
    $("#tabledatos").bootstrapTable("refresh");
}

//-----------------------------------------------

function msrpFormatter(value, row, index) {
    return "$ " + Number(row.MSRP).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function operateFormatter(value, row, index) {
    return [
        '<a href="javascript:verDatos(' + row.PRODUCTOID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function customParams(p)
{
    return {
        categoria: $("#categoria").val(),
        marca: $("#marca").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

function verDatos(pId)
{
    window.location.href="?mod=inventario&opc=productos&subopc=verproducto&pid=" + pId;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------