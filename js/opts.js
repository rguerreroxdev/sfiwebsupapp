//-----------------------------------------------

const selectModulos = document.getElementById("selectModulos");

selectModulos.addEventListener("change", (event) => {
    cargarMenuDeModulo();
});

//-----------------------------------------------

function cargarMenuDeModulo()
{
    guardarModuloEnSesion();

    let datos = new FormData();
    datos.append("mId", selectModulos.value);
    datos.append("uId", document.getElementById("uId").value);

    fetch(
        "./inc/procs/menu.php",
        {
            method: "POST",
            body: datos
        }
    )
    .then(response => response.json())
    .then(data => mostrarMenu(data))
    .catch(error => console.warn(error));
}

//-----------------------------------------------

function mostrarMenu(data)
{
    const menuPrincipal = document.getElementById("menuPrincipal");
    menuPrincipal.innerHTML = data.menu;
}

//-----------------------------------------------

function mostrarModulos(data)
{
    selectModulos.innerHTML = data.listaOptions;
    cargarMenuDeModulo();

    guardarModuloEnSesion();
}

//-----------------------------------------------

async function guardarModuloEnSesion()
{
    let datos = new FormData();
    datos.append("mId", selectModulos.value);

    await fetch(
        "./inc/procs/moduloseleccionado.php",
        {
            method: "POST",
            body: datos
        }
    );
}

//-----------------------------------------------

function mantenerSesionActiva() {
    setInterval(function() {
        fetch('./inc/procs/renovarsesion.php')
        .catch(error => {
            console.error('Error al renovar la sesión:', error);
        });
    }, 5 * 60 * 1000); // cada 5 minutos
}

//-----------------------------------------------

mantenerSesionActiva();

//-----------------------------------------------
