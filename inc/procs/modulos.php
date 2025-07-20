<?php
//-----------------------------------------------

session_start();

require_once("../includes.inc.php");
require_once("../class/MenuDeSistema.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$usuarioId = $_POST["uId"];

//-----------------------------------------------

$listaOptions = "";

if ($conn->getExisteError())
{
    // TODO: Error
}
else
{
    $objMenu = new MenuDeSistema($conn, $usuarioId);
    $modulos = $objMenu->getModulos($usuarioId);

    foreach($modulos as $modulo)
    {
        $texto = $modulo["NOMBRE"];
        $valor = $modulo["MODULOID"];

        $isSelected = "";
        if (isset($_SESSION["moduloId"]))
        {
            $isSelected = $_SESSION["moduloId"] == $valor ? " selected" : "";
        }

        $listaOptions .= "
            <option value=\"$valor\"$isSelected>$texto</option>
        ";
    }

} // else de if ($conn->getExisteError())

//-----------------------------------------------

$resultado = array();
$resultado["listaOptions"] = $listaOptions;

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------