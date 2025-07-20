<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/Traslados.php");
require_once("../../../../inc/class/TrasladosDetalle.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$trasladoId = isset($_GET["tid"]) && trim($_GET["tid"]) != "" ? $_GET["tid"] : -1;
$trasladoId = is_numeric($trasladoId) ? $trasladoId : -1;

// Validar si existe el traslado
$objTraslado = new Traslados($conn);
$objTraslado->getById($trasladoId);

if ($objTraslado->trasladoId == -1)
{
    header("Location: /?mod=error404");
}

//-----------------------------------------------

// Cargar la librería para generar archivo de Excel
require '../../../../libs/simplexlsxgen/SimpleXLSXGen.php';
use Shuchkin\SimpleXLSXGen;

// Obtener el detalle del traslado
$objTrasladoDetalle = new TrasladosDetalle($conn);
$listaDeDetalles = $objTrasladoDetalle->getAll($trasladoId);

$objEmpresa = new Empresa($conn);
$objEmpresa->getDatos();
$empresa = $objEmpresa->nombre;

// Crear el arreglo de datos que se mostrará en Excel
$data = array();

// Datos del traslado
array_push($data, ["<b>Inventory transfer</b>"]);
array_push($data, ["<b>" . $empresa . "</b>"]);
array_push($data, ["Correlative:", $objTraslado->correlativo]);
array_push($data, ["Date:", $objTraslado->fechaCreacion->format("m-d-Y")]);
array_push($data, ["From:", $objTraslado->sucursalOrigen]);
array_push($data, ["To:", $objTraslado->sucursalDestino]);
array_push($data, [""]);

// Encabezado de detalle
array_push($data, ["<b>#</b>", "<b>Inventory item</b>", "<b>Category</b>", "<b>Brand</b>", "<b>Model</b>", "<b>Description</b>", "<b>MSRP $</b>", "<b>Stock type distr.</b>"]);

// Agregando las filas del traslado
$conteo = 0;
foreach($listaDeDetalles as $detalle)
{
    $conteo++;
    array_push($data, [$conteo, $detalle["CODIGOINVENTARIO"], $detalle["CATEGORIA"], $detalle["MARCA"], $detalle["MODELO"], $detalle["DESCRIPCION"], $detalle["MSRP"], $detalle["PORCENTAJETIPODESTOCKDIST"]]);
}

// Mostrar total de ítems
array_push($data, [""]);
array_push($data, ["Total items:", $conteo]);

// Crear el archivo de Excel
$xlsx = SimpleXLSXGen::fromArray($data);

// Enviar el archivo al navegador para descarga
$xlsx->downloadAs('Inventory transfer.xlsx');