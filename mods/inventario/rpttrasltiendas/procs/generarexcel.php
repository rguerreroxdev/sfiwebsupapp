<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/RptsInventario.php");
require_once("../../../../inc/class/Usuario.php");
require_once("../../../../inc/class/Sucursales.php");
require_once("../../../../inc/class/Categorias.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$usuarioId = isset($_GET["u"]) && trim($_GET["u"]) != "" ? $_GET["u"] : -1;
$sucursalId = isset($_GET["s"]) && trim($_GET["s"]) != "" ? $_GET["s"] : -1;
$categoriaId = isset($_GET["c"]) && trim($_GET["c"]) != "" ? $_GET["c"] : -1;
$fechaInicial = isset($_GET["fi"]) && trim($_GET["fi"]) != "" ? $_GET["fi"] : '25000101';
$fechaFinal = isset($_GET["ff"]) && trim($_GET["ff"]) != "" ? $_GET["ff"] : '25000101';

$fechaInicial = str_replace("-", "", $fechaInicial);
$fechaFinal = str_replace("-", "", $fechaFinal);

// Validar si existe el usuario
$objUsuario = new Usuario($conn);
$objUsuario->getById($usuarioId);

if ($objUsuario->usuarioId == -1)
{
    header("Location: /?mod=error404");
}

//-----------------------------------------------

// Cargar la librería para generar archivo de Excel
require '../../../../libs/simplexlsxgen/SimpleXLSXGen.php';
use Shuchkin\SimpleXLSXGen;

// Obtener datos
$objReportes = new RptsInventario($conn);
$datos = $objReportes->trasladoDeItemsATiendas($usuarioId, $sucursalId, $categoriaId, $fechaInicial, $fechaFinal);

// Crear el arreglo de datos que se mostrará en Excel
$data = array();

// Datos del reporte
$fechaDeEmision = new DateTime();
$objEmpresa = new Empresa($conn);
$objSucursal = new Sucursales($conn);
$objCategoria = new Categorias($conn);
$objSucursal->getById($sucursalId);
$objCategoria->getById($categoriaId);
$objEmpresa->getDatos();
$empresa = $objEmpresa->nombre;
$sucursal = $sucursalId == -1 ? "All" : $objSucursal->nombre;
$categoria = $categoriaId == -1 ? "All" : $objCategoria->nombre;

$fechaInicial = substr($fechaInicial, 4, 2) . "-" . substr($fechaInicial, 6, 2) . "-" . substr($fechaInicial, 0, 4);
$fechaFinal = substr($fechaFinal, 4, 2) . "-" . substr($fechaFinal, 6, 2) . "-" . substr($fechaFinal, 0, 4);

array_push($data, ["<b>Inventory translated to stores</b>"]);
array_push($data, ["<b>" . $empresa . "</b>"]);
array_push($data, ["Date:", $fechaDeEmision->format("m-d-Y")]);
array_push($data, ["Store:", $sucursal]);
array_push($data, ["Category:", $categoria]);
array_push($data, ["From :", $fechaInicial]);
array_push($data, ["To :", $fechaFinal]);
array_push($data, [""]);

// Encabezado de filas de datos
array_push($data, [
    "<b>#</b>", "<b>Translate date</b>", "<b>Store</b>", "<b>Category</b>", "<b>Quantity</b>", "<b>MSRP ($)</b>", "<b>Stock type origin</b>", "<b>Total cost origin</b>", "<b>Stock type distr.</b>", "<b>Total cost distr.</b>"
]);

// Agregando las filas de datos
$conteo = 0;
$cantidadTotal = 0;
$granTotalCostoOrigen = 0;
$granTotalCostoDistr = 0;
foreach($datos as $dato)
{
    $conteo++;

    array_push($data, [
        $conteo,
        str_replace("/", "-", $dato["FECHACREACIONVARCHAR"]), $dato["DESTINO"], $dato["CATEGORIA"], $dato["CANTIDAD"], $dato["MSRP"], $dato["TIPODESTOCKORIGEN"], $dato["TOTALCOSTOORIGEN"], $dato["TIPODESTOCKDISTR"], $dato["TOTALCOSTODISTR"]
    ]);

    $cantidadTotal += $dato["CANTIDAD"];
    $granTotalCostoOrigen += $dato["TOTALCOSTOORIGEN"];
    $granTotalCostoDistr += $dato["TOTALCOSTODISTR"];
}

// Mostrar total
array_push($data, [""]);
array_push($data, [
    "", "", "",
    "Total quantity:", $cantidadTotal,
    "",
    "Grand total origin:", number_format($granTotalCostoOrigen, 2, ".", ""),
    "Grand total distr:", number_format($granTotalCostoDistr, 2, ".", "")
]);

// Crear el archivo de Excel
$xlsx = SimpleXLSXGen::fromArray($data);

// Enviar el archivo al navegador para descarga
$xlsx->downloadAs('Inventory translated to stores.xlsx');