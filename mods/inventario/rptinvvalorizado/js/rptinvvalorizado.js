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
    const c = document.getElementById("categoria").value
    const u = document.getElementById("uid").value;

    let url = `mods/inventario/rptinvvalorizado/procs/generarpdf.php?u=${u}&s=${s}&c=${c}`;
    window.open(url, "_blank");
}

function generarExcel()
{
    const s = document.getElementById("sucursal").value;
    const c = document.getElementById("categoria").value
    const u = document.getElementById("uid").value;

    let url = `mods/inventario/rptinvvalorizado/procs/generarexcel.php?u=${u}&s=${s}&c=${c}`;
    window.open(url, "_blank");
}


//-----------------------------------------------