<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/RptsInventario.php");
require_once("../../../../inc/class/Usuario.php");
require_once("../../../../inc/class/Sucursales.php");
require_once("../../../../inc/class/Categorias.php");
require_once("../../../../libs/tcpdf/tcpdf.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$usuarioId = isset($_GET["u"]) && trim($_GET["u"]) != "" ? $_GET["u"] : -1;
$sucursalId = isset($_GET["s"]) && trim($_GET["s"]) != "" ? $_GET["s"] : -1;
$categoriaId = isset($_GET["c"]) && trim($_GET["c"]) != "" ? $_GET["c"] : -1;

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
        $objSucursal = new Sucursales($conn);
        $objCategoria = new Categorias($conn);
        $objSucursal->getById($sucursalId);
        $objCategoria->getById($categoriaId);
        $sucursal = $sucursalId == -1 ? "All" : $objSucursal->nombre;
        $categoria = $categoriaId == -1 ? "All" : $objCategoria->nombre;

        $this->setCellPaddings(1, 0, 1, 0);

        // Logo
        $this->Image("../../../../imgs/logojpg.jpg", 15, 15, 25, 0);
        // Títulos
		$this->SetFont("Helvetica", "", 8);
		$this->SetXY(15, 15);	$this->Cell(0, 5, $objEmpresa->nombre, 0, 0, "C");
        $this->SetFont("Helvetica", "B", 8);
        $this->SetXY(15, 20);	$this->Cell(0, 5, "GENERAL INVENTORY", 0, 0, "C");

        // Fecha y hora de generación
        $fechaDeEmision = new DateTime();
        $this->SetFont("Helvetica", "", 6);
		$this->SetXY(235, 20);	$this->Cell(30, 5, $fechaDeEmision->format("m/d/Y H:i"), 0, 0, 'R');

        // Datos de reporte
        $this->SetFont("Helvetica", "", 8);
        $this->SetXY(15, 25);	$this->Cell(150, 4, 'Store: ' . $sucursal, 0, 0);
        $this->SetXY(15, 29);	$this->Cell(150, 4, 'Category: ' . $categoria, 0, 0);

        // Encabezado de columnas
        $x = 15;
        $y = 40;
        $this->SetFont("Helvetica", "", 5);
        $this->SetXY($x, $y);           $this->Cell(7, 5, '#', 1, 0, '', false);
        $this->SetXY($x +=  7, $y);     $this->Cell(25, 5, 'Store', 1, 0, '', false);
        $this->SetXY($x += 25, $y);     $this->Cell(13, 5, 'Code', 1, 0, '', false);
        $this->SetXY($x += 13, $y);     $this->Cell(25, 5, 'Category', 1, 0, '', false);
        $this->SetXY($x += 25, $y);     $this->Cell(18, 5, 'Brand', 1, 0, '', false);
        $this->SetXY($x += 18, $y);     $this->Cell(23, 5, 'Model', 1, 0, '', false);
        $this->SetXY($x += 23, $y);     $this->Cell(23, 5, 'Color', 1, 0, '', false);
        $this->SetXY($x += 23, $y);     $this->Cell(50, 5, 'Description', 1, 0, '', false);
        $this->SetXY($x += 50, $y);     $this->Cell(13, 5, 'MSRP', 1, 0, '', false);
        $this->SetXY($x += 13, $y);     $this->Cell(18, 5, 'Stock type distr.', 1, 0, '', false);
        $this->SetXY($x += 18, $y);     $this->Cell(10, 5, 'Stock', 1, 0, '', false);
        $this->SetXY($x += 10, $y);     $this->Cell(10, 5, 'In transit', 1, 0, '', false);
        $this->SetXY($x += 10, $y);     $this->Cell(15, 5, 'MSRP x STD', 1, 0, '', false);
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
$datos = $objReportes->inventarioGeneral($usuarioId, $sucursalId, $categoriaId);

$arrayDatosLimpios = array();
$filaConteo = 0;
$totalMsrpXtipodestockdist = 0;
foreach ($datos as $fila)
{
    $filaConteo++;
    $msrpXtypodestockdist = $fila["MSRP"] * $fila["PORCENTAJETIPODESTOCKDIST"] / 100;
    $totalMsrpXtipodestockdist += $msrpXtypodestockdist;

    $arrayFila = [
        $filaConteo,
        $fila["SUCURSAL"],
        $fila["CODIGOINVENTARIO"],
        $fila["CATEGORIA"],
        $fila["MARCA"],
        $fila["MODELO"],
        $fila["COLOR"],
        $fila["DESCRIPCION"],
        "$ " . $fila["MSRP"],
        $fila["TIPODESTOCKDIST"],
        $fila["EXISTENCIA"],
        $fila["ENTRANSITO"],
        "$ " . number_format($msrpXtypodestockdist, 2, ".", ",")
    ];

    array_push($arrayDatosLimpios, $arrayFila);
}

// Ancho de columnas (Ver los anchos en Header)
$anchoDeColumnas = [7, 25, 13, 25, 18, 23, 23, 50, 13, 18, 10, 10, 15];
$alineacionDeCelda = ["L", "L", "L", "L", "L", "L", "L", "L", "R", "L", "R", "R", "R"];

// Preparar valores para controlar posición de fila y columna en que se muestran datos
$startX = 15;
$startY = 45;
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
        $pdf->SetFont("Helvetica", "", 5);
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

// //-----------------------------------------------

// Mostrar área de total de ítems y de firmas
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
$pdf->setXY($startX + 215, $currentY - 20);
$pdf->Cell(35, 5, "Total: $ " . number_format($totalMsrpXtipodestockdist, 2, ".", ","), 0, 0, "R");


//-----------------------------------------------

// Generar el PDF y enviarlo al navegador
$pdf->Output("General inventory.pdf");

//-----------------------------------------------