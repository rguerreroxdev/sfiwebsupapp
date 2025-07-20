<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/RptsFacturacion.php");
require_once("../../../../inc/class/Usuario.php");
require_once("../../../../inc/class/Sucursales.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$usuarioId = isset($_GET["u"]) && trim($_GET["u"]) != "" ? $_GET["u"] : -1;
$sucursalId = isset($_GET["s"]) && trim($_GET["s"]) != "" ? $_GET["s"] : -1;
$usuarioVendedorId = isset($_GET["v"]) && trim($_GET["v"]) != "" ? $_GET["v"] : -1;
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
$objReportes = new RptsFacturacion($conn);
$datos = $objReportes->ventasPorVendedorDetalle($fechaInicial, $fechaFinal, $sucursalId, $usuarioVendedorId);

// Crear el arreglo de datos que se mostrará en Excel
$data = array();

// Datos del reporte
$fechaDeEmision = new DateTime();
$objEmpresa = new Empresa($conn);
$objEmpresa->getDatos();
$objSucursal = new Sucursales($conn);
$objSucursal->getById($sucursalId);
$objVendedor = new Usuario($conn);
$objVendedor->getById($usuarioVendedorId);
$empresa = $objEmpresa->nombre;
$sucursal = $sucursalId == -1 ? "All" : $objSucursal->nombre;
$vendedor = $usuarioVendedorId == -1 ? "All salesperson" : $objVendedor->nombreCompleto; 

$fechaInicial = substr($fechaInicial, 4, 2) . "-" . substr($fechaInicial, 6, 2) . "-" . substr($fechaInicial, 0, 4);
$fechaFinal = substr($fechaFinal, 4, 2) . "-" . substr($fechaFinal, 6, 2) . "-" . substr($fechaFinal, 0, 4);

array_push($data, ["<b>Sales by employee</b>"]);
array_push($data, ["<b>" . $empresa . "</b>"]);
array_push($data, ["Date:", $fechaDeEmision->format("m-d-Y")]);
array_push($data, ["Store:", $sucursal]);
array_push($data, ["Salesperson:", $vendedor]);
array_push($data, ["From :", $fechaInicial]);
array_push($data, ["To :", $fechaFinal]);
array_push($data, [""]);

// Encabezado de filas de datos
array_push($data, [
    "<b>#</b>", "<b>Salesperson</b>", "<b>Date</b>", "<b>Invoice #</b>", "<b>Product</b>", "<b>Price</b>"
]);

// Agregando las filas de datos
$conteo = 0;
$totalPrecios = 0;
foreach($datos as $dato)
{
    $conteo++;
    $totalPrecios += $dato["PRECIO"];

    array_push($data, [
        $conteo,
        $dato["VENDEDOR"],
        str_replace("/", "-", $dato["FECHA"]), $dato["CORRELATIVO"], $dato["PRODUCTO"], $dato["PRECIO"]
    ]);
}

// Mostrar total de ítems
array_push($data, [""]);
array_push($data, [
    "Total items:", $conteo,
    "", "",
    "Total:", number_format($totalPrecios, 2, ".", "")
]);

// Crear el archivo de Excel
$xlsx = SimpleXLSXGen::fromArray($data);

// Enviar el archivo al navegador para descarga
$xlsx->downloadAs('Sales by employee.xlsx');