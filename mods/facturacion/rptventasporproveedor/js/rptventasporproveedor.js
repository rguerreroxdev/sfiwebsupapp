//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalSeleccionarProveedor = new bootstrap.Modal(document.getElementById('modalSeleccionarProveedor'));

//-----------------------------------------------

const hoy = new Date();
const fechaActual = hoy.toISOString().split('T')[0];
document.getElementById("fechainicial").value = fechaActual;
document.getElementById("fechafinal").value = fechaActual;

//-----------------------------------------------

document.getElementById("btntodos").addEventListener("click", () => {
    todosLosProveedores();
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
    const p = document.getElementById("proveedorid").value;

    let url = `mods/facturacion/rptventasporproveedor/procs/generarpdfdetalle.php?u=${u}&s=${s}&fi=${fi}&ff=${ff}&p=${p}`;
    window.open(url, "_blank");
}

function generarExcel()
{
    const s = document.getElementById("sucursal").value;
    const fi = document.getElementById("fechainicial").value;
    const ff = document.getElementById("fechafinal").value;
    const u = document.getElementById("uid").value;
    const p = document.getElementById("proveedorid").value;

    let url = `mods/facturacion/rptventasporproveedor/procs/generarexceldetalle.php?u=${u}&s=${s}&fi=${fi}&ff=${ff}&p=${p}`;
    window.open(url, "_blank");
}


//-----------------------------------------------

function todosLosProveedores()
{
    document.getElementById("proveedor").value = "ALL SUPPLIERS";
    document.getElementById("proveedorid").value = -1;
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

document.getElementById("btnproveedor").addEventListener("click", () => {
    modalSeleccionarProveedor.show();
});

function proveedoresOperateFormatter(value, row, index) {
    return [
        '<a class="sel-proveedor" href="javascript:void(0)" title="Aplicar">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.proveedoresOperateEvents = {
    "click .sel-proveedor": function(e, value, row, index) {
        document.getElementById("proveedor").value = row.NOMBRE;
        document.getElementById("proveedorid").value = row.PROVEEDORID;
    
        modalSeleccionarProveedor.hide();
    }
}

function proveedoresCustomParams(p)
{
    return {
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

// Por defecto que se muestren todos los proveedores
todosLosProveedores();

//-----------------------------------------------