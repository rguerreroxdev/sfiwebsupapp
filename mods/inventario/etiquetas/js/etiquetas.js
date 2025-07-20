//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalEmitirEtiquetas = new bootstrap.Modal(document.getElementById('modalEmitirEtiquetas'));

//-----------------------------------------------

document.getElementById("categoria").addEventListener("change", () => {
    actualizarTablaDeDatos();
});

document.getElementById("btnetiquetas").addEventListener('click', function() {
    const tabla = document.getElementById("tablaDetalle");
    const filas = tabla.getElementsByTagName("tr");
    if (filas.length - 1 == 0)
    {
        document.getElementById("mensajetoast").innerHTML = "There are no inventory items selected";
        toastMensaje.show();
        return;
    }

    document.getElementById("ubicacioninicial").value = 1;
    modalEmitirEtiquetas.show();
});

//-----------------------------------------------

function actualizarTablaDeDatos()
{
    $("#tabledatos").bootstrapTable("refresh");
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

function operateFormatter(value, row, index) {
    return [
        '<a class="sel-iteminventario" href="javascript:void(0)" title="Add to list">',
        '<i class="bi bi-red bi-plus-square"></i>',
        '</a>'
    ].join('');
}

window.operateEvents = {
    "click .sel-iteminventario": function(e, value, row, index) {
        if (yaExisteId(row.INVENTARIOID))
        {
            return;
        }
        
        let tabla = document.getElementById("tablaDetalle").getElementsByTagName('tbody')[0];
        let newRow = tabla.insertRow(tabla.rows.length);

        let celda0 = newRow.insertCell(0);
        let celda1 = newRow.insertCell(1);
        let celda2 = newRow.insertCell(2);
        let celda3 = newRow.insertCell(3);

        celda0.innerHTML = `${row.CODIGOINVENTARIO}`;
        celda1.innerHTML = `${row.CATEGORIA}`;
        celda2.innerHTML = `${row.MODELO}`;
        celda3.innerHTML = `<input type="hidden" id="inventarioid[]" name="inventarioid[]" value="${row.INVENTARIOID}">
                            <button class="btn btn-sm btn-outline-danger pt-0 pb-0" type="button" onclick="eliminarFila(this)" title="Remove"><i class="bi bi-trash small"></i></button>`;

        mostrarNumeroDeItems();
    }
}

//-----------------------------------------------

function yaExisteId(inventarioId) {
    const elementos = document.querySelectorAll('input[id="inventarioid[]"]');

    for (let i = 0; i < elementos.length; i++) {
        if (elementos[i].value == inventarioId) {
            return true;
        }
    }

    return false;
}

//-----------------------------------------------

function mostrarNumeroDeItems()
{
    const tabla = document.getElementById("tablaDetalle");
    const filas = tabla.getElementsByTagName("tr");
    const numeroDeFilas = filas.length - 1;

    document.getElementById("numFilas").innerHTML = numeroDeFilas;
}

//-----------------------------------------------

function eliminarFila(boton)
{
    let fila = boton.parentNode.parentNode;
    fila.parentNode.removeChild(fila);

    mostrarNumeroDeItems();
}

//-----------------------------------------------

function customParams(p)
{
    return {
        cid: $("#categoria").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

function setPos(pos)
{
    document.getElementById("ubicacioninicial").value = pos;
}

//-----------------------------------------------

document.getElementById("frmprint").addEventListener("submit", (event) => {
    event.preventDefault();

    const inputs = document.querySelectorAll('input[name="inventarioid[]"]');
    let ids = [];
    inputs.forEach(input => {
        ids.push('ids[]=' + encodeURIComponent(input.value));
    });
    idsparam = ids.join('&');
    
    const ubi = document.getElementById("ubicacioninicial").value;

    let url = `./mods/inventario/etiquetas/procs/imprimiretiquetas.php?tip=A&ubi=${ubi}&${idsparam}`;
    window.open(url, "_blank");
});

//-----------------------------------------------