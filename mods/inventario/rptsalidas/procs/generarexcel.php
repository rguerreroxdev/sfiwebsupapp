<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/RptsInventario.php");
require_once("../../../../inc/class/Usuario.php");
require_once("../../../../inc/class/Sucursales.php");
require_once("../../../../inc/class/Categorias.php");
require_once("../../../../inc/class/TiposDeSalida.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$usuarioId = isset($_GET["u"]) && trim($_GET["u"]) != "" ? $_GET["u"] : -1;
$sucursalId = isset($_GET["s"]) && trim($_GET["s"]) != "" ? $_GET["s"] : -1;
$categoriaId = isset($_GET["c"]) && trim($_GET["c"]) != "" ? $_GET["c"] : -1;
$fechaInicial = isset($_GET["fi"]) && trim($_GET["fi"]) != "" ? $_GET["fi"] : '25000101';
$fechaFinal = isset($_GET["ff"]) && trim($_GET["ff"]) != "" ? $_GET["ff"] : '25000101';
$tipo = isset($_GET["t"]) && trim($_GET["t"]) != "" ? $_GET["t"] : -1;

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
$datos = $objReportes->inventarioSalidas($usuarioId, $sucursalId, $categoriaId, $fechaInicial, $fechaFinal);

// Crear el arreglo de datos que se mostrará en Excel
$data = array();

// Datos del reporte
$fechaDeEmision = new DateTime();
$objEmpresa = new Empresa($conn);
$objEmpresa->getDatos();
$objSucursal = new Sucursales($conn);
$objCategoria = new Categorias($conn);
$objTipoDeSalida = new TiposDeSalidas($conn);
$objSucursal->getById($sucursalId);
$objCategoria->getById($categoriaId);
$objTipoDeSalida->getById($tipo);
$empresa = $objEmpresa->nombre;
$sucursal = $sucursalId == -1 ? "All" : $objSucursal->nombre;
$categoria = $categoriaId == -1 ? "All" : $objCategoria->nombre;
$tipoDeSalida = $tipo == -1 ? "ALL" : $objTipoDeSalida->nombre;

$fechaInicial = substr($fechaInicial, 4, 2) . "-" . substr($fechaInicial, 6, 2) . "-" . substr($fechaInicial, 0, 4);
$fechaFinal = substr($fechaFinal, 4, 2) . "-" . substr($fechaFinal, 6, 2) . "-" . substr($fechaFinal, 0, 4);

array_push($data, ["<b>Discharged items</b>"]);
array_push($data, ["<b>" . $empresa . "</b>"]);
array_push($data, ["Date:", $fechaDeEmision->format("m-d-Y")]);
array_push($data, ["Store:", $sucursal]);
array_push($data, ["Category:", $categoria]);
array_push($data, ["From :", $fechaInicial]);
array_push($data, ["To :", $fechaFinal]);
array_push($data, ["Type :", $tipoDeSalida]);
array_push($data, [""]);

// Encabezado de filas de datos
array_push($data, [
    "<b>#</b>", "<b>Date</b>", "<b>Discharge #</b>", "<b>Type</b>", "<b>Store</b>", "<b>Code</b>", "<b>Category</b>", "<b>Brand</b>",
    "<b>Model</b>", "<b>Color</b>", "<b>Description</b>"
]);

// Agregando las filas de datos
$conteo = 0;
foreach($datos as $dato)
{
    $conteo++;

    array_push($data, [
        $conteo,
        str_replace("/", "-", $dato["FECHA"]), $dato["CORRELATIVO"], $dato["TIPODESALIDA"], $dato["SUCURSAL"], $dato["CODIGOINVENTARIO"], $dato["CATEGORIA"], $dato["MARCA"],
        $dato["MODELO"], $dato["COLOR"], $dato["DESCRIPCION"]
    ]);
}

// Mostrar total de ítems
array_push($data, [""]);
array_push($data, [
    "Total items:", $conteo
]);

// Crear el archivo de Excel
$xlsx = SimpleXLSXGen::fromArray($data);

// Enviar el archivo al navegador para descarga
$xlsx->downloadAs('Discharged items.xlsx');