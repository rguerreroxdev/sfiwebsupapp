<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/Salidas.php");
require_once("../../../../inc/class/SalidasDetalle.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$salidaId = isset($_GET["sid"]) && trim($_GET["sid"]) != "" ? $_GET["sid"] : -1;
$salidaId = is_numeric($salidaId) ? $salidaId : -1;

// Validar si existe la salida
$objSalida = new Salidas($conn);
$objSalida->getById($salidaId);

if ($objSalida->salidaId == -1)
{
    header("Location: /?mod=error404");
}

//-----------------------------------------------

// Cargar la librería para generar archivo de Excel
require '../../../../libs/simplexlsxgen/SimpleXLSXGen.php';
use Shuchkin\SimpleXLSXGen;

// Obtener el detalle de la salida
$objSalidaDetalle = new SalidasDetalle($conn);
$listaDeDetalles = $objSalidaDetalle->getAll($salidaId);

$objEmpresa = new Empresa($conn);
$objEmpresa->getDatos();
$empresa = $objEmpresa->nombre;

// Crear el arreglo de datos que se mostrará en Excel
$data = array();

// Datos de la salida
array_push($data, ["<b>Inventory discharge</b>"]);
array_push($data, ["<b>" . $empresa . "</b>"]);
array_push($data, ["Correlative:", $objSalida->correlativo]);
array_push($data, ["Date:", $objSalida->fechaCreacion->format("m-d-Y")]);
array_push($data, ["Store:", $objSalida->sucursal]);
array_push($data, ["Type:", $objSalida->tipoDeSalida]);
array_push($data, ["Concept:", $objSalida->concepto]);
array_push($data, [""]);

// Encabezado de detalle
array_push($data, ["<b>#</b>", "<b>Inventory item</b>", "<b>Category</b>", "<b>Brand</b>", "<b>Model</b>", "<b>Description</b>",
                   "<b>MSRP $</b>", "<b>Stock type orig.</b>", "<b>Cost orig. $</b>", "<b>Stock type distr.</b>", "<b>Cost distr. $</b>"]);

// Agregando las filas del detalle
$conteo = 0;
foreach($listaDeDetalles as $detalle)
{
    $conteo++;
    array_push($data, [$conteo, $detalle["CODIGOINVENTARIO"], $detalle["CATEGORIA"], $detalle["MARCA"], $detalle["MODELO"], $detalle["DESCRIPCION"],
                       $detalle["MSRP"], $detalle["TIPODESTOCKORIGEN"], $detalle["COSTOORIGEN"], $detalle["TIPODESTOCK"], $detalle["COSTODIST"]]);
}

// Mostrar total de ítems
array_push($data, [""]);
array_push($data, ["Total items:", $conteo]);

// Crear el archivo de Excel
$xlsx = SimpleXLSXGen::fromArray($data);

// Enviar el archivo al navegador para descarga
$xlsx->downloadAs('Inventory discharge.xlsx');