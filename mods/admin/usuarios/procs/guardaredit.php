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

$usuarioId = $_POST["uid"];
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
$arrayFilasEliminadas = json_decode($_POST["filaseliminadas"], true);

//-----------------------------------------------

$objUsuario = new Usuario($conn);
$objSucursalesXUsuario = new SucursalesXUsuario($conn);

$fechaModificacion = date("Ymd H:i:s");
$rsEdicion = $objUsuario->editarRegistro(
    $usuarioId,
    [
        "NOMBRECOMPLETO", $nombre,
        "USUARIO", $usuario,
        "ACTIVO", $activo,
        "CAMBIARCONTRASENA", $cambiarContrasena,
        "PERFILID", $perfilId,
        "MODULODEINICIOID", $moduloInicialId,
        "USUARIOIDMODIFICACION", $loggedUsuarioId,
        "FECHAMODIFICACION", $fechaModificacion
    ]
);

if (!$rsEdicion)
{
    $resultado["error"] = 1;
    $resultado["mensaje"] = $objUsuario->mensajeError;
}
else if ($contrasena != "")
{
    $md5Contrasena = md5($contrasena);
    $rsEdicion = $objUsuario->editarRegistro(
        $usuarioId,
        [
            "CONTRASENA", $md5Contrasena,
        ]
    );
}

// Se realizan los DELETE primero y después los INSERT de sucursales
if ($rsEdicion)
{
    $error = false;

    // DELETE
    foreach($arrayFilasEliminadas as $id)
    {
        $rsEliminar = $objSucursalesXUsuario->eliminarRegistro($id);

        if (!$rsEliminar)
        {
            $error = true;
            $resultado["error"] = 1;
            $resultado["mensaje"] = $objSucursalesXUsuario->mensajeError;

            break;
        }
    }

    // INSERT
    if (!$error)
    {
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
    } // if(!$error) -> para insertar sucursales
}

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------