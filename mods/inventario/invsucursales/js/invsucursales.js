//-----------------------------------------------

document.getElementById("categoria").addEventListener("change", () => {
    actualizarTablaDeProductos();
});

document.getElementById("marca").addEventListener("change", () => {
    actualizarTablaDeProductos();
});

//-----------------------------------------------

function actualizarTablaDeProductos()
{
    $("#tableproductos").bootstrapTable("refresh");
}

//-----------------------------------------------

function productosCustomParams(p)
{
    return {
        categoria: $("#categoria").val(),
        marca: $("#marca").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

function existenciasCustomParams(p)
{
    return {
        uid: $("#uid").val(),
        pid: $("#pid").val()
    };
}

//-----------------------------------------------

function onClickRow(row, $element) {
    document.getElementById("pid").value = row.PRODUCTOID;
    document.getElementById("detproducto").innerHTML = row.CATEGORIA + " - " + row.MODELO;

    $("#tableexistencias").bootstrapTable("refresh");
}

$(document).ready(function () {
    $('#tableproductos').bootstrapTable("destroy");
    $('#tableproductos').bootstrapTable({
        onClickRow: onClickRow
    });
});

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------