//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=admin&opc=sucursales&subopc=crearsucursal";
});

//-----------------------------------------------

function esCasaMatrizFormatter(value, row, index) {
    let elemento = "";

    if (row.ESCASAMATRIZ == 1)
    {
        elemento = `<i class="bi bi-check2-circle text-success"></i>`
    }
    else
    {
        elemento = `-`
    }

    return elemento;
}

function operateFormatter(value, row, index) {
    return [
        '<a href="javascript:verDatos(' + row.SUCURSALID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function verDatos(sId)
{
    window.location.href="?mod=admin&opc=sucursales&subopc=versucursal&sid=" + sId;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------