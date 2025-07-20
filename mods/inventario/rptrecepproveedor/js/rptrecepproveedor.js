//-----------------------------------------------

const hoy = new Date();
const fechaActual = hoy.toISOString().split('T')[0];
document.getElementById("fechainicial").value = fechaActual;
document.getElementById("fechafinal").value = fechaActual;

//-----------------------------------------------

let modalSeleccionarProveedor = new bootstrap.Modal(document.getElementById('modalSeleccionarProveedor'));

//-----------------------------------------------

document.getElementById("btnproveedor").addEventListener("click", () => {
    modalSeleccionarProveedor.show();
});

document.getElementById("btnpdf").addEventListener("click", () => {
    validarFechas();
    generarPdf();
});

document.getElementById("btnexcel").addEventListener("click", () => {
    validarFechas();
    generarExcel();
});

//-----------------------------------------------

function validarFechas()
{
    const hoy = new Date();
    const fechaActual = hoy.toISOString().split('T')[0];

    const fInicial = new Date(document.getElementById("fechainicial").value);
    if (isNaN(fInicial))
    {
        document.getElementById("fechainicial").value = fechaActual;
    }

    const fFinal = new Date(document.getElementById("fechafinal").value);
    if (isNaN(fFinal))
    {
        document.getElementById("fechafinal").value = fechaActual;
    }
}

//-----------------------------------------------

function generarPdf()
{
    const s = document.getElementById("sucursal").value;
    const p = document.getElementById("proveedorid").value;
    const fi = document.getElementById("fechainicial").value;
    const ff = document.getElementById("fechafinal").value;
    const u = document.getElementById("uid").value;

    let url = `mods/inventario/rptrecepproveedor/procs/generarpdf.php?u=${u}&s=${s}&p=${p}&fi=${fi}&ff=${ff}`;
    window.open(url, "_blank");
}

function generarExcel()
{
    const s = document.getElementById("sucursal").value;
    const p = document.getElementById("proveedorid").value;
    const fi = document.getElementById("fechainicial").value;
    const ff = document.getElementById("fechafinal").value;
    const u = document.getElementById("uid").value;

    let url = `mods/inventario/rptrecepproveedor/procs/generarexcel.php?u=${u}&s=${s}&p=${p}&fi=${fi}&ff=${ff}`;
    window.open(url, "_blank");
}

//-----------------------------------------------

function rowProveedorIndexFormatter(value, row, index) {
    return index + 1;
}

function proveedoresOperateFormatter(value, row, index) {
    return [
        '<a class="sel-proveedor" href="javascript:void(0)" title="Aplicar">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.proveedoresOperateEvents = {
    "click .sel-proveedor": function(e, value, row, index) {
        document.getElementById("proveedorid").value = row.PROVEEDORID;
        document.getElementById("codigoproveedor").value = row.CODIGO;
        document.getElementById("proveedor").value = row.NOMBRE;

        modalSeleccionarProveedor.hide();
    }
}

//-----------------------------------------------

function buscarProveedor(event)
{
    if(event.target.value.length == 4)
    {
        let datos = new FormData();
        datos.append("codigo", document.getElementById("codigoproveedor").value);
    
        fetch(
            "./mods/inventario/rptrecepproveedor/procs/buscarproveedor.php",
            {
                method: "POST",
                body: datos
            }
        )
        .then(response => response.json())
        .then(data => ubicarDatosProvedor(data))
        .catch(error => console.warn(error)); 
    }
    else
    {
        let refrescarTiposDeStock = document.getElementById("proveedorid").value != "";

        document.getElementById("proveedor").value = "";
        document.getElementById("proveedorid").value = "";
    }
}

function ubicarDatosProvedor(data)
{
    document.getElementById("proveedor").value = data.nombre;
    document.getElementById("proveedorid").value = data.proveedorid;
}

//-----------------------------------------------