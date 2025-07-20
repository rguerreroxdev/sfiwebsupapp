<?php
//-----------------------------------------------

session_start();

require_once("../../../../inc/includes.inc.php");
require_once("../../../../inc/class/Usuario.php");
require_once("../../../../inc/class/Empresa.php");
require_once("../../../../inc/class/FacDevoluciones.php");
require_once("../../../../inc/class/FacDevolucionesDetalle.php");
require_once("../../../../inc/class/FacDevolucionesOtrosDetalles.php");
require_once("../../../../inc/class/FacDevolucionesPagos.php");
require_once("../../../../libs/tcpdf/tcpdf.php");

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

//-----------------------------------------------

$devolucionId = isset($_GET["did"]) && trim($_GET["did"]) != "" ? $_GET["did"] : -1;
$devolucionId = is_numeric($devolucionId) ? $devolucionId : -1;

// Validar si existe la devolución
$objDevolucion = new FacDevoluciones($conn);
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
        $objDevolucion = new FacDevoluciones($conn);
        $objDevolucion->getById($devolucionId);

        $objEmpresa = new Empresa($conn);
        $objEmpresa->getDatos();

        $this->setCellPaddings(1, 0, 1, 0);

        // Logo
        $this->Image("../../../../imgs/logojpg.jpg", 15, 25, 40, 0);

        // Teléfonos y dirección de tienda
        $this->SetFont("Helvetica", "", 5);
        $this->Image("../../../../imgs/iconotelefono.jpg", 60, 25, 5, 0);
        $this->SetXY(65, 25);	$this->Cell(50, 2, "Office number", 0, 0, "L");
        $this->SetXY(65, 27);	$this->Cell(50, 2, $objDevolucion->sucursalTelefono, 0, 0, "L");
        $this->Image("../../../../imgs/iconodireccion.jpg", 60, 31, 5, 0);
        $this->SetXY(65, 31);	$this->Cell(50, 2, $objDevolucion->sucursalDireccion, 0, 0, "L");
        $this->SetXY(65, 33);	$this->Cell(50, 2, $objDevolucion->sucursalDireccionComplemento . " " . $objDevolucion->sucursalCodigoPostal, 0, 0, "L");
        $this->Image("../../../../imgs/iconotelefono.jpg", 95, 25, 5, 0);
        $this->SetXY(100, 25);	$this->Cell(50, 2, "Service request number", 0, 0, "L");
        $this->SetXY(100, 27);	$this->Cell(50, 2, $objDevolucion->sucursalTelefonoServicio, 0, 0, "L");

        // Número de devolución
        $this->SetFont("Helvetica", "B", 14);
        $this->SetTextColor(192, 0, 0);
        $correlativoConPrefijo = strtoupper($objDevolucion->prefijoCorrelativoDevolucion) . "-" . $objDevolucion->correlativoDevolucion;
        $this->SetXY(132, 23);	$this->Cell(70, 8, "CREDIT MEMO " . $correlativoConPrefijo, 1, 0, "C");
        $this->SetFont("Helvetica", "", 10);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(0, 0, 0);
        $this->SetXY(132, 31);	$this->Cell(70, 5, "Retail", 1, 0, "C", true);

        // Notas
        // $this->SetFont("Helvetica", "", 9);
        // $this->SetTextColor(192, 0, 0);
        // $this->SetXY(132, 36);	$this->Cell(70, 5, "ACCOUNT NOTES", 0, 0, "C");
        // $this->SetFont("Helvetica", "B", 5);
        // $this->SetTextColor(0, 0, 0);
        // $this->SetXY(132, 41); $this->MultiCell(70, 2, strtoupper($objDevolucion->notas), 0, "L", '', true);
        // $actualY = $this->GetY();
        // $this->Rect(132, 41, 70, $actualY - 41);

        // En lugar de notas, mostrar datos de factura devuelta
        $this->SetFont("Helvetica", "", 9);
        $this->SetTextColor(192, 0, 0);
        $this->SetXY(132, 36);	$this->Cell(70, 5, "RELATED INVOICES", 0, 0, "C");
        $this->SetFont("Helvetica", "B", 5);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont("Helvetica", "BU", 5);
        $this->SetXY(132, 41); $this->Cell(70, 3, "Returned invoice", 0, 0, "L");
        $this->SetFont("Helvetica", "B", 5);
        $this->SetXY(132, 44); $this->Cell(70, 3, "Invoice number: ", 0, 0, "L");
        $this->SetXY(132, 47); $this->Cell(70, 3, "Invoice date: ", 0, 0, "L");
        $this->SetXY(150, 44); $this->Cell(70, 3, $objDevolucion->prefijoDeCorrelativoDeFactura . "-" . $objDevolucion->correlativoDeFactura, 0, 0, "L");
        $this->SetXY(150, 47); $this->Cell(70, 3, $objDevolucion->fechaDeFactura, 0, 0, "L");
        // Mostrar factura sustituta si existe
        $altoDeRectangulo = 9;
        if ($objDevolucion->facturaSustituyeId > 0)
        {
            $this->SetFont("Helvetica", "BU", 5);
            $this->SetXY(132, 50); $this->Cell(70, 3, "Substitute invoice:", 0, 0, "L");
            $this->SetFont("Helvetica", "B", 5);
            $this->SetXY(132, 53); $this->Cell(70, 3, "Invoice number: ", 0, 0, "L");
            $this->SetXY(132, 56); $this->Cell(70, 3, "Invoice date: ", 0, 0, "L");
            $this->SetXY(150, 53); $this->Cell(70, 3, $objDevolucion->prefijoDeCorrelativoSustituye . "-" . $objDevolucion->CorrelativoFacturaSustituye, 0, 0, "L");
            $this->SetXY(150, 56); $this->Cell(70, 3, $objDevolucion->fechaFacturaSustituye, 0, 0, "L");

            $altoDeRectangulo = 18;
        }
        $this->Rect(132, 41, 70, $altoDeRectangulo);

        // Cliente títulos
        $this->SetFont("Helvetica", "B", 7);
        $this->SetTextColor(192, 0, 0);
        $this->SetXY(45, 37);	$this->Cell(23, 3, "BILLED TO", 0, 0, "L");
        $this->SetFont("Helvetica", "B", 6);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY(15, 41);	$this->Cell(23, 3, "Customer Name:", 0, 0, "C");
        $this->SetXY(15, 44);	$this->Cell(23, 3, "Address:", 0, 0, "C");
        $this->SetXY(15, 47);	$this->Cell(23, 3, "Addres Continued:", 0, 0, "C");
        $this->SetXY(15, 50);	$this->Cell(23, 3, "Phone:", 0, 0, "C");
        $this->SetXY(15, 53);	$this->Cell(23, 3, "Email:", 0, 0, "C");
        // Cliente Datos
        $this->SetFont("Helvetica", "", 6);

        $maxTextomm = 70;
        $this->SetXY(38, 41);	$this->Cell(70, 3, strtoupper($objDevolucion->clienteNombre), 0, 0, "L");
        $this->SetXY(38, 44);	$this->Cell(70, 3, $this->recortarTexto(strtoupper($objDevolucion->clienteDireccion), $maxTextomm), 0, 0, "L");
        $this->SetXY(38, 47);	$this->Cell(70, 3, $this->recortarTexto(strtoupper($objDevolucion->clienteDireccionComplemento) . " " . $objDevolucion->clienteCodigoPostal, $maxTextomm), 0, 0, "L");
        $this->SetXY(38, 50);	$this->Cell(70, 3, $objDevolucion->clienteTelefono, 0, 0, "L");
        $this->SetXY(38, 53);	$this->Cell(70, 3, $objDevolucion->clienteCorreoElectronico, 0, 0, "L");
        // Dibujar línea derecha en gris
        $this->SetDrawColor(150, 150, 150);
        $this->Line(38, 41, 38, 56);    // Agrego de nuevo esta línea por bug que se ve más delgada

        // Bloque de datos 1
        // Datos para Bloque de datos 1
        $objDevolucionPagos = new FacDevolucionesPagos($conn);
        $listaDeFormasDepago = strtoupper($objDevolucionPagos->getStringFormasDePago($devolucionId));
        $listaDeRecibosCheques = $objDevolucionPagos->getStringRecibosCheques($devolucionId);
        $listaDeRecibosCheques = strlen($listaDeRecibosCheques) > 30 ? substr($listaDeRecibosCheques, 0, 30) : $listaDeRecibosCheques;
        // Títulos
        $this->SetFont("Helvetica", "", 6);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY(15, 60);	$this->Cell(23, 3, "Credit memo No:", 0, 0, "L");
        $this->SetXY(15, 63);	$this->Cell(23, 3, "Credit memo Date:", 0, 0, "L");
        $this->SetXY(15, 66);	$this->Cell(23, 3, "Customer ID:", 0, 0, "L");
        $this->SetXY(15, 69);	$this->Cell(23, 3, "Credit memo created By:", 0, 0, "L");
        $this->SetXY(15, 72);	$this->Cell(23, 3, "Form of Payment:", 0, 0, "L");
        $this->SetXY(15, 75);	$this->Cell(23, 3, "Card Receipt # / Check #:", 0, 0, "L");
        $this->SetXY(15, 78);	$this->Cell(23, 3, "Previos Customer:", 0, 0, "L");
        // Datos
        $horaDeEmision = $objDevolucion->fechaCreacion->format("H:i");
        $esClientePrevio = $objDevolucion->esClientePrevio == 1 ? "YES" : "NO";
        $this->SetFont("Helvetica", "B", 6);
        $this->SetFillColor(240, 240, 240);
        $maxTextomm = 30;
        $this->SetXY(40, 60);	$this->Cell(30, 3, $correlativoConPrefijo, 0, 0, "C", true);
        $this->SetXY(40, 63);	$this->Cell(30, 3, $objDevolucion->fechaDevolucion . "  " . $horaDeEmision, 0, 0, "C", true);
        $this->SetXY(40, 66);	$this->Cell(30, 3, $objDevolucion->clienteCodigo, 0, 0, "C", true);
        $this->SetFont("Helvetica", "", 6);
        $this->SetXY(40, 69);	$this->Cell(30, 3, $this->recortarTexto(strtoupper($objDevolucion->usuarioCreoNombre), $maxTextomm), 0, 0, "C");
        $this->SetXY(40, 72);	$this->Cell(30, 3, $listaDeFormasDepago, 0, 0, "C");
        $this->SetXY(40, 75);	$this->Cell(30, 3, $listaDeRecibosCheques, 0, 0, "C");
        $this->SetXY(40, 78);	$this->Cell(30, 3, $esClientePrevio, 0, 0, "C");
        // Dibujar línea derecha en gris
        $this->SetDrawColor(150, 150, 150);
        $this->Line(40, 60, 40, 81);
        $this->Line(38, 41, 38, 56);

        // Bloque de datos 2
        // Datos para Bloque de datos 2
        $listaDeFinancieras = strtoupper($objDevolucionPagos->getStringFinancieras($devolucionId));
        $listaDeContratos = $objDevolucionPagos->getStringContratosFinancieras($devolucionId);
        $listaDeContratos = strlen($listaDeContratos) > 33 ? substr($listaDeContratos, 0, 33) : $listaDeContratos;
        $personaDeReferencia = strtoupper($objDevolucion->personaDeReferencia);
        $personaDeReferencia = strlen($personaDeReferencia) > 33 ? substr($personaDeReferencia, 0, 33) : $personaDeReferencia;
        // Títulos
        $this->SetFont("Helvetica", "", 6);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY(70, 60);	$this->Cell(30, 3, "Financial Entity:", 0, 0, "L");
        $this->SetXY(70, 63);	$this->Cell(30, 3, "Finance contract number:", 0, 0, "L");
        $this->SetXY(70, 66);	$this->Cell(30, 3, "Referral Platform:", 0, 0, "L");
        $this->SetXY(70, 69);	$this->Cell(30, 3, "Referral Person:", 0, 0, "L");
        $this->SetXY(70, 72);	$this->Cell(30, 3, "Invoice Salesperson:", 0, 0, "L");
        $this->SetXY(70, 75);	$this->Cell(30, 3, "Estimated pickup date:", 0, 0, "L");
        $this->SetXY(70, 78);	$this->Cell(30, 3, "3rd Party Delivery or Self-Pickup:", 0, 0, "L");
        $this->SetXY(70, 81);	$this->Cell(30, 3, "Had Installation (Extra Charge):", 0, 0, "L");
        $this->SetXY(70, 84);	$this->Cell(30, 3, "Had Accesories (Extra Charge):", 0, 0, "L");
        // Datos
        $agregarInstalacion = $objDevolucion->agregarInstalacion == 1 ? "YES" : "NO";
        $agregarAccesorios = $objDevolucion->agregarAccesorios == 1 ? "YES" : "NO";
        $this->SetFont("Helvetica", "", 6);
        $this->SetTextColor(0, 0, 0);
        $maxTextomm = 35;
        $this->SetXY(102, 60);	$this->Cell(35, 3, $listaDeFinancieras, 0, 0, "C");
        $this->SetXY(102, 63);	$this->Cell(35, 3, $listaDeContratos, 0, 0, "C");
        $this->SetXY(102, 66);	$this->Cell(35, 3, strtoupper($objDevolucion->plataformaDeReferencia), 0, 0, "C");
        $this->SetXY(102, 69);	$this->Cell(35, 3, $this->recortarTexto($personaDeReferencia, $maxTextomm), 0, 0, "C");
        $this->SetXY(102, 72);	$this->Cell(35, 3, $this->recortarTexto(strtoupper($objDevolucion->usuarioVendedorNombre), $maxTextomm), 0, 0, "C");
        $this->SetXY(102, 75);	$this->Cell(35, 3, $objDevolucion->fechaDeRetiro, 0, 0, "C");
        $this->SetXY(102, 78);	$this->Cell(35, 3, strtoupper($objDevolucion->formaDeRetiro), 0, 0, "C");
        $this->SetXY(102, 81);	$this->Cell(35, 3, $agregarInstalacion, 0, 0, "C");
        $this->SetXY(102, 84);	$this->Cell(35, 3, $agregarAccesorios, 0, 0, "C");
        // Dibujar línea derecha en gris
        $this->SetDrawColor(150, 150, 150);
        $this->Line(102, 60, 102, 87);

        // Para firma de cliente
        $this->SetXY(137, 68);	$this->Cell(45, 3, "Customer Signature:", "", 0, "L");
        $this->SetDrawColor(0, 0, 0);
        $this->SetXY(137, 75);	$this->Cell(40, 3, "", "B", 0, "C");

        // Concepto de devolución
        $this->SetXY(15, 88);   $this->Cell(20, 3, "Notes:", 0, "L");
        $this->SetXY(25, 88);   $this->MultiCell(150, 3, $objDevolucion->concepto, 0, "L", '', true);
        $this->Rect(25, 88, 150, 6);

        // Cantidad de ítems
        $cantidadDeItems = $objDevolucion->getTotalDeItems($devolucionId);
        $this->SetFont("Helvetica", "B", 8);
        $this->SetFillColor(240, 240, 240);
        $this->SetXY(178, 87);	$this->Cell(24, 3, "Items", 0, 0, "C");
        $this->SetXY(178, 90);	$this->Cell(24, 4, $cantidadDeItems, 0, 0, "C", true);

        // Encabezado de columnas
        $x = 15;
        $y = 94;
        $this->SetFont("Helvetica", "B", 7);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(166, 166, 166);
        $this->SetXY($x, $y);           $this->Cell(23, 5, 'Inventory Number', 1, 0, 'C', true);
        $this->SetFillColor(196, 96, 96);
        $this->SetXY($x += 23, $y);     $this->Cell(30, 5, 'BRAND', 1, 0, 'C', true);
        $this->SetFillColor(166, 166, 166);
        $this->SetXY($x += 30, $y);     $this->Cell(30, 5, 'MODEL', 1, 0, 'C', true);
        $this->SetFillColor(196, 96, 96);
        $this->SetXY($x += 30, $y);     $this->Cell(25, 5, 'MSRP', 1, 0, 'C', true);
        $this->SetFillColor(166, 166, 166);
        $this->SetXY($x += 25, $y);     $this->Cell(35, 5, 'DESCRIPTION', 1, 0, 'C', true);
        $this->SetFillColor(196, 96, 96);
        $this->SetXY($x += 35, $y);     $this->Cell(20, 5, 'WARRANTY', 1, 0, 'C', true);
        $this->SetFillColor(166, 166, 166);
        $this->SetXY($x += 20, $y);     $this->Cell(24, 5, 'PRICE', 1, 0, 'C', true);
        $this->Ln(5);   $this->SetX(15);
    }

    // Pié de página
    function footer()
    {
        // Texto de garantía
        // $y = -50;
        // $this->SetFont("Helvetica", "B", 6);
        // $this->SetXY(15, $y);       $this->Cell(187, 3, "1 Year Warranty through us on all appliances that does not have Manufacture Warranty. Warranty extends to 30 Miles of distance from the purchase location, customers are asked", 0, 0, "C");
        // $this->SetXY(15, $y += 3);  $this->Cell(187, 3, "to bring the items if the distance exceeds 30 miles. 1 Year start at the day of pick up/delivery. No refunds, sold as-is, all sales are final. No service calls for cosmetic damages, or", 0, 0, "C");
        // $this->SetXY(15, $y += 3);  $this->Cell(187, 3, "to correct the installation of your product when self installed. A deductible of $70 will be charged at the moment you schedule a technician visit. Warranty doesn't cover service", 0, 0, "C");
        // $this->SetXY(15, $y += 3);  $this->Cell(187, 3, "calls which don't involve malfunction or defects in the appliances. Please wait 24 hrs after delivery/pick-up to plug in your Refrigerator to allow oil fluids to settle in. I am aware", 0, 0, "C");
        // $this->SetXY(15, $y += 3);  $this->Cell(187, 3, "that delivery and installation services are performed by a third-party company, which operates independently from Supreme Appliances. Supreme Appliances is not responsible for", 0, 0, "C");
        // $this->SetXY(15, $y += 3);  $this->Cell(187, 3, "any damages, incidents or issues that may occur during the delivery or installation. This includes, but is not limited to, cosmetic damages to appliances, damage to property, or", 0, 0, "C");
        // $this->SetXY(15, $y += 3);  $this->Cell(187, 3, "improper installation. Any concern or claims must be addressed with the service provider.", 0, 0, "C");
        // $this->SetFont("Helvetica", "B", 10);
        // $this->SetXY(15, $y += 6);  $this->Cell(187, 3, "Thank you for your business!", 0, 0, "C");

        if ($this->getNumPages() > 1)
        {
            $this->SetY(-15);
            $this->SetFont("Helvetica", "I", 7);
            $this->Cell(0, 10, "Page " . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, "C");
        }
    }

    // Recortar texto según largo definido en mm
    public function recortarTexto(string $textoOriginal, int $anchoEnMilimetros)
    {
        // Si se pasa, lo vamos recortando
        $texto = $textoOriginal;
        while ($this->GetStringWidth($texto . '...') > $anchoEnMilimetros && mb_strlen($texto) > 0) {
            $texto = mb_substr($texto, 0, -1);
        }

        if ($texto !== $textoOriginal) {
            $texto .= '...';
        }

        return $texto;
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

// Obtener datos de detalles
$objDevolucionDetalle = new FacDevolucionesDetalle($conn);
$datos = $objDevolucionDetalle->getAll($devolucionId);

$arrayDatosLimpios = array();
$filaConteo = 0;
foreach ($datos as $fila)
{
    $filaConteo++;

    $modelo = $pdf->recortarTexto(trim($fila["MODELO"]), 30);
    //$modelo = strlen($modelo) > 25 ? substr($modelo, 0, 25) : $modelo;

    $descripcion = $pdf->recortarTexto(strtoupper(trim($fila["CATEGORIA"])), 35);
    //$descripcion = strlen($descripcion) > 30 ? substr($descripcion, 0, 30) : $descripcion;

    $garantia = trim($fila["TIPODEGARANTIA"]);
    $garantia = strtolower($garantia) == "none" ? "N/A" : $garantia;

    $arrayFila = [
        $fila["CODIGOINVENTARIO"],
        strtoupper($fila["MARCA"]),
        $modelo,
        "$ " . number_format($fila["MSRP"], 2, ".", ","),
        $descripcion,
        $garantia,
        "$ " . number_format($fila["PRECIO"], 2, ".", ",")
    ];

    array_push($arrayDatosLimpios, $arrayFila);
}

// Ancho de columnas (Ver los anchos en Header)
$anchoDeColumnas = [23, 30, 30, 25, 35, 20, 24];
$alineacionDeCelda = ["C", "C", "C", "C", "C", "C", "R"];

// Preparar valores para controlar posición de fila y columna en que se muestran datos
$startX = 15;
$startY = 99;
$currentX = $startX;
$currentY = $startY;
$pdf->setXY($currentX, $currentY);
$maximoY = $currentY;
$altoDeFila = 4;

// Recorrer los datos para mostrarlos
foreach ($arrayDatosLimpios as $fila)
{
    // En cada conjunto de datos, se recorren las columnas que se van a mostrar
    for ($i = 0; $i < count($fila); $i++)
    {
        $pdf->SetFont("Helvetica", "B", 6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($anchoDeColumnas[$i], $altoDeFila, $fila[$i], 1, 0, $alineacionDeCelda[$i], false);

        $currentX += $anchoDeColumnas[$i];
        $pdf->setXY($currentX, $currentY);
    }

    // Calcular la siguiente fila
    $currentX = $startX;
    $currentY += $altoDeFila;
    $pdf->setXY($currentX, $currentY);
}

// Obtener datos de otrosdetalles
$objDevolucionOtroDetalle = new FacDevolucionesOtrosDetalles($conn);
$datos = $objDevolucionOtroDetalle->getAll($devolucionId);

$arrayDatosLimpios = array();
foreach ($datos as $fila)
{
    $filaConteo++;

    $modelo = $pdf->recortarTexto(trim($fila["MODELO"]), 30);
    //$modelo = strlen($modelo) > 25 ? substr($modelo, 0, 25) : $modelo;

    $descripcion = $pdf->recortarTexto(strtoupper(trim($fila["DESCRIPCION"])), 35);
    //$descripcion = strlen($descripcion) > 30 ? substr($descripcion, 0, 30) : $descripcion;

    $arrayFila = [
        $fila["PRODUCTOCODIGO"],
        strtoupper($fila["MARCA"]),
        $modelo,
        " ",
        $descripcion,
        "N/A",
        "$ " . number_format($fila["PRECIO"], 2, ".", ",")
    ];

    array_push($arrayDatosLimpios, $arrayFila);
}

// Recorrer los datos para mostrarlos
foreach ($arrayDatosLimpios as $fila)
{
    // En cada conjunto de datos, se recorren las columnas que se van a mostrar
    for ($i = 0; $i < count($fila); $i++)
    {
        $pdf->SetFont("Helvetica", "B", 6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($anchoDeColumnas[$i], $altoDeFila, $fila[$i], 1, 0, $alineacionDeCelda[$i], false);

        $currentX += $anchoDeColumnas[$i];
        $pdf->setXY($currentX, $currentY);
    }

    // Calcular la siguiente fila
    $currentX = $startX;
    $currentY += $altoDeFila;
    $pdf->setXY($currentX, $currentY);
}

// Llenar de filas vacías
$maximoFilas = 25;

for ($j = $filaConteo; $j < $maximoFilas; $j++)
{
    // En cada fila, se recorren las columnas que se van a mostrar
    for ($i = 0; $i < 7; $i++)
    {
        $pdf->SetFont("Helvetica", "B", 6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($anchoDeColumnas[$i], $altoDeFila, "", 1, 0, $alineacionDeCelda[$i], false);

        $currentX += $anchoDeColumnas[$i];
        $pdf->setXY($currentX, $currentY);
    }

    // Calcular la siguiente fila
    $currentX = $startX;
    $currentY += $altoDeFila;
    $pdf->setXY($currentX, $currentY);
}

$pagosY = $currentY;

//-----------------------------------------------

// Mostrar totales
$pdf->SetFont("Helvetica", "B", 8);
$pdf->setXY(154, $currentY);                $pdf->Cell(24, $altoDeFila+1, "Total before taxes", 0, 0, "R", false);
$pdf->setXY(178, $currentY);                $pdf->Cell(24, $altoDeFila+1, "$ " . number_format($objDevolucion->totalAntesDeImpuesto, 2, ".", ","), 1, 0, "R", false);
$pdf->setXY(154, $currentY += $altoDeFila+1); $pdf->Cell(24, $altoDeFila+1, "+ Sales Tax @ " . $objDevolucion->impuestoPorcentaje . "%", 0, 0, "R", false);
$pdf->setXY(178, $currentY);                $pdf->Cell(24, $altoDeFila+1, "$ " . number_format($objDevolucion->impuesto, 2, ".", ","), 1, 0, "R", false);
$pdf->setXY(154, $currentY += $altoDeFila+1); $pdf->Cell(24, $altoDeFila+1, "Total + Taxes", 0, 0, "R", false);
$pdf->setXY(178, $currentY);                $pdf->Cell(24, $altoDeFila+1, "$ " . number_format($objDevolucion->totalConImpuesto, 2, ".", ","), 1, 0, "R", false);
$pdf->setXY(154, $currentY += $altoDeFila+1); $pdf->Cell(24, $altoDeFila+1, "- Finance Company Tax", 0, 0, "R", false);
$pdf->setXY(178, $currentY);                $pdf->Cell(24, $altoDeFila+1, "$ " . number_format($objDevolucion->impuestoFinanciera, 2, ".", ","), 1, 0, "R", false);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFillColor(192, 0, 0);
$pdf->setXY(154, $currentY += $altoDeFila+1); $pdf->Cell(24, $altoDeFila+2, "TOTAL", 0, 0, "C", true);
$pdf->SetFont("Helvetica", "B", 9);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFillColor(0, 0, 0);
$pdf->setXY(178, $currentY);                $pdf->Cell(24, $altoDeFila+2, "$ " . number_format($objDevolucion->totalFinal, 2, ".", ","), 1, 0, "R", true);

//-----------------------------------------------

// Mostrar cuadro de pagos
$altoDeFila = 3;
$pdf->SetFont("Helvetica", "", 6);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(15, $pagosY + $altoDeFila);     $pdf->Cell(25, $altoDeFila, "Form of payment", 1, 0, "L");
$pdf->SetXY(40, $pagosY + $altoDeFila);     $pdf->Cell(25, $altoDeFila, "Financial entity", 1, 0, "L");
$pdf->SetXY(65, $pagosY + $altoDeFila);     $pdf->Cell(20, $altoDeFila, "Amount", 1, 0, "L");
$pdf->SetXY(85, $pagosY + $altoDeFila);     $pdf->Cell(20, $altoDeFila, "Taxes", 1, 0, "L");
$pdf->SetXY(105, $pagosY + $altoDeFila);    $pdf->Cell(20, $altoDeFila, "Amount+Taxes", 1, 0, "L");
$pagosY += $altoDeFila;

$objDevolucionPagos = new FacDevolucionesPagos($conn);
$listaDePagos = $objDevolucionPagos->getAll($devolucionId);

foreach($listaDePagos as $pago)
{
    $pagosY += $altoDeFila;
    $pdf->SetXY(15, $pagosY);     $pdf->Cell(25, $altoDeFila, $pago["TIPODEPAGO"], 1, 0, "L");
    $pdf->SetXY(40, $pagosY);     $pdf->Cell(25, $altoDeFila, $pago["FINANCIERA"], 1, 0, "L");
    $pdf->SetXY(65, $pagosY);     $pdf->Cell(20, $altoDeFila, "$ " . number_format($pago["MONTO"], 2, ".", ","), 1, 0, "R");
    $pdf->SetXY(85, $pagosY);     $pdf->Cell(20, $altoDeFila, "$ " . number_format($pago["IMPUESTO"], 2, ".", ","), 1, 0, "R");
    $pdf->SetXY(105, $pagosY);    $pdf->Cell(20, $altoDeFila, "$ " . number_format($pago["TOTAL"], 2, ".", ","), 1, 0, "R");
}

// Mostrar texto cuando sea una vista previa
$mostrarMarcaDeAgua = false;
$textoMarcaDeAgua = "";
if ($objDevolucion->estado == "FOR" || $objDevolucion->estado == "CER")
{
    $mostrarMarcaDeAgua = true;
    $textoMarcaDeAgua = "PREVIEW";
}
// if ($objDevolucion->estado == "ANU")
// {
//     $mostrarMarcaDeAgua = true;
//     $textoMarcaDeAgua = "CANCELED";
// }
if ($mostrarMarcaDeAgua)
{
    $pdf->SetFont('Helvetica', 'B', 50);
    $pdf->StartTransform();
    $pdf->Rotate(45, $pdf->getPageWidth() / 2, $pdf->getPageHeight() / 2);
    $pdf->SetTextColor(200, 200, 200);

    $pdf->Text(
        ($pdf->getPageWidth() - 100) / 2, 
        ($pdf->getPageHeight() - 10) / 2, 
        $textoMarcaDeAgua
    );

    $pdf->StopTransform();
}



//-----------------------------------------------

// Generar el PDF y enviarlo al navegador
$pdf->Output("Credit memo " . $objDevolucion->prefijoCorrelativoDevolucion . "-" . $objDevolucion->correlativoDevolucion . ".pdf");

//-----------------------------------------------