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
require_once("../../../../libs/tcpdf/tcpdf.php");

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
        $categoriaId = isset($_GET["c"]) && trim($_GET["c"]) != "" ? $_GET["c"] : -1;
        $fechaInicial = isset($_GET["fi"]) && trim($_GET["fi"]) != "" ? $_GET["fi"] : '2500-01-01';
        $fechaFinal = isset($_GET["ff"]) && trim($_GET["ff"]) != "" ? $_GET["ff"] : '2500-01-01';
        $tipo = isset($_GET["t"]) && trim($_GET["t"]) != "" ? $_GET["t"] : -1;
        
        $fechaInicial = substr($fechaInicial, 5, 2) . "/" . substr($fechaInicial, 8, 2) . "/" . substr($fechaInicial, 0, 4);
        $fechaFinal = substr($fechaFinal, 5, 2) . "/" . substr($fechaFinal, 8, 2) . "/" . substr($fechaFinal, 0, 4);

        $objSucursal = new Sucursales($conn);
        $objCategoria = new Categorias($conn);
        $objTipoDeSalida = new TiposDeSalidas($conn);
        $objSucursal->getById($sucursalId);
        $objCategoria->getById($categoriaId);
        $objTipoDeSalida->getById($tipo);
        $sucursal = $sucursalId == -1 ? "All" : $objSucursal->nombre;
        $categoria = $categoriaId == -1 ? "All" : $objCategoria->nombre;
        $tipoDeSalida = $tipo == -1 ? "ALL" : $objTipoDeSalida->nombre;

        $this->setCellPaddings(1, 0, 1, 0);

        // Logo
        $this->Image("../../../../imgs/logojpg.jpg", 15, 15, 25, 0);
        // Títulos
		$this->SetFont("Helvetica", "", 8);
		$this->SetXY(15, 15);	$this->Cell(0, 5, $objEmpresa->nombre, 0, 0, "C");
        $this->SetFont("Helvetica", "B", 8);
        $this->SetXY(15, 20);	$this->Cell(0, 5, "DISCHARGED ITEMS", 0, 0, "C");

        // Fecha y hora de generación
        $fechaDeEmision = new DateTime();
        $this->SetFont("Helvetica", "", 6);
		$this->SetXY(235, 20);	$this->Cell(30, 5, $fechaDeEmision->format("m/d/Y H:i"), 0, 0, 'R');

        // Datos de reporte
        $this->SetFont("Helvetica", "", 8);
        $this->SetXY(15, 25);	$this->Cell(150, 4, 'Store: ' . $sucursal, 0, 0);
        $this->SetXY(15, 29);	$this->Cell(150, 4, 'Category: ' . $categoria, 0, 0);
        $this->SetXY(15, 33);	$this->Cell(150, 4, 'From: ' . $fechaInicial . " To: " . $fechaFinal, 0, 0);
        $this->SetXY(15, 37);	$this->Cell(150, 4, 'Type: ' . $tipoDeSalida, 0, 0);

        // Encabezado de columnas
        $x = 15;
        $y = 45;
        $this->SetFont("Helvetica", "", 6);
        $this->SetXY($x, $y);           $this->Cell(7, 5, '#', 1, 0, '', false);
        $this->SetXY($x +=  7, $y);     $this->Cell(15, 5, 'Date', 1, 0, '', false);
        $this->SetXY($x += 15, $y);     $this->Cell(15, 5, 'Discharge #', 1, 0, '', false);
        $this->SetXY($x += 15, $y);     $this->Cell(20, 5, 'Type', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(25, 5, 'Store', 1, 0, '', false);
        $this->SetXY($x += 25, $y);     $this->Cell(15, 5, 'Code', 1, 0, '', false);
        $this->SetXY($x += 15, $y);     $this->Cell(28, 5, 'Category', 1, 0, '', false);
        $this->SetXY($x += 28, $y);     $this->Cell(20, 5, 'Brand', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(23, 5, 'Model', 1, 0, '', false);
        $this->SetXY($x += 23, $y);     $this->Cell(23, 5, 'Color', 1, 0, '', false);
        $this->SetXY($x += 23, $y);     $this->Cell(60, 5, 'Description', 1, 0, '', false);
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
$objReportes = new RptsInventario($conn);
$datos = $objReportes->inventarioSalidas($usuarioId, $sucursalId, $categoriaId, $fechaInicial, $fechaFinal);

$arrayDatosLimpios = array();
$filaConteo = 0;
foreach ($datos as $fila)
{
    $filaConteo++;

    $arrayFila = [
        $filaConteo,
        $fila["FECHA"],
        $fila["CORRELATIVO"],
        $fila["TIPODESALIDA"],
        $fila["SUCURSAL"],
        $fila["CODIGOINVENTARIO"],
        $fila["CATEGORIA"],
        $fila["MARCA"],
        $fila["MODELO"],
        $fila["COLOR"],
        $fila["DESCRIPCION"],
    ];

    array_push($arrayDatosLimpios, $arrayFila);
}

// Ancho de columnas (Ver los anchos en Header)
$anchoDeColumnas = [7, 15, 15, 20, 25, 15, 28, 20, 23, 23, 60];
$alineacionDeCelda = ["L", "L", "L", "L", "L", "L", "L", "L", "L", "L", "L"];

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

$pdf->setXY($startX, $currentY - 20);
$pdf->Cell(70, 5, "Total items: " . count($arrayDatosLimpios));

//-----------------------------------------------

// Generar el PDF y enviarlo al navegador
$pdf->Output("Discharged items.pdf");

//-----------------------------------------------