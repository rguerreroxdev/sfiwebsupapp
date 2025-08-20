<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/RptsFacturacion.php");
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
        $objSucursal->getById($sucursalId);
        $sucursal = $sucursalId == -1 ? "All" : $objSucursal->nombre;

        $objProveedor = new Proveedores($conn);
        $objProveedor->getById($proveedorId);
        $proveedor = $proveedorId == -1 ? "All suppliers" : $objProveedor->nombre;

        $this->setCellPaddings(1, 0, 1, 0);

        // Logo
        $this->Image("../../../../imgs/logojpg.jpg", 15, 15, 25, 0);
        // Títulos
		$this->SetFont("Helvetica", "", 8);
		$this->SetXY(15, 15);	$this->Cell(0, 5, $objEmpresa->nombre, 0, 0, "C");
        $this->SetFont("Helvetica", "B", 8);
        $this->SetXY(15, 20);	$this->Cell(0, 5, "SALES BY SUPPLIER", 0, 0, "C");

        // Fecha y hora de generación
        $fechaDeEmision = new DateTime();
        $this->SetFont("Helvetica", "", 6);
		$this->SetXY(239, 20);	$this->Cell(30, 5, $fechaDeEmision->format("m/d/Y H:i"), 0, 0, 'R');

        // Datos de reporte
        $this->SetFont("Helvetica", "", 8);
        $this->SetXY(15, 25);	$this->Cell(150, 4, 'Store: ' . $sucursal, 0, 0);
        $this->SetXY(15, 29);	$this->Cell(150, 4, 'Supplier: ' . $proveedor, 0, 0);
        $this->SetXY(15, 33);	$this->Cell(150, 4, 'From: ' . $fechaInicial . " To: " . $fechaFinal, 0, 0);

        // Encabezado de columnas
        $x = 15;
        $y = 45;
        $this->SetFont("Helvetica", "", 6);
        $this->SetXY($x, $y);           $this->Cell(7, 5, '#', 1, 0, '', false);
        $this->SetXY($x +=  7, $y);     $this->Cell(20, 5, 'Date', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(20, 5, 'Invoice #', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(40, 5, 'Customer name', 1, 0, '', false);
        $this->SetXY($x += 40, $y);     $this->Cell(20, 5, 'Inventory number', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(35, 5, 'Product', 1, 0, '', false);
        $this->SetXY($x += 35, $y);     $this->Cell(20, 5, 'Price', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(20, 5, 'MSRP', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(20, 5, 'Stock type distr.', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(20, 5, 'Total cost distr.', 1, 0, '', false);
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
$objReportes = new RptsFacturacion($conn);
$datos = $objReportes->ventasPorProveedorDetalle($fechaInicial, $fechaFinal, $sucursalId, $proveedorId);

$arrayDatosLimpios = array();
$filaConteo = 0;
$totalPrecios = 0;
$proveedor = "";
foreach ($datos as $fila)
{
    if ($proveedor != $fila["PROVEEDOR"])
    {
        $proveedor = $fila["PROVEEDOR"];
        $filaConteo = 1;
    }
    else
    {
        $filaConteo++;    
    }
    
    $totalPrecios += $fila["PRECIO"];

    $arrayFila = [
        $filaConteo,
        $fila["FECHA"],
        $fila["CORRELATIVO"],
        $fila["CLIENTENOMBRE"],
        $fila["CODIGOPRODUCTO"],
        $fila["PRODUCTO"],
        "$ " . number_format($fila["PRECIO"], 2, ".", ","),
        "$ " . number_format($fila["MSRP"], 2, ".", ","),
        $fila["TIPODESTOCKDIST"],
        "$ " . number_format($fila["COSTODIST"], 2, ".", ","),
    ];

    array_push($arrayDatosLimpios, $arrayFila);
}

// Ancho de columnas (Ver los anchos en Header)
$anchoDeColumnas = [7, 20, 20, 40, 20, 35, 20, 20, 20, 20];
$alineacionDeCelda = ["L", "L", "L", "L", "L", "L", "R", "R", "L", "R"];

// Preparar valores para controlar posición de fila y columna en que se muestran datos
$startX = 15;
$startY = 50;
$currentX = $startX;
$currentY = $startY;
$pdf->setXY($currentX, $currentY);
$maximoY = $currentY;

// Recorrer los datos para mostrarlos
$proveedor = "";
$conteoFilasTotales = 0;
$conteoFilasProveedor = 0;
$totalProveedor = 0;
foreach ($arrayDatosLimpios as $fila)
{
    // Se verifica si es necesario crear la fila del proveedor
    if ($proveedor != $datos[$conteoFilasTotales]["PROVEEDOR"])
    {
        // Mostrar total del proveedor anterior
        if ($conteoFilasTotales > 0)
        {
            $pdf->setXY($startX, $currentY);
            $pdf->Cell(70, 5, "Total items: " . $conteoFilasProveedor);

            $pdf->setXY(122, $currentY); $pdf->Cell(35, 5, "Total $proveedor: ", 0, 0, "R");
            $pdf->setXY(157, $currentY); $pdf->Cell(20, 5, "$ " . number_format($totalProveedor, 2, ".", ","), 1, 0, "R");
            $currentX = $startX;
            $currentY += 5;
            $pdf->setXY($currentX, $currentY);

            // Verificar si es momento de agregar una nueva página
            if ($pdf->GetY() + 30 > $pdf->getPageHeight()) {
                $pdf->AddPage();
                $currentX = $startX;
                $currentY = $startY;
                $maximoY = $currentY;
                $pdf->setXY($currentX, $currentY);
            }
        }

        // Mostrar nombre de proveedor actual
        $proveedor = $datos[$conteoFilasTotales]["PROVEEDOR"];
        $pdf->SetFont("Helvetica", "", 6);
        $pdf->setXY($currentX, $currentY);  $pdf->Cell(222, 5, "Supplier: " . $proveedor, 1, 0, "L");
        $currentX = $startX;
        $currentY += 5;
        $pdf->setXY($currentX, $currentY);

        // Verificar si es momento de agregar una nueva página
        if ($pdf->GetY() + 30 > $pdf->getPageHeight()) {
            $pdf->AddPage();
            $currentX = $startX;
            $currentY = $startY;
            $maximoY = $currentY;
            $pdf->setXY($currentX, $currentY);
        }

        $conteoFilasProveedor = 1;
        $totalProveedor = $datos[$conteoFilasTotales]["PRECIO"];
    }
    else
    {
        $conteoFilasProveedor++;
        $totalProveedor += $datos[$conteoFilasTotales]["PRECIO"];
    }

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

    $conteoFilasTotales++;
}

// //-----------------------------------------------

// Mostrar área de total de ítems y total de precios de último vendedor
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
$pdf->Cell(70, 5, "Total items: " . $conteoFilasProveedor);

$pdf->setXY(122, $currentY - 20); $pdf->Cell(35, 5, "Total $proveedor: ", 0, 0, "R");
$pdf->setXY(157, $currentY - 20); $pdf->Cell(20, 5, "$ " . number_format($totalProveedor, 2, ".", ","), 1, 0, "R");

// //-----------------------------------------------

// Mostrar área de total general
$currentY += 0;
$pdf->setXY($startX, $currentY);

// Verificar si es momento de agregar una nueva página
if ($pdf->GetY() + 30 > $pdf->getPageHeight()) {
    $pdf->AddPage();
    $currentX = $startX;
    $currentY = $startY + 20;
    $pdf->setXY($currentX, $currentY);
}

$pdf->setXY(122, $currentY - 10); $pdf->Cell(35, 5, "Overall total: ", 0, 0, "R");
$pdf->setXY(157, $currentY - 10); $pdf->Cell(20, 5, "$ " . number_format($totalPrecios, 2, ".", ","), 1, 0, "R");

//-----------------------------------------------

// Generar el PDF y enviarlo al navegador
$pdf->Output("Sales by supplier.pdf");

//-----------------------------------------------