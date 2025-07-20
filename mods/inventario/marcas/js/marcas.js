//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=marcas&subopc=crearmarca";
});

//-----------------------------------------------

function operateFormatter(value, row, index) {
    return [
        '<a href="javascript:verDatos(' + row.MARCAID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function verDatos(mId)
{
    window.location.href="?mod=inventario&opc=marcas&subopc=vermarca&mid=" + mId;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------