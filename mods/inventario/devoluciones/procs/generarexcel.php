<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/DevolucionesInv.php");
require_once("../../../../inc/class/DevolucionesDetalleInv.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$devolucionId = isset($_GET["did"]) && trim($_GET["did"]) != "" ? $_GET["did"] : -1;
$devolucionId = is_numeric($devolucionId) ? $devolucionId : -1;

// Validar si existe la deovlución
$objDevolucion = new DevolucionesInv($conn);
$objDevolucion->getById($devolucionId);

if ($objDevolucion->devolucionId == -1)
{
    header("Location: /?mod=error404");
}

//-----------------------------------------------

// Cargar la librería para generar archivo de Excel
require '../../../../libs/simplexlsxgen/SimpleXLSXGen.php';
use Shuchkin\SimpleXLSXGen;

// Obtener el detalle de la devolución
$objDevolucionDetalle = new DevolucionesDetalleInv($conn);
$listaDeDetalles = $objDevolucionDetalle->getAll($devolucionId);

$objEmpresa = new Empresa($conn);
$objEmpresa->getDatos();
$empresa = $objEmpresa->nombre;

// Crear el arreglo de datos que se mostrará en Excel
$data = array();

// Datos de la salida
array_push($data, ["<b>Inventory return</b>"]);
array_push($data, ["<b>" . $empresa . "</b>"]);
array_push($data, ["Correlative:", $objDevolucion->correlativo]);
array_push($data, ["Date:", $objDevolucion->fechaCreacion->format("m-d-Y")]);
array_push($data, ["Store:", $objDevolucion->sucursal]);
array_push($data, ["Type:", $objDevolucion->tipoDeDevolucion]);
array_push($data, ["Notes:", $objDevolucion->concepto]);
array_push($data, [""]);

// Encabezado de detalle
array_push(
    $data,
    [
        "<b>#</b>", "<b>Inventory item</b>", "<b>Category</b>", "<b>Brand</b>", "<b>Model</b>", "<b>Description</b>",
        "<b>Disch. #</b>", "<b>Disch. date</b>", "<b>Disch. type</b>", "<b>MSRP $</b>", "<b>Stock type distr.</b>"
    ]);

// Agregando las filas del detalle
$conteo = 0;
foreach($listaDeDetalles as $detalle)
{
    $conteo++;
    $fechaDeSalida = str_replace("/", "-", $detalle["FECHASALIDA"]);
    array_push(
        $data,
        [
            $conteo, $detalle["CODIGOINVENTARIO"], $detalle["CATEGORIA"], $detalle["MARCA"], $detalle["MODELO"], $detalle["DESCRIPCION"],
            $detalle["CORRELATIVOSALIDA"], $fechaDeSalida, $detalle["TIPODESALIDA"], $detalle["MSRP"], $detalle["TIPODESTOCK"]
        ]);
}

// Mostrar total de ítems
array_push($data, [""]);
array_push($data, ["Total items:", $conteo]);

// Crear el archivo de Excel
$xlsx = SimpleXLSXGen::fromArray($data);

// Enviar el archivo al navegador para descarga
$xlsx->downloadAs('Inventory return.xlsx');