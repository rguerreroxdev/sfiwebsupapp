//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));

//-----------------------------------------------

const hoy = new Date();
const fechaActual = hoy.toISOString().split('T')[0];
document.getElementById("fechainicial").value = fechaActual;
document.getElementById("fechafinal").value = fechaActual;

//-----------------------------------------------

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

    const mostrarDetalle = document.getElementById("mostrardetalle").checked;

    let url = mostrarDetalle ?
        `mods/facturacion/rptimpuestosporventa/procs/generarpdfdetalle.php?u=${u}&s=${s}&fi=${fi}&ff=${ff}`
        :
        `mods/facturacion/rptimpuestosporventa/procs/generarpdf.php?u=${u}&s=${s}&fi=${fi}&ff=${ff}`;
    window.open(url, "_blank");
}

function generarExcel()
{
    const s = document.getElementById("sucursal").value;
    const fi = document.getElementById("fechainicial").value;
    const ff = document.getElementById("fechafinal").value;
    const u = document.getElementById("uid").value;

    const mostrarDetalle = document.getElementById("mostrardetalle").checked;


    let url = mostrarDetalle ?
        `mods/facturacion/rptimpuestosporventa/procs/generarexceldetalle.php?u=${u}&s=${s}&fi=${fi}&ff=${ff}`
        :
        `mods/facturacion/rptimpuestosporventa/procs/generarexcel.php?u=${u}&s=${s}&fi=${fi}&ff=${ff}`;
    window.open(url, "_blank");
}


//-----------------------------------------------
