<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Usuario.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/Facturas.php");
require_once("../../../../inc/class/FacturasDetalle.php");
require_once("../../../../inc/class/FacturasOtrosDetalles.php");
require_once("../../../../inc/class/FacturasPagos.php");
require_once("../../../../libs/tcpdf/tcpdf.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$facturaId = isset($_GET["fid"]) && trim($_GET["fid"]) != "" ? $_GET["fid"] : -1;
$facturaId = is_numeric($facturaId) ? $facturaId : -1;

// Validar si existe la factura
$objFactura = new Facturas($conn);
$objFactura->getById($facturaId);

if ($objFactura->facturaId == -1)
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
        $facturaId = isset($_GET["fid"]) && trim($_GET["fid"]) != "" ? $_GET["fid"] : -1;
        $facturaId = is_numeric($facturaId) ? $facturaId : -1;
        $objFactura = new Facturas($conn);
        $objFactura->getById($facturaId);

        $objEmpresa = new Empresa($conn);
        $objEmpresa->getDatos();

        $this->setCellPaddings(1, 0, 1, 0);

        // Logo
        $this->Image("../../../../imgs/logojpg.jpg", 15, 25, 40, 0);
        // Nombre de sucursal bajo el logo
        $this->SetFont("Helvetica", "", 7);
        $this->SetXY(15, 38);   $this->Cell(40, 4, strtoupper($objFactura->sucursalNombre), 1, 0, "C");

        // Código de barra de número de factura
        $codigoDeBarraCorrelativo = "*" . strtoupper($objFactura->prefijoDeCorrelativo) . "-" . $objFactura->correlativo . "*";
        $this->SetFont('IDAutomationHC39M','', 10);
        $this->SetXY(15, 25);   $this->Cell(0, 14, $codigoDeBarraCorrelativo, 0, 0, "C");

        // Cantidad de ítems
        $cantidadDeItems = $objFactura->getTotalDeItems($facturaId);
        $this->SetFont("Helvetica", "B", 19);
        $this->SetXY(156, 25);  $this->Cell(50, 8, "Items QTY", 0, 0, "C");
        $this->SetXY(156, 33);  $this->Cell(50, 8, $cantidadDeItems, 0, 0, "C");

        // Nombre de cliente
        $this->SetFont("Helvetica", "B", 14);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(0, 0, 0);
        $this->SetXY(15, 43);   $this->Cell(0, 8, "HOLD FOR CUSTOMER - " . strtoupper($objFactura->clienteNombre), 0, 0, "C", true);

        // Cuadro que cubre toda la página
        $this->Rect(15, 20, 191, 245);
    }

    // Pié de página
    function footer()
    {
        if ($this->getNumPages() > 0)
        {
            $this->SetY(-15);
            $this->SetFont("Helvetica", "I", 6);
            $this->Cell(0, 10, "Page " . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, "C");
        }
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

// Datos de factura - Bloque 1
$objUsuarioVenta = new Usuario($conn);
$objUsuarioVenta->getById($objFactura->usuarioIdVendedor);
$pdf->SetFont("Helvetica", "B", 8);
// Títulos
$pdf->SetXY(15, 53);    $pdf->Cell(30, 5, "INVOICE DATE:", 0, 0, "C");
$pdf->SetXY(15, 58);    $pdf->Cell(30, 5, "INVOICE NUMBER:", 0, 0, "C");
$pdf->SetXY(15, 63);    $pdf->Cell(30, 5, "SALESPERSON:", 0, 0, "C");
$pdf->SetXY(15, 68);    $pdf->Cell(30, 5, "CUSTOMER NAME:", 0, 0, "C");
$pdf->SetXY(15, 73);    $pdf->Cell(30, 5, "ADDRESS:", 0, 0, "C");
$pdf->SetXY(15, 83);    $pdf->Cell(30, 5, "PHONE:", 0, 0, "C");
// Datos
$pdf->SetXY(45, 53);    $pdf->Cell(60, 5, $objFactura->fecha, 0, 0, "C");
$pdf->SetXY(45, 58);    $pdf->Cell(60, 5, strtoupper($objFactura->prefijoDeCorrelativo) . "-" . $objFactura->correlativo, 0, 0, "C");
$pdf->SetXY(45, 63);    $pdf->Cell(60, 5, strtoupper($objUsuarioVenta->nombreCompleto), 0, 0, "C");
$pdf->SetXY(45, 68);    $pdf->Cell(60, 5, strtoupper($objFactura->clienteNombre), 0, 0, "C");
$pdf->SetXY(45, 73);    $pdf->Cell(60, 5, strtoupper($objFactura->clienteDireccion), 0, 0, "C");
$pdf->SetXY(45, 78);    $pdf->Cell(60, 5, strtoupper($objFactura->clienteDireccionComplemento) . " " . $objFactura->clienteCodigoPostal, 0, 0, "C");
$pdf->SetXY(45, 83);    $pdf->Cell(60, 5, $objFactura->clienteTelefono, 0, 0, "C");

// Datos de factura - Bloque 2
$objFacturaPagos = new FacturasPagos($conn);
$listaDeFormasDepago = strtoupper($objFacturaPagos->getStringFormasDePago($facturaId));
$agregarInstalacion = $objFactura->agregarInstalacion == 1 ? "YES" : "NO";
$agregarAccesorios = $objFactura->agregarAccesorios == 1 ? "YES" : "NO";
// Títulos
$pdf->SetXY(115, 53);   $pdf->Cell(30, 5, "FORM OF PAYMENT:", 0, 0, "C");
$pdf->SetXY(115, 58);   $pdf->Cell(30, 5, "INSTALLATION NEEDED:", 0, 0, "C");
$pdf->SetXY(115, 68);   $pdf->Cell(30, 5, "ACCESORIES NEEDED:", 0, 0, "C");
$pdf->SetXY(115, 73);   $pdf->Cell(30, 5, "PICK UP / DELIV. DATE:", 0, 0, "C");
$pdf->SetXY(115, 78);   $pdf->Cell(30, 5, "SELF PICK UP / DELIVERY:", 0, 0, "C");
// Datos
$pdf->SetXY(145, 53);   $pdf->Cell(60, 5, $listaDeFormasDepago, 0, 0, "C");
$pdf->SetXY(145, 58);   $pdf->Cell(60, 5, $agregarInstalacion, 0, 0, "C");
$pdf->SetXY(145, 68);   $pdf->Cell(60, 5, $agregarAccesorios, 0, 0, "C");
$pdf->SetXY(145, 73);   $pdf->Cell(60, 5, $objFactura->fechaDeRetiro, 0, 0, "C");
$pdf->SetXY(145, 78);   $pdf->Cell(60, 5, $objFactura->formaDeRetiro, 0, 0, "C");

// Bloque 3: notas e íconos
$pdf->SetFont("Helvetica", "B", 7);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFillColor(0, 0, 0);
$pdf->SetXY(115, 83);   $pdf->Cell(30, 5, "NOTES", 1, 0, "C", true);
$pdf->SetFont("Helvetica", "B", 7);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(115, 93);   $pdf->Cell(30, 5, "3RD PARTY DELIVERY?", 0, 0, "C");
$pdf->Image("../../../../imgs/iconocarita.jpg", 118, 103, 7, 0);
$pdf->Image("../../../../imgs/iconocamion.jpg", 132, 103, 7, 0);
$pdf->Rect(145, 83, 60, 40);

// Bloque 4: debajo del cuadro de notas
$pdf->SetXY(145, 124);  $pdf->Cell(30, 5, "IS DELIVERY PAID?    YES", 0, 0, "L");
$pdf->SetXY(178, 124);  $pdf->Cell(10, 5, "", 1, 0, "L");
$pdf->SetXY(190, 124);  $pdf->Cell(5, 5, "NO", 0, 0, "L");
$pdf->SetXY(195, 124);  $pdf->Cell(10, 5, "", 1, 0, "L");
$pdf->SetXY(145, 130);  $pdf->Cell(25, 5, "DID CUSTOMER BUY:", 0, 0, "L");
$pdf->SetXY(145, 135);  $pdf->Cell(20, 5, "WATER HOSES", 0, 0, "L");
$pdf->SetXY(165, 135);  $pdf->Cell(10, 5, "", 1, 0, "L");
$pdf->SetXY(145, 140);  $pdf->Cell(20, 5, "POWER CORD", 0, 0, "L");
$pdf->SetXY(165, 140);  $pdf->Cell(10, 5, "", 1, 0, "L");
$pdf->SetXY(145, 145);  $pdf->Cell(20, 5, "VENT DUCT", 0, 0, "L");
$pdf->SetXY(165, 145);  $pdf->Cell(10, 5, "", 1, 0, "L");
$pdf->SetXY(145, 150);  $pdf->Cell(20, 5, "WATER LINE", 0, 0, "L");
$pdf->SetXY(165, 150);  $pdf->Cell(10, 5, "", 1, 0, "L");

// Bloque 5: Espacio para firma y fecha
$pdf->SetXY(15, 125);   $pdf->Cell(30, 5, "RECEIVED BY:", 0, 0, "C");
$pdf->SetXY(45, 125);   $pdf->Cell(60, 5, "", "B", 0, "C");
$pdf->SetXY(15, 145);   $pdf->Cell(30, 5, "DATE:", 0, 0, "C");
$pdf->SetXY(45, 145);   $pdf->Cell(60, 5, "", "B", 0, "C");

// Título de inventario
$pdf->SetFont("Helvetica", "BU", 8);
$pdf->SetXY(15, 155);  $pdf->Cell(0, 5, "INVENTORY NUMBER(S)", 0, 0, "C");

//-----------------------------------------------

// Obtener datos de detalles
$objFacturaDetalle = new FacturasDetalle($conn);
$datos = $objFacturaDetalle->getAll($facturaId);

$arrayDatosLimpios = array();
foreach ($datos as $fila)
{
    $arrayFila = [
        strtoupper($fila["CATEGORIA"]),
        $fila["CODIGOINVENTARIO"],
    ];

    array_push($arrayDatosLimpios, $arrayFila);
}

// Ancho de columnas
$anchoDeColumnas = [50, 60];
$alineacionDeCelda = ["C", "L"];

// Preparar valores para controlar posición de fila y columna en que se muestran datos
$startX = 15;
$startY = 160;
$startNuevaPaginaY = 63;
$currentX = $startX;
$currentY = $startY;
$pdf->setXY($currentX, $currentY);
$maximoY = $currentY;
$altoDeFila = 15;

// Recorrer los datos para mostrarlos
foreach ($arrayDatosLimpios as $fila)
{
    // Verificar si es momento de agregar una nueva columna o página
    if ($pdf->GetY() + 30 > $pdf->getPageHeight())
    {
        if ($currentX < 110)
        {
            $currentX = 110;
            $currentY = $startY;
            $startX = 110;
        }
        else
        {
            $pdf->AddPage();
            $startX = 15;
            $currentX = $startX;
            $currentY = $startNuevaPaginaY;
            $startY = $startNuevaPaginaY;
        }

        $pdf->setXY($currentX, $currentY);
    }

    $pdf->SetFont("Helvetica", "B", 10);
    $pdf->setXY($currentX, $currentY + 3);
    $pdf->Cell($anchoDeColumnas[0],4, $fila[1], 0, 0, $alineacionDeCelda[0]);

    $pdf->setXY($currentX, $currentY + 6);
    $pdf->Cell($anchoDeColumnas[0], 5, $fila[0], 0, 0, $alineacionDeCelda[0]);

    $pdf->SetFont('IDAutomationHC39M','', 10);
    $codigoDeBarra = "*" . $fila[1] . "*";
    $pdf->setXY($currentX + $anchoDeColumnas[0], $currentY);
    $pdf->Cell($anchoDeColumnas[1], $altoDeFila, $codigoDeBarra, 0, 0, $alineacionDeCelda[1]);

    // Calcular la siguiente fila
    $currentX = $startX;
    $currentY += $altoDeFila;
    $pdf->setXY($currentX, $currentY);
}

// Obtener datos de otrosdetalles
$objFacturaOtroDetalle = new FacturasOtrosDetalles($conn);
$datos = $objFacturaOtroDetalle->getAll($facturaId);

$arrayDatosLimpios = array();
foreach ($datos as $fila)
{
    $arrayFila = [
        strtoupper($fila["DESCRIPCION"]),
        $fila["PRODUCTOCODIGO"],
    ];

    array_push($arrayDatosLimpios, $arrayFila);
}

// Recorrer los datos para mostrarlos
foreach ($arrayDatosLimpios as $fila)
{
    // Verificar si es momento de agregar una nueva página
    if ($pdf->GetY() + 30 > $pdf->getPageHeight())
    {
        if ($currentX < 110)
        {
            $currentX = 110;
            $currentY = $startY;
            $startX = 110;
        }
        else
        {
            $pdf->AddPage();
            $startX = 15;
            $currentX = $startX;
            $currentY = $startNuevaPaginaY;
            $startY = $startNuevaPaginaY;
        }

        $pdf->setXY($currentX, $currentY);
    }

    $pdf->SetFont("Helvetica", "B", 10);
    $pdf->setXY($currentX, $currentY + 3);
    $pdf->Cell($anchoDeColumnas[0], 4, $fila[1], 0, 0, $alineacionDeCelda[0]);

    $pdf->setXY($currentX, $currentY + 6);
    $pdf->Cell($anchoDeColumnas[0], 5, $fila[0], 0, 0, $alineacionDeCelda[0]);

    $pdf->SetFont('IDAutomationHC39M','', 10);
    $codigoDeBarra = "*" . $fila[1] . "*";
    $pdf->setXY($currentX + $anchoDeColumnas[0], $currentY);
    $pdf->Cell($anchoDeColumnas[1], $altoDeFila, $codigoDeBarra, 0, 0, $alineacionDeCelda[1]);

    // Calcular la siguiente fila
    $currentX = $startX;
    $currentY += $altoDeFila;
    $pdf->setXY($currentX, $currentY);
}

//-----------------------------------------------

// Generar el PDF y enviarlo al navegador
$pdf->Output("Invoice hold " . $objFactura->prefijoDeCorrelativo . "-" . $objFactura->correlativo . ".pdf");

//-----------------------------------------------