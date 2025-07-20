<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Perfiles.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$moduloId = isset($_POST["mid"]) && trim($_POST["mid"]) != "" ? $_POST["mid"] : "";

//-----------------------------------------------

$objPerfiles = new Perfiles($conn);

$listaDeOpciones = $objPerfiles->getMenusPrincipalesParaCombo($moduloId);

$listaDeMenuOptions = "";
foreach ($listaDeOpciones as $opcion)
{
    $texto = $opcion["DESCRIPCION"];
    $valor = $opcion["CODIGO"];
    $listaDeMenuOptions .= "
        <option value=\"$valor\">$texto</option>
    ";
}

$resultado = $listaDeMenuOptions;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------