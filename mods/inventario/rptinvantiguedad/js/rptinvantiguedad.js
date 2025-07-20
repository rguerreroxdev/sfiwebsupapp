//-----------------------------------------------

dias = document.getElementById("dias");

//-----------------------------------------------

document.getElementById("btnpdf").addEventListener("click", () => {
    if (dias.value == "") dias.value = 0;

    generarPdf();
});

document.getElementById("btnexcel").addEventListener("click", () => {
    if (dias.value == "") dias.value = 0;

    generarExcel();
});

//-----------------------------------------------

function generarPdf()
{
    const s = document.getElementById("sucursal").value;
    const c = document.getElementById("categoria").value;
    const d = parseInt(document.getElementById("dias").value, 10);
    const u = document.getElementById("uid").value;

    let url = `mods/inventario/rptinvantiguedad/procs/generarpdf.php?u=${u}&s=${s}&c=${c}&d=${d}`;
    window.open(url, "_blank");
}

function generarExcel()
{
    const s = document.getElementById("sucursal").value;
    const c = document.getElementById("categoria").value;
    const d = parseInt(document.getElementById("dias").value, 10);
    const u = document.getElementById("uid").value;

    let url = `mods/inventario/rptinvantiguedad/procs/generarexcel.php?u=${u}&s=${s}&c=${c}&d=${d}`;
    window.open(url, "_blank");
}


//-----------------------------------------------

function limitarDigitos(input)
{
    input.value = input.value.replace(/\D/g, '');

}

//-----------------------------------------------