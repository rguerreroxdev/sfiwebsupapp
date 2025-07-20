<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/RecepcionesDeCarga.php");
require_once("../../../../inc/class/RecepcionesDeCargaDetalle.php");
require_once("../../../../libs/fpdf/fpdf.php");

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

class PDF extends FPDF
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

        // Logo
        $this->Image("../../../../imgs/logo.png", 15, 15, 25, 0);
        // Títulos
		$this->SetFont("Arial", "", 8);
		$this->SetXY(15, 15);	$this->Cell(250, 5, $objEmpresa->nombre, 0, 0, "C");
        $this->SetFont("Arial", "B", 8);
        $this->SetXY(15, 20);	$this->Cell(250, 5, "BILL OF LADING", 0, 0, "C");

        // Fecha y hora de generación
        $fechaDeEmision = new DateTime();
        $this->SetFont("Arial", "", 6);
		$this->SetXY(235, 20);	$this->Cell(30, 5, $fechaDeEmision->format("m/d/Y H:i"), 0, 0, 'R');

        // Datos de Recepción
        $this->SetFont("Arial", "", 8);
        $this->SetXY(15, 25);	$this->Cell(150, 4, 'Store: ' . $objRecepcion->sucursal, 0, 0);
        $this->SetXY(15, 29);	$this->Cell(50, 4, 'Correlative: ' . $objRecepcion->correlativo, 0, 0);
        $this->SetXY(80, 29);	$this->Cell(50, 4, 'Status: ' . $objRecepcion->nombreDeEstado, 0, 0);
        $this->SetXY(15, 33);	$this->Cell(150, 4, 'Supplier: ' . $objRecepcion->codigoProveedor . " - " . $objRecepcion->proveedor, 0, 0);
        $this->SetXY(15, 37);	$this->Cell(50, 4, 'Document date: ' . $objRecepcion->fechaDeEmision->format("m/d/Y"), 0, 0);
        $this->SetXY(80, 37);	$this->Cell(50, 4, 'Reception date: ' . $objRecepcion->fechaDeRecepcion->format("m/d/Y"), 0, 0);
        $this->SetXY(15, 41);	$this->Cell(65, 4, 'Stock type: ' . $objRecepcion->tipoDeStock, 0, 0);
        $this->SetXY(80, 41);	$this->Cell(50, 4, 'Stock type %: ' . $objRecepcion->porcentajeTipoDeStock, 0, 0);

        // Encabezado de columnas
        $x = 15;
        $y = 50;
        $this->SetFont("Arial", "", 8);
        $this->SetXY($x, $y);	        $this->Cell(15, 5, 'Quantity', 1, 0, '', false);
        $this->SetXY($x += 15, $y);     $this->Cell(15, 5, 'Product', 1, 0, '', false);
        $this->SetXY($x += 15, $y);     $this->Cell(40, 5, 'Category', 1, 0, '', false);
        $this->SetXY($x += 40, $y);     $this->Cell(25, 5, 'Brand', 1, 0, '', false);
        $this->SetXY($x += 25, $y);     $this->Cell(35, 5, 'Model', 1, 0, '', false);
        $this->SetXY($x += 35, $y);     $this->Cell(60, 5, 'Description', 1, 0, '', false);
        $this->SetXY($x += 60, $y);     $this->Cell(15, 5, 'MSRP ($)', 1, 0, '', false);
        $this->SetXY($x += 15, $y);     $this->Cell(20, 5, 'Stock type', 1, 0, '', false);
        $this->SetXY($x += 20, $y);     $this->Cell(10, 5, '%', 1, 0, 'C', false);
        $this->SetXY($x += 10, $y);     $this->Cell(15, 5, 'Cost ($)', 1, 0, '', false);
        
        $this->Ln(5);   $this->SetX(15);
    }

    // Pié de página
    function footer()
    {
        $this->SetY(-15);
        $this->SetFont("Arial", "I", 8);
        $this->Cell(0, 10, "Page " . $this->PageNo() . "/{nb}", 0, 0, "C");
    }

    // Calcular la altura máxima de una fila
    function Row($data, $widths, $align, $posX = 15)
    {
        $nb = 0;
        // Determinar la cantidad máxima de líneas necesarias en una celda
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($widths[$i], $data[$i]));
        }
        $h = 5 * $nb; // Altura de la fila

        // Asegurarse de que haya suficiente espacio para la fila
        $this->CheckPageBreak($h);

        // Dibujar las celdas de la fila
        for ($i = 0; $i < count($data); $i++) {
            $w = $widths[$i];
            $a = isset($align[$i]) ? $align[$i] : '';

            // Guardar la posición actual
            $x = $this->GetX();
            $y = $this->GetY();

            // Dibujar el borde
            $this->Rect($x, $y, $w, $h);

            // Imprimir el texto
            $this->MultiCell($w, 5, $data[$i], 0, $a);

            // Volver a la posición original
            $this->SetXY($x + $w, $y);
        }

        // Ir a la siguiente línea
        $this->Ln($h);  $this->SetX($posX);
    }

    // Verificar si hay un salto de página
    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    // Calcular el número de líneas necesarias para una celda
    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }

    //-------------------------------------------
}

//-----------------------------------------------

// Crear instancia de objeto PDF
$pdf = new PDF("L", "mm", "Letter");
$pdf->AliasNbPages();
$pdf->AddPage();

// Establecer fuente
$pdf->SetFont("Arial", "", 8);

//-----------------------------------------------

// Obtener datos
$objRecepcionDetalle = new RecepcionesDeCargaDetalle($conn);
$datos = $objRecepcionDetalle->getAll($recepcionDeCargaId);

$arrayDatosLimpios = array();
foreach ($datos as $fila)
{
    $arrayFila = [
        $fila["CANTIDAD"],
        $fila["CODIGOPRODUCTO"],
        $fila["CATEGORIA"],
        $fila["MARCA"],
        $fila["PRODUCTO"],
        $fila["DESCRIPCION"],
        number_format($fila["MSRP"], 2),
        $fila["TIPODESTOCK"],
        number_format($fila["PORCENTAJETIPODESTOCK"], 2),
        number_format($fila["COSTO"], 2)
    ];

    array_push($arrayDatosLimpios, $arrayFila);
}


// Ancho de columnas (Ver los anchos en Header)
$anchoDeColumnas = [15, 15, 40, 25, 35, 60, 15, 20, 10, 15];
$alineacionDeCelda = ["R", "L", "L", "L", "L", "L", "R", "L", "R", "R"];

// Mostrar resultados utilizando la función Row para cálculo de línea en celdas
foreach ($arrayDatosLimpios as $fila)
{
    $pdf->Row($fila, $anchoDeColumnas, $alineacionDeCelda);
}

//-----------------------------------------------

// Generar el PDF y enviarlo al navegador
$pdf->Output();

//-----------------------------------------------