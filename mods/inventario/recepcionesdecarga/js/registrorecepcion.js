//-----------------------------------------------

let filaDetalleActual = null;
let filasEliminadas = [];
let datoDeInputOriginal = "";
let tipoDeStockSeleccion = "";

//-----------------------------------------------

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));

let toastErrorElement = document.getElementById('toastError');
let toastError = bootstrap.Toast.getOrCreateInstance(toastErrorElement);

let modalSeleccionarProveedor = new bootstrap.Modal(document.getElementById('modalSeleccionarProveedor'));
let modalSeleccionarProducto = new bootstrap.Modal(document.getElementById('modalSeleccionarProducto'));
let modalSeleccionarTipoDeStockDetalle = new bootstrap.Modal(document.getElementById('modalSeleccionarTipoDeStockDetalle'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));

//-----------------------------------------------

document.getElementById("btncancelar").addEventListener("click", () => {
    let rId = document.getElementById("rid").value;
    if (rId == -1)
    {
        window.location.href="?mod=inventario&opc=recepcionesdecarga";
    }
    else
    {
        window.location.href="?mod=inventario&opc=recepcionesdecarga&subopc=verrecepcion&rid=" + rId;
    }
});

document.getElementById("btnproveedor").addEventListener("click", () => {
    modalSeleccionarProveedor.show();
});

document.getElementById("tipodestockorigen").addEventListener("change", async (event) => {
    const datosTipoDeStock = await obtenerDatosTipoDeStock(event.target.value);
    document.getElementById("porcentajeorigen").value = datosTipoDeStock.porcentaje != null ? datosTipoDeStock.porcentaje : "0.00";
});

document.getElementById("tipodestockdist").addEventListener("change", async (event) => {
    const datosTipoDeStock = await obtenerDatosTipoDeStock(event.target.value);
    document.getElementById("porcentajedist").value = datosTipoDeStock.porcentaje != null ? datosTipoDeStock.porcentaje : "0.00";
});


//-----------------------------------------------

async function obtenerDatosTipoDeStock(id)
{
    let datos = new FormData();
    datos.append("tsid", id);

    const response = await fetch(
        "./mods/inventario/recepcionesdecarga/procs/gettipodestock.php",
        {
            method: "POST",
            body: datos
        }
    );

    const datosTipoStock = await response.json();
    return datosTipoStock;
}

//-----------------------------------------------

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
    
        actualizarTiposDeStock();

        document.getElementById("numerodedocumento").focus();
    }
}

function productosOperateFormatter(value, row, index) {
    return [
        '<a class="sel-producto" href="javascript:void(0)" title="Apply">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.productosOperateEvents = {
    "click .sel-producto": function(e, value, row, index) {
        filaDetalleActual.getElementsByTagName("input")[0].value = row.CODIGO;
        filaDetalleActual.getElementsByTagName("input")[1].value = row.PRODUCTOID;
        filaDetalleActual.parentNode.cells[2].getElementsByTagName("input")[0].value = row.CATEGORIA;
        filaDetalleActual.parentNode.cells[3].getElementsByTagName("input")[0].value = row.MARCA;
        filaDetalleActual.parentNode.cells[4].getElementsByTagName("input")[0].value = row.MODELO;
        filaDetalleActual.parentNode.cells[5].getElementsByTagName("input")[0].value = row.DESCRIPCION;
        filaDetalleActual.parentNode.cells[10].getElementsByTagName("input")[0].value = row.MSRP;
        
        eliminarUltimaFilaVacia();
        agregarFila();

        modalSeleccionarProducto.hide();

        filaDetalleActual.parentNode.cells[6].getElementsByTagName("input")[0].focus();
    }
}

function tiposDeStockDetalleOperateFormatter(value, row, index) {
    return [
        '<a class="sel-tipodestockdetalle" href="javascript:void(0)" title="Apply">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.tiposDeStockDetalleOperateEvents = {
    "click .sel-tipodestockdetalle": function(e, value, row, index) {
        filaDetalleActual.getElementsByTagName("input")[0].value = row.NOMBRECORTO;
        filaDetalleActual.getElementsByTagName("input")[1].value = row.TIPODESTOCKID;
        
        let celdaPorcentaje = tipoDeStockSeleccion == "O" ? 7 : 9;

        filaDetalleActual.parentNode.cells[celdaPorcentaje].getElementsByTagName("input")[0].value = row.PORCENTAJE;
            
        modalSeleccionarTipoDeStockDetalle.hide();

        filaDetalleActual.parentNode.cells[celdaPorcentaje].getElementsByTagName("input")[0].focus();
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
            "./mods/inventario/recepcionesdecarga/procs/buscarproveedor.php",
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

        if (refrescarTiposDeStock)
        {
            actualizarTiposDeStock();
        }
    }
}

function ubicarDatosProvedor(data)
{
    document.getElementById("proveedor").value = data.nombre;
    document.getElementById("proveedorid").value = data.proveedorid;
    
    actualizarTiposDeStock();
}

//-----------------------------------------------

function actualizarTiposDeStock()
{
    actualizarComboTiposDeStock();
    
    ubicarProveedorEnModalTiposDeStock();
    $("#tabletipodestockdetalle").bootstrapTable("refresh");

    modalSeleccionarProveedor.hide();
}

function actualizarComboTiposDeStock()
{
    let datos = new FormData();
    datos.append("pid", document.getElementById("proveedorid").value);

    fetch(
        "./mods/inventario/recepcionesdecarga/procs/getcombotiposdestock.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => {
        document.getElementById("tipodestockorigen").innerHTML = data.opciones;
        document.getElementById("porcentajeorigen").value = "0.00";
        document.getElementById("tipodestockdist").innerHTML = data.opciones;
        document.getElementById("porcentajedist").value = "0.00";
    })
    .catch(error => console.warn(error));    
}

function ubicarProveedorEnModalTiposDeStock()
{
    document.getElementById("proveedormodaltiposdestock").innerHTML =
    document.getElementById("codigoproveedor").value + " - " +
    document.getElementById("proveedor").value;
}

//-----------------------------------------------

document.getElementById("categoria").addEventListener("change", () => {
    actualizarTablaDeProductos();
});

document.getElementById("marca").addEventListener("change", () => {
    actualizarTablaDeProductos();
});

function actualizarTablaDeProductos()
{
    $("#tableproductos").bootstrapTable("refresh");
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
        document.getElementById("textodeerror").innerHTML = "There are rows without a product selected.";
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
        "./mods/inventario/recepcionesdecarga/procs/guardarrecepcion.php",
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
            window.location.href="?mod=inventario&opc=recepcionesdecarga&subopc=verrecepcion&rid=" + data.rid;
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

function buscarProducto(input)
{
    if(input.value.length == 5)
    {
        let datos = new FormData();
        datos.append("codigo", input.value);
    
        fetch(
            "./mods/inventario/recepcionesdecarga/procs/buscarproducto.php",
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
                filaDetalleActual.cells[1].getElementsByTagName("input")[0].value = data.codigo;
                filaDetalleActual.cells[1].getElementsByTagName("input")[1].value = data.productoid;
                filaDetalleActual.cells[2].getElementsByTagName("input")[0].value = data.categoria;
                filaDetalleActual.cells[3].getElementsByTagName("input")[0].value = data.marca;
                filaDetalleActual.cells[4].getElementsByTagName("input")[0].value = data.modelo;
                filaDetalleActual.cells[5].getElementsByTagName("input")[0].value = data.descripcion;
                filaDetalleActual.cells[10].getElementsByTagName("input")[0].value = data.msrp;

                eliminarUltimaFilaVacia();
                agregarFila();
            }
        })
        .catch(error => console.warn(error)); 
    }
    else
    {
        filaDetalleActual = input.parentNode.parentNode.parentNode;
        filaDetalleActual.cells[1].getElementsByTagName("input")[1].value = "";
        filaDetalleActual.cells[2].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[3].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[4].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[5].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[10].getElementsByTagName("input")[0].value = "";
    }
}

//-----------------------------------------------

function seleccionarProducto(boton)
{
    filaDetalleActual = boton.parentNode.parentNode;
    document.getElementById("categoria").value = "";
    document.getElementById("marca").value = "";
    let searchInputs = document.querySelectorAll('input[type="search"]');
    searchInputs.forEach(function(input) {
        input.value = '';
    });

    $('#tableproductos').bootstrapTable('resetSearch');
    actualizarTablaDeProductos();
    modalSeleccionarProducto.show();
}

function seleccionarTipoDeStockDetalle(boton, tipoSeleccion)
{
    tipoDeStockSeleccion = tipoSeleccion;
    $("#tabletipodestockdetalle").bootstrapTable("refresh");
    filaDetalleActual = boton.parentNode.parentNode;
    modalSeleccionarTipoDeStockDetalle.show();
}

//-----------------------------------------------

function agregarFila()
{
    if (document.getElementById("tipodestockorigen").value == "" || document.getElementById("tipodestockdist").value == "")
    {
        document.getElementById("textodeerror").innerHTML = "You must select origin and distribution stock types to add rows.";
        toastError.show();
        return;
    }

    let selectTipoDeStockOrigen = document.getElementById("tipodestockorigen");
    let selectTipoDeStockDist = document.getElementById("tipodestockdist");
    let tipoDeStockOrigenId = selectTipoDeStockOrigen.value;
    let tipoDeStockDistId = selectTipoDeStockDist.value;
    let tipoDeStockOrigen = selectTipoDeStockOrigen.options[selectTipoDeStockOrigen.selectedIndex].text;
    let tipoDeStockDist = selectTipoDeStockDist.options[selectTipoDeStockDist.selectedIndex].text;
    let porcentajeOrigen = document.getElementById("porcentajeorigen").value;
    let porcentajeDist = document.getElementById("porcentajedist").value;

    let tabla = document.getElementById("tablaDetalle").getElementsByTagName('tbody')[0];
    let newRow = tabla.insertRow(tabla.rows.length);

    let celda1 = newRow.insertCell(0);
    let celda2 = newRow.insertCell(1);
    let celda3 = newRow.insertCell(2);
    let celda4 = newRow.insertCell(3);
    let celda5 = newRow.insertCell(4);
    let celda6 = newRow.insertCell(5);
    let celda7 = newRow.insertCell(6);
    let celda8 = newRow.insertCell(7);
    let celda9 = newRow.insertCell(8);
    let celda10 = newRow.insertCell(9);
    let celda11 = newRow.insertCell(10);
    let celda12 = newRow.insertCell(11);

    celda1.innerHTML = `<input type="number" class="form-control form-control-sm" id="cantidad[]" name="cantidad[]" maxlength="4" step="1" min="1" value="" oninput="mostrarCantidadItems(this)" required>`;
    celda2.innerHTML = `<div class="input-group input-group-sm">
                            <input type="text" id="producto[]" name="producto[]" class="form-control form-control-sm" maxlength="5" onfocus="readonlyPre(event)" oninput="buscarProducto(this)" required>
                            <button class="btn btn-outline-secondary" type="button" id="btnproducto[]" onclick="seleccionarProducto(this)"><i class="bi bi-search"></i></button>
                            <input type="hidden" id="productoid[]" name="productoid[]" value="">
                        </div>`;
    celda3.innerHTML = `<input type="text" id="categoria[]" name="categoria[]" class="form-control form-control-sm form-control-readonly" onfocus"readonlyPre(event)" oninput="readonly(event)" readonly required>`;
    celda4.innerHTML = `<input type="text" id="marca[]" name="marca[]" class="form-control form-control-sm form-control-readonly" value="" onfocus="readonlyPre(event)" oninput="readonly(event)" readonly required>`;
    celda5.innerHTML = `<input type="text" id="modelo[]" name="modelo[]" class="form-control form-control-sm form-control-readonly" onfocus="readonlyPre(event)" oninput="readonly(event)" readonly required>`;
    celda6.innerHTML = `<input type="text" id="descripcion[]" name="descripcion[]" class="form-control form-control-sm form-control-readonly" value="" onfocus="readonlyPre(event)" oninput="readonly(event)" readonly required>`;
    celda7.innerHTML = `<div class="input-group input-group-sm">
                            <input type="text" id="tipodestockorigendetalle[]" name="tipodestockorigendetalle[]" class="form-control form-control-sm form-control-readonly" value="${tipoDeStockOrigen}" onfocus="readonlyPre(event)" oninput="readonly(event)" readonly required>
                            <button class="btn btn-outline-secondary" type="button" id="btntipodestockorigendetalle[]" onclick="seleccionarTipoDeStockDetalle(this, 'O')"><i class="bi bi-search"></i></button>
                            <input type="hidden" id="tipodestockorigendetalleid[]" name="tipodestockorigendetalleid[]" value="${tipoDeStockOrigenId}">
                        </div>`;
    celda8.innerHTML = `<input type="text" class="form-control form-control-sm text-end" id="porcentajeorigendetalle[]" name="porcentajeorigendetalle[]" step="0.01" min="0.00" value="${porcentajeOrigen}" readonly required>`;
    celda9.innerHTML = `<div class="input-group input-group-sm">
                            <input type="text" id="tipodestockdistdetalle[]" name="tipodestockdistdetalle[]" class="form-control form-control-sm form-control-readonly" value="${tipoDeStockDist}" onfocus="readonlyPre(event)" oninput="readonly(event)" readonly required>
                            <button class="btn btn-outline-secondary" type="button" id="btntipodestockdistdetalle[]" onclick="seleccionarTipoDeStockDetalle(this, 'D')"><i class="bi bi-search"></i></button>
                            <input type="hidden" id="tipodestockdistdetalleid[]" name="tipodestockdistdetalleid[]" value="${tipoDeStockDistId}">
                        </div>`;
    celda10.innerHTML = `<input type="text" class="form-control form-control-sm text-end" id="porcentajedistdetalle[]" name="porcentajedistdetalle[]" step="0.01" min="0.00" value="${porcentajeDist}" readonly required>`;
    celda11.innerHTML = `<input type="text" class="form-control form-control-sm text-end" id="msrp[]" name="msrp[]" value="" readonly required>`;
    celda12.innerHTML = `<input type="hidden" id="detalleid[]" name="detalleid[]" value="">
                        <button class="btn btn-sm btn-outline-danger" type="button" onclick="eliminarFila(this)" title="Delete"><i class="bi bi-trash"></i></button>`;
    
    setTimeout(function() {
        celda1.getElementsByTagName("input")[0].focus();
    }, 0);

    mostrarCantidadItems();
}

//-----------------------------------------------

function eliminarFila(boton)
{
    let fila = boton.parentNode.parentNode;

    detid = fila.cells[11].getElementsByTagName("input")[0].value;
    if (detid != "")
    {
        filasEliminadas.push(detid);
    }
    
    fila.parentNode.removeChild(fila);

    mostrarCantidadItems();
}

//-----------------------------------------------

function productosCustomParams(p)
{
    return {
        categoria: $("#categoria").val(),
        marca: $("#marca").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

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

function aplicarTrimAElementos()
{
    let numeroDeDocumento = document.getElementById("numerodedocumento");
    
    numeroDeDocumento.value = numeroDeDocumento.value.trim();
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

//-----------------------------------------------

function tiposDeStockDetalleCustomParams(p)
{
    return {
        pid: $("#proveedorid").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

document.getElementById("sucursal").focus();

ubicarProveedorEnModalTiposDeStock();

//-----------------------------------------------

function rowProveedorIndexFormatter(value, row, index) {
    return index + 1;
}

function rowProductoIndexFormatter(value, row, index) {
    return index + 1;
}

function rowTipoStockIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------

function mostrarCantidadItems()
{
    let inputs = document.querySelectorAll('input[id="cantidad[]"]');
    let suma = 0;

    inputs.forEach(input => {
        let valor = parseInt(input.value);
        if (!isNaN(valor))
        {
            suma += valor;
        }
    });
    
    document.getElementById("totalitems").innerHTML = suma;
}

mostrarCantidadItems();

//-----------------------------------------------