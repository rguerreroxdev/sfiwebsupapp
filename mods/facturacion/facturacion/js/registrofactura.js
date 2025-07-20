//-----------------------------------------------

let datoDeInputOriginal = "";

let filasDetalleEliminadas = [];
let filaDetalleActual = null;

let filasServiciosEliminadas = [];
let filaServiciosActual = null;

let filasPagoEliminadas = [];

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

let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));

let toastErrorElement = document.getElementById('toastError');
let toastError = bootstrap.Toast.getOrCreateInstance(toastErrorElement);

let modalSeleccionarCliente = new bootstrap.Modal(document.getElementById('modalSeleccionarCliente'));
let modalSeleccionarVendedor = new bootstrap.Modal(document.getElementById('modalSeleccionarVendedor'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));
let modalSeleccionarInventario = new bootstrap.Modal(document.getElementById('modalSeleccionarInventario'));
let modalSeleccionarServicio = new bootstrap.Modal(document.getElementById('modalSeleccionarServicio'));
let modalNoConfig = new bootstrap.Modal(document.getElementById('modalNoConfig'));

//-----------------------------------------------

document.getElementById("btncancelar").addEventListener("click", () => {
    let fId = document.getElementById("fid").value;
    if (fId == -1)
    {
        window.location.href="?mod=facturacion&opc=facturacion";
    }
    else
    {
        window.location.href="?mod=facturacion&opc=facturacion&subopc=verfactura&fid=" + fId;
    }
});

//-----------------------------------------------

document.getElementById("btncliente").addEventListener("click", () => {
    modalSeleccionarCliente.show();
});

function clientesOperateFormatter(value, row, index) {
    return [
        '<a class="sel-cliente" href="javascript:void(0)" title="Aplicar">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.clientesOperateEvents = {
    "click .sel-cliente": function(e, value, row, index) {
        document.getElementById("clienteid").value = row.CLIENTEID;
        document.getElementById("codigocliente").value = row.CODIGO;
        document.getElementById("cliente").value = row.NOMBRE;

        document.getElementById("clientedireccion").value = row.DIRECCION;
        document.getElementById("clientedireccioncomplemento").value = row.DIRECCIONCOMPLEMENTO;
        document.getElementById("clientecodigopostal").value = row.CODIGOPOSTAL;
        document.getElementById("clientetelefono").value = row.TELEFONO;
        document.getElementById("clientecorreo").value = row.CORREOELECTRONICO;
        document.getElementById("esclienteprevio").checked = row.FACTURAS > 0;
    
        modalSeleccionarCliente.hide();

        document.getElementById("clientedireccion").focus();
    }
}

//-----------------------------------------------

document.getElementById("btnvendedor").addEventListener("click", () => {
    modalSeleccionarVendedor.show();
});

function vendedoresOperateFormatter(value, row, index) {
    return [
        '<a class="sel-vendedor" href="javascript:void(0)" title="Aplicar">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.vendedoresOperateEvents = {
    "click .sel-vendedor": function(e, value, row, index) {
        document.getElementById("vendedor").value = row.NOMBRECOMPLETO;
        document.getElementById("vendedorid").value = row.USUARIOID;
    
        modalSeleccionarVendedor.hide();

        document.getElementById("formaderetiro").focus();
    }
}

function vendedoresCustomParams(p)
{
    return {
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

//-----------------------------------------------

const selectSucursal = document.getElementById('sucursal');
const spanNombreSucursal = document.getElementById('nombresucursal');
selectSucursal.addEventListener('change', function() {
    const textoSeleccionado = selectSucursal.options[selectSucursal.selectedIndex].text;
    spanNombreSucursal.textContent = textoSeleccionado;

    if (selectSucursal.value == "")
    {
        document.getElementById("sucursalnombre").value = "";
        document.getElementById("sucursalid").value = "";
        document.getElementById("sucursaldireccion").value = "";
        document.getElementById("sucursaldireccioncomplemento").value = "";
        document.getElementById("sucursalcodigopostal").value = "";
        document.getElementById("sucursaltelefono").value = "";
        document.getElementById("sucursaltelefonoservicio").value = "";

        document.getElementById("prefijodecorrelativo").value = "";
        document.getElementById("correlativocompuesto").value = "-" + document.getElementById("correlativo").value;

        document.getElementById("impuestoporcentaje").value = 0.00;
        document.getElementById("porcentajevisto").innerHTML = 0.00.toFixed(2);

        return;
    }

    let datos = new FormData();
    datos.append("sid", selectSucursal.value);

    fetch(
        "./mods/facturacion/facturacion/procs/seleccionarsucursal.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizarSucursal(data))
    .catch(error => console.warn(error));

});

function finalizarSucursal(data)
{
    if (data.configuracionid != -1)
    {
        document.getElementById("sucursalnombre").value = data.sucursalnombre;
        document.getElementById("sucursalid").value = data.sucursalid;
        document.getElementById("sucursaldireccion").value = data.sucursaldireccion;
        document.getElementById("sucursaldireccioncomplemento").value = data.sucursaldireccioncomplemento;
        document.getElementById("sucursalcodigopostal").value = data.sucursalcodigopostal;
        document.getElementById("sucursaltelefono").value = data.sucursaltelefono;
        document.getElementById("sucursaltelefonoservicio").value = data.sucursaltelefonoservicio;

        document.getElementById("prefijodecorrelativo").value = data.prefijodecorrelativo;
        document.getElementById("correlativocompuesto").value = data.prefijodecorrelativo + "-" + document.getElementById("correlativo").value;

        document.getElementById("impuestoporcentaje").value = data.impuestoporcentaje;
        document.getElementById("porcentajevisto").innerHTML = data.impuestoporcentaje;
    }
    else
    {
        document.getElementById("sucursalnombre").value = "";
        document.getElementById("sucursalid").value = "";
        document.getElementById("sucursaldireccion").value = "";
        document.getElementById("sucursaldireccioncomplemento").value = "";
        document.getElementById("sucursalcodigopostal").value = "";
        document.getElementById("sucursaltelefono").value = "";
        document.getElementById("sucursaltelefonoservicio").value = "";

        document.getElementById("prefijodecorrelativo").value = "";
        document.getElementById("correlativocompuesto").value = "-" + document.getElementById("correlativo").value;

        document.getElementById("impuestoporcentaje").value = 0.00;
        document.getElementById("porcentajevisto").innerHTML = 0.00.toFixed(2);

        modalNoConfig.show();
        selectSucursal.value = "";
    }
}

function evaluarHabilitarSucursal()
{
    // Contar filas de tabla detalle y servicios y pagos
    let tablaDetalle = document.getElementById('tablaDetalle');
    let conteoDeFilasDetalle = tablaDetalle.getElementsByTagName('tbody')[0].getElementsByTagName('tr').length;

    let tablaDetalleServicios = document.getElementById('tablaServiciosOtrosProductos');
    let conteoDeFilasServicios = tablaDetalleServicios.getElementsByTagName('tbody')[0].getElementsByTagName('tr').length;

    let tablaPagos = document.getElementById('tablaPagos');
    let conteoDePagos = tablaPagos.getElementsByTagName('tbody')[0].getElementsByTagName('tr').length;

    if ((conteoDeFilasDetalle + conteoDeFilasServicios + conteoDePagos) == 0)
    {
        document.getElementById('sucursal').style.pointerEvents = 'auto';
        document.getElementById('sucursal').style.backgroundColor = '';
    }

}

//-----------------------------------------------

const codigoCliente = document.getElementById("codigocliente");
codigoCliente.addEventListener('blur', function() {
    if(document.getElementById("cliente").value == "")
    {
        codigoCliente.value = "";
    }
});

function buscarCliente(event)
{
    if(event.target.value.length == 7)
    {
        let datos = new FormData();
        datos.append("codigo", document.getElementById("codigocliente").value);
    
        fetch(
            "./mods/facturacion/facturacion/procs/buscarcliente.php",
            {
                method: "POST",
                body: datos
            }
        )
        .then(response => response.json())
        .then(data => ubicarDatosCliente(data))
        .catch(error => console.warn(error)); 
    }
    else
    {
        document.getElementById("cliente").value = "";
        document.getElementById("clienteid").value = "";

        document.getElementById("clientedireccion").value = "";
        document.getElementById("clientedireccioncomplemento").value = "";
        document.getElementById("clientecodigopostal").value = "";
        document.getElementById("clientetelefono").value = "";
        document.getElementById("clientecorreo").value = "";
        document.getElementById("esclienteprevio").checked = false;
    }
}

function ubicarDatosCliente(data)
{
    document.getElementById("cliente").value = data.nombre;
    document.getElementById("clienteid").value = data.clienteid;

    document.getElementById("clientedireccion").value = data.direccion;
    document.getElementById("clientedireccioncomplemento").value = data.direccioncomplemento;
    document.getElementById("clientecodigopostal").value = data.codigopostal;
    document.getElementById("clientetelefono").value = data.telefono;
    document.getElementById("clientecorreo").value = data.correo;
    document.getElementById("esclienteprevio").checked = data.facturas > 0;
}

//-----------------------------------------------

document.getElementById("nocalcularimpuesto").addEventListener('change', function () {
    let tablaPagos = document.getElementById('tablaPagos');
    let conteoDePagos = tablaPagos.getElementsByTagName('tbody')[0].getElementsByTagName('tr').length;
    
    if (conteoDePagos > 0)
    {
        document.getElementById("textodeerror").innerHTML = "You must delete all payment rows before change this option.";
        toastError.show();

        document.getElementById("nocalcularimpuesto").checked = !document.getElementById("nocalcularimpuesto").checked;
        return;        
    }
    
    // Obtener la lista de tipos de pago para el combobox
    let datos = new FormData();
    let soloMostrarPagosSinImpuesto = document.getElementById("nocalcularimpuesto").checked ? 1 : 0;

    datos.append("solomostrarpagosinimpuesto", soloMostrarPagosSinImpuesto);

    fetch(
        "./mods/facturacion/facturacion/procs/getcombotiposdepago.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => actualizarComboTiposDePago(data))
    .catch(error => console.warn(error)); 
});

function actualizarComboTiposDePago(data)
{
    const select = document.getElementById('selecttipodepago');
    select.innerHTML = '';

    for (let i=0; i<data.length; i++)
    {
        const option = document.createElement('option');
        if (data[i][0] == -1)
        {
            option.value = "";
        }
        else
        {
            option.value = data[i][0];
        }
        option.textContent = data[i][1];
        select.appendChild(option);
    }

    calcularTotales();
}

//-----------------------------------------------

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------

function agregarFilaInventario()
{
    if (document.getElementById('sucursal').value == "")
    {
        document.getElementById("textodeerror").innerHTML = "You must select a store.";
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
    let celda7 = newRow.insertCell(6);
    let celda8 = newRow.insertCell(7);

    celda1.innerHTML = `<div class="input-group input-group-sm">
                            <input type="text" id="inventario[]" name="inventario[]" class="form-control form-control-sm" maxlength="9" oninput="buscarItemInventario(this)" required>
                            <button class="btn btn-outline-secondary" type="button" id="btninventario[]" onclick="seleccionarInventario(this)"><i class="bi bi-search"></i></button>
                            <input type="hidden" id="inventarioid[]" name="inventarioid[]" value="">
                        </div>`;
    celda2.innerHTML = `<input type="text" id="marca[]" name="marca[]" class="form-control form-control-sm" value="" readonly>`;
    celda3.innerHTML = `<input type="text" id="modelo[]" name="modelo[]" class="form-control form-control-sm" readonly>`;
    celda4.innerHTML = `<input type="text" id="msrp[]" name="msrp[]" class="form-control form-control-sm text-end" readonly>`;
    celda5.innerHTML = `<input type="text" id="descripcion[]" name="descripcion[]" class="form-control form-control-sm" value="" readonly>`;
    celda6.innerHTML = `<input type="text" id="garantia[]" name="garantia[]" class="form-control form-control-sm" value="" readonly>
                        <input type="hidden" id=garantiaid[] name=garantiaid[] value="">`;
    celda7.innerHTML = `<input type="number" id="precio[]" name="precio[]" class="form-control form-control-sm text-end" value="0.00" min="0.01" max="99999.99" step="0.01" oninput="calcularTotales()" required>`;
    celda8.innerHTML = `<input type="hidden" id="detalleid[]" name="detalleid[]" value="">
                        <button class="btn btn-sm btn-outline-danger" type="button" onclick="eliminarFilaDetalle(this)" title="Delete"><i class="bi bi-trash"></i></button>`;
    
    setTimeout(function() {
        celda1.getElementsByTagName("input")[0].focus();
    }, 0);

    // La sucursal no puede ser cambiada si ya hay al menos un ítem de inventario
    document.getElementById('sucursal').style.pointerEvents = 'none';
    document.getElementById('sucursal').style.backgroundColor = '#e9ecef';
}

function eliminarFilaDetalle(boton)
{
    let fila = boton.parentNode.parentNode;

    detid = fila.cells[7].getElementsByTagName("input")[0].value;
    if (detid != "")
    {
        filasDetalleEliminadas.push(detid);
    }
    
    fila.parentNode.removeChild(fila);

    calcularTotales();

    // Evaluar si no hay filas detalle, servicios y pagos para habilitar sucursal
    evaluarHabilitarSucursal();
}

//-----------------------------------------------

function calcularTotales()
{
    const impuestoPorcentaje = document.getElementById("nocalcularimpuesto").checked ?
        0.00
        :
        document.getElementById("impuestoporcentaje").value;

    const preciosDetalle = document.querySelectorAll('input[id="precio[]"]');
    const preciosServicios = document.querySelectorAll('input[id="servprecio[]"]');
    let totalSinImpuesto = 0;

    preciosDetalle.forEach(input => {
        const valor = parseFloat(input.value);
        if (!isNaN(valor)) {
            totalSinImpuesto += valor;
        }
    });

    preciosServicios.forEach(input => {
        const valor = parseFloat(input.value);
        if (!isNaN(valor)) {
            totalSinImpuesto += valor;
        }
    });

    document.getElementById("totalantesdeimpuesto").value = totalSinImpuesto.toFixed(2);

    let impuesto = totalSinImpuesto * impuestoPorcentaje / 100;
    document.getElementById("impuesto").value = impuesto.toFixed(2);

    let totalConImpuesto = totalSinImpuesto + impuesto;
    document.getElementById("totalconimpuesto").value = totalConImpuesto.toFixed(2);

    let impuestoFinancieraReal = document.getElementById("impuestofinancierareal").value;
    let totalFinal = totalConImpuesto - impuestoFinancieraReal;
    document.getElementById("totalfinal").value = totalFinal.toFixed(2);

    calcularTotalMenosPagos();
}

//-----------------------------------------------

document.getElementById("categoria").addEventListener("change", () => {
    actualizarTablaDeInventario();
});

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

function inventarioOperateFormatter(value, row, index) {
    return [
        '<a class="sel-inventario" href="javascript:void(0)" title="Apply">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.inventarioOperateEvents = {
    "click .sel-inventario": function(e, value, row, index) {
        if (existeInventarioEnFila(row.INVENTARIOID))
        {
            return;
        }

        filaDetalleActual.getElementsByTagName("input")[0].value = row.CODIGOINVENTARIO;
        filaDetalleActual.getElementsByTagName("input")[1].value = row.INVENTARIOID;
        filaDetalleActual.parentNode.cells[1].getElementsByTagName("input")[0].value = row.MARCA;
        filaDetalleActual.parentNode.cells[2].getElementsByTagName("input")[0].value = row.MODELO;
        filaDetalleActual.parentNode.cells[3].getElementsByTagName("input")[0].value = row.MSRP;
        filaDetalleActual.parentNode.cells[4].getElementsByTagName("input")[0].value = row.CATEGORIA + " - " + row.DESCRIPCION;
        filaDetalleActual.parentNode.cells[5].getElementsByTagName("input")[0].value = row.TIPODEGARANTIA;
        filaDetalleActual.parentNode.cells[5].getElementsByTagName("input")[1].value = row.TIPODEGARANTIAID;
        filaDetalleActual.parentNode.cells[6].getElementsByTagName("input")[0].value = row.MSRP;
        
        eliminarUltimaFilaVaciaInventario();
        agregarFilaInventario();

        calcularTotales();

        modalSeleccionarInventario.hide();
    }
}

function existeInventarioEnFila(invId)
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

function inventarioCustomParams(p)
{
    return {
        sid: $("#sucursalid").val(),
        cid: $("#categoria").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

function eliminarUltimaFilaVaciaInventario()
{
    let inputs = document.querySelectorAll('input[id="marca[]"]');
    if (inputs.length == 0) return;

    let ultimoInput = inputs[inputs.length - 1];

    if (ultimoInput.value === "") {
        eliminarFilaDetalle(ultimoInput);
    }
}

function buscarItemInventario(input)
{
    if(input.value.length == 9)
    {
        let datos = new FormData();
        datos.append("codigo", input.value);
    
        fetch(
            "./mods/facturacion/facturacion/procs/buscariteminventario.php",
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
                if (validarItemInventario(data))
                {
                    filaDetalleActual.cells[0].getElementsByTagName("input")[1].value = data.inventarioid;
                    filaDetalleActual.cells[1].getElementsByTagName("input")[0].value = data.marca;
                    filaDetalleActual.cells[2].getElementsByTagName("input")[0].value = data.modelo;
                    filaDetalleActual.cells[3].getElementsByTagName("input")[0].value = data.msrp;
                    filaDetalleActual.cells[4].getElementsByTagName("input")[0].value = data.categoria + " - " + data.descripcion;
                    filaDetalleActual.cells[5].getElementsByTagName("input")[0].value = data.tipodegarantia;
                    filaDetalleActual.cells[5].getElementsByTagName("input")[1].value = data.tipodegarantiaid;
                    filaDetalleActual.cells[6].getElementsByTagName("input")[0].value = data.msrp;

                    eliminarUltimaFilaVaciaInventario();
                    agregarFilaInventario();
                }
                else
                {
                    let precio = 0;
                    filaDetalleActual.cells[0].getElementsByTagName("input")[1].value = "";
                    filaDetalleActual.cells[1].getElementsByTagName("input")[0].value = "";
                    filaDetalleActual.cells[2].getElementsByTagName("input")[0].value = "";
                    filaDetalleActual.cells[3].getElementsByTagName("input")[0].value = "";
                    filaDetalleActual.cells[4].getElementsByTagName("input")[0].value = "";
                    filaDetalleActual.cells[5].getElementsByTagName("input")[0].value = "";
                    filaDetalleActual.cells[5].getElementsByTagName("input")[1].value = "";
                    filaDetalleActual.cells[6].getElementsByTagName("input")[0].value = precio.toFixed(2);

                    input.value = "";
                }
            }

        })
        .catch(error => console.warn(error)); 
    }
    else
    {
        let precio = 0;
        filaDetalleActual = input.parentNode.parentNode.parentNode;
        filaDetalleActual.cells[0].getElementsByTagName("input")[1].value = "";
        filaDetalleActual.cells[1].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[2].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[3].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[4].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[5].getElementsByTagName("input")[0].value = "";
        filaDetalleActual.cells[5].getElementsByTagName("input")[1].value = "";
        filaDetalleActual.cells[6].getElementsByTagName("input")[0].value = precio.toFixed(2);
    }

    calcularTotales();

}

function validarItemInventario(data)
{
    // Ver que el ítem se encuentre en la sucursal seleccionada
    if (data.sucursalid != document.getElementById("sucursalid").value)
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
    if (existeInventarioEnFila(data.inventarioid))
    {
        return false;
    }

    return true;
}

//-----------------------------------------------

function agregarFilaServicio()
{
    if (document.getElementById('sucursal').value == "")
    {
        document.getElementById("textodeerror").innerHTML = "You must select a store.";
        toastError.show();
        return;
    }

    let tabla = document.getElementById("tablaServiciosOtrosProductos").getElementsByTagName('tbody')[0];
    let newRow = tabla.insertRow(tabla.rows.length);

    let celda1 = newRow.insertCell(0);
    let celda2 = newRow.insertCell(1);
    let celda3 = newRow.insertCell(2);
    let celda4 = newRow.insertCell(3);
    let celda5 = newRow.insertCell(4);
    let celda6 = newRow.insertCell(5);

    celda1.innerHTML = `<div class="input-group input-group-sm">
                            <input type="text" id="servicio[]" name="servicio[]" class="form-control form-control-sm" maxlength="5" oninput="buscarItemServicio(this)" required>
                            <button class="btn btn-outline-secondary" type="button" id="btnservicio[]" onclick="seleccionarServicio(this)"><i class="bi bi-search"></i></button>
                            <input type="hidden" id="servicioid[]" name="servicioid[]" value="">
                        </div>`;
    celda2.innerHTML = `<input type="text" id="servmarca[]" name="servmarca[]" class="form-control form-control-sm" value="" readonly>`;
    celda3.innerHTML = `<input type="text" id="servmodelo[]" name="servmodelo[]" class="form-control form-control-sm" readonly>`;
    celda4.innerHTML = `<input type="text" id="servdescripcion[]" name="servdescripcion[]" class="form-control form-control-sm" value="" readonly>`;
    celda5.innerHTML = `<input type="number" id="servprecio[]" name="servprecio[]" class="form-control form-control-sm text-end" value="0.00" min="0.01" max="99999.99" step="0.01" oninput="calcularTotales()" required>`;
    celda6.innerHTML = `<input type="hidden" id="servdetalleid[]" name="servdetalleid[]" value="">
                        <button class="btn btn-sm btn-outline-danger" type="button" onclick="eliminarFilaServicio(this)" title="Delete"><i class="bi bi-trash"></i></button>`;
    
    setTimeout(function() {
        celda1.getElementsByTagName("input")[0].focus();
    }, 0);

    // La sucursal no puede ser cambiada si ya hay al menos un ítem de servicio
    document.getElementById('sucursal').style.pointerEvents = 'none';
    document.getElementById('sucursal').style.backgroundColor = '#e9ecef';
}

function eliminarFilaServicio(boton)
{
    let fila = boton.parentNode.parentNode;

    servid = fila.cells[5].getElementsByTagName("input")[0].value;
    if (servid != "")
    {
        filasServiciosEliminadas.push(servid);
    }
    
    fila.parentNode.removeChild(fila);

    calcularTotales();

    // Evaluar si ya no hay filas detalle y servicio para habilitar selección de sucursal
    evaluarHabilitarSucursal();
}

//-----------------------------------------------

document.getElementById("marca").addEventListener("change", () => {
    actualizarTablaDeServicios();
});

function actualizarTablaDeServicios()
{
    $("#tableservicios").bootstrapTable("refresh");
}

function seleccionarServicio(boton)
{
    filaServiciosActual = boton.parentNode.parentNode;
    document.getElementById("marca").value = "";
    let searchInputs = document.querySelectorAll('input[type="search"]');
    searchInputs.forEach(function(input) {
        input.value = '';
    });

    $('#tableservicios').bootstrapTable('resetSearch');
    actualizarTablaDeServicios();
    modalSeleccionarServicio.show();
}

function serviciosOperateFormatter(value, row, index) {
    return [
        '<a class="sel-servicio" href="javascript:void(0)" title="Apply">',
        '<i class="bi bi-red bi-check-circle"></i>',
        '</a>'
    ].join('');
}

window.serviciosOperateEvents = {
    "click .sel-servicio": function(e, value, row, index) {
        if (existeServicioEnFila(row.OTROSERVICIOPRODUCTOID))
        {
            return;
        }

        let precio = 0.00;
        filaServiciosActual.getElementsByTagName("input")[0].value = row.CODIGO;
        filaServiciosActual.getElementsByTagName("input")[1].value = row.OTROSERVICIOPRODUCTOID;
        filaServiciosActual.parentNode.cells[1].getElementsByTagName("input")[0].value = row.MARCA;
        filaServiciosActual.parentNode.cells[2].getElementsByTagName("input")[0].value = row.MODELO;
        filaServiciosActual.parentNode.cells[3].getElementsByTagName("input")[0].value = row.DESCRIPCION;
        filaServiciosActual.parentNode.cells[4].getElementsByTagName("input")[0].value = precio.toFixed(2);
        
        eliminarUltimaFilaVaciaServicios();
        agregarFilaServicio();

        calcularTotales();

        modalSeleccionarServicio.hide();
    }
}

function serviciosCustomParams(p)
{
    return {
        mid: $("#marca").val(),
        offset: p.offset,
        limit: p.limit,
        search: p.search
    };
}

function existeServicioEnFila(servId)
{
    let inputs = document.querySelectorAll('input[id="servicioid[]"]');
    let yaExiste = Array.from(inputs).some(input => input.value == servId);
    if (yaExiste)
    {
        document.getElementById("textodeerror").innerHTML = "The item has already been selected.";
        toastError.show();
        return true;
    }

    return false;
}

function eliminarUltimaFilaVaciaServicios()
{
    let inputs = document.querySelectorAll('input[id="servdescripcion[]"]');
    if (inputs.length == 0) return;

    let ultimoInput = inputs[inputs.length - 1];

    if (ultimoInput.value === "") {
        eliminarFilaServicio(ultimoInput);
    }
}

function buscarItemServicio(input)
{
    let precio = 0;

    if(input.value.length == 5)
    {
        let datos = new FormData();
        datos.append("codigo", input.value);
    
        fetch(
            "./mods/facturacion/facturacion/procs/buscaritemservicio.php",
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
                if (validarItemServicio(data))
                {
                    filaDetalleActual.cells[0].getElementsByTagName("input")[1].value = data.servicioid;
                    filaDetalleActual.cells[1].getElementsByTagName("input")[0].value = data.marca;
                    filaDetalleActual.cells[2].getElementsByTagName("input")[0].value = data.modelo;
                    filaDetalleActual.cells[3].getElementsByTagName("input")[0].value = data.descripcion;
                    filaDetalleActual.cells[4].getElementsByTagName("input")[0].value = precio.toFixed(2);

                    eliminarUltimaFilaVaciaServicios();
                    agregarFilaServicio();
                }
                else
                {
                    filaDetalleActual.cells[0].getElementsByTagName("input")[1].value = "";
                    filaDetalleActual.cells[1].getElementsByTagName("input")[0].value = "";
                    filaDetalleActual.cells[2].getElementsByTagName("input")[0].value = "";
                    filaDetalleActual.cells[3].getElementsByTagName("input")[0].value = "";
                    filaDetalleActual.cells[4].getElementsByTagName("input")[0].value = precio.toFixed(2);

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
        filaDetalleActual.cells[4].getElementsByTagName("input")[0].value = precio.toFixed(2);
    }

    calcularTotales();
}

function validarItemServicio(data)
{
    // El ítem no tiene que existir en otra fila
    if (existeServicioEnFila(data.servicioid))
    {
        return false;
    }

    return true;
}

//-----------------------------------------------

document.getElementById('selectmonto').addEventListener('input', function () {
    const monto = document.getElementById("selectmonto").value;

    if (monto == "")
    {
        document.getElementById("selectimpuesto").value = "";
        document.getElementById("selecttotalmasimpuesto").value = "";
        
        return;
    }

    const impuestoPorcentaje = document.getElementById("nocalcularimpuesto").checked ?
        0
        :
        document.getElementById("impuestoporcentaje").value;
    const impuesto = (parseFloat(monto) * parseFloat(impuestoPorcentaje) / 100).toFixed(2);
    const filaTotal = (parseFloat(monto) + parseFloat(impuesto)).toFixed(2);
    
    document.getElementById("selectimpuesto").value = impuesto;
    document.getElementById("selecttotalmasimpuesto").value = filaTotal;
});

function agregarFilaPago()
{
    if (document.getElementById('sucursal').value == "")
    {
        document.getElementById("textodeerror").innerHTML = "You must select a store.";
        toastError.show();
        return;
    }

    if (!validarFilaPago())
        return;

    const tipoDePagoId = document.getElementById("selecttipodepago").value;
    const selectTipoDePago = document.getElementById("selecttipodepago");
    const tipoDePago = selectTipoDePago.options[selectTipoDePago.selectedIndex].text;
    const financieraId = document.getElementById("selectfinanciera").value;
    const selectFinanciera = document.getElementById("selectfinanciera");
    let financiera = selectFinanciera.options[selectFinanciera.selectedIndex].text;
    financiera = financieraId == -1 ? "" : financiera;
    const contrato = document.getElementById("selectcontrato").value;
    const recibocheque = document.getElementById("selectrecibocheque").value;

    // Cálculo desde monto con impuesto
    // const montoMasImpuesto = document.getElementById("selectmonto").value;
    // const impuestoPorcentaje = document.getElementById("nocalcularimpuesto").checked ?
    //     0
    //     :
    //     document.getElementById("impuestoporcentaje").value;
    // const monto = montoMasImpuesto / (1 + impuestoPorcentaje / 100);
    // const impuesto = parseFloat(monto.toFixed(2)) * parseFloat(impuestoPorcentaje) / 100;
    // const filaTotal = parseFloat(montoMasImpuesto);

    // Cálculo desde monto sin impuesto
    const monto = document.getElementById("selectmonto").value;
    const impuestoPorcentaje = document.getElementById("nocalcularimpuesto").checked ?
        0
        :
        document.getElementById("impuestoporcentaje").value;
    const impuesto = (parseFloat(monto) * parseFloat(impuestoPorcentaje) / 100).toFixed(3);
    const filaTotal = (parseFloat(monto) + parseFloat(impuesto)).toFixed(3);

    // Si es tipo de pago FINANCE, actualizar el monto de impuestofinanciera
    if (tipoDePagoId == 1)
    {
        let impuestoFinanciera = document.getElementById("impuestofinanciera");
        let impuestoFinancieraReal = document.getElementById("impuestofinancierareal");
        impuestoFinancieraReal.value = parseFloat((parseFloat(impuestoFinancieraReal.value) + parseFloat(impuesto))).toFixed(3);
        impuestoFinanciera.value = parseFloat(impuestoFinancieraReal.value).toFixed(2);

        calcularTotales();
    }

    let tabla = document.getElementById("tablaPagos").getElementsByTagName('tbody')[0];
    let newRow = tabla.insertRow(tabla.rows.length);

    let celda1 = newRow.insertCell(0);
    let celda2 = newRow.insertCell(1);
    let celda3 = newRow.insertCell(2);
    let celda4 = newRow.insertCell(3);
    let celda5 = newRow.insertCell(4);
    let celda6 = newRow.insertCell(5);
    let celda7 = newRow.insertCell(6);
    let celda8 = newRow.insertCell(7);

    celda1.innerHTML = `<input type="text" id="tipopago[]" name="tipopago[]" class="form-control form-control-sm" value="${tipoDePago}" readonly>
                        <input type="hidden" id="tipopagoid[]" name="tipopagoid[]" value="${tipoDePagoId}">`;
    celda2.innerHTML = `<input type="text" id="financiera[]" name="financiera[]" class="form-control form-control-sm" value="${financiera}" readonly>
                        <input type="hidden" id="financieraid[]" name="financieraid[]" value="${financieraId}">`;
    celda3.innerHTML = `<input type="text" id="contrato[]" name="contrato[]" class="form-control form-control-sm" value="${contrato}" readonly>`;
    celda4.innerHTML = `<input type="text" id="recibocheque[]" name="recibocheque[]" class="form-control form-control-sm" value="${recibocheque}" readonly>`;
    celda5.innerHTML = `<input type="text" id="pagomonto[]" name="pagomonto[]" class="form-control form-control-sm text-end" value="${parseFloat(monto).toFixed(2)}" readonly>`;
    celda6.innerHTML = `<input type="text" id="pagoimpuesto[]" name="pagoimpuesto[]" class="form-control form-control-sm text-end" value="${parseFloat(impuesto).toFixed(2)}" readonly>
                        <input type="hidden" id="pagoimpuestoreal[]" name="pagoimpuestoreal[]" class="form-control form-control-sm text-end" value="${parseFloat(impuesto).toFixed(3)}">`;
    celda7.innerHTML = `<input type="text" id="pagofilatotal[]" name="pagofilatotal[]" class="form-control form-control-sm text-end" value="${parseFloat(filaTotal).toFixed(2)}" readonly>
                        <input type="hidden" id="pagofilatotalreal[]" name="pagofilatotalreal[]" class="form-control form-control-sm text-end" value="${parseFloat(filaTotal).toFixed(3)}">`;
    celda8.innerHTML = `<input type="hidden" id="facpagoid[]" name="facpagoid[]" value="">
                        <button class="btn btn-sm btn-outline-danger" type="button" onclick="eliminarFilaPago(this)" title="Delete"><i class="bi bi-trash"></i></button>`;
    
    setTimeout(function() {
        document.getElementById("selecttipodepago").value = "";
        document.getElementById("selectfinanciera").value = -1;
        document.getElementById("selectcontrato").value = "";
        document.getElementById("selectrecibocheque").value = "";
        document.getElementById("selectmonto").value = "";
        document.getElementById("selectimpuesto").value = "";
        document.getElementById("selecttotalmasimpuesto").value = "";

        calcularTotalDePagos();
    }, 0);

    // La sucursal no puede ser cambiada si ya hay al menos un ítem de servicio
    document.getElementById('sucursal').style.pointerEvents = 'none';
    document.getElementById('sucursal').style.backgroundColor = '#e9ecef';
}

function eliminarFilaPago(boton)
{
    let fila = boton.parentNode.parentNode;

    facpagoid = fila.cells[7].getElementsByTagName("input")[0].value;
    if (facpagoid != "")
    {
        filasPagoEliminadas.push(facpagoid);
    }
    
    fila.parentNode.removeChild(fila);

    calcularTotalDePagos();

    // Si es tipo de pago FINANCE, actualizar el monto de impuestofinanciera
    let tipoDePagoId = fila.cells[0].getElementsByTagName("input")[1].value;
    if (tipoDePagoId == 1)
    {
        let impuestoFinanciera = document.getElementById("impuestofinanciera");
        let impuestoFinancieraReal = document.getElementById("impuestofinancierareal");
        let impuestoADescontar = fila.cells[5].getElementsByTagName("input")[1].value;

        impuestoFinancieraReal.value = parseFloat(parseFloat(impuestoFinancieraReal.value) - parseFloat(impuestoADescontar)).toFixed(3);
        impuestoFinanciera.value = parseFloat(impuestoFinancieraReal.value).toFixed(2);

        calcularTotales();
    }

    // Evaluar si no hay filas detalle, servicios y pagos para habilitar la sucursal
    evaluarHabilitarSucursal();
}

function validarFilaPago()
{
    const tipoDePago = document.getElementById("selecttipodepago").value;
    if (tipoDePago == "")
    {
        document.getElementById("textodeerror").innerHTML = "You must select a form of payment.";
        toastError.show();
        return false;
    }

    let montoDePago = document.getElementById("selectmonto").value;
    montoDePago = montoDePago == "" ? 0 : montoDePago;
    if (montoDePago < 0.01 || montoDePago > 99999.99)
    {
        document.getElementById("textodeerror").innerHTML = "Amount must be a number between 0.01 and 99999.99.";
        toastError.show();
        return false;        
    }
    return true;
}

function calcularTotalDePagos()
{
    const montosTotal = document.querySelectorAll('input[id="pagofilatotalreal[]"]');
    let total = 0;
    montosTotal.forEach(input => {
        const valor = parseFloat(input.value);
        if (!isNaN(valor)) {
            total += valor;
        }
    });

    const montosImpuesto = document.querySelectorAll('input[id="pagoimpuestoreal[]"]');
    let totalImpuestos = 0;
    montosImpuesto.forEach(input => {
        const valor = parseFloat(input.value);
        if (!isNaN(valor)) {
            totalImpuestos += valor;
        }
    });

    const montosSinImpuesto = document.querySelectorAll('input[id="pagomonto[]"]');
    let totalSinImpuestos = 0;
    montosSinImpuesto.forEach(input => {
        const valor = parseFloat(input.value);
        if (!isNaN(valor)) {
            totalSinImpuestos += valor;
        }
    });

    document.getElementById("totalpagos").value = total.toFixed(2);
    document.getElementById("totalpagosimpuesto").value = totalImpuestos.toFixed(2);
    document.getElementById("totalpagosmonto").value = totalSinImpuestos.toFixed(2);

    calcularTotalMenosPagos();
}

function calcularTotalMenosPagos()
{
    document.getElementById("totalmenospago").value =
        (parseFloat(document.getElementById("totalantesdeimpuesto").value) -
        parseFloat(document.getElementById("totalpagosmonto").value)).toFixed(2);
}

//-----------------------------------------------

document.getElementById("btnguardar").addEventListener("click", (event) => {
    eliminarUltimaFilaVaciaInventario();
    eliminarUltimaFilaVaciaServicios();
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
        document.getElementById("textodeerror").innerHTML = "There are rows with empty data.";
        toastError.show();
        return;
    }

    aplicarTrimAElementos();

    if (event.target.checkValidity())
    {
        let datos = new FormData(event.target);
        let jsonFilasDetalleEliminadas = JSON.stringify(filasDetalleEliminadas);
        datos.append("filasdetalleeliminadas", jsonFilasDetalleEliminadas);
        let jsonFilasServicioEliminadas = JSON.stringify(filasServiciosEliminadas);
        datos.append("filasservicioeliminadas", jsonFilasServicioEliminadas);
        let jsonFilasPagosEliminadas = JSON.stringify(filasPagoEliminadas);
        datos.append("filaspagoeliminadas", jsonFilasPagosEliminadas);

        guardar(datos);
    }
});

function guardar(datos)
{
    document.getElementById("btnguardar").setAttribute("disabled", "true");
    document.getElementById("btnguardarspinner").classList.remove("visually-hidden");

    fetch(
        "./mods/facturacion/facturacion/procs/guardarfactura.php",
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
            window.location.href="?mod=facturacion&opc=facturacion&subopc=verfactura&fid=" + data.fid;
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

function validarExistenciaDeFilas()
{
    let inputsDetalle = document.querySelectorAll('input[id^="detalleid"]');
    let inputsServicios = document.querySelectorAll('input[id^="servdetalleid"]');

    return inputsDetalle.length + inputsServicios.length > 0;
}

function verificarInputsDeFilas() {
    let inputsDetalle = document.querySelectorAll('input[id^="inventarioid"]');
    let inputsServicios = document.querySelectorAll('input[id^="servicioid"]');
    let algunoVacio = false;

    inputsDetalle.forEach(function(input) {
        if (input.value.trim() === '') {
            algunoVacio = true;
        }
    });

    inputsServicios.forEach(function(input) {
        if (input.value.trim() === '') {
            algunoVacio = true;
        }
    });

    if (algunoVacio) {
        return false;
    }

    return true;
}

function aplicarTrimAElementos()
{
    let clienteDireccion = document.getElementById("clientedireccion");
    let clienteDireccionComplemento = document.getElementById("clientedireccioncomplemento");
    let clienteCodigoPostal = document.getElementById("clientecodigopostal");
    let clienteTelefono = document.getElementById("clientetelefono");
    let clienteCorreo = document.getElementById("clientecorreo");
    let personaDeReferencia = document.getElementById("personadereferencia");
    let notas = document.getElementById("notas");
    
    clienteDireccion.value = clienteDireccion.value.trim();
    clienteDireccionComplemento.value = clienteDireccionComplemento.value.trim();
    clienteCodigoPostal.value = clienteCodigoPostal.value.trim();
    clienteTelefono.value = clienteTelefono.value.trim();
    clienteCorreo.value = clienteCorreo.value.trim();
    personaDeReferencia.value = personaDeReferencia.value.trim();
    notas.value = notas.value.trim();
}

//-----------------------------------------------

// Al cargar se pone en readonly la sucursal y luego se evalúa si se activa
document.getElementById('sucursal').style.pointerEvents = 'none';
document.getElementById('sucursal').style.backgroundColor = '#e9ecef';
evaluarHabilitarSucursal();
// Se cargan los totales de los pagos
calcularTotalDePagos();

//-----------------------------------------------
