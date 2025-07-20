<?php

require_once("SQLSrvBD.php");

class FacturasEstados
{
    //-------------------------------------------

    private $conn;
    
    private $facturaEstadoId;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto FacturasEstados
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
     * Obtener todos los registros de la tabla (FACFACTURASESTADOS) con paginación
     * 
     * @param int $facturaId Factura a la que se le van a buscar los cambios de estado
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAll(int $facturaId): array
    {
        $sentenciaSql = "
            SELECT
                FE.FACTURAESTADOID
                ,FE.FACTURAID
                ,CONVERT(VARCHAR, FE.FECHA, 101) + ' ' + CONVERT(VARCHAR, FE.FECHA, 108) AS FECHA
                ,FE.ESTADO
                ,CASE
                    WHEN FE.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN FE.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN FE.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN FE.ESTADO = 'ANU' THEN 'CANCELED'
                    WHEN FE.ESTADO = 'DEV' THEN 'RETURNED'
                END AS NOMBREDEESTADO
                ,FE.DESCRIPCION
                ,FE.USUARIOID
                ,U.USUARIO
            FROM
                FACFACTURASESTADOS FE
                JOIN ACCUSUARIOS U ON U.USUARIOID=FE.USUARIOID
            WHERE
                FE.FACTURAID = ?
            ORDER BY
                FE.FECHA DESC
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

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
        $this->facturaEstadoId = -1;
        $this->mensajeError = null;
    }    

    //-------------------------------------------

    /**
     * Agregar un nuevo registro (FACFACTURASESTADOS)
     * 
     * @param int $facturaId Factura a la que pertenece el cambio de estado a registrar
     * @param string $estado Estado al que se está haciendo cambio
     * @param string $descripcion Descripción del cambio de estado
     * @param int $usuarioId ID del usuario que registró el cambio de estado
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $facturaId, string $estado, string $descripcion, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                FACFACTURASESTADOS
                (FACTURAID, FECHA, ESTADO, DESCRIPCION, USUARIOID)
            VALUES
                (?, GETDATE(), ?, ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $facturaId, $estado, $descripcion, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->facturaEstadoId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------
}