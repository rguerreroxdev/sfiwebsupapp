//-----------------------------------------------

const hoy = new Date();
const fechaActual = hoy.toISOString().split('T')[0];
document.getElementById("fechainicial").value = fechaActual;
document.getElementById("fechafinal").value = fechaActual;

//-----------------------------------------------

document.getElementById("btnpdf").addEventListener("click", () => {
    generarPdf();
});

document.getElementById("btnexcel").addEventListener("click", () => {
    generarExcel();
});

//-----------------------------------------------

function generarPdf()
{
    const s = document.getElementById("sucursal").value;
    const c = document.getElementById("categoria").value;
    const fi = document.getElementById("fechainicial").value;
    const ff = document.getElementById("fechafinal").value;
    const t = document.getElementById("tipo").value;
    const u = document.getElementById("uid").value;

    let url = `mods/inventario/rptsalidas/procs/generarpdf.php?u=${u}&s=${s}&c=${c}&fi=${fi}&ff=${ff}&t=${t}`;
    window.open(url, "_blank");
}

function generarExcel()
{
    const s = document.getElementById("sucursal").value;
    const c = document.getElementById("categoria").value;
    const fi = document.getElementById("fechainicial").value;
    const ff = document.getElementById("fechafinal").value;
    const t = document.getElementById("tipo").value;
    const u = document.getElementById("uid").value;

    let url = `mods/inventario/rptsalidas/procs/generarexcel.php?u=${u}&s=${s}&c=${c}&fi=${fi}&ff=${ff}&t=${t}`;
    window.open(url, "_blank");
}


//-----------------------------------------------