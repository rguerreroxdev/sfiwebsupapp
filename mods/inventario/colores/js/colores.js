//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=colores&subopc=crearcolor";
});

//-----------------------------------------------

function operateFormatter(value, row, index) {
    return [
        '<a href="javascript:verDatos(' + row.COLORID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function verDatos(cId)
{
    window.location.href="?mod=inventario&opc=colores&subopc=vercolor&cid=" + cId;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------