<?php
//-----------------------------------------------

$tipo = isset($_POST["tipo"]) ? $_POST['tipo'] : "none";

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

// Crear respaldo de base de datos

if ($tipo == "bd")
{
    $connectionInfo = array(
        "Database" => "SFIWEB",
        "UID"      => "usuariosfi",
        "PWD"      => "usuarioSfiPwd",
        "CharacterSet"           => "UTF-8",
        "TrustServerCertificate" => "yes"
    );
    $conn = sqlsrv_connect("localhost", $connectionInfo);
    sqlsrv_configure("WarningsReturnAsErrors", 0);
    
    $sentenciaSql = "BACKUP DATABASE SFIWEB TO DISK = '/var/www/html/sfiwebdev/bk/SFIWEB.bak' WITH INIT";
    $stmt = sqlsrv_query($conn, $sentenciaSql);
    
    if ($stmt === false) {
        $resultado["error"] = 1;
        $resultado["mensaje"] = sqlsrv_errors();
    }
    
    // Recorrer todos los resultados para asegurarse de que el comando finalizó:
    while (sqlsrv_next_result($stmt)) {
        // Pass
    }
}

//-----------------------------------------------

if ($tipo == "app")
{
    // Rutas de los directorios
    $sourceDir = '/var/www/html/sfiweb';
    $tempDir = '/temp';
    $backupDir = '/var/www/html/sfiwebdev/bk';

    // Nombre del archivo comprimido
    $backupFilename = 'backup_app.zip';
    $tempBackupPath = $tempDir . '/' . $backupFilename;
    $finalBackupPath = $backupDir . '/' . $backupFilename;

    // Crear el archivo ZIP
    $zip = new ZipArchive();
    if ($zip->open($tempBackupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $filePath = $file->getPathname();
            $relativePath = substr($filePath, strlen($sourceDir) + 1);
            $zip->addFile($filePath, $relativePath);
        }

        $zip->close();

        // Mover el archivo a la carpeta final
        if (!rename($tempBackupPath, $finalBackupPath)) {
            $resultado["error"] = 1;
            $resultado["mensaje"] = "Error al mover el archivo a $backupDir.";
            exit;
        }
    } else {
        $resultado["error"] = 1;
        $resultado["mensaje"] = "Error al crear el archivo ZIP.";
    }
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------