<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/DevolucionesInv.php");
require_once("../../../../inc/class/DevolucionesDetalleInv.php");
require_once("../../../../libs/tcpdf/tcpdf.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$devolucionId = isset($_GET["did"]) && trim($_GET["did"]) != "" ? $_GET["did"] : -1;
$devolucionId = is_numeric($devolucionId) ? $devolucionId : -1;

// Validar si existe la devolución
$objDevolucion = new DevolucionesInv($conn);
$objDevolucion->getById($devolucionId);

if ($objDevolucion->devolucionId == -1)
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
        $devolucionId = isset($_GET["did"]) && trim($_GET["did"]) != "" ? $_GET["did"] : -1;
        $devolucionId = is_numeric($devolucionId) ? $devolucionId : -1;
        $objDevolucion = new DevolucionesInv($conn);
        $objDevolucion->getById($devolucionId);

        $objEmpresa = new Empresa($conn);
        $objEmpresa->getDatos();

        $this->setCellPaddings(1, 0, 1, 0);

        // Logo
        $this->Image("../../../../imgs/logojpg.jpg", 15, 15, 25, 0);
        // Títulos
		$this->SetFont("Helvetica", "", 8);
		$this->SetXY(15, 15);	$this->Cell(0, 5, $objEmpresa->nombre, 0, 0, "C");
        $this->SetFont("Helvetica", "B", 8);
        $this->SetXY(15, 20);	$this->Cell(0, 5, "INVENTORY RETURN", 0, 0, "C");

        // Fecha y hora de generación
        $fechaDeEmision = new DateTime();
        $this->SetFont("Helvetica", "", 6);
		$this->SetXY(170, 20);	$this->Cell(30, 5, $fechaDeEmision->format("m/d/Y H:i"), 0, 0, 'R');

        // Datos de Recepción
        $this->SetFont("Helvetica", "", 8);
        $this->SetXY(15, 25);	$this->Cell(150, 4, 'Store: ' . $objDevolucion->sucursal, 0, 0);
        $this->SetXY(15, 33);	$this->Cell(50, 4, 'Correlative: ' . $objDevolucion->correlativo, 0, 0);
        $this->SetXY(80, 33);	$this->Cell(50, 4, 'Status: ' . $objDevolucion->nombreDeEstado, 0, 0);
        $this->SetXY(15, 37);	$this->Cell(50, 4, 'Return date: ' . $objDevolucion->fecha, 0, 0);
        $this->SetXY(80, 37);	$this->Cell(50, 4, 'Type: ' . $objDevolucion->tipoDeDevolucion, 0, 0);
        $this->SetXY(15, 41);	$this->MultiCell(185, 4, 'Notes: ' . $objDevolucion->concepto, 0, 0);

        // Encabezado de columnas
        $x = 15;
        $y = 55;
        $this->SetFont("Helvetica", "", 6);
        $this->SetXY($x, $y);           $this->Cell(6, 5, '#', 1, 0, '', false);
        $this->SetXY($x +=  6, $y);     $this->Cell(15, 5, 'Item code', 1, 0, '', false);
        $this->SetXY($x += 15, $y);     $this->Cell(30, 5, 'Category', 1, 0, '', false);
        $this->SetXY($x += 30, $y);     $this->Cell(20, 5, 'Brand', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(25, 5, 'Model', 1, 0, '', false);
        $this->SetXY($x += 25, $y);     $this->Cell(50, 5, 'Description', 1, 0, '', false);
        $this->SetXY($x += 50, $y);     $this->Cell(10, 5, 'Disch. #', 1, 0, '', false);
        $this->SetXY($x += 10, $y);     $this->Cell(15, 5, 'Disch. date', 1, 0, '', false);
        $this->SetXY($x += 15, $y);     $this->Cell(20, 5, 'Disch. type', 1, 0, '', false);
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
$objDevolucionDetalle = new DevolucionesDetalleInv($conn);
$datos = $objDevolucionDetalle->getAll($devolucionId);

$arrayDatosLimpios = array();
$filaConteo = 0;
foreach ($datos as $fila)
{
    $filaConteo++;

    $arrayFila = [
        $filaConteo,
        $fila["CODIGOINVENTARIO"],
        $fila["CATEGORIA"],
        $fila["MARCA"],
        $fila["MODELO"],
        $fila["DESCRIPCION"],
        $fila["CORRELATIVOSALIDA"],
        $fila["FECHASALIDA"],
        $fila["TIPODESALIDA"],
    ];

    array_push($arrayDatosLimpios, $arrayFila);
}

// Ancho de columnas (Ver los anchos en Header)
$anchoDeColumnas = [6, 15, 30, 20, 25, 50, 10, 15, 20];
$alineacionDeCelda = ["L", "L", "L", "L", "L", "L", "C", "L", "L"];

// Preparar valores para controlar posición de fila y columna en que se muestran datos
$startX = 15;
$startY = 60;
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

// Mostrar área de firmas
$currentY += 20;
$pdf->setXY($startX, $currentY);

// Verificar si es momento de agregar una nueva página
if ($pdf->GetY() + 30 > $pdf->getPageHeight()) {
    $pdf->AddPage();
    $currentX = $startX;
    $currentY = $startY + 20;
    $pdf->setXY($currentX, $currentY);
}

$pdf->setXY($startX, $currentY - 20);
$pdf->Cell(70, 5, "Total items: " . count($arrayDatosLimpios));

$pdf->setXY($startX, $currentY);
$pdf->Cell(70, 5, 'Name and signature', "T", 0, '', false);

//-----------------------------------------------

// Generar el PDF y enviarlo al navegador
$pdf->Output("Inventory return " . $objDevolucion->correlativo . ".pdf");

//-----------------------------------------------