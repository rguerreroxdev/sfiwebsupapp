<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/RptsInventario.php");
require_once("../../../../inc/class/Usuario.php");
require_once("../../../../inc/class/Sucursales.php");
require_once("../../../../inc/class/Proveedores.php");
require_once("../../../../libs/tcpdf/tcpdf.php");

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

class PDF extends TCPDF
{
    //-------------------------------------------

    // Encabezado
    function Header()
    {
        // Datos
        $conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
        $conn->conectar();

        $objEmpresa = new Empresa($conn);
        $objEmpresa->getDatos();

        $sucursalId = isset($_GET["s"]) && trim($_GET["s"]) != "" ? $_GET["s"] : -1;
        $proveedorId = isset($_GET["p"]) && trim($_GET["p"]) != "" ? $_GET["p"] : -1;
        $fechaInicial = isset($_GET["fi"]) && trim($_GET["fi"]) != "" ? $_GET["fi"] : '2500-01-01';
        $fechaFinal = isset($_GET["ff"]) && trim($_GET["ff"]) != "" ? $_GET["ff"] : '2500-01-01';
        
        $fechaInicial = substr($fechaInicial, 5, 2) . "/" . substr($fechaInicial, 8, 2) . "/" . substr($fechaInicial, 0, 4);
        $fechaFinal = substr($fechaFinal, 5, 2) . "/" . substr($fechaFinal, 8, 2) . "/" . substr($fechaFinal, 0, 4);

        $objSucursal = new Sucursales($conn);
        $objProveedor = new Proveedores($conn);
        $objSucursal->getById($sucursalId);
        $objProveedor->getById($proveedorId);
        $sucursal = $sucursalId == -1 ? "All" : $objSucursal->nombre;
        $proveedor = $proveedorId == -1 ? "All" : $objProveedor->nombre;

        $this->setCellPaddings(1, 0, 1, 0);

        // Logo
        $this->Image("../../../../imgs/logojpg.jpg", 15, 15, 25, 0);
        // Títulos
		$this->SetFont("Helvetica", "", 8);
		$this->SetXY(15, 15);	$this->Cell(0, 5, $objEmpresa->nombre, 0, 0, "C");
        $this->SetFont("Helvetica", "B", 8);
        $this->SetXY(15, 20);	$this->Cell(0, 5, "PURCHASING BY SUPPLIER", 0, 0, "C");

        // Fecha y hora de generación
        $fechaDeEmision = new DateTime();
        $this->SetFont("Helvetica", "", 6);
		$this->SetXY(175, 20);	$this->Cell(30, 5, $fechaDeEmision->format("m/d/Y H:i"), 0, 0, 'R');

        // Datos de reporte
        $this->SetFont("Helvetica", "", 8);
        $this->SetXY(15, 25);	$this->Cell(150, 4, 'Store: ' . $sucursal, 0, 0);
        $this->SetXY(15, 29);	$this->Cell(150, 4, 'Supplier: ' . $proveedor, 0, 0);
        $this->SetXY(15, 33);	$this->Cell(150, 4, 'From: ' . $fechaInicial . " To: " . $fechaFinal, 0, 0);

        // Encabezado de columnas
        $x = 15;
        $y = 45;
        $this->SetFont("Helvetica", "", 6);
        $this->SetXY($x, $y);           $this->Cell(10, 5, '#', 1, 0, '', false);
        $this->SetXY($x += 10, $y);     $this->Cell(20, 5, 'Reception date', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(40, 5, 'Supplier', 1, 0, '', false);
        $this->SetXY($x += 40, $y);     $this->Cell(20, 5, 'Quantity', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(25, 5, 'Total MSRP', 1, 0, '', false);
        $this->SetXY($x += 25, $y);     $this->Cell(25, 5, 'Stock type origin', 1, 0, '', false);
        $this->SetXY($x += 25, $y);     $this->Cell(25, 5, 'Total cost', 1, 0, '', false);
        $this->Ln(5);   $this->SetX(15);
    }

    // Pié de página
    function footer()
    {
        $this->SetY(-15);
        $this->SetFont("Helvetica", "I", 7);
        $this->Cell(0, 10, "Page " . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, "C");
    }

    //-------------------------------------------
}

//-----------------------------------------------

// Crear instancia de objeto PDF
$pdf = new PDF("P", "mm", "LETTER");
$pdf->AddPage();

// Establecer fuente
$pdf->SetFont("Helvetica", "", 8);

//-----------------------------------------------

// Obtener datos
$objReportes = new RptsInventario($conn);
$datos = $objReportes->recepcionesPorProveedor($usuarioId, $sucursalId, $proveedorId, $fechaInicial, $fechaFinal);

$arrayDatosLimpios = array();
$filaConteo = 0;
$cantidadTotal = 0;
$granTotalCosto = 0;
foreach ($datos as $fila)
{
    $filaConteo++;

    $arrayFila = [
        $filaConteo,
        $fila["FECHADERECEPCIONVARCHAR"],
        $fila["PROVEEDOR"],
        $fila["CANTIDAD"],
        "$ " . number_format($fila["TOTALMSRP"], 2),
        $fila["TIPODESTOCKORIGEN"],
        "$ " . number_format($fila["TOTALCOSTO"], 2)
    ];

    array_push($arrayDatosLimpios, $arrayFila);

    $cantidadTotal += $fila["CANTIDAD"];
    $granTotalCosto += $fila["TOTALCOSTO"];
}

// Ancho de columnas (Ver los anchos en Header)
$anchoDeColumnas = [10, 20, 40, 20, 25, 25, 25];
$alineacionDeCelda = ["L", "L", "L", "R", "R", "L", "R"];

// Preparar valores para controlar posición de fila y columna en que se muestran datos
$startX = 15;
$startY = 50;
$currentX = $startX;
$currentY = $startY;
$pdf->setXY($currentX, $currentY);
$maximoY = $currentY;

// Recorrer los datos para mostrarlos
foreach ($arrayDatosLimpios as $fila)
{
    // En cada conjunto de datos, se recorren las columnas que se van a mostrar
    for ($i = 0; $i < count($fila); $i++)
    {
        $pdf->SetFont("Helvetica", "", 6);
        $pdf->MultiCell($anchoDeColumnas[$i], 5, $fila[$i], 0, $alineacionDeCelda[$i], '', true);

        $siguienteY = $pdf->GetY();
        $maximoY = max($maximoY, $siguienteY);

        $currentX += $anchoDeColumnas[$i];
        $pdf->setXY($currentX, $currentY);
    }

    // Se crea el cuadro para cada celda, con la altura máxima encontrada
    $currentX = $startX;
    for ($i = 0; $i < count($anchoDeColumnas); $i++)
    {
        $pdf->Rect($currentX, $currentY, $anchoDeColumnas[$i], $maximoY - $currentY);
        $currentX += $anchoDeColumnas[$i];
    }

    // Se resetean los datos para mostrar la siguiente fila de datos
    $pdf->ln($maximoY);
    $currentX = $startX;
    $currentY = $maximoY;
    $pdf->setXY($currentX, $currentY);

    // Verificar si es momento de agregar una nueva página
    if ($pdf->GetY() + 30 > $pdf->getPageHeight()) {
        // Línea final
        for ($i = 0; $i < count($anchoDeColumnas); $i++)
        {
            $pdf->MultiCell($anchoDeColumnas[$i], 0, "", 'T', $alineacionDeCelda[$i], '', false);
        }

        $pdf->AddPage();
        $currentX = $startX;
        $currentY = $startY;
        $maximoY = $currentY;
        $pdf->setXY($currentX, $currentY);
    }
}

//-----------------------------------------------

// Mostrar área de total de ítems
$currentY += 20;
$pdf->setXY($startX, $currentY);

// Verificar si es momento de agregar una nueva página
if ($pdf->GetY() + 30 > $pdf->getPageHeight()) {
    $pdf->AddPage();
    $currentX = $startX;
    $currentY = $startY + 20;
    $pdf->setXY($currentX, $currentY);
}


$pdf->setXY($startX + 40, $currentY - 20);
$pdf->Cell(50, 5, "Total quantity: " . $cantidadTotal, 0, 0, "R");
$pdf->setXY($startX + 115, $currentY - 20);
$pdf->Cell(50, 5, "Grand total cost: $ " . number_format($granTotalCosto, 2), 0, 0, "R");

//-----------------------------------------------

// Generar el PDF y enviarlo al navegador
$pdf->Output("Discharged items.pdf");

//-----------------------------------------------