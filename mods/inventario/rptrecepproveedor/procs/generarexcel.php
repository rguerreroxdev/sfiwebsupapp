<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/RptsInventario.php");
require_once("../../../../inc/class/Usuario.php");
require_once("../../../../inc/class/Sucursales.php");
require_once("../../../../inc/class/Proveedores.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$usuarioId = isset($_GET["u"]) && trim($_GET["u"]) != "" ? $_GET["u"] : -1;
$sucursalId = isset($_GET["s"]) && trim($_GET["s"]) != "" ? $_GET["s"] : -1;
$proveedorId = isset($_GET["p"]) && trim($_GET["p"]) != "" ? $_GET["p"] : -1;
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
$datos = $objReportes->recepcionesPorProveedor($usuarioId, $sucursalId, $proveedorId, $fechaInicial, $fechaFinal);

// Crear el arreglo de datos que se mostrará en Excel
$data = array();

// Datos del reporte
$fechaDeEmision = new DateTime();
$objEmpresa = new Empresa($conn);
$objEmpresa->getDatos();
$objSucursal = new Sucursales($conn);
$objProveedor = new Proveedores($conn);
$objSucursal->getById($sucursalId);
$objProveedor->getById($proveedorId);
$empresa = $objEmpresa->nombre;
$sucursal = $sucursalId == -1 ? "All" : $objSucursal->nombre;
$provñeedor = $proveedorId == -1 ? "All" : $objProveedor->nombre;

$fechaInicial = substr($fechaInicial, 4, 2) . "-" . substr($fechaInicial, 6, 2) . "-" . substr($fechaInicial, 0, 4);
$fechaFinal = substr($fechaFinal, 4, 2) . "-" . substr($fechaFinal, 6, 2) . "-" . substr($fechaFinal, 0, 4);

array_push($data, ["<b>Purchasing by supplier</b>"]);
array_push($data, ["<b>" . $empresa . "</b>"]);
array_push($data, ["Date:", $fechaDeEmision->format("m-d-Y")]);
array_push($data, ["Store:", $sucursal]);
array_push($data, ["Supplier:", $provñeedor]);
array_push($data, ["From :", $fechaInicial]);
array_push($data, ["To :", $fechaFinal]);
array_push($data, [""]);

// Encabezado de filas de datos
array_push($data, [
    "<b>#</b>", "<b>Reception date</b>", "<b>Supplier</b>", "<b>Quantity</b>", "<b>Total MSRP ($)</b>", "<b>Stock type origin</b>", "<b>Total cost</b>"
]);

// Agregando las filas de datos
$conteo = 0;
$cantidadTotal = 0;
$granTotalCosto = 0;
foreach($datos as $dato)
{
    $conteo++;

    array_push($data, [
        $conteo,
        str_replace("/", "-", $dato["FECHADERECEPCIONVARCHAR"]), $dato["PROVEEDOR"], $dato["CANTIDAD"], $dato["TOTALMSRP"], $dato["TIPODESTOCKORIGEN"], $dato["TOTALCOSTO"]
    ]);

    $cantidadTotal += $dato["CANTIDAD"];
    $granTotalCosto += $dato["TOTALCOSTO"];
}

// Mostrar total
array_push($data, [""]);
array_push($data, [
    "", "",
    "Total quantity:", $cantidadTotal,
    "",
    "Grand total cost:", number_format($granTotalCosto, 2, ".", "")
]);

// Crear el archivo de Excel
$xlsx = SimpleXLSXGen::fromArray($data);

// Enviar el archivo al navegador para descarga
$xlsx->downloadAs('Purchasing by supplier.xlsx');