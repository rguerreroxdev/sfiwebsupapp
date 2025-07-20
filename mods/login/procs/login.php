<?php
//-----------------------------------------------

session_start();

require_once("../../../inc/includes.inc.php");
require_once("../../../inc/class/Usuario.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 1;
$resultado["mensaje"] = "Incorrect user or password.";

//-----------------------------------------------

if ($conn->getExisteError())
{
    $resultado["mensaje"] = $conn->getMensajeError();
}
else
{
    //-------------------------------------------

    if($_POST && isset($_POST["usuario"]))
    {
        $usuario = $_POST["usuario"];
        $contrasena = $_POST["contrasena"];
        
        $objUsuario = new Usuario($conn);
        if($objUsuario->login($usuario, $contrasena))
        {
            $resultado["error"] = 0;
            $resultado["mensaje"] = "";
        }
    }

    //-------------------------------------------
} // else de if ($conn->getExisteError())

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------