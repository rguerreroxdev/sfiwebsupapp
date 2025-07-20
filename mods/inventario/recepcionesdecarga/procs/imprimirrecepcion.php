<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/RecepcionesDeCarga.php");
require_once("../../../../inc/class/RecepcionesDeCargaDetalle.php");
require_once("../../../../libs/tcpdf/tcpdf.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$recepcionDeCargaId = isset($_GET["rid"]) && trim($_GET["rid"]) != "" ? $_GET["rid"] : -1;
$recepcionDeCargaId = is_numeric($recepcionDeCargaId) ? $recepcionDeCargaId : -1;

// Validar si existe la recepción
$objRecepcion = new RecepcionesDeCarga($conn);
$objRecepcion->getById($recepcionDeCargaId);

if ($objRecepcion->recepcionDeCargaId == -1)
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
        $recepcionDeCargaId = isset($_GET["rid"]) && trim($_GET["rid"]) != "" ? $_GET["rid"] : -1;
        $recepcionDeCargaId = is_numeric($recepcionDeCargaId) ? $recepcionDeCargaId : -1;
        $objRecepcion = new RecepcionesDeCarga($conn);
        $objRecepcion->getById($recepcionDeCargaId);

        $objEmpresa = new Empresa($conn);
        $objEmpresa->getDatos();

        $this->setCellPaddings(1, 0, 1, 0);

        // Logo
        $this->Image("../../../../imgs/logojpg.jpg", 15, 15, 25, 0);
        // Títulos
		$this->SetFont("Helvetica", "", 8);
		$this->SetXY(15, 15);	$this->Cell(250, 5, $objEmpresa->nombre, 0, 0, "C");
        $this->SetFont("Helvetica", "B", 8);
        $this->SetXY(15, 20);	$this->Cell(250, 5, "BILL OF LADING", 0, 0, "C");

        // Fecha y hora de generación
        $fechaDeEmision = new DateTime();
        $this->SetFont("Helvetica", "", 6);
		$this->SetXY(235, 20);	$this->Cell(30, 5, $fechaDeEmision->format("m/d/Y H:i"), 0, 0, 'R');

        // Datos de Recepción
        $this->SetFont("Helvetica", "", 8);
        $this->SetXY(15, 25);	$this->Cell(150, 4, 'Store: ' . $objRecepcion->sucursal, 0, 0);
        $this->SetXY(15, 29);	$this->Cell(50, 4, 'Correlative: ' . $objRecepcion->correlativo, 0, 0);
        $this->SetXY(80, 29);	$this->Cell(50, 4, 'Status: ' . $objRecepcion->nombreDeEstado, 0, 0);
        $this->SetXY(15, 33);	$this->Cell(150, 4, 'Supplier: ' . $objRecepcion->codigoProveedor . " - " . $objRecepcion->proveedor, 0, 0);
        $this->SetXY(15, 37);	$this->Cell(50, 4, 'Document date: ' . $objRecepcion->fechaDeEmision->format("m/d/Y"), 0, 0);
        $this->SetXY(80, 37);	$this->Cell(50, 4, 'Reception date: ' . $objRecepcion->fechaDeRecepcion->format("m/d/Y"), 0, 0);
        $this->SetXY(15, 41);	$this->Cell(65, 4, 'Stock type: ' . $objRecepcion->tipoDeStockOrigen, 0, 0);
        $this->SetXY(80, 41);	$this->Cell(50, 4, 'Stock type %: ' . $objRecepcion->porcentajeTipoDeStockOrigen, 0, 0);
        $this->SetXY(15, 45);	$this->Cell(50, 4, 'Warranty: ' . $objRecepcion->tipoDeGarantia, 0, 0);

        // Encabezado de columnas
        $x = 15;
        $y = 50;
        $this->SetFont("Helvetica", "", 6);
        $this->SetXY($x, $y);	        $this->Cell(6, 5, '#', 1, 0, '', false);
        $this->SetXY($x +=  6, $y);     $this->Cell(12, 5, 'Quantity', 1, 0, '', false);
        $this->SetXY($x += 12, $y);     $this->Cell(12, 5, 'Product', 1, 0, '', false);
        $this->SetXY($x += 12, $y);     $this->Cell(35, 5, 'Category', 1, 0, '', false);
        $this->SetXY($x += 35, $y);     $this->Cell(20, 5, 'Brand', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(29, 5, 'Model', 1, 0, '', false);
        $this->SetXY($x += 29, $y);     $this->Cell(54, 5, 'Description', 1, 0, '', false);
        $this->SetXY($x += 54, $y);     $this->Cell(13, 5, 'MSRP', 1, 0, '', false);
        $this->SetXY($x += 13, $y);     $this->Cell(18, 5, 'Stock type origin', 1, 0, '', false);
        $this->SetXY($x += 18, $y);     $this->Cell(10, 5, '% origin', 1, 0, 'C', false);
        $this->SetXY($x += 10, $y);     $this->Cell(13, 5, 'Cost', 1, 0, '', false);
        $this->SetXY($x += 13, $y);     $this->Cell(18, 5, 'Stock type distr.', 1, 0, '', false);
        $this->SetXY($x += 18, $y);     $this->Cell(10, 5, '% distr.', 1, 0, 'C', false);

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
$pdf = new PDF("L", "mm", "LETTER");
$pdf->AddPage();

// Establecer fuente
$pdf->SetFont("Helvetica", "", 8);

//-----------------------------------------------

// Obtener datos
$objRecepcionDetalle = new RecepcionesDeCargaDetalle($conn);
$datos = $objRecepcionDetalle->getAll($recepcionDeCargaId);

$arrayDatosLimpios = array();
$filaConteo = 0;
foreach ($datos as $fila)
{
    $filaConteo++;

    $arrayFila = [
        $filaConteo,
        $fila["CANTIDAD"],
        $fila["CODIGOPRODUCTO"],
        $fila["CATEGORIA"],
        $fila["MARCA"],
        $fila["PRODUCTO"],
        $fila["DESCRIPCION"],
        "$ " . number_format($fila["MSRP"], 2),
        $fila["TIPODESTOCKORIGEN"],
        number_format($fila["PORCENTAJETIPODESTOCKORIGEN"], 2),
        "$ " . number_format($fila["COSTOORIGEN"], 2),
        $fila["TIPODESTOCKDIST"],
        number_format($fila["PORCENTAJETIPODESTOCKDIST"], 2)
    ];

    array_push($arrayDatosLimpios, $arrayFila);
}

// Ancho de columnas (Ver los anchos en Header)
$anchoDeColumnas = [6, 12, 12, 35, 20, 29, 54, 13, 18, 10, 13, 18, 10];
$alineacionDeCelda = ["L", "R", "L", "L", "L", "L", "L", "R", "L", "R", "R", "L", "R"];

// Preparar valores para controlar posición de fila y columna en que se muestran datos
$startX = 15;
$startY = 55;
$currentX = $startX;
$currentY = $startY;
$pdf->setXY($currentX, $currentY);
$maximoY = $currentY;

$totalItems = 0;

// Recorrer los datos para mostrarlos
foreach ($arrayDatosLimpios as $fila)
{
    // Calculando el total de ítems
    $totalItems += $fila[1];

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

// Mostrar área de total de ítems y de firmas
$pdf->setXY($startX, $currentY);

// Verificar si es momento de agregar una nueva página
if ($pdf->GetY() + 30 > $pdf->getPageHeight()) {
    $pdf->AddPage();
    $currentX = $startX;
    $currentY = $startY + 20;
    $pdf->setXY($currentX, $currentY);
}

$pdf->setXY($startX, $currentY);
$pdf->Cell(70, 5, "Total items: " . $totalItems);

//-----------------------------------------------

// Generar el PDF y enviarlo al navegador
$pdf->Output("Bill of lading " . $objRecepcion->correlativo . ".pdf");

//-----------------------------------------------