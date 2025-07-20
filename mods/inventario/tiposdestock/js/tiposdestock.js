//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=tiposdestock&subopc=creartipodestock";
});

//-----------------------------------------------

function porcentajeFormatter(value, row, index) {
    return Number(row.PORCENTAJE).toFixed(2) + " %";
}

function operateFormatter(value, row, index) {
    return [
        '<a href="javascript:verDatos(' + row.TIPODESTOCKID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function verDatos(tsId)
{
    window.location.href="?mod=inventario&opc=tiposdestock&subopc=vertipodestock&tsid=" + tsId;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------