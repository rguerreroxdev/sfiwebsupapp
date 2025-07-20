//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=tiposdepago&subopc=creartipodepago";
});

//-----------------------------------------------

function sumaImpuestoFormatter(value, row, index) {
    let elemento = "";

    if (row.SUMAIMPUESTO == 1)
    {
        elemento = `<i class="bi bi-check2-circle text-success"></i>`
    }
    else
    {
        elemento = `-`
    }

    return elemento;
}

function pagoSinImpuestoFormatter(value, row, index) {
    let elemento = "";

    if (row.PERMITEPAGOSINIMPUESTO == 1)
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
        '<a href="javascript:verDatos(' + row.TIPODEPAGOID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function verDatos(tpId)
{
    window.location.href="?mod=facturacion&opc=tiposdepago&subopc=vertipodepago&tpid=" + tpId;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------