<?php

require_once("SQLSrvBD.php");

class FacConfiguracionesPorSucursal
{
    //-------------------------------------------

    private $conn;

    public $configuracionPorSucursalId;
    public $sucursalId;
    public $sucursal;
    public $prefijoDeCorrelativo;
    public $siguienteCorrelativo;
    public $impuestosPorcentaje;

    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto FacConfiguracionesPorSucursal
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
     * Obtener datos de un registro (FACCONFIGURACIONESPORSUCURSAL) por medio de ID
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
                CS.CONFIGURACIONPORSUCURSALID
                ,CS.SUCURSALID
                ,S.NOMBRE AS SUCURSAL
                ,CS.PREFIJODECORRELATIVO
                ,CS.SIGUIENTECORRELATIVO
                ,CS.IMPUESTOSPORCENTAJE
                ,CS.FECHACREACION
                ,CS.FECHAMODIFICACION
                ,CS.USUARIOIDCREACION
                ,CS.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACCONFIGURACIONESPORSUCURSAL CS
                JOIN CONFSUCURSALES S ON S.SUCURSALID=CS.SUCURSALID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=CS.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=CS.USUARIOIDMODIFICACION
            WHERE
                CONFIGURACIONPORSUCURSALID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->configuracionPorSucursalId = $dato["CONFIGURACIONPORSUCURSALID"];
            $this->sucursalId = $dato["SUCURSALID"];
            $this->sucursal = $dato["SUCURSAL"];
            $this->prefijoDeCorrelativo = $dato["PREFIJODECORRELATIVO"];
            $this->siguienteCorrelativo = $dato["SIGUIENTECORRELATIVO"];
            $this->impuestosPorcentaje = $dato["IMPUESTOSPORCENTAJE"];
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
     * Obtener datos de un registro (FACCONFIGURACIONESPORSUCURSAL) por medio de la sucursal
     * 
     * @param int $sucursalId ID de la sucursal a la que se le consulta su configuración
     * 
     * @return void No se retorna dato, pero se guardan los datos del registro en las propiedades del objeto
     * 
     */
    public function getBySucursalId(int $sucursalId): void
    {
        $sentenciaSql = "
            SELECT
                CS.CONFIGURACIONPORSUCURSALID
                ,CS.SUCURSALID
                ,S.NOMBRE AS SUCURSAL
                ,CS.PREFIJODECORRELATIVO
                ,CS.SIGUIENTECORRELATIVO
                ,CS.IMPUESTOSPORCENTAJE
                ,CS.FECHACREACION
                ,CS.FECHAMODIFICACION
                ,CS.USUARIOIDCREACION
                ,CS.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACCONFIGURACIONESPORSUCURSAL CS
                JOIN CONFSUCURSALES S ON S.SUCURSALID=CS.SUCURSALID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=CS.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=CS.USUARIOIDMODIFICACION
            WHERE
                S.SUCURSALID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$sucursalId]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->configuracionPorSucursalId = $dato["CONFIGURACIONPORSUCURSALID"];
            $this->sucursalId = $dato["SUCURSALID"];
            $this->sucursal = $dato["SUCURSAL"];
            $this->prefijoDeCorrelativo = $dato["PREFIJODECORRELATIVO"];
            $this->siguienteCorrelativo = $dato["SIGUIENTECORRELATIVO"];
            $this->impuestosPorcentaje = $dato["IMPUESTOSPORCENTAJE"];
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
     * Obtener todos los registros de la tabla (FACCONFIGURACIONESPORSUCURSAL) con paginación
     * 
     * @param string $buscar Texto a utilizar para realizar filtro en la consulta
     * @param int $sucursalId Sucursal por la que se va a filtrar la consulta
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllConPaginacion(string $buscar, int $sucursalId, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "";
        if (trim($buscar) != "")
        {
            $condicion = "
            WHERE
                (S.NOMBRE LIKE '%$buscar%')
            ";
        }

        if ($sucursalId != -1)
        {
            $condicion .= strlen($condicion) > 0 ? " AND S.SUCURSALID = $sucursalId " : " WHERE S.SUCURSALID = $sucursalId ";
        }

        $sentenciaSql = "
            SELECT
                CS.CONFIGURACIONPORSUCURSALID
                ,CS.SUCURSALID
                ,S.NOMBRE AS SUCURSAL
                ,CS.PREFIJODECORRELATIVO
                ,CS.SIGUIENTECORRELATIVO
                ,CS.IMPUESTOSPORCENTAJE
            FROM
                FACCONFIGURACIONESPORSUCURSAL CS
                JOIN CONFSUCURSALES S ON S.SUCURSALID=CS.SUCURSALID
            
            $condicion

            ORDER BY
                S.NOMBRE ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(CS.CONFIGURACIONPORSUCURSALID) AS CONTEO
            FROM
                FACCONFIGURACIONESPORSUCURSAL CS
                JOIN CONFSUCURSALES S ON S.SUCURSALID=CS.SUCURSALID

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
     * Obtener registros de la tabla (FACCONFIGURACIONESPORSUCURSAL) con filtros
     * 
     * @param string $filtro Filtros a aplicar a la consulta, ejemplo: $filtro = "CAMPO=0 AND CAMPO='ALGO'"
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
                CS.CONFIGURACIONPORSUCURSALID
                ,CS.SUCURSALID
                ,S.NOMBRE AS SUCURSAL
                ,CS.PREFIJODECORRELATIVO
                ,CS.SIGUIENTECORRELATIVO
                ,CS.IMPUESTOSPORCENTAJE
            FROM
                FACCONFIGURACIONESPORSUCURSAL CS
                JOIN CONFSUCURSALES S ON S.SUCURSALID=CS.SUCURSALID
                $filtro
            ORDER BY
                S.NOMBRE ASC
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
        $this->configuracionPorSucursalId = -1;
        $this->sucursalId = null;
        $this->sucursal = null;
        $this->prefijoDeCorrelativo = null;
        $this->siguienteCorrelativo = null;
        $this->impuestosPorcentaje = null;
        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (FACCONFIGURACIONESPORSUCURSAL) existente
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
            UPDATE FACCONFIGURACIONESPORSUCURSAL SET " . $updates . " WHERE CONFIGURACIONPORSUCURSALID = ?
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
     * Agregar un nuevo registro (FACCONFIGURACIONESPORSUCURSAL)
     * 
     * @param int $sucursalId Sucursal a la que pertenece la configuración
     * @param string $prefijoDeCorrelativo Prefijo que se le agrega a los correlativos de las facturas
     * @param int $siguienteCorrelativo Siguiente correlativo a emitir cuando se procese una factura
     * @param float $impuestosPorcentaje Porcentaje de impuestos que se aplica a cada factura
     * @param int $usuarioId ID del ussuario que está creando el registro
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $sucursalId, string $prefijoDeCorrelativo, int $siguienteCorrelativo, float $impuestosPorcentaje, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO FACCONFIGURACIONESPORSUCURSAL
                (SUCURSALID, PREFIJODECORRELATIVO, SIGUIENTECORRELATIVO, IMPUESTOSPORCENTAJE, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, ?, GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $sucursalId, $prefijoDeCorrelativo, $siguienteCorrelativo, $impuestosPorcentaje, $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->configuracionPorSucursalId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (FACCONFIGURACIONESPORSUCURSAL)
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
            DELETE FROM FACCONFIGURACIONESPORSUCURSAL WHERE CONFIGURACIONPORSUCURSALID = ?
        ";
        $eliminado = $this->conn->delete($sentenciaSql, [$id]);
        
        if (!$eliminado)
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $eliminado;
    }

    //-------------------------------------------

    /**
     * Obtener las sucursales que todavía no tienen configuración
     * (incluye fila de "SELECT")
     * 
     * @param string $primeraOpcion Opción a mostrar en el combo en la parte superior, por defecto es "SELECT"
     * 
     * @return array Todos los registros encontrados en la tabla en orden alfabético con la primer opción "SELECT"
     * 
     */
    public function getListaDeSucursalesParaCombo(string $primeraOpcion = "SELECT", ): array
    {
        $sentenciaSql = "
            SELECT
                -1 AS SUCURSALID
                ,'- $primeraOpcion -' AS NOMBRE

            UNION

            SELECT
                SUCURSALID,
                NOMBRE
            FROM
                CONFSUCURSALES
            WHERE
                SUCURSALID NOT IN (SELECT SUCURSALID FROM FACCONFIGURACIONESPORSUCURSAL)
            ORDER BY
                NOMBRE
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Verifica si el siguiente correlativo de una sucursal ya existe en alguna factura emitida
     * 
     * @param int $sucursalId ID de la sucursal a la que se verificará el correlativo siguiente
     * 
     * @return bool Se retorna verdadero si ya existe y falso si no existe
     * 
     */
    public function existeSiguienteCorrelativo(int $sucursalId): bool
    {
        $sentenciaSql = "
            SELECT
                COUNT(*) AS CONTEO
            FROM
                FACFACTURAS F
            WHERE
                F.SUCURSALID = ?
	            AND CORRELATIVO = (SELECT SIGUIENTECORRELATIVO FROM FACCONFIGURACIONESPORSUCURSAL CPS WHERE CPS.SUCURSALID = ?)
        ";
        $datos = $this->conn->select($sentenciaSql, [$sucursalId, $sucursalId]);

        $yaExiste = $datos[0]["CONTEO"] > 0;
        $this->getBySucursalId($sucursalId);

        return $yaExiste;
    }

    //-------------------------------------------
}