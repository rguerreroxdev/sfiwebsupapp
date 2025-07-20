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
$dias = isset($_GET["d"]) && trim($_GET["d"]) != "" ? $_GET["d"] : -1;

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
$datos = $objReportes->inventarioAntiguedad($usuarioId, $sucursalId, $categoriaId, $dias);

// Crear el arreglo de datos que se mostrará en Excel
$data = array();

// Datos del reporte
$fechaDeEmision = new DateTime();
$objEmpresa = new Empresa($conn);
$objEmpresa->getDatos();
$objSucursal = new Sucursales($conn);
$objCategoria = new Categorias($conn);
$objSucursal->getById($sucursalId);
$objCategoria->getById($categoriaId);
$empresa = $objEmpresa->nombre;
$sucursal = $sucursalId == -1 ? "All" : $objSucursal->nombre;
$categoria = $categoriaId == -1 ? "All" : $objCategoria->nombre;

array_push($data, ["<b>Stock aging</b>"]);
array_push($data, ["<b>" . $empresa . "</b>"]);
array_push($data, ["Date:", $fechaDeEmision->format("m-d-Y")]);
array_push($data, ["Store:", $sucursal]);
array_push($data, ["Category:", $categoria]);
array_push($data, ["Days greater than or equal to :", $dias]);
array_push($data, [""]);

// Encabezado de filas de datos
array_push($data, [
    "<b>#</b>", "<b>Entry date</b>", "<b>Days</b>", "<b>Store</b>", "<b>Code</b>", "<b>Category</b>", "<b>Brand</b>",
    "<b>Model</b>", "<b>Color</b>", "<b>Description</b>",
    "<b>MSRP $</b>", "<b>Stock type orig.</b>", "<b>Stock", "<b>In transit</b>",
]);

// Agregando las filas de datos
$conteo = 0;
foreach($datos as $dato)
{
    $conteo++;

    array_push($data, [
        $conteo,
        str_replace("/", "-", $dato["FECHADERECEPCION"]), $dato["DIAS"], $dato["SUCURSAL"], $dato["CODIGOINVENTARIO"], $dato["CATEGORIA"], $dato["MARCA"],
        $dato["MODELO"], $dato["COLOR"], $dato["DESCRIPCION"],
        $dato["MSRP"], $dato["TIPODESTOCKORIGEN"], $dato["EXISTENCIA"], $dato["ENTRANSITO"]
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
$xlsx->downloadAs('Stock aging.xlsx');