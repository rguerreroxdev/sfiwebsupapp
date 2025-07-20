<?php

require_once("SQLSrvBD.php");

class DevolucionesInv
{
    //-------------------------------------------

    private $conn;

    public $devolucionId;
    public $sucursalId;
    public $sucursal;
    public $tipoDeDevolucionId;
    public $tipoDeDevolucion;
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
     * Instancia un objeto DevolucionesInv
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
     * Obtener todos los registros de la tabla (INVDEVOLUCIONES) con paginación, filtrando las sucursales a que tiene acceso el usuario
     * 
     * @param int $usuarioId Usuario al que se le van a filtrar las sucursales a las que tiene acceso
     * @param int $sucursalId Sucursal que se filtrará para mostrar documentos (-1 muestra todas)
     * @param int $tipoDeDevolucionId Tipo de devolución con que se filtrará para mostrar documentos (-1 muestra todas)
     * @param string $correlativo Correlativo que se está buscando de forma directa
     * @param string $fechaDesde Fecha inicial para filtrar registros
     * @param string $estado Estado de los documentos para filtrar registros
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllSucursalXUsuarioConPaginacion(int $usuarioId, int $sucursalId, int $tipoDeDevolucionId, string $correlativo, string $fechaDesde, string $estado, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "
            WHERE
                D.SUCURSALID IN (SELECT SUCURSALID FROM ACCSUCURSALESXUSUARIO WHERE USUARIOID=$usuarioId)
                AND D.FECHA >= '$fechaDesde' ";

        if (is_numeric($correlativo))
        {
            $condicion .= " AND D.CORRELATIVO = $correlativo";
        }

        if ($sucursalId != -1)
        {
            $condicion .= " AND D.SUCURSALID = $sucursalId";
        }

        if ($tipoDeDevolucionId != -1)
        {
            $condicion .= " AND D.TIPODEDEVOLUCIONID = $tipoDeDevolucionId";
        }

        if ($estado != "")
        {
            $condicion .= " AND D.ESTADO = '$estado' ";
        }

        $sentenciaSql = "
            SELECT
                D.DEVOLUCIONID,
                D.SUCURSALID,
                SUC.NOMBRE AS SUCURSAL,
                D.TIPODEDEVOLUCIONID,
                TD.NOMBRE AS TIPODEDEVOLUCION,
                CONVERT(VARCHAR, D.FECHA, 101) AS FECHA,
                D.CORRELATIVO,
                D.CONCEPTO,
                D.ESTADO,
                CASE
                    WHEN D.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN D.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN D.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN D.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO,
                D.FECHACREACION,
                D.FECHAMODIFICACION,
                D.USUARIOIDCREACION,
                UC.USUARIO AS USUARIOCREO,
                D.USUARIOIDMODIFICACION,
                UM.USUARIO AS USUARIOMODIFICA,
                (SELECT COUNT(*) FROM INVDEVOLUCIONESDETALLE DD WHERE DD.DEVOLUCIONID=D.DEVOLUCIONID) AS PIEZAS
            FROM
                INVDEVOLUCIONES D
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=D.SUCURSALID
                JOIN INVTIPOSDEDEVOLUCION TD ON TD.TIPODEDEVOLUCIONID=D.TIPODEDEVOLUCIONID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=D.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=D.USUARIOIDMODIFICACION

            $condicion
            
            ORDER BY
                D.FECHA DESC,
                D.CORRELATIVO DESC

            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(D.DEVOLUCIONID) AS CONTEO
            FROM
                INVDEVOLUCIONES D
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=D.SUCURSALID
                JOIN INVTIPOSDEDEVOLUCION TD ON TD.TIPODEDEVOLUCIONID=D.TIPODEDEVOLUCIONID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=D.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=D.USUARIOIDMODIFICACION

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
     * Obtener datos de un registro (INVDEVOLUCIONES) por medio de ID
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
                D.DEVOLUCIONID,
                D.SUCURSALID,
                SUC.NOMBRE AS SUCURSAL,
                D.TIPODEDEVOLUCIONID,
                TD.NOMBRE AS TIPODEDEVOLUCION,
                CONVERT(VARCHAR, D.FECHA, 101) AS FECHA,
                FECHA AS FECHADT,
                D.CORRELATIVO,
                D.CONCEPTO,
                D.ESTADO,
                CASE
                    WHEN D.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN D.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN D.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN D.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO,
                D.FECHACREACION,
                D.FECHAMODIFICACION,
                D.USUARIOIDCREACION,
                UC.USUARIO AS USUARIOCREO,
                D.USUARIOIDMODIFICACION,
                UM.USUARIO AS USUARIOMODIFICA
            FROM
                INVDEVOLUCIONES D
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=D.SUCURSALID
                JOIN INVTIPOSDEDEVOLUCION TD ON TD.TIPODEDEVOLUCIONID=D.TIPODEDEVOLUCIONID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=D.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=D.USUARIOIDMODIFICACION
            WHERE
                D.DEVOLUCIONID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->devolucionId = $dato["DEVOLUCIONID"];
            $this->sucursalId = $dato["SUCURSALID"];
            $this->sucursal = $dato["SUCURSAL"];
            $this->tipoDeDevolucionId = $dato["TIPODEDEVOLUCIONID"];
            $this->tipoDeDevolucion = $dato["TIPODEDEVOLUCION"];
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

        $this->devolucionId = -1;
        $this->sucursalId = -1;
        $this->sucursal = null;
        $this->tipoDeDevolucionId = -1;
        $this->tipoDeDevolucion = null;
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
     * Agregar un nuevo registro (INVDEVOLUCIONES)
     * 
     * @param int $sucursalId Sucursal en la que se registra la devolución
     * @param int $tipoDeDevolucionId ID del tipo de devolución
     * @param int $fecha Fecha en que se registró la devolución
     * @param int $correlativo Número correlativo de la devolución
     * @param string $estado Estado con el que quedará guardada la devolución
     * @param string $concepto Observaciones registradas para la devolución
     * @param int $usuarioId Usuario que está registrando la devolución
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $sucursald, int $tipoDeDevolucionId, string $fecha, int $correlativo, string $estado, string $concepto, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                INVDEVOLUCIONES
                (SUCURSALID, TIPODEDEVOLUCIONID, FECHA, CORRELATIVO, CONCEPTO, ESTADO,
                FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, ?, ?, ?,
                GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $sucursald, $tipoDeDevolucionId, $fecha, $correlativo, $concepto, $estado,
                                                $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->devolucionId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Edita un registro (INVDEVOLUCIONES) existente
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
            UPDATE INVDEVOLUCIONES SET " . $updates . " WHERE DEVOLUCIONID = ?
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
     * Eliminar un registro (INVDEVOLUCIONES)
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
            EXECUTE SPINVELIMINARDEVOLUCION ?
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
        $this->devolucionId = -1;
        $this->sucursalId = null;
        $this->sucursal = null;
        $this->tipoDeDevolucionId = null;
        $this->tipoDeDevolucion = null;
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
     * Obtener la lista de estados que puede tomar una devolución para mostrar en combo
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
     * Cambiar de estado una devolución: CER, FOR, PRO, ANU
     * 
     * @param int $devolucionId Devolución que va a cambiar de estado
     * @param int $usuarioId Usuario que realiza el cambio de estado
     * @param string $estado Estado al que va a cambiar la devolución
     * 
     * @return bool Estado final del cambio de estado: true: se cambió el estado, false: no fue cambiado
     * 
     */
    public function cambiarEstado(int $devolucionId, int $usuarioId, string $estado): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            EXECUTE SPINVCAMBIARESTADODEVOLUCION ?, ?, ?
        ";
        $resultado = $this->conn->execute($sentenciaSql, [$devolucionId, $estado, $usuarioId]);
        
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