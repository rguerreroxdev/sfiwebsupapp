<?php

require_once("SQLSrvBD.php");

class TiposDeStock
{
    //-------------------------------------------

    private $conn;

    public $tipoDeStockId;
    public $proveedorId;
    public $codigoProveedor;
    public $proveedor;
    public $nombreCorto;
    public $porcentaje;

    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto TiposDeStock
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
     * Obtener datos de un registro (INVTIPOSDESTOCK) por medio de ID
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
                TS.TIPODESTOCKID
                ,TS.PROVEEDORID
                ,P.CODIGO AS CODIGOPROVEEDOR
                ,P.NOMBRE AS PROVEEDOR
                ,TS.NOMBRECORTO
                ,TS.PORCENTAJE
                ,TS.FECHACREACION
                ,TS.FECHAMODIFICACION
                ,TS.USUARIOIDCREACION
                ,TS.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                INVTIPOSDESTOCK TS
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=TS.PROVEEDORID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=TS.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=TS.USUARIOIDMODIFICACION
            WHERE
                TS.TIPODESTOCKID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->tipoDeStockId = $dato["TIPODESTOCKID"];
            $this->proveedorId = $dato["PROVEEDORID"];
            $this->codigoProveedor = $dato["CODIGOPROVEEDOR"];
            $this->proveedor = $dato["PROVEEDOR"];
            $this->nombreCorto = $dato["NOMBRECORTO"];
            $this->porcentaje = $dato["PORCENTAJE"];
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
     * Obtener todos los registros de la tabla (INVTIPOSDESTOCK) con paginación
     * 
     * @param string $buscar Texto a utilizar para realizar filtro en la consulta
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * @param int $proveedorId (Opcional) Proveedor con el que se filtra el resultado
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllConPaginacion(string $buscar, int $numeroDePagina = 1, int $tamanoDePagina = 25, int $proveedorId = -1): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "";
        if (trim($buscar) != "")
        {
            if(is_numeric($buscar))
            {
                $condicion = "
                WHERE
                    (P.NOMBRE LIKE %$buscar%
                    OR TS.NOMBRECORTO LIKE '%$buscar%'
                    OR TS.PORCENTAJE = $buscar)
                ";
            }
            else
            {
                $condicion = "
                WHERE
                    (P.NOMBRE LIKE '%$buscar%'
                    OR TS.NOMBRECORTO LIKE '%$buscar%')
                ";
            }

        }

        if ($proveedorId != -1)
        {
            $condicion .= strlen($condicion) > 0 ? " AND P.PROVEEDORID = $proveedorId " : " WHERE P.PROVEEDORID = $proveedorId ";
        }

        $sentenciaSql = "
            SELECT
                TS.TIPODESTOCKID
                ,P.PROVEEDORID
                ,P.CODIGO AS CODIGOPROVEEDOR
                ,P.NOMBRE AS PROVEEDOR
                ,TS.NOMBRECORTO
                ,TS.PORCENTAJE
            FROM
                INVTIPOSDESTOCK TS
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=TS.PROVEEDORID
            
            $condicion

            ORDER BY
                P.NOMBRE ASC
                ,TS.NOMBRECORTO ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(TS.TIPODESTOCKID) AS CONTEO
            FROM
                INVTIPOSDESTOCK TS
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=TS.PROVEEDORID

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
     * Obtener registros de la tabla (INVTIPOSDESTOCK) con filtros
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
                TS.TIPODESTOCKID
                ,P.PROVEEDORID
                ,P.CODIGO AS CODIGOPROVEEDOR
                ,P.NOMBRE AS PROVEEDOR
                ,TS.NOMBRECORTO
                ,TS.PORCENTAJE
            FROM
                INVTIPOSDESTOCK TS
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=TS.PROVEEDORID
                $filtro
            ORDER BY
                P.NOMBRE ASC
                ,TS.NOMBRECORTO ASC 
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
        $this->tipoDeStockId = -1;
        $this->proveedorId = null;
        $this->codigoProveedor = null;
        $this->proveedor = null;
        $this->nombreCorto = null;
        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (INVTIPOSDESTOCK) existente
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
            UPDATE INVTIPOSDESTOCK SET " . $updates . " WHERE TIPODESTOCKID = ?
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
     * Agregar un nuevo registro (INVTIPOSDESTOCK)
     * 
     * @param int $proveedorId Proveedor al que pertenece el tipo de stock
     * @param string $nombreCorto Nombre corto (abreviatura) del tipo de stock
     * @param float $porcentaje Porcentaje que se aplica por el tipo de stock
     * @param int $usuarioId ID del ussuario que está creando el registro
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(string $nombreCorto, int $proveedorId, float $porcentaje, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO INVTIPOSDESTOCK
                (PROVEEDORID, NOMBRECORTO, PORCENTAJE, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $proveedorId, $nombreCorto, $porcentaje, $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->tipoDeStockId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (INVTIPOSDESTOCK)
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
            DELETE FROM INVTIPOSDESTOCK WHERE TIPODESTOCKID = ?
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
     * Obtener todos los registros de la tabla (INVTIPOSDESTOCK) para mostrar en combo
     * (incluye fila de "SELECT")
     * 
     * @param int $proveedorId Proveedor con el que se filtrará la lista de opciones
     * @param string $primeraOpcion Opción a mostrar en el combo en la parte superior, por defecto es "SELECT"
     * 
     * @return array Todos los registros encontrados en la tabla en orden alfabético con la primer opción "SELECT"
     * 
     */
    public function getListaParaCombo(int $proveedorId, string $primeraOpcion = "SELECT", ): array
    {
        $sentenciaSql = "
            SELECT
                -1 AS TIPODESTOCKID
                ,'- $primeraOpcion -' AS NOMBRECORTO

            UNION

            SELECT
                TIPODESTOCKID
                ,NOMBRECORTO
            FROM
                INVTIPOSDESTOCK
            WHERE
                PROVEEDORID = ?
            ORDER BY
                NOMBRECORTO ASC
        ";
        $datos = $this->conn->select($sentenciaSql, [$proveedorId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener todos los registros de la tabla (INVTIPOSDESTOCK) sin filtro de proveedor para mostrar en combo
     * (incluye fila de "SELECT")
     * 
     * @param int $proveedorId Proveedor con el que se filtrará la lista de opciones
     * @param string $primeraOpcion Opción a mostrar en el combo en la parte superior, por defecto es "SELECT"
     * 
     * @return array Todos los registros encontrados en la tabla en orden alfabético con la primer opción "SELECT"
     * 
     */
    public function getListaSinFiltroParaCombo(string $primeraOpcion = "SELECT", ): array
    {
        $sentenciaSql = "
            SELECT
                -1 AS TIPODESTOCKID
                ,'- $primeraOpcion -' AS NOMBRECORTO

            UNION

            SELECT
                TIPODESTOCKID
                ,NOMBRECORTO
            FROM
                INVTIPOSDESTOCK
            ORDER BY
                NOMBRECORTO ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------
}