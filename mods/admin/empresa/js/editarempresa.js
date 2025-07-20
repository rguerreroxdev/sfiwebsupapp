//-----------------------------------------------

document.getElementById("nombre").focus();

document.getElementById("btncancelar").addEventListener("click", () => {
    window.location.href="?mod=admin&opc=empresa";
});

document.getElementById("btnqr").addEventListener("click", () => {
    fileInput.value = '';
    preview.style.display = 'none';

    const uploadText = document.getElementById('upload-text');
    const altUploadText = document.getElementById('alt-upload-text');
    uploadText.style.display = 'block';
    altUploadText.style.display = 'none';
    
    modalQR.show();
});

document.getElementById("btnguardarqr").addEventListener("click", () => {
    guardarQR();
});

document.getElementById("frm").addEventListener("submit", (event) => {
    event.preventDefault();

    aplicarTrimAElementos();
    if (event.target.checkValidity())
    {
        let datos = new FormData(event.target);
        guardar(datos);
    }
});

//-----------------------------------------------

// Definir elementos para mostrar mensajes
let toastMensaje = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastMensaje'));
let toastError = bootstrap.Toast.getOrCreateInstance(document.getElementById('toastError'));
let modalMensaje = new bootstrap.Modal(document.getElementById('modalMensaje'));
let modalQR = new bootstrap.Modal(document.getElementById('modalQR'));

//-----------------------------------------------

function guardar(datos)
{
    document.getElementById("btnguardar").setAttribute("disabled", "true");
    document.getElementById("btnguardarspinner").classList.remove("visually-hidden");

    fetch(
        "./mods/admin/empresa/procs/guardaredit.php",
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
            window.location.href="?mod=admin&opc=empresa";
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
    let nombre = document.getElementById("nombre");
    let direccion = document.getElementById("direccion");
    let telefono = document.getElementById("telefono");
    
    nombre.value = nombre.value.trim();
    direccion.value = direccion.value.trim();
    telefono.value = telefono.value.trim();
}

//-----------------------------------------------

const uploadArea = document.getElementById('upload-area');
const fileInput = document.getElementById('file-input');
const preview = document.getElementById('preview');

uploadArea.addEventListener('click', () => fileInput.click());

fileInput.addEventListener('change', handleFile);

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.style.backgroundColor = '#f0f0f0';
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.style.backgroundColor = 'transparent';
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.style.backgroundColor = 'transparent';
    fileInput.files = e.dataTransfer.files;
    handleFile();
});

function handleFile() {
    const tamanoMaximo = 1048576;
    const tamanoMaximoLetras = "1MB";
    const file = fileInput.files[0];
    const uploadText = document.getElementById('upload-text');
    const altUploadText = document.getElementById('alt-upload-text');

    if (file && file.type === 'image/jpeg' && file.size <= tamanoMaximo) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            uploadText.style.display = 'none'; // Ocultar el texto
            altUploadText.style.display = 'block'; // Mostrar texto alternativo
        };
        reader.readAsDataURL(file);
    } else {
        let errorDescripcion = "";

        errorDescripcion += file.type != "image/jpeg" ? "The file is not a JPG image.<br>" : "";
        errorDescripcion += errorDescripcion == "" && file.size > tamanoMaximo ? `The file is greater than ${tamanoMaximoLetras}.<br>` : "";

        document.getElementById("toasterrormsg").innerHTML = `${errorDescripcion} Please, upload a JPG image with a maximum size of ${tamanoMaximoLetras}`;
        toastError.show();

        fileInput.value = '';
        preview.style.display = 'none';
        uploadText.style.display = 'block';
        altUploadText.style.display = 'none';
    }
}

//-----------------------------------------------

function guardarQR()
{
    if (fileInput.value == '')
    {
        document.getElementById("toasterrormsg").innerHTML = "There is no image selected";
        toastError.show();
        return;
    }

    const file = fileInput.files[0];

    const formData = new FormData();
    formData.append('file', file);

    fetch('./mods/admin/empresa/procs/guardarqr.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error == 0)
        {
            document.getElementById("toasterrormsg").innerHTML = "The new QR image was uploaded";
            toastError.show();
            modalQR.hide();

            refreshImage();
        }
    })
    .catch(error => {
        console.error('Error when uploading the image:', error);
    });
}

//-----------------------------------------------

function refreshImage() {
    const img = document.getElementById('qractual');
    const timestamp = new Date().getTime(); // Crear un timestamp para evitar caché
    img.src = './imgs/QRCode.jpg?t=' + timestamp; // Agregar el timestamp como parámetro
}

//-----------------------------------------------