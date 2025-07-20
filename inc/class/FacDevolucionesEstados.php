<?php

require_once("SQLSrvBD.php");

class FacDevolucionesEstados
{
    //-------------------------------------------

    private $conn;
    
    private $devolucionEstadoId;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto FacDevolucionesEstados
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
     * Obtener todos los registros de la tabla (FACDEVOLUCIONESESTADOS) con paginación
     * 
     * @param int $devolucionId Devolución a la que se le van a buscar los cambios de estado
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAll(int $devolucionId): array
    {
        $sentenciaSql = "
            SELECT
                DE.DEVOLUCIONESTADOID
                ,DE.DEVOLUCIONID
                ,CONVERT(VARCHAR, DE.FECHA, 101) + ' ' + CONVERT(VARCHAR, DE.FECHA, 108) AS FECHA
                ,DE.ESTADO
                ,CASE
                    WHEN DE.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN DE.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN DE.ESTADO = 'PRO' THEN 'POSTED'
                END AS NOMBREDEESTADO
                ,DE.DESCRIPCION
                ,DE.USUARIOID
                ,U.USUARIO
            FROM
                FACDEVOLUCIONESESTADOS DE
                JOIN ACCUSUARIOS U ON U.USUARIOID=DE.USUARIOID
            WHERE
                DE.DEVOLUCIONID = ?
            ORDER BY
                DE.FECHA DESC
        ";
        $datos = $this->conn->select($sentenciaSql, [$devolucionId]);

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
        $this->devolucionEstadoId = -1;
        $this->mensajeError = null;
    }    

    //-------------------------------------------

    /**
     * Agregar un nuevo registro (FACDEVOLUCIONESESTADOS)
     * 
     * @param int $devolucionId Devolución a la que pertenece el cambio de estado a registrar
     * @param string $estado Estado al que se está haciendo cambio
     * @param string $descripcion Descripción del cambio de estado
     * @param int $usuarioId ID del usuario que registró el cambio de estado
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $devolucionId, string $estado, string $descripcion, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                FACDEVOLUCIONESESTADOS
                (DEVOLUCIONID, FECHA, ESTADO, DESCRIPCION, USUARIOID)
            VALUES
                (?, GETDATE(), ?, ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $devolucionId, $estado, $descripcion, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->devolucionEstadoId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------
}