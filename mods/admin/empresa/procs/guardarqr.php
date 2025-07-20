<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0)
    {
        $file = $_FILES['file'];

        // Validar el tipo de archivo y el tamaño
        if ($file['type'] == 'image/jpeg' && $file['size'] <= 1048576)
        {
            $uploadDir = '../../../../imgs/';
            $uploadFile = $uploadDir . 'QRCode.jpg';

            // Mover el archivo subido al directorio de destino
            if (move_uploaded_file($file['tmp_name'], $uploadFile))
            {
                $resultado["error"] = 0;
            }
            else
            {
                $resultado["error"] = 1;
            }
        }
        else
        {
            $resultado["error"] = 1;
        }
    }
    else
    {
        $resultado["error"] = 1;
    }
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------