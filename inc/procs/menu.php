<?php
//-----------------------------------------------

require_once("../includes.inc.php");
require_once("../class/MenuDeSistema.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$moduloId = $_POST["mId"];
$usuarioId = $_POST["uId"];

//-----------------------------------------------

$menu = "";

if ($conn->getExisteError())
{
    // TODO: Error
}
else
{
    $objMenu = new MenuDeSistema($conn, $usuarioId);
    $menu = $objMenu->getMenuDeSistema($moduloId);
} // else de if ($conn->getExisteError())

//-----------------------------------------------

$resultado = array();
$resultado["menu"] = $menu;

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------