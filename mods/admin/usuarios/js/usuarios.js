//-----------------------------------------------

document.getElementById("btncrear").addEventListener("click", () => {
    window.location.href="?mod=admin&opc=usuarios&subopc=crearusuario";
});

document.getElementById("activo").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("perfil").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

//-----------------------------------------------

function actualizarTablaDeDatos()
{
    $("#tabledatos").bootstrapTable("refresh");
}

//-----------------------------------------------

function activeFormatter(value, row, index) {
    let elemento = "";

    switch (row.ACTIVO) {
        case 0:
            elemento = `<span class="text-danger">Deactivated</span>`;
            break;
        case 1:
            elemento = `<span class="text-success">Active</span>`;
            break;
    }

    return elemento;
}

function operateFormatter(value, row, index) {
    return [
        '<a href="javascript:verDatos(' + row.USUARIOID + ')" title="See">',
        '<i class="bi bi-red bi-eye"></i>',
        '</a>'
    ].join('');
}

//-----------------------------------------------

function verDatos(uId)
{
    window.location.href="?mod=admin&opc=usuarios&subopc=verusuario&uid=" + uId;
}

//-----------------------------------------------

function customParams(p)
{
    return {
        activo: $("#activo").val(),
        perfil: $("#perfil").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------