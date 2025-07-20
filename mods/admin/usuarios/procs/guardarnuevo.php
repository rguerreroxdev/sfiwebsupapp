<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Usuario.php");
require_once("../../../../inc/class/SucursalesXUsuario.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;
$resultado["mensaje"] = "";

//-----------------------------------------------

$loggedUsuarioId = $_POST["loggeduid"];
$nombre = $_POST["nombre"];
$usuario = $_POST["usuario"];
$contrasena = $_POST["password"];
$activo = isset($_POST["activo"]) ? 1 : 0;
$cambiarContrasena = isset($_POST["cambiarpwd"]) ? 1 : 0;
$perfilId = $_POST["perfil"];
$moduloInicialId = $_POST["moduloinicial"];

$arraySucursalXUsuarioId = isset($_POST["sucursalxusuarioid"]) ? $_POST["sucursalxusuarioid"] : [];
$arraySucursalesId = isset($_POST["sucursalid"]) ? $_POST["sucursalid"] : [];

//-----------------------------------------------

$objUsuario = new Usuario($conn);
$objSucursalesXUsuario = new SucursalesXUsuario($conn);

$md5Contrasena = md5($contrasena);

$fechaModificacion = date("Ymd H:i:s");
$rsAgregar = $objUsuario->agregarRegistro(
    $nombre, $usuario, $md5Contrasena, $activo, $cambiarContrasena, $perfilId, $loggedUsuarioId, $moduloInicialId
);

if (!$rsAgregar)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objUsuario->mensajeError;
}
else
{
    $resultado["id"] = $objUsuario->usuarioId;
}

// Se realizan los INSERT de sucursales
if ($rsAgregar)
{
    $usuarioId = $objUsuario->usuarioId;

    for ($i=0; $i < count($arraySucursalXUsuarioId); $i++)
    {
        if ($arraySucursalXUsuarioId[$i] == "")
        {
            $rsAgregar = $objSucursalesXUsuario->agregarRegistro(
                $usuarioId, $arraySucursalesId[$i]
            );

            if (!$rsAgregar)
            {
                $error = true;
                $resultado["error"] = 1;
                $resultado["mensaje"] = $objSucursalesXUsuario->mensajeError;

                break;
            }
        }
    }
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------