//-----------------------------------------------

let modalSeleccionarProveedor = new bootstrap.Modal(document.getElementById('modalSeleccionarProveedor'));

//-----------------------------------------------

document.getElementById("codigoproveedor").focus();

//-----------------------------------------------

document.getElementById("btnproveedor").addEventListener("click", () => {
    modalSeleccionarProveedor.show();
});

document.getElementById("btncancelar").addEventListener("click", () => {
    window.location.href="?mod=inventario&opc=tiposdestock";
});

const inputporcentaje = document.getElementById('porcentaje');
// Limitar a un máximo de 18 caracteres
inputporcentaje.addEventListener('input', function() {
    if (inputporcentaje.value.length > 6) {
        inputporcentaje.value = inputporcentaje.value.slice(0, 6);
    }
});

document.getElementById("frm").addEventListener("submit", (event) => {
    event.preventDefault();

    aplicarTrimAElementos();
    if (event.target.checkValidity())
    {
        let datos = new FormData(event.target);
        guardar(datos);
    }
    else
    {
        document.getElementById("textodeerror").innerHTML = "Supplier is a required data";
        toastError.show();
    }
});

//-----------------------------------------------

// Definir elementos para mostrar mensajes
let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));

let toastErrorElement = document.getElementById('toastError');
let toastError = bootstrap.Toast.getOrCreateInstance(toastErrorElement);

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

        modalSeleccionarProveedor.hide();
        document.getElementById("nombrecorto").focus();
    }
}

//-----------------------------------------------

function guardar(datos)
{
    document.getElementById("btnguardar").setAttribute("disabled", "true");
    document.getElementById("btnguardarspinner").classList.remove("visually-hidden");

    fetch(
        "./mods/inventario/tiposdestock/procs/guardarnuevo.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => finalizar(data))
    .catch(error => console.warn(error));
}

//-----------------------------------------------

function finalizar(data)
{
    document.getElementById("btnguardarspinner").classList.add("visually-hidden");

    if (data.error == 0)
    {
        toastMensaje.show();
        setTimeout(() => {
            window.location.href="?mod=inventario&opc=tiposdestock&subopc=vertipodestock&tsid=" + data.id;
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
    let codigoProveedor = document.getElementById("codigoproveedor");
    let nombreCorto = document.getElementById("nombrecorto");
    let proveedor = document.getElementById("proveedor");
    let proveedorId = document.getElementById("proveedorid");
    
    codigoProveedor.value = codigoProveedor.value.trim();
    nombreCorto.value = nombreCorto.value.trim();

    if (proveedor.value == "" || proveedorId.value == "")
    {
        codigoProveedor.value = "";
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
            "./mods/inventario/tiposdestock/procs/buscarproveedor.php",
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

function rowIndexFormatter(value, row, index) {
    return index + 1;
}

//-----------------------------------------------