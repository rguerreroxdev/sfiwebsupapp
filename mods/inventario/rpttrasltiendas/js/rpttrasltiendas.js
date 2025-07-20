//-----------------------------------------------

const hoy = new Date();
const fechaActual = hoy.toISOString().split('T')[0];
document.getElementById("fechainicial").value = fechaActual;
document.getElementById("fechafinal").value = fechaActual;

//-----------------------------------------------

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
    const c = document.getElementById("categoria").value;
    const fi = document.getElementById("fechainicial").value;
    const ff = document.getElementById("fechafinal").value;
    const u = document.getElementById("uid").value;

    let url = `mods/inventario/rpttrasltiendas/procs/generarpdf.php?u=${u}&s=${s}&c=${c}&fi=${fi}&ff=${ff}`;
    window.open(url, "_blank");
}

function generarExcel()
{
    const s = document.getElementById("sucursal").value;
    const c = document.getElementById("categoria").value;
    const fi = document.getElementById("fechainicial").value;
    const ff = document.getElementById("fechafinal").value;
    const u = document.getElementById("uid").value;

    let url = `mods/inventario/rpttrasltiendas/procs/generarexcel.php?u=${u}&s=${s}&c=${c}&fi=${fi}&ff=${ff}`;
    window.open(url, "_blank");
}


//-----------------------------------------------