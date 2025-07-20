<?php

require_once("SQLSrvBD.php");

class SalidasEstados
{
    //-------------------------------------------

    private $conn;
    
    private $salidaEstadoId;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto SalidasEstados
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
     * Obtener todos los registros de la tabla (INVSALIDASESTADOS) con paginación
     * 
     * @param int $salidaId Salida a la que se le van a buscar los cambios de estado
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAll(int $salidaId): array
    {
        $sentenciaSql = "
            SELECT
                SE.SALIDAESTADOID
                ,SE.SALIDAID
                ,CONVERT(VARCHAR, SE.FECHA, 101) + ' ' + CONVERT(VARCHAR, SE.FECHA, 108) AS FECHA
                ,SE.ESTADO
                ,CASE
                    WHEN SE.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN SE.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN SE.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN SE.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO
                ,SE.DESCRIPCION
                ,SE.USUARIOID
                ,U.USUARIO
            FROM
                INVSALIDASESTADOS SE
                JOIN ACCUSUARIOS U ON U.USUARIOID=SE.USUARIOID
            WHERE
                SE.SALIDAID = ?
            ORDER BY
                SE.FECHA DESC
        ";
        $datos = $this->conn->select($sentenciaSql, [$salidaId]);

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
        $this->salidaEstadoId = -1;
    }    

    //-------------------------------------------

    /**
     * Agregar un nuevo registro (INVSALIDASESTADOS)
     * 
     * @param int $salidaId Salida a la que pertenece el cambio de estado a registrar
     * @param string $estado Estado al que se está haciendo cambio
     * @param string $descripcion Descripción del cambio de estado
     * @param int $usuarioId ID del usuario que registró el cambio de estado
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $salidaId, string $estado, string $descripcion, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                INVSALIDASESTADOS
                (SALIDAID, FECHA, ESTADO, DESCRIPCION, USUARIOID)
            VALUES
                (?, GETDATE(), ?, ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $salidaId, $estado, $descripcion, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->salidaEstadoId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------
}