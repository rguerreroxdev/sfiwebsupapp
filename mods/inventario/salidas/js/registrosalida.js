//-----------------------------------------------

let filasEliminadas = [];
let datoDeInputOriginal = "";
let filaDetalleActual = null;

//-----------------------------------------------

let toastErrorElement = document.getElementById('toastError');
let toastError = bootstrap.Toast.getOrCreateInstance(toastErrorElement);

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalSeleccionarInventario = new bootstrap.Modal(document.getElementById('modalSeleccionarInventario'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));

//-----------------------------------------------

const selectSucursal = document.getElementById('sucursal');
const spanNombreSucursal = document.getElementById('nombresucursal');
selectSucursal.addEventListener('change', function() {
    const textoSeleccionado = selectSucursal.options[selectSucursal.selectedIndex].text;
    spanNombreSucursal.textContent = textoSeleccionado;
});

document.getElementById("btncancelar").addEventListener("click", () => {
    let sId = document.getElementById("sid").value;
    if (sId == -1)
    {
        window.location.href="?mod=inventario&opc=salidas";
    }
    else
    {
        window.location.href="?mod=inventario&opc=salidas&subopc=versalida&sid=" + sId;
    }
});

document.getElementById("categoria").addEventListener("change", () => {
    actualizarTablaDeInventario();
});

//-----------------------------------------------

function readonlyPre(event)
{
    datoDeInputOriginal = event.target.value;
}

function readonly(event)
{
    event.target.value = datoDeInputOriginal;
}

//-----------------------------------------------

function agregarFila()
{
    if (document.getElementById("sucursal").value == "")
    {
        document.getElementById("textodeerror").innerHTML = "You must select a store to add inventory items.";
        toastError.show();
        return;
    }

    let tabla = document.getElementById("tablaDetalle").getElementsByTagName('tbody')[0];
    let newRow = tabla.insertRow(tabla.rows.length);

    let celda1 = newRow.insertCell(0);
    let celda2 = newRow.insertCell(1);
    let celda3 = newRow.insertCell(2);
    let celda4 = newRow.insertCell(3);
    let celda5 = newRow.insertCell(4);
    let celda6 = newRow.insertCell(5);

    celda1.innerHTML = `<div class="input-group input-group-sm">
                            <input type="text" id="inventario[]" name="inventario[]" class="form-control form-control-sm" maxlength="9" oninput="buscarItem(this)" required>
                            <button class="btn btn-outline-secondary" type="button" id="btninventario[]" onclick="seleccionarInventario(this)"><i class="bi bi-search"></i></button>
                            <input type="hidden" id="inventarioid[]" name="inventarioid[]" value="">
                        </div>`;
    celda2.innerHTML = `<input type="text" id="categoria[]" name="categoria[]" class="form-control form-control-sm form-control-readonly" onfocus"readonlyPre(event)" oninput="readonly(event)" readonly required>`;
    celda3.innerHTML = `<input type="text" id="marca[]" name="marca[]" class="form-control form-control-sm form-control-readonly" value="" onfocus="readonlyPre(event)" oninput="readonly(event)" readonly required>`;
    celda4.innerHTML = `<input type="text" id="modelo[]" name="modelo[]" class="form-control form-control-sm form-control-readonly" onfocus="readonlyPre(event)" oninput="readonly(event)" readonly required>`;
    celda5.innerHTML = `<input type="text" id="descripcion[]" name="descripcion[]" class="form-control form-control-sm form-control-readonly" value="" onfocus="readonlyPre(event)" oninput="readonly(event)" readonly required>`;
    celda6.innerHTML = `<input type="hidden" id="detalleid[]" name="detalleid[]" value="">
                        <button class="btn btn-sm btn-outline-danger" type="button" onclick="eliminarFila(this)" title="Delete"><i class="bi bi-trash"></i></button>`;
    
    setTimeout(function() {
        celda1.getElementsByTagName("input")[0].focus();
    }, 0);

    // La sucursal no puede ser cambiada si ya hay al menos un ítem de inventario
    document.getElementById('sucursal').style.pointerEvents = 'none';
    document.getElementById('sucursal').style.backgroundColor = '#e9ecef';

    mostrarCantidadFilas();
}

//-----------------------------------------------

function eliminarFila(boton)
{
    let fila = boton.parentNode.parentNode;

    detid = fila.cells[5].getElementsByTagName("input")[0].value;
    if (detid != "")
    {
        filasEliminadas.push(detid);
    }
    
    fila.parentNode.removeChild(fila);

    // Evaluar si ya no hay filas para que se pueda volver a elegir la sucursal de origen
    var tabla = document.getElementById('tablaDetalle');
    let conteoDeFilas = tabla.getElementsByTagName('tbody')[0].getElementsByTagName('tr').length;
    if (conteoDeFilas == 0)
    {
        document.getElementById('sucursal').style.pointerEvents = 'auto';
        document.getElementById('sucursal').style.backgroundColor = '';
    }

    mostrarCantidadFilas();
}

function mostrarCantidadFilas()
{
    let inputs = document.querySelectorAll('input[id^="inventarioid"]');
    let filledInputs = Array.from(inputs).filter(input => input.value.trim() !== '');
    
    document.getElementById("totalitems").innerHTML = filledInputs.length;
}

//-----------------------------------------------

function buscarItem(input)
{
    if(input.value.length == 9)
    {
        let datos = new FormData();
        datos.append("codigo", input.value);
    
        fetch(
            "./mods/inventario/salidas/procs/buscaritem.php",
            {
                method: "POST",
                body: datos
            }
        )
        .then(response => response.json())
        .then(data => {
            filaDetalleActual = input.parentNode.parentNode.parentNode;

            if (data.encontrado == 1)
            {
                if (validarItem(data))
                {
                    filaDetalleActual.cells[0].getElementsByTagName("input")[1].value = data.inventarioid;
                    filaDetalleActual.cells[1].getElementsByTagName("input")[0].value = data.categoria;
                    filaDetalleActual.cells[2].getElementsByTagName("input")[0].value = data.marca;
                    filaDetalleActual.cells[3].getElementsByTagName("input")[0].value = data.modelo;
                    filaDetalleActual.cells[4].getElementsByTagName("input")[0].value = data.descripcion;

                    eliminarUltimaFilaVacia();
                    agregarFila();
                }
                else
                {
                    filaDetalleActual.cells[0].getElementsByTagName("input")[1].value = "";
                    filaDetalleActual.cells[1].getElementsByTagName("input")[0].value = "";
                    filaDetalleActual.cells[2].getElementsByTagName("input")[0].value = "";
                    filaDetalleActual.cells[3].getElementsByTagName("input")[0].value = "";
                    filaDetalleActual.cells[4].getElementsByTagName("input")[0].value = "";

                    input.value = "";
                }
            }

        })
        .catch(error => console.warn(error)); 
    }
    else
    {
        filaDetalleActual = input.parentNode.parentNode.parentNode;
        filaDetalleActual.cells[0].getElementsByTagName("input")[1].value = "";
        filaDetalleActual.cells[1].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[2].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[3].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[4].getElementsByTagName("input")[0].value = "";
    }

    mostrarCantidadFilas();
}

function validarItem(data)
{
    // Ver que el ítem se encuentre en la sucursal seleccionada
    if (data.sucursalid != document.getElementById("sucursal").value)
    {
        document.getElementById("textodeerror").innerHTML = "The item is not found at the selected store.";
        toastError.show();
        return false;
    }

    // No se puede seleccionar un ítem que no tenga existencia
    if (data.existencia <= 0)
    {
        document.getElementById("textodeerror").innerHTML = "There is no stock of the item.";
        toastError.show();
        return false;
    }

    // El ítem no tiene que existir en otra fila del detalle
    if (existeItemEnFila(data.inventarioid))
    {
        return false;
    }

    return true;
}

function existeItemEnFila(invId)
{
    let inputs = document.querySelectorAll('input[id="inventarioid[]"]');
    let yaExiste = Array.from(inputs).some(input => input.value == invId);
    if (yaExiste)
    {
        document.getElementById("textodeerror").innerHTML = "The item has already been selected.";
        toastError.show();
        return true;
    }

    return false;
}

//-----------------------------------------------

function actualizarTablaDeInventario()
{
    $("#tableinventario").bootstrapTable("refresh");
}

function seleccionarInventario(boton)
{
    filaDetalleActual = boton.parentNode.parentNode;
    document.getElementById("categoria").value = "";
    let searchInputs = document.querySelectorAll('input[type="search"]');
    searchInputs.forEach(function(input) {
        input.value = '';
    });

    $('#tableinventario').bootstrapTable('resetSearch');
    actualizarTablaDeInventario();
    modalSeleccionarInventario.show();
}

//-----------------------------------------------

function inventarioCustomParams(p)
{
    return {
        sid: $("#sucursal").val(),
        cid: $("#categoria").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

function rowInventarioIndexFormatter(value, row, index) {
    return index + 1;
}

function inventarioOperateFormatter(value, row, index) {
    return [
        '<a class="sel-inventario" href="javascript:void(0)" title="Apply">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.inventarioOperateEvents = {
    "click .sel-inventario": function(e, value, row, index) {
        if (existeItemEnFila(row.INVENTARIOID))
        {
            return;
        }

        filaDetalleActual.getElementsByTagName("input")[0].value = row.CODIGOINVENTARIO;
        filaDetalleActual.getElementsByTagName("input")[1].value = row.INVENTARIOID;
        filaDetalleActual.parentNode.cells[1].getElementsByTagName("input")[0].value = row.CATEGORIA;
        filaDetalleActual.parentNode.cells[2].getElementsByTagName("input")[0].value = row.MARCA;
        filaDetalleActual.parentNode.cells[3].getElementsByTagName("input")[0].value = row.MODELO;
        filaDetalleActual.parentNode.cells[4].getElementsByTagName("input")[0].value = row.DESCRIPCION;
        
        eliminarUltimaFilaVacia();
        agregarFila();

        modalSeleccionarInventario.hide();

        setTimeout(() => {
            //document.getElementById("btnagregarfila").focus();
            //filaDetalleActual.parentNode.cells[4].getElementsByTagName("input")[0].focus();
        }, 500);
    }
}

//-----------------------------------------------

document.getElementById("btnguardar").addEventListener("click", (event) => {
    eliminarUltimaFilaVacia();
});

document.getElementById("frm").addEventListener("submit", (event) => {
    event.preventDefault();

    if (!validarExistenciaDeFilas())
    {
        document.getElementById("textodeerror").innerHTML = "Cannot save without item rows.";
        toastError.show();
        return;
    }

    if (!verificarInputsDeFilas())
    {
        document.getElementById("textodeerror").innerHTML = "There are rows without inventory item.";
        toastError.show();
        return;
    }

    aplicarTrimAElementos();

    if (event.target.checkValidity())
    {
        let datos = new FormData(event.target);
        let jsonFilasEliminadas = JSON.stringify(filasEliminadas);
        datos.append("filaseliminadas", jsonFilasEliminadas);

        guardar(datos);
    }
});

//-----------------------------------------------

function guardar(datos)
{
    document.getElementById("btnguardar").setAttribute("disabled", "true");
    document.getElementById("btnguardarspinner").classList.remove("visually-hidden");

    fetch(
        "./mods/inventario/salidas/procs/guardarsalida.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizar(data))
    .catch(error => console.warn(error));
}

function finalizar(data)
{
    document.getElementById("btnguardarspinner").classList.add("visually-hidden");

    if (data.error == 0)
    {
        toastMensaje.show();
        setTimeout(() => {
            window.location.href="?mod=inventario&opc=salidas&subopc=versalida&sid=" + data.sid;
        }, 2000);
    }
    else
    {
        document.getElementById("mensajedeerror").innerHTML = data.mensaje;
        modalMensaje.show();

        document.getElementById("btnguardar").removeAttribute("disabled");
    }
}

//-----------------------------------------------

function aplicarTrimAElementos()
{
    let concepto = document.getElementById("concepto");
    
    concepto.value = concepto.value.trim();
}

function eliminarUltimaFilaVacia()
{
    let inputs = document.querySelectorAll('input[id="categoria[]"]');
    if (inputs.length == 0) return;

    let ultimoInput = inputs[inputs.length - 1];

    if (ultimoInput.value === "") {
        eliminarFila(ultimoInput);
    }
}

function validarExistenciaDeFilas()
{
    let inputs = document.querySelectorAll('input[id^="detalleid"]');
    return inputs.length > 0;
}

function verificarInputsDeFilas() {
    let inputs = document.querySelectorAll('input[id^="categoria"]');
    let algunoVacio = false;

    inputs.forEach(function(input) {
        if (input.value.trim() === '') {
            algunoVacio = true;
        }
    });

    if (algunoVacio) {
        return false;
    }

    return true;
}

// //-----------------------------------------------

// Al cargar: verifica si hay filas en detalle para poner readonly la tienda origen
if (document.getElementById('tablaDetalle').getElementsByTagName('tbody')[0].getElementsByTagName('tr').length > 0)
{
    document.getElementById('sucursal').style.pointerEvents = 'none';
    document.getElementById('sucursal').style.backgroundColor = '#e9ecef';
}

mostrarCantidadFilas();

//-----------------------------------------------