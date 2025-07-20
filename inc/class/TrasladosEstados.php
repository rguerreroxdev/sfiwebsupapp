<?php

require_once("SQLSrvBD.php");

class TrasladosEstados
{
    //-------------------------------------------

    private $conn;
    
    private $trasladoEstadoId;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto TrasladosEstados
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
     * Obtener todos los registros de la tabla (INVTRASLADOSESTADOS) con paginación
     * 
     * @param int $trasladoId Traslado al que se le van a buscar los cambios de estado
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAll(int $trasladoId): array
    {
        $sentenciaSql = "
            SELECT
                TE.TRASLADOESTADOID
                ,TE.TRASLADOID
                ,CONVERT(VARCHAR, TE.FECHA, 101) + ' ' + CONVERT(VARCHAR, TE.FECHA, 108) AS FECHA
                ,TE.ESTADO
                ,CASE
                    WHEN TE.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN TE.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN TE.ESTADO = 'PRO' THEN 'POST ORIGIN'
                    WHEN TE.ESTADO = 'PRD' THEN 'POST DESTINATION'
                    WHEN TE.ESTADO = 'LIB' THEN 'REJECTED BY DESTINATION'
                    WHEN TE.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO
                ,TE.DESCRIPCION
                ,TE.USUARIOID
                ,U.USUARIO
            FROM
                INVTRASLADOSESTADOS TE
                JOIN ACCUSUARIOS U ON U.USUARIOID=TE.USUARIOID
            WHERE
                TE.TRASLADOID = ?
            ORDER BY
                TE.FECHA DESC
        ";
        $datos = $this->conn->select($sentenciaSql, [$trasladoId]);

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
        $this->trasladoEstadoId = -1;
    }    

    //-------------------------------------------

    /**
     * Agregar un nuevo registro (INVTRASLADOSESTADOS)
     * 
     * @param int $trasladoId Traslado al que pertenece el cambio de estado a registrar
     * @param string $estado Estado al que se está haciendo cambio
     * @param string $descripcion Descripción del cambio de estado
     * @param int $usuarioId ID del usuario que registró el cambio de estado
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $trasladoId, string $estado, string $descripcion, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                INVTRASLADOSESTADOS
                (TRASLADOID, FECHA, ESTADO, DESCRIPCION, USUARIOID)
            VALUES
                (?, GETDATE(), ?, ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $trasladoId, $estado, $descripcion, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->trasladoEstadoId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------
}