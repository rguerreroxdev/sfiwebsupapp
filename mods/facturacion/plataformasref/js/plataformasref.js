//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=facturacion&opc=plataformasref&subopc=crearplataforma";
});

//-----------------------------------------------

function operateFormatter(value, row, index) {
    return [
        '<a href="javascript:verDatos(' + row.PLATAFORMADEREFERENCIAID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function verDatos(prId)
{
    window.location.href="?mod=facturacion&opc=plataformasref&subopc=verplataforma&prid=" + prId;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------