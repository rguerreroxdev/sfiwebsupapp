<?php

require_once("SQLSrvBD.php");

class RecepcionesDeCargaEstados
{
    //-------------------------------------------

    private $conn;
    
    private $recepcionDeCargaEstadoId;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto RecepcionDeCargaEstados
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
     * Obtener todos los registros de la tabla (INVRECEPCIONESDECARGAESTADOS) con paginación
     * 
     * @param int $recepcionDeCargaId Recepción de carga a la que se le van a buscar los cambios de estado
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAll(int $recepcionDeCargaId): array
    {
        $sentenciaSql = "
            SELECT
                RCE.RECEPCIONDECARGAESTADOID
                ,RCE.RECEPCIONDECARGAID
                ,CONVERT(VARCHAR, RCE.FECHA, 101) + ' ' + CONVERT(VARCHAR, RCE.FECHA, 108) AS FECHA
                ,RCE.ESTADO
                ,CASE
                    WHEN RCE.ESTADO='FOR' THEN 'FORMULATION'
                    WHEN RCE.ESTADO='CER' THEN 'CLOSED'
                    WHEN RCE.ESTADO='PRO' THEN 'POSTED'
                    WHEN RCE.ESTADO='ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO
                ,RCE.DESCRIPCION
                ,RCE.USUARIOID
                ,U.USUARIO
            FROM
                INVRECEPCIONESDECARGASESTADOS RCE
                JOIN ACCUSUARIOS U ON U.USUARIOID=RCE.USUARIOID
            WHERE
                RCE.RECEPCIONDECARGAID = ?
            ORDER BY
                RCE.FECHA DESC
        ";
        $datos = $this->conn->select($sentenciaSql, [$recepcionDeCargaId]);

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
        $this->recepcionDeCargaEstadoId = -1;
    }    

    //-------------------------------------------

    /**
     * Agregar un nuevo registro (INVRECEPCIONESDECARGAESTADOS)
     * 
     * @param int $recepcionDeCargaId Recepción de carga a la que pertenece el cambio de estado a registrar
     * @param string $estado Estado al que se está haciendo cambio
     * @param string $descripcion Descripción del cambio de estado
     * @param int $usuarioId ID del usuario que registró el cambio de estado
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $recepcionDeCargaId, string $estado, string $descripcion, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                INVRECEPCIONESDECARGASESTADOS
                (RECEPCIONDECARGAID, FECHA, ESTADO, DESCRIPCION, USUARIOID)
            VALUES
                (?, GETDATE(), ?, ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $recepcionDeCargaId, $estado, $descripcion, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->recepcionDeCargaEstadoId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------
}