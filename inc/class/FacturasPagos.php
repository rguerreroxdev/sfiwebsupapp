<?php

require_once("SQLSrvBD.php");

class FacturasPagos
{
    //-------------------------------------------

    private $conn;

    public $facturaPagoId;
    public $facturaId;
    public $tipoDePagoId;
    public $tipoDePago;
    public $financieraId;
    public $financiera;
    public $contratoFinanciera;
    public $numeroReciboCheque;
    public $monto;
    public $impuesto;
    public $total;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto FacturasPagos
     * 
     * @param SQLSrvBD $conn Conexión a base de datos para realizar acciones sobre registros
     * 
     */
    // Constructor: Recibe conexión a base de datos
    // para realizar acciones sobre tabla
    public function __construct(SQLSrvBD $conn)
    {
        $this->conn = $conn;
        $this->resetPropiedades();
    }

    //-------------------------------------------

    /**
     * Obtener datos de un registro (FACFACTURASPAGOS) por medio de ID
     * 
     * @param int $id ID del registro que será consultado
     * 
     * @return void No se retorna dato, pero se guardan los datos del registro en las propiedades del objeto
     * 
     */
    public function getById(int $id): void
    {
        $sentenciaSql = "
            SELECT
                FP.FACTURAPAGOID
                ,FP.FACTURAID
                ,FP.TIPODEPAGOID
                ,TP.NOMBRE AS TIPODEPAGO
                ,FP.FINANCIERAID
                ,ISNULL(FIN.NOMBRE, '') AS FINANCIERA
                ,FP.CONTRATOFINANCIERA
                ,FP.NUMERORECIBOCHEQUE
                ,FP.MONTO
                ,FP.IMPUESTO
                ,FP.TOTAL
            FROM
                FACFACTURASPAGOS FP
                JOIN FACTIPOSDEPAGO TP ON TP.TIPODEPAGOID=FP.TIPODEPAGOID
                LEFT JOIN FACFINANCIERAS FIN ON FIN.FINANCIERAID=FP.FINANCIERAID
            WHERE
                FP.FACTURAPAGOID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->facturaPagoId = $dato["FACTURAPAGOID"];
            $this->facturaId = $dato["FACTURAID"];
            $this->tipoDePagoId = $dato["TIPODEPAGOID"];
            $this->tipoDePago = $dato["TIPODEPAGO"];
            $this->financieraId = $dato["FINANCIERAID"];
            $this->financiera = $dato["FINANCIERA"];
            $this->contratoFinanciera = $dato["CONTRATOFINANCIERA"];
            $this->numeroReciboCheque = $dato["NUMERORECIBOCHEQUE"];
            $this->monto = $dato["MONTO"];
            $this->impuesto = $dato["IMPUESTO"];
            $this->total = $dato["TOTAL"];
        }
    }

    //-------------------------------------------

    /**
     * Obtener todos los registros de la tabla (FACFACTURASPAGOS)
     * 
     * @param int $facturaId Factura a la que pertenecen las líneas de pagos
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAll(int $facturaId): array
    {
        $sentenciaSql = "
            SELECT
                FP.FACTURAPAGOID
                ,FP.FACTURAID
                ,FP.TIPODEPAGOID
                ,TP.NOMBRE AS TIPODEPAGO
                ,FP.FINANCIERAID
                ,ISNULL(FIN.NOMBRE, '') AS FINANCIERA
                ,FP.CONTRATOFINANCIERA
                ,FP.NUMERORECIBOCHEQUE
                ,FP.MONTO
                ,FP.IMPUESTO
                ,FP.TOTAL
            FROM
                FACFACTURASPAGOS FP
                JOIN FACTIPOSDEPAGO TP ON TP.TIPODEPAGOID=FP.TIPODEPAGOID
                LEFT JOIN FACFINANCIERAS FIN ON FIN.FINANCIERAID=FP.FINANCIERAID
            WHERE
                FP.FACTURAID = ?
            ORDER BY
                FP.FACTURAPAGOID ASC
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        return $datos;
    }
    
    //-------------------------------------------

    /**
     * Obtener registros de la tabla (FACFACTURASPAGOS) con filtros
     * 
     * @param string $filtro Filtros a aplicar, por ejemplo: $filtro = "CAMPO1=0 AND CAMPO2='ALGO'"
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     * Ejemplo de uso de filtro:
     * - $filtro = "CAMPO=0 AND CAMPO='ALGO'"
     * 
     */
    public function getWithFilters(string $filtro): array
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            SELECT
                FP.FACTURAPAGOID
                ,FP.FACTURAID
                ,FP.TIPODEPAGOID
                ,TP.NOMBRE AS TIPODEPAGO
                ,FP.FINANCIERAID
                ,ISNULL(FIN.NOMBRE, '') AS FINANCIERA
                ,FP.CONTRATOFINANCIERA
                ,FP.NUMERORECIBOCHEQUE
                ,FP.MONTO
                ,FP.IMPUESTO
                ,FP.TOTAL
            FROM
                FACFACTURASPAGOS FP
                JOIN FACTIPOSDEPAGO TP ON TP.TIPODEPAGOID=FP.TIPODEPAGOID
                LEFT JOIN FACFINANCIERAS FIN ON FIN.FINANCIERAID=FP.FINANCIERAID
            WHERE
                $filtro
            ORDER BY
                FP.FACTURAPAGOID ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Resetear a valores neutros las propiedades del objeto
     * 
     * @param void No necesita parámetros
     * 
     * @return void No retorna valor sino que quedan actualizadas las propiedades del objeto
     * 
     */
    // Resetear a valores neutros las propiedades del objeto
    private function resetPropiedades(): void
    {
        $this->facturaPagoId = -1;
        $this->facturaId = null;
        $this->tipoDePagoId = null;
        $this->tipoDePago = null;
        $this->financieraId = null;
        $this->financiera = null;
        $this->contratoFinanciera = null;
        $this->numeroReciboCheque = null;
        $this->monto = null;
        $this->impuesto = null;
        $this->total = null;
        $this->mensajeError = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (FACFACTURASPAGOS) existente
     * 
     * @param int $id El id del registro a editar
     * @param array $camposValores Array que contiene campos y valores a ser actualizados [campo, valor, campo, valor...]
     * 
     * @return bool Resultado de actualizar el registro: true: fue editado, false: no fue editado
     * 
     */
    public function editarRegistro(int $id, array $camposValores): bool
    {
        $this->resetPropiedades();

        $updates = "";
        $valores = array();
        for ($i=0; $i < count($camposValores); $i++)
        {
            if($i % 2 == 0 && $camposValores[$i] == "FINANCIERAID" && $camposValores[$i + 1] == -1)
            {
                $updates .= "FINANCIERAID = NULL, ";
                $i += 1;
            }
            else
            {
                $updates .= $i % 2 == 0 ? $camposValores[$i] . " = " : "?, ";
                if ($i % 2 == 1)
                {
                    array_push($valores, $camposValores[$i]);
                }
            }
        }
        $updates = rtrim($updates, ", ");

        array_push($valores, $id);

        $sentenciaSql = "
            UPDATE FACFACTURASPAGOS SET " . $updates . " WHERE FACTURAPAGOID = ?
        ";

        $editado = $this->conn->update($sentenciaSql, $valores);

        if ($editado)
        {
            // TODO: poner en propiedades los datos del registro
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $editado;
    }

    //-------------------------------------------

    /**
     * Agregar un nuevo registro (FACFACTURASPAGOS)
     * 
     * @param int $facturaId Factura a la que se le agrega un pago
     * @param int $tipoDePagoId Tipo de pago que se está aplicando a la factura
     * @param int $financieraId Financiera que interviene en el pago
     * @param string $contratoFinanciera Número de contrato en caso de que el pago sea con financiera
     * @param string $numeroReciboCheque Número de recibo o cheque según el tipo de pago aplicado
     * @param float $monto Monto que se está pagando
     * @param float $impuesto Impuesto aplicado al pago
     * @param float $total Suma de monto más impuesto
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(
        int $facturaId, int $tipoDePagoId, int $financieraId, string $contratoFinanciera,
        string $numeroReciboCheque, float $monto, float $impuesto, float $total
    ): bool
    {
        $this->resetPropiedades();

        if ($financieraId != -1)
        {
            $sentenciaSql = "
                INSERT INTO
                    FACFACTURASPAGOS
                    (FACTURAID, TIPODEPAGOID, FINANCIERAID, CONTRATOFINANCIERA,
                    NUMERORECIBOCHEQUE, MONTO, IMPUESTO, TOTAL)
                VALUES
                    (?, ?, ?, ?,
                    ?, ?, ?, ?)
            ";
            $datoResultado = $this->conn->insert($sentenciaSql,
                                                [
                                                    $facturaId, $tipoDePagoId, $financieraId, $contratoFinanciera,
                                                    $numeroReciboCheque, $monto, $impuesto, $total
                                                ],
                                                true);
        }
        else
        {
            $sentenciaSql = "
                INSERT INTO
                    FACFACTURASPAGOS
                    (FACTURAID, TIPODEPAGOID, FINANCIERAID, CONTRATOFINANCIERA,
                    NUMERORECIBOCHEQUE, MONTO, IMPUESTO, TOTAL)
                VALUES
                    (?, ?, NULL, ?,
                    ?, ?, ?, ?)
            ";
            $datoResultado = $this->conn->insert($sentenciaSql,
                                                [
                                                    $facturaId, $tipoDePagoId, $contratoFinanciera,
                                                    $numeroReciboCheque, $monto, $impuesto, $total
                                                ],
                                                true);
        }

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->facturaPagoId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (FACFACTURASPAGOS)
     * 
     * @param int $id ID del registro a ser eliminado
     * 
     * @return bool Estado final de eliminación: true: fue eliminado, false: no fue eliminado
     * 
     */
    public function eliminarRegistro(int $id): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            DELETE FROM FACFACTURASPAGOS WHERE FACTURAPAGOID = ?
        ";
        
        $eliminado = false;
        $eliminado = $this->conn->delete($sentenciaSql, [$id]);
        
        if (!$eliminado)
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $eliminado;
    }

    //-------------------------------------------

    /**
     * Devuelve el total de pagos de una factura
     * 
     * @param int $facturaId Factura a la que se le calculará el total de pagos
     * 
     * @return float Total de pagos de una factura
     * 
     */
    public function getTotalDePagosPorFactura(int $facturaId): float
    {
        $sentenciaSql = "
            SELECT
                ISNULL(SUM(TOTAL), 0) AS TOTAL
            FROM
                FACFACTURASPAGOS
            WHERE
                FACTURAID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        $total = $datos[0]["TOTAL"];

        return $total;
    }

    //-------------------------------------------

    /**
     * Devuelve el total de montos de una factura
     * 
     * @param int $facturaId Factura a la que se le calculará el total de pagos
     * 
     * @return float Total de montos de una factura
     * 
     */
    public function getTotalDeMontosPorFactura(int $facturaId): float
    {
        $sentenciaSql = "
            SELECT
                ISNULL(SUM(MONTO), 0) AS TOTAL
            FROM
                FACFACTURASPAGOS
            WHERE
                FACTURAID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        $total = $datos[0]["TOTAL"];

        return $total;
    }

    //-------------------------------------------

    /**
     * Devuelve un string con la lista de formas de pago de una factura
     * 
     * @param int $facturaId Factura a la que se le tomaran sus formas de pago
     * 
     * @return string Listado de formas de pago
     * 
     */
    public function getStringFormasDePago(int $facturaId): string
    {
        $sentenciaSql = "
            SELECT
                TP.NOMBRE AS TIPODEPAGO
            FROM
                FACFACTURASPAGOS FP
                JOIN FACTIPOSDEPAGO TP ON TP.TIPODEPAGOID=FP.TIPODEPAGOID
            WHERE
                FP.FACTURAID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        $listaDeFormasDePago = "";
        $arrayFormasDePago = array();
        foreach ($datos as $dato)
        {
            if (!in_array($dato["TIPODEPAGO"], $arrayFormasDePago))
            {
                $listaDeFormasDePago .= $dato["TIPODEPAGO"] . ", ";
                array_push($arrayFormasDePago, $dato["TIPODEPAGO"]);
            }
        }
        $listaDeFormasDePago = rtrim($listaDeFormasDePago, ", ");

        return $listaDeFormasDePago;
    }

    //-------------------------------------------

    /**
     * Devuelve un string con la lista de números de contrato de financieras
     * 
     * @param int $facturaId Factura a la que se le tomaran sus números de contrato
     * 
     * @return string Listado de números de contrato
     * 
     */
    public function getStringContratosFinancieras(int $facturaId): string
    {
        $sentenciaSql = "
            SELECT
                FP.CONTRATOFINANCIERA
            FROM
                FACFACTURASPAGOS FP
            WHERE
                FP.FACTURAID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        $listaDeRecibosCheques = "";
        foreach ($datos as $dato)
        {
            $listaDeRecibosCheques .= trim($dato["CONTRATOFINANCIERA"]) == "" ? "" : trim($dato["CONTRATOFINANCIERA"]) . ", ";
        }
        $listaDeRecibosCheques = rtrim($listaDeRecibosCheques, ", ");

        return $listaDeRecibosCheques;
    }

    //-------------------------------------------

    /**
     * Devuelve un string con la lista de financieras en una factura
     * 
     * @param int $facturaId Factura a la que se le tomaran sus financieras
     * 
     * @return string Listado de financieras
     * 
     */
    public function getStringFinancieras(int $facturaId): string
    {
        $sentenciaSql = "
            SELECT
                FIN.NOMBRE AS FINANCIERA
            FROM
                FACFACTURASPAGOS FP
                JOIN FACFINANCIERAS FIN ON FIN.FINANCIERAID=FP.FINANCIERAID
            WHERE
                FP.FACTURAID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        $listaDeRecibosCheques = "";
        foreach ($datos as $dato)
        {
            $listaDeRecibosCheques .= $dato["FINANCIERA"] . ", ";
        }
        $listaDeRecibosCheques = rtrim($listaDeRecibosCheques, ", ");

        return $listaDeRecibosCheques;
    }

    //-------------------------------------------

    /**
     * Devuelve un string con la lista de números de recibo o cheque de una factura
     * 
     * @param int $facturaId Factura a la que se le tomaran sus números de recibo o cheque
     * 
     * @return string Listado de números de recibo o cheque
     * 
     */
    public function getStringRecibosCheques(int $facturaId): string
    {
        $sentenciaSql = "
            SELECT
                FP.NUMERORECIBOCHEQUE
            FROM
                FACFACTURASPAGOS FP
            WHERE
                FP.FACTURAID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        $listaDeRecibosCheques = "";
        foreach ($datos as $dato)
        {
            $listaDeRecibosCheques .= trim($dato["NUMERORECIBOCHEQUE"]) == "" ? "" : trim($dato["NUMERORECIBOCHEQUE"]) . ", ";
        }
        $listaDeRecibosCheques = rtrim($listaDeRecibosCheques, ", ");

        return $listaDeRecibosCheques;
    }

    //-------------------------------------------
}