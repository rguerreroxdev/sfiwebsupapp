//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalSeleccionarVendedor = new bootstrap.Modal(document.getElementById('modalSeleccionarVendedor'));

//-----------------------------------------------

const hoy = new Date();
const fechaActual = hoy.toISOString().split('T')[0];
document.getElementById("fechainicial").value = fechaActual;
document.getElementById("fechafinal").value = fechaActual;

//-----------------------------------------------

document.getElementById("btntodos").addEventListener("click", () => {
    todosLosVendedores();
});

document.getElementById("btnpdf").addEventListener("click", () => {
    if (document.getElementById("sucursal").value == "")
    {
        document.getElementById("mensajetoast").innerHTML = "You must select a store";
        toastMensaje.show();
        return;
    }

    generarPdf();
});

document.getElementById("btnexcel").addEventListener("click", () => {
    if (document.getElementById("sucursal").value == "")
    {
        document.getElementById("mensajetoast").innerHTML = "You must select a store";
        toastMensaje.show();
        return;
    }

    generarExcel();
});

//-----------------------------------------------

function generarPdf()
{
    const s = document.getElementById("sucursal").value;
    const fi = document.getElementById("fechainicial").value;
    const ff = document.getElementById("fechafinal").value;
    const u = document.getElementById("uid").value;
    const v = document.getElementById("vendedorid").value;

    const mostrarDetalle = document.getElementById("mostrardetalle").checked;

    let url = mostrarDetalle ?
        `mods/facturacion/rptventasporempleado/procs/generarpdfdetalle.php?u=${u}&s=${s}&fi=${fi}&ff=${ff}&v=${v}`
        :
        `mods/facturacion/rptventasporempleado/procs/generarpdf.php?u=${u}&s=${s}&fi=${fi}&ff=${ff}&v=${v}`;
    window.open(url, "_blank");
}

function generarExcel()
{
    const s = document.getElementById("sucursal").value;
    const fi = document.getElementById("fechainicial").value;
    const ff = document.getElementById("fechafinal").value;
    const u = document.getElementById("uid").value;
    const v = document.getElementById("vendedorid").value;

    const mostrarDetalle = document.getElementById("mostrardetalle").checked;


    let url = mostrarDetalle ?
        `mods/facturacion/rptventasporempleado/procs/generarexceldetalle.php?u=${u}&s=${s}&fi=${fi}&ff=${ff}&v=${v}`
        :
        `mods/facturacion/rptventasporempleado/procs/generarexcel.php?u=${u}&s=${s}&fi=${fi}&ff=${ff}&v=${v}`;
    window.open(url, "_blank");
}


//-----------------------------------------------

function todosLosVendedores()
{
    document.getElementById("vendedor").value = "ALL SALESPERSON";
    document.getElementById("vendedorid").value = -1;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

document.getElementById("btnvendedor").addEventListener("click", () => {
    modalSeleccionarVendedor.show();
});

function vendedoresOperateFormatter(value, row, index) {
    return [
        '<a class="sel-vendedor" href="javascript:void(0)" title="Aplicar">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.vendedoresOperateEvents = {
    "click .sel-vendedor": function(e, value, row, index) {
        document.getElementById("vendedor").value = row.NOMBRECOMPLETO;
        document.getElementById("vendedorid").value = row.USUARIOID;
    
        modalSeleccionarVendedor.hide();
    }
}

function vendedoresCustomParams(p)
{
    return {
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

// Por defecto que se muestren todos los vendedores
todosLosVendedores();

//-----------------------------------------------