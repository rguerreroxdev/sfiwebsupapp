//-----------------------------------------------

// Gestión de cierre de sesión

let linkLogout = document.getElementById("linkLogout");

if (linkLogout)
{
    linkLogout.addEventListener("click", logout);
}

function logout(event)
{
    event.preventDefault();

    fetch("./mods/login/procs/logout.php")
    .then(response => window.location.replace("/"))
    .catch(error => console.warn(error));
}

//-----------------------------------------------