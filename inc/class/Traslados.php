<?php

require_once("SQLSrvBD.php");

class Traslados
{
    //-------------------------------------------

    private $conn;

    public $trasladoId;
    public $correlativo;
    public $sucursalOrigenId;
    public $sucursalOrigen;
    public $sucursalDestinoId;
    public $sucursalDestino;
    public $fechaOrigen;
    public $fechaDestino;
    public $estado;
    public $nombreDeEstado;
    public $observaciones;

    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto Traslados
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
     * Obtener todos los registros de la tabla (INVTRASLADOS) con paginación, filtrando las sucursales a que tiene acceso el usuario
     * 
     * @param int $usuarioId Usuario al que se le van a filtrar las sucursales a las que tiene acceso
     * @param int $sucursalOrigenId Sucursal de origen que se filtrará para mostrar documentos (-1 muestra todas)
     * @param int $sucursalDestinoId Sucursal de destino que se filtrará para mostrar documentos (-1 muestra todas)
     * @param string $correlativo Correlativo que se está buscando de forma directa
     * @param string $fechaDesde Fecha inicial para filtrar registros
     * @param string $estado Estado de los documentos para filtrar registros
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllSucursalXUsuarioConPaginacion(int $usuarioId, int $sucursalOrigenId, int $sucursalDestinoId, string $correlativo, string $fechaDesde, string $estado, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "
            WHERE
            (T.SUCURSALORIGENID IN (SELECT SUCURSALID FROM ACCSUCURSALESXUSUARIO WHERE USUARIOID=$usuarioId)
	        OR T.SUCURSALDESTINOID IN (SELECT SUCURSALID FROM ACCSUCURSALESXUSUARIO WHERE USUARIOID=$usuarioId))
            AND T.FECHACREACION >= '$fechaDesde'";

        if (is_numeric($correlativo))
        {
            $condicion .= " AND T.CORRELATIVO = $correlativo";
        }

        if ($sucursalOrigenId != -1)
        {
            $condicion .= " AND T.SUCURSALORIGENID = $sucursalOrigenId";
        }

        if ($sucursalDestinoId != -1)
        {
            $condicion .= " AND T.SUCURSALDESTINOID = $sucursalDestinoId";
        }

        if ($estado != "")
        {
            $condicion .= " AND T.ESTADO = '$estado' ";
        }

        $sentenciaSql = "
            SELECT
                T.TRASLADOID,
                T.CORRELATIVO,
                T.SUCURSALORIGENID,
                SO.NOMBRE AS SUCURSALORIGEN,
                T.SUCURSALDESTINOID,
                SD.NOMBRE AS SUCURSALDESTINO,
                CONVERT(VARCHAR, T.FECHACREACION, 101) AS FECHACREACION,
                T.ESTADO,
                CASE
                    WHEN T.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN T.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN T.ESTADO = 'PRO' THEN 'POST ORIGIN'
                    WHEN T.ESTADO = 'PRD' THEN 'POST DESTINATION'
                    WHEN T.ESTADO = 'LIB' THEN 'REJECTED BY DESTINATION'
                    WHEN T.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO,
                U.USUARIO AS USUARIOCREO,
                (SELECT COUNT(*) FROM INVTRASLADOSDETALLE TD WHERE TD.TRASLADOID=T.TRASLADOID) AS PIEZAS
            FROM
                INVTRASLADOS T
                JOIN CONFSUCURSALES SO ON SO.SUCURSALID=T.SUCURSALORIGENID
                JOIN CONFSUCURSALES SD ON SD.SUCURSALID=T.SUCURSALDESTINOID
                JOIN ACCUSUARIOS U ON U.USUARIOID=T.USUARIOIDCREACION

            $condicion
            
            ORDER BY
                T.FECHACREACION DESC,
	            T.CORRELATIVO DESC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(T.TRASLADOID) AS CONTEO
            FROM
                INVTRASLADOS T
                JOIN CONFSUCURSALES SO ON SO.SUCURSALID=T.SUCURSALORIGENID
                JOIN CONFSUCURSALES SD ON SD.SUCURSALID=T.SUCURSALDESTINOID
                JOIN ACCUSUARIOS U ON U.USUARIOID=T.USUARIOIDCREACION

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
     * Obtener datos de un registro (INVTRASLADOS) por medio de ID
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
                T.TRASLADOID
                ,T.CORRELATIVO
                ,T.SUCURSALORIGENID
                ,SO.NOMBRE AS SUCURSALORIGEN
                ,T.SUCURSALDESTINOID
                ,SD.NOMBRE AS SUCURSALDESTINO
                ,ISNULL(T.FECHAORIGEN, CONVERT(DATETIME, '19000101')) AS FECHAORIGEN
                ,ISNULL(T.FECHADESTINO, CONVERT(DATETIME, '19000101')) AS FECHADESTINO
                ,T.ESTADO
                ,CASE
                    WHEN T.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN T.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN T.ESTADO = 'PRO' THEN 'POST ORIGIN'
                    WHEN T.ESTADO = 'PRD' THEN 'POST DESTINATION'
                    WHEN T.ESTADO = 'LIB' THEN 'REJECTED BY DESTINATION'
                    WHEN T.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO
                ,T.OBSERVACIONES
                ,T.FECHACREACION
                ,T.FECHAMODIFICACION
                ,T.USUARIOIDCREACION
                ,UC.USUARIO AS USUARIOCREO
                ,T.USUARIOIDMODIFICACION
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                INVTRASLADOS T
                JOIN CONFSUCURSALES SO ON SO.SUCURSALID=T.SUCURSALORIGENID
                JOIN CONFSUCURSALES SD ON SD.SUCURSALID=T.SUCURSALDESTINOID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=T.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=T.USUARIOIDMODIFICACION
            WHERE
                TRASLADOID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->trasladoId = $dato["TRASLADOID"];
            $this->correlativo = $dato["CORRELATIVO"];
            $this->sucursalOrigenId = $dato["SUCURSALORIGENID"];
            $this->sucursalOrigen = $dato["SUCURSALORIGEN"];
            $this->sucursalDestinoId = $dato["SUCURSALDESTINOID"];
            $this->sucursalDestino = $dato["SUCURSALDESTINO"];
            $this->fechaOrigen = $dato["FECHAORIGEN"];
            $this->fechaDestino = $dato["FECHADESTINO"];
            $this->estado = $dato["ESTADO"];
            $this->nombreDeEstado = $dato["NOMBREDEESTADO"];
            $this->observaciones = $dato["OBSERVACIONES"];

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
        $fechaDeEmision = new DateTime();
        $fechaDeRecepcion = new DateTime();

        $this->trasladoId = -1;
        $this->correlativo = -1;
        $this->sucursalOrigenId = -1;
        $this->sucursalOrigen = null;
        $this->sucursalDestinoId = -1;
        $this->sucursalDestino = null;
        $this->fechaOrigen = null;
        $this->fechaDestino = null;
        $this->estado = "FOR";
        $this->nombreDeEstado = "FORMULATION";
        $this->observaciones = null;

        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Agregar un nuevo registro (INVTRASLADOS)
     * 
     * @param int $sucursalOrigenId Sucursal de origen (disminuye inventario)
     * @param int $sucursalDestinoId Sucursal de destino (aumenta inventario)
     * @param int $correlativo Número correlativo del traslado
     * @param string $estado Estado con el que quedará guardado el traslado
     * @param string $observaciones Observaciones registradas para el traslado
     * @param int $usuarioId Usuario que está registrando el traslado
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $sucursalOrigenId, int $sucursalDestinoId, int $correlativo, string $estado, string $observaciones, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                INVTRASLADOS
                (SUCURSALORIGENID, SUCURSALDESTINOID, CORRELATIVO, ESTADO, FECHACREACION, FECHAMODIFICACION,
                OBSERVACIONES, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, ?, GETDATE(), GETDATE(),
                ?, ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $sucursalOrigenId, $sucursalDestinoId, $correlativo, $estado,
                                                $observaciones, $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->trasladoId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Edita un registro (INVTRASLADOS) existente
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
            UPDATE INVTRASLADOS SET " . $updates . " WHERE TRASLADOID = ?
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
     * Eliminar un registro (INVTRASLADOS)
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
            EXECUTE SPINVELIMINARTRASLADO ?
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
        $this->trasladoId = -1;
        $this->correlativo = null;
        $this->sucursalOrigenId = null;
        $this->sucursalOrigen = null;
        $this->sucursalDestinoId = null;
        $this->sucursalDestino = null;
        $this->fechaOrigen = null;
        $this->fechaDestino = null;
        $this->estado = null;
        $this->nombreDeEstado = null;
        $this->observaciones = null;

        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Obtener la lista de estados que puede tomar un traslado para mostrar en combo
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
                ,'PRD' AS ESTADO
                ,'POST DESTINATION' AS NOMBRE
            
            UNION

            SELECT
                6 AS ORDEN
                ,'LIB' AS ESTADO
                ,'REJECTED BY DESTINATION' AS NOMBRE
            
            UNION

            SELECT
                7 AS ORDEN
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
     * Cambiar de estado un traslado: CER, FOR, PRO, PRD, LIB, ANU
     * 
     * @param int $trasladoId Traslado que va a cambiar de estado
     * @param int $usuarioId Usuario que realiza el cambio de estado
     * @param string $estado Estado al que va a cambiar el traslado
     * 
     * @return bool Estado final del cambio de estado: true: se cambió el estado, false: no fue cambiado
     * 
     */
    public function cambiarEstado(int $trasladoId, int $usuarioId, string $estado): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            EXECUTE SPINVCAMBIARESTADOTRASLADO ?, ?, ?
        ";
        $resultado = $this->conn->execute($sentenciaSql, [$trasladoId, $estado, $usuarioId]);
        
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