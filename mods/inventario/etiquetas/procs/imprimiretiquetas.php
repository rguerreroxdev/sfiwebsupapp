<?php
//-----------------------------------------------

require_once("../../../../libs/tcpdf/tcpdf.php");

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Inventario.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

$objInventario = new Inventario($conn);

//-----------------------------------------------

// Tipo de datos:
//  - A = Arreglo de IDs
//  - R = ID De recepción

// $tipoDeDatos = $_POST["tipo"];
// $ubicacionInicial = $_POST["ubicacioninicial"];
$tipoDeDatos = $_GET["tip"];
$ubicacionInicial = $_GET["ubi"];

$datos = array();

if ($tipoDeDatos == "R")
{
    //$recepcionDeCargaId = $_POST["rid"];
    $recepcionDeCargaId = $_GET["rid"];
    $datos = $objInventario->getItemsParaEtiquetasDesdeRecepcion($recepcionDeCargaId);
}
else if ($tipoDeDatos == "A")
{
    $arregloDeInventarioID = $_GET["ids"];
    $stringInventarioIDs = implode(", ", $arregloDeInventarioID);
    $datos = $objInventario->getItemsParaEtiquetasDesdeListaDeInventarioIDs($stringInventarioIDs);
}
else
{
    // No es un tipo de emisión válido
    exit();
}


//-----------------------------------------------

class PDF extends TCPDF
{
    //-------------------------------------------

    // Encabezado
    function Header()
    {
    }

    //-------------------------------------------

    // Pie
    function footer()
    {
    }

    //-------------------------------------------
}

//-----------------------------------------------

$pdf = new PDF("P", "mm", "LETTER");
$pdf->AddFont('IDAutomationHC39M','','idautomationhc39m.php');

$pdf->AddPage();

//-----------------------------------------------

// Valores para generar etiquetas
$etiquetaInicial = $ubicacionInicial;
$totalDeEtiquetas = 10;

$xInicial = 4.572;
$yInicial = 12.7;

$anchoDeEtiqueta = 101.6;
$altoDeEtiqueta = 50.8;

$espaciadoHorizontal = 3.556;
$espaciadoVertical = 0;

//-----------------------------------------------

// Datos fijos a mostrar
$fechaDeImpresion = date("m/d/Y");
$codigoQR = "../../../../imgs/QRCode.jpg";
$textoBajoQR = "SUPREME SERVICE REQUEST";
$logoSA = "../../../../imgs/logoetiqueta.jpg";

// Recorrer los ítems de inventario para generar sus etiquetas desde la posición solicitada
$i = $etiquetaInicial;
$conteoEtiquetas = 0;
foreach ($datos as $item)
{
    // Actualizar el conteo de etiquetas
    $conteoEtiquetas++;

    // Calcular posición de etiqueta
    $xActual = $i % 2 == 1 ? $xInicial : $xInicial + $anchoDeEtiqueta + $espaciadoHorizontal;
    $yActual = intval(($i - 1) / 2) * $altoDeEtiqueta + $yInicial;

    // Mostrar rectángulo guía
    //$pdf->Rect($xActual, $yActual, $anchoDeEtiqueta, $altoDeEtiqueta);

    // Obtener datos a mostrar en variables
    $codigoDeInventario = $item["CODIGOINVENTARIO"];
    $modelo = $item["MODELO"];
    $msrp = number_format($item["MSRP"], 0, ".", ",");
    $stockType = $item["TIPODESTOCKDIST"];
    $fechaDeRecepcion = $item["FECHADERECEPCION"];

    // Mostrar códigos de barra
    $pdf->SetFont('IDAutomationHC39M','', 10);
    $pdf->setXY($xActual + 7, $yActual + 15);
    $pdf->Write(0, "*" . $codigoDeInventario . "*");
    $pdf->setXY($xActual + 7, $yActual + 33);
    $pdf->Write(0, "*" . $modelo . "*");

    // Mostrar datos
    $pdf->SetFont('Helvetica','', 7);
    $pdf->setXY($xActual + 59, $yActual + 10);
    $pdf->Cell(40, 0, $codigoDeInventario, 0);
    $pdf->setXY($xActual + 59, $yActual + 13);
    $pdf->Cell(40, 0, "MSRP: $" . $msrp, 0);


    // Mostrar QR
    $pdf->Image($codigoQR, $xActual + 60, $yActual + 20, 20);
    $pdf->setXY($xActual + 58.5, $yActual + 40);
    $pdf->SetFont('Helvetica','', 4);
    $pdf->Cell(40, 0, $textoBajoQR, 0);

    // Mostrar logo
    $pdf->Image($logoSA, $xActual + 83, $yActual + 8, 12);

    // Mostrar stock type y fecha
    $pdf->setXY($xActual + 90, $yActual + 40);
    $pdf->StartTransform();
    $pdf->Rotate(90);
    $pdf->SetFont('Helvetica','', 6);
    //$pdf->Cell(30, 0, $stockType . "   " . $fechaDeImpresion, 0);
    $pdf->Cell(30, 0, $stockType . "   " . $fechaDeRecepcion, 0);
    $pdf->StopTransform();

    // Aumentar contador que define ubicación de etiqueta
    $i++;
    if ($i > $totalDeEtiquetas && $conteoEtiquetas < count($datos))
    {
        $pdf->AddPage();
        $i = 1;
    }
}

//-----------------------------------------------

$pdf->Output("labels.pdf");

//-----------------------------------------------