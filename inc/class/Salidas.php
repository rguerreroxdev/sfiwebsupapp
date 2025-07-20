<?php

require_once("SQLSrvBD.php");

class Salidas
{
    //-------------------------------------------

    private $conn;

    public $salidaId;
    public $sucursalId;
    public $sucursal;
    public $tipoDeSalidaId;
    public $tipoDeSalida;
    public $fecha;
    public $fechadt;
    public $correlativo;
    public $concepto;
    public $estado;
    public $nombreDeEstado;

    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto Salidas
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
     * Obtener todos los registros de la tabla (INVSALIDAS) con paginación, filtrando las sucursales a que tiene acceso el usuario
     * 
     * @param int $usuarioId Usuario al que se le van a filtrar las sucursales a las que tiene acceso
     * @param int $sucursalId Sucursal que se filtrará para mostrar documentos (-1 muestra todas)
     * @param int $tipoDeSalidaId Tipo de salida con que se filtrará para mostrar documentos (-1 muestra todas)
     * @param string $correlativo Correlativo que se está buscando de forma directa
     * @param string $fechaDesde Fecha inicial para filtrar registros
     * @param string $estado Estado de los documentos para filtrar registros
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllSucursalXUsuarioConPaginacion(int $usuarioId, int $sucursalId, int $tipoDeSalidaId, string $correlativo, string $fechaDesde, string $estado, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "
            WHERE
                S.SUCURSALID IN (SELECT SUCURSALID FROM ACCSUCURSALESXUSUARIO WHERE USUARIOID=$usuarioId)
                AND S.FECHA >= '$fechaDesde' ";

        if (is_numeric($correlativo))
        {
            $condicion .= " AND S.CORRELATIVO = $correlativo";
        }

        if ($sucursalId != -1)
        {
            $condicion .= " AND S.SUCURSALID = $sucursalId";
        }

        if ($tipoDeSalidaId != -1)
        {
            $condicion .= " AND S.TIPODESALIDAID = $tipoDeSalidaId";
        }

        if ($estado != "")
        {
            $condicion .= " AND S.ESTADO = '$estado' ";
        }

        $sentenciaSql = "
            SELECT
                S.SALIDAID,
                S.SUCURSALID,
                SUC.NOMBRE AS SUCURSAL,
                S.TIPODESALIDAID,
                TS.NOMBRE AS TIPODESALIDA,
                CONVERT(VARCHAR, S.FECHA, 101) AS FECHA,
                S.CORRELATIVO,
                S.CONCEPTO,
                S.ESTADO,
                CASE
                    WHEN S.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN S.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN S.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN S.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO,
                S.FECHACREACION,
                S.FECHAMODIFICACION,
                S.USUARIOIDCREACION,
                UC.USUARIO AS USUARIOCREO,
                S.USUARIOIDMODIFICACION,
                UM.USUARIO AS USUARIOMODIFICA,
                (SELECT COUNT(*) FROM INVSALIDASDETALLE SD WHERE SD.SALIDAID=S.SALIDAID) AS PIEZAS
            FROM
                INVSALIDAS S
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=S.SUCURSALID
                JOIN INVTIPOSDESALIDA TS ON TS.TIPODESALIDAID=S.TIPODESALIDAID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=S.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=S.USUARIOIDMODIFICACION

            $condicion
            
            ORDER BY
                S.FECHA DESC,
                S.CORRELATIVO DESC

            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(S.SALIDAID) AS CONTEO
            FROM
                INVSALIDAS S
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=S.SUCURSALID
                JOIN INVTIPOSDESALIDA TS ON TS.TIPODESALIDAID=S.TIPODESALIDAID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=S.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=S.USUARIOIDMODIFICACION

            $condicion
        ";
        $datoConteo = $this->conn->select($sentenciaSql, []);

        $resultado = [
            "total" => $datoConteo[0]["CONTEO"],
            "rows" => $datos
        ];

        return $resultado;
    }

    //-------------------------------------------

    /**
     * Obtener datos de un registro (INVSALIDAS) por medio de ID
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
                S.SALIDAID,
                S.SUCURSALID,
                SUC.NOMBRE AS SUCURSAL,
                S.TIPODESALIDAID,
                TS.NOMBRE AS TIPODESALIDA,
                CONVERT(VARCHAR, S.FECHA, 101) AS FECHA,
                FECHA AS FECHADT,
                S.CORRELATIVO,
                S.CONCEPTO,
                S.ESTADO,
                CASE
                    WHEN S.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN S.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN S.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN S.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO,
                S.FECHACREACION,
                S.FECHAMODIFICACION,
                S.USUARIOIDCREACION,
                UC.USUARIO AS USUARIOCREO,
                S.USUARIOIDMODIFICACION,
                UM.USUARIO AS USUARIOMODIFICA
            FROM
                INVSALIDAS S
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=S.SUCURSALID
                JOIN INVTIPOSDESALIDA TS ON TS.TIPODESALIDAID=S.TIPODESALIDAID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=S.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=S.USUARIOIDMODIFICACION
            WHERE
                S.SALIDAID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->salidaId = $dato["SALIDAID"];
            $this->sucursalId = $dato["SUCURSALID"];
            $this->sucursal = $dato["SUCURSAL"];
            $this->tipoDeSalidaId = $dato["TIPODESALIDAID"];
            $this->tipoDeSalida = $dato["TIPODESALIDA"];
            $this->fecha = $dato["FECHA"];
            $this->fechadt = $dato["FECHADT"];
            $this->correlativo = $dato["CORRELATIVO"];
            $this->concepto = $dato["CONCEPTO"];
            $this->estado = $dato["ESTADO"];
            $this->nombreDeEstado = $dato["NOMBREDEESTADO"];
        
            $this->fechaCreacion = $dato["FECHACREACION"];
            $this->fechaModificacion = $dato["FECHAMODIFICACION"];
            $this->usuarioIdCreacion = $dato["USUARIOIDCREACION"];
            $this->usuarioIdModificacion = $dato["USUARIOIDMODIFICACION"];
            $this->usuarioCreo = $dato["USUARIOCREO"];
            $this->usuarioModifica = $dato["USUARIOMODIFICA"];
        }
    }

    //-------------------------------------------

    /**
     * Iniciar datos para crear un nuevo registro
     * 
     * @param void No necesita parámetros
     * 
     * @return void No retorna valor sino que quedan actualizadas las propiedades del objeto
     * 
     */
    // Resetear a valores neutros las propiedades del objeto
    public function iniciarDatosParaNuevoRegistro(): void
    {
        $fecha = new DateTime();

        $this->salidaId = -1;
        $this->sucursalId = -1;
        $this->sucursal = null;
        $this->tipoDeSalidaId = -1;
        $this->tipoDeSalida = null;
        $this->fecha = $fecha;
        $this->fechadt = $fecha;
        $this->correlativo = -1;
        $this->concepto = null;
        $this->estado = "FOR";
        $this->nombreDeEstado = "FORMULATION";
    
        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Agregar un nuevo registro (INVSALIDAS)
     * 
     * @param int $sucursalId Sucursal en la que se registra la salida
     * @param int $tipoDeSalidaId ID del tipo de salida
     * @param int $fecha Fecha en que se registró la salida
     * @param int $correlativo Número correlativo de la salida
     * @param string $estado Estado con el que quedará guardada la salida
     * @param string $concepto Observaciones registradas para la salida
     * @param int $usuarioId Usuario que está registrando la salida
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $sucursald, int $tipoDeSalidaId, string $fecha, int $correlativo, string $estado, string $concepto, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                INVSALIDAS
                (SUCURSALID, TIPODESALIDAID, FECHA, CORRELATIVO, CONCEPTO, ESTADO,
                FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, ?, ?, ?,
                GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $sucursald, $tipoDeSalidaId, $fecha, $correlativo, $concepto, $estado,
                                                $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->salidaId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Edita un registro (INVSALIDAS) existente
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
            $updates .= $i % 2 == 0 ? $camposValores[$i] . " = " : "?, ";
            if ($i % 2 == 1)
            {
                array_push($valores, $camposValores[$i]);
            }
        }
        $updates = rtrim($updates, ", ");

        array_push($valores, $id);

        $sentenciaSql = "
            UPDATE INVSALIDAS SET " . $updates . " WHERE SALIDAID = ?
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
     * Eliminar un registro (INVSALIDAS)
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
            EXECUTE SPINVELIMINARSALIDA ?
        ";
        $resultado = $this->conn->execute($sentenciaSql, [$id]);
        
        $eliminado = false;
        if (count($resultado) > 0)
        {
            $eliminado = $resultado[0]["EXISTEERROR"] == 0;
            $this->mensajeError = $resultado[0]["MENSAJEDEERROR"];
        }

        return $eliminado;
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
        $this->salidaId = -1;
        $this->sucursalId = null;
        $this->sucursal = null;
        $this->tipoDeSalidaId = null;
        $this->tipoDeSalida = null;
        $this->fecha = null;
        $this->fechadt = null;
        $this->correlativo = null;
        $this->concepto = null;
        $this->estado = null;
        $this->nombreDeEstado = null;

        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Obtener la lista de estados que puede tomar una salida para mostrar en combo
     * (incluye fila de "SELECT")
     * 
     * @param string @primeraOpcion Primer opción a mostrar en el combo, por defecto es "SELECT"
     * 
     * @return array Lista de estados, con el primer elemento "SELECT"  o personalizado
     * 
     */
    public function getListaDeEstadosParaCombo(string $primeraOpcion = "SELECT"): array
    {
        $sentenciaSql = "
            SELECT
                1 AS ORDEN
                ,'' AS ESTADO
                ,'- $primeraOpcion -' AS NOMBRE

            UNION

            SELECT
                2 AS ORDEN
                ,'FOR' AS ESTADO
                ,'FORMULATION' AS NOMBRE

            UNION

            SELECT
                3 AS ORDEN
                ,'CER' AS ESTADO
                ,'CLOSED' AS NOMBRE
            UNION

            SELECT
                4 AS ORDEN
                ,'PRO' AS ESTADO
                ,'POST ORIGIN' AS NOMBRE
            UNION
            
            SELECT
                5 AS ORDEN
                ,'ANU' AS ESTADO
                ,'CANCELED' AS NOMBRE            

            ORDER BY
                ORDEN ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Cambiar de estado una salida: CER, FOR, PRO, ANU
     * 
     * @param int $salidaId Salida que va a cambiar de estado
     * @param int $usuarioId Usuario que realiza el cambio de estado
     * @param string $estado Estado al que va a cambiar la salida
     * 
     * @return bool Estado final del cambio de estado: true: se cambió el estado, false: no fue cambiado
     * 
     */
    public function cambiarEstado(int $salidaId, int $usuarioId, string $estado): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            EXECUTE SPINVCAMBIARESTADOSALIDA ?, ?, ?
        ";
        $resultado = $this->conn->execute($sentenciaSql, [$salidaId, $estado, $usuarioId]);
        
        $cambiado = false;
        if (count($resultado) > 0)
        {
            $cambiado = $resultado[0]["EXISTEERROR"] == 0;
            $this->mensajeError = $resultado[0]["MENSAJEDEERROR"];
        }

        return $cambiado;
    }
    
    //-------------------------------------------
}