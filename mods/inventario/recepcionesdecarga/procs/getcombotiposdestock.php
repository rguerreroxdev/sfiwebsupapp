<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/TiposDeStock.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$resultado = array();

//-----------------------------------------------

$proveedorId = isset($_POST["pid"]) && trim($_POST["pid"]) != "" ? $_POST["pid"] : -1;

//-----------------------------------------------

$objTiposDeStock = new TiposDeStock($conn);

$textoParaCombo = $proveedorId != -1 ? "SELECT" : "SELECT PROVIDER";
$listaDeTiposDeStock = $objTiposDeStock->getListaParaCombo($proveedorId, $textoParaCombo);

$listaDeTiposDeStockOptions = "";
foreach ($listaDeTiposDeStock as $tipoDeStock)
{
    $texto = $tipoDeStock["NOMBRECORTO"];
    $valor = $tipoDeStock["TIPODESTOCKID"] == -1 ? "" : $tipoDeStock["TIPODESTOCKID"];
    
    $listaDeTiposDeStockOptions .= "
        <option value=\"$valor\">$texto</option>
    ";
}

$resultado["opciones"] = $listaDeTiposDeStockOptions;

//-----------------------------------------------

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------