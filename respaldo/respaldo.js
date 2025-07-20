//-----------------------------------------------

const btnBD = document.getElementById("btnbd");
const btnApp = document.getElementById("btnapp");

const bdSpinner = document.getElementById("btnbdspinner");
const appSpinner = document.getElementById("btnappspinner");

//-----------------------------------------------

btnBD.addEventListener("click", function() {
    btnBD.setAttribute("disabled", true);
    btnApp.setAttribute("disabled", true);
    bdSpinner.classList.remove("visually-hidden");

    const formData = new FormData();
    formData.append("tipo", "bd");

    fetch(
        "crearrespaldo.php",
        {
            method: "POST",
            body: formData
        }
    )
    .then(response => response.json())
    .then(data => location.reload())
    .catch(error => console.warn(error));
});

//-----------------------------------------------

btnApp.addEventListener("click", function() {
    btnBD.setAttribute("disabled", true);
    btnApp.setAttribute("disabled", true);
    appSpinner.classList.remove("visually-hidden");

    const formData = new FormData();
    formData.append("tipo", "app");

    fetch(
        "crearrespaldo.php",
        {
            method: "POST",
            body: formData
        }
    )
    .then(response => response.json())
    .then(data => location.reload())
    .catch(error => console.warn(error));
});


//-----------------------------------------------