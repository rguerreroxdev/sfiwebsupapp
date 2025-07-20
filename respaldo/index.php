<?php
//-----------------------------------------------

define("APP_TITULO", "");

//-----------------------------------------------

// Directorio donde están los archivos .bak y .zip
$directory = '/var/www/html/sfiwebdev/bk';

//-----------------------------------------------

// Listado de respaldos de base de datos

$listaDeRespaldosBD = "";

// Verifica si el directorio existe
if (is_dir($directory)) {
    // Abre el directorio
    if ($handle = opendir($directory)) {
        // Recorre los archivos del directorio
        $listaDeRespaldosBD = "<ul>";
        while (($file = readdir($handle)) !== false) {
            // Filtra los archivos con extensión .bak
            if (pathinfo($file, PATHINFO_EXTENSION) === 'bak') {
                // Genera el enlace de descarga
                $fileWebPath =  '../bk/' . $file;
                $filePath = $directory . '/' . $file;
                $fileDate = date("d-m-Y H:i:s", filemtime($filePath));
                $listaDeRespaldosBD .= "\n<li><a href='$fileWebPath' download>$file - $fileDate</a></li>";
            }
        }

        $listaDeRespaldosBD .= "\n</ul>";
        closedir($handle);
    } else {
        $listaDeRespaldosBD = "No se pudo abrir el directorio: " . $directory;
    }
} else {
    $listaDeRespaldosBD = "El directorio no existe: " . $directory;
}

//-----------------------------------------------

// Listado de respaldos de aplicación

$listaDeRespaldosApp = "";

// Verifica si el directorio existe
if (is_dir($directory)) {
    // Abre el directorio
    if ($handle = opendir($directory)) {
        // Recorre los archivos del directorio
        $listaDeRespaldosApp = "<ul>";
        while (($file = readdir($handle)) !== false) {
            // Filtra los archivos con extensión .zip
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                // Genera el enlace de descarga
                $fileWebPath =  '../bk/' . $file;
                $filePath = $directory . '/' . $file;
                $fileDate = date("d-m-Y H:i:s", filemtime($filePath));
                $listaDeRespaldosApp .= "\n<li><a href='$fileWebPath' download>$file - $fileDate</a></li>";
            }
        }

        $listaDeRespaldosApp .= "\n</ul>";
        closedir($handle);
    } else {
        $listaDeRespaldosApp = "No se pudo abrir el directorio: " . $directory;
    }
} else {
    $listaDeRespaldosApp = "El directorio no existe: " . $directory;
}

//-----------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_TITULO ?></title>

    <link rel="stylesheet" href="../libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <link rel="stylesheet" href="../libs/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/main.css">
</head>

<body>

<div class="container py-5">
    <h5>Respaldo de base y aplicaci&oacute;n</h5>

    <div class="py-3">
        <h6>Respaldos de base de datos</h6>
        <?= $listaDeRespaldosBD ?>
        <div class="py-2">
            <button type="button" class="btn btn-sm btn-primary" id="btnbd">
                Crear respaldo de Base de datos
                <span class="spinner-border spinner-border-sm visually-hidden" id="btnbdspinner" role="status" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div class="py-3">
        <h6>Respaldos de aplicaci&oacute;n</h6>
        <?= $listaDeRespaldosApp ?>
        <div class="py-2">
            <button type="button" class="btn btn-sm btn-primary" id="btnapp">
                Crear respaldo de Aplicaci&oacute;n
                <span class="spinner-border spinner-border-sm visually-hidden" id="btnappspinner" role="status" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</div>


</body>

<script src="../libs/jquery/jquery.min.js"></script>
<script src="../libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>
<script src="./respaldo.js"></script>

</html>