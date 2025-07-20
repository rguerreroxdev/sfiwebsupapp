<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Usuario.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$usuarioId = $_POST["uid"];
$contrasenaActual = $_POST["currentpassword"];
$contrasena = $_POST["newpassword"];

$md5ContrasenaActual = md5($contrasenaActual);
$md5Contrasena = md5($contrasena);

//-----------------------------------------------

$objUsuario = new Usuario($conn);

// Validar que la contraseña actual sea correcta
$rs = $objUsuario->getWithFilters("USUARIOID=$usuarioId AND CONTRASENA='$md5ContrasenaActual'");
if (count($rs) > 0)
{
    $rsEdicion = $objUsuario->editarRegistro(
        $usuarioId,
        [
            "CONTRASENA", $md5Contrasena,
            "CAMBIARCONTRASENA", 0
        ]
    );
}
else
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = "The current password is incorrect.";
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------