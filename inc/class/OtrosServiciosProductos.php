<?php

require_once("SQLSrvBD.php");

class OtrosServiciosProductos
{
    //-------------------------------------------

    private $conn;

    public $otroServicioProductoId;
    public $codigo;
    public $descripcion;
    public $marcaId;
    public $marca;
    public $modelo;

    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto OtrosServiciosProductos
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
     * Obtener datos de un registro (FACOTROSSERVICIOSPRODUCTOS) por medio de ID
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
                SP.OTROSERVICIOPRODUCTOID
                ,SP.CODIGO
                ,ISNULL(SP.MARCAID, -1) AS MARCAID
                ,ISNULL(M.NOMBRE, '') AS MARCA
                ,SP.MODELO
                ,SP.DESCRIPCION
                ,SP.FECHACREACION
                ,SP.FECHAMODIFICACION
                ,SP.USUARIOIDCREACION
                ,SP.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACOTROSSERVICIOSPRODUCTOS SP
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=SP.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=SP.USUARIOIDMODIFICACION
                LEFT JOIN INVMARCAS M ON M.MARCAID=SP.MARCAID
            WHERE
                SP.OTROSERVICIOPRODUCTOID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->otroServicioProductoId = $dato["OTROSERVICIOPRODUCTOID"];
            $this->codigo = $dato["CODIGO"];
            $this->marcaId = $dato["MARCAID"];
            $this->marca = $dato["MARCA"];
            $this->modelo = $dato["MODELO"];
            $this->descripcion = $dato["DESCRIPCION"];
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
     * Obtener datos de ítem por su código
     * 
     * @param string $codigo Código del Ítem al que se le van a leer sus datos
     * 
     * @return array Los datos del ítem
     * 
     */
    public function getByCodigo(string $codigo): array
    {
        $sentenciaSql = "
            SELECT
                SP.OTROSERVICIOPRODUCTOID
                ,SP.CODIGO
                ,ISNULL(SP.MARCAID, -1) AS MARCAID
                ,ISNULL(M.NOMBRE, '') AS MARCA
                ,SP.MODELO
                ,SP.DESCRIPCION
                ,SP.FECHACREACION
                ,SP.FECHAMODIFICACION
                ,SP.USUARIOIDCREACION
                ,SP.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACOTROSSERVICIOSPRODUCTOS SP
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=SP.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=SP.USUARIOIDMODIFICACION
                LEFT JOIN INVMARCAS M ON M.MARCAID=SP.MARCAID
            WHERE
                SP.CODIGO = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$codigo]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener todos los registros de la tabla (FACOTROSSERVICIOSPRODUCTOS) con paginación
     * 
     * @param string $buscar Texto a buscar en campos de tipo VARCHAR
     * @param int $marcaId ID de marca a filtrar
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllConPaginacion(string $buscar, int $marcaId, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "";
        if (trim($buscar) != "")
        {
            $condicion .= "
                WHERE
                    (SP.CODIGO LIKE '%$buscar%'
                    OR SP.MODELO LIKE '%$buscar%'
                    OR SP.DESCRIPCION LIKE '%$buscar%')
            ";
        }

        if ($marcaId == -2)
        {
            $condicion .= strlen($condicion) > 0 ? " AND SP.MARCAID IS NULL " : " WHERE SP.MARCAID IS NULL ";
        }
        else if ($marcaId != -1)
        {
            $condicion .= strlen($condicion) > 0 ? " AND SP.MARCAID = $marcaId " : " WHERE SP.MARCAID = $marcaId ";
        }

        $sentenciaSql = "
            SELECT
                SP.OTROSERVICIOPRODUCTOID
                ,SP.CODIGO
                ,ISNULL(SP.MARCAID, -1) AS MARCAID
                ,ISNULL(M.NOMBRE, '') AS MARCA
                ,SP.MODELO
                ,SP.DESCRIPCION
                ,SP.FECHACREACION
                ,SP.FECHAMODIFICACION
                ,SP.USUARIOIDCREACION
                ,SP.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACOTROSSERVICIOSPRODUCTOS SP
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=SP.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=SP.USUARIOIDMODIFICACION
                LEFT JOIN INVMARCAS M ON M.MARCAID=SP.MARCAID

            $condicion
            
            ORDER BY
                SP.CODIGO ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(SP.OTROSERVICIOPRODUCTOID) AS CONTEO
            FROM
                FACOTROSSERVICIOSPRODUCTOS SP
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=SP.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=SP.USUARIOIDMODIFICACION
                LEFT JOIN INVMARCAS M ON M.MARCAID=SP.MARCAID

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
     * Obtener registros de la tabla (FACOTROSSERVICIOSPRODUCTOS) con filtros
     * 
     * @param void
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
                SP.OTROSERVICIOPRODUCTOID
                ,SP.CODIGO
                ,ISNULL(SP.MARCAID, -1) AS MARCAID
                ,ISNULL(M.NOMBRE, '') AS MARCA
                ,SP.MODELO
                ,SP.DESCRIPCION
                ,SP.FECHACREACION
                ,SP.FECHAMODIFICACION
                ,SP.USUARIOIDCREACION
                ,SP.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACOTROSSERVICIOSPRODUCTOS SP
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=SP.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=SP.USUARIOIDMODIFICACION
                LEFT JOIN INVMARCAS M ON M.MARCAID=SP.MARCAID
            WHERE
                $filtro
            ORDER BY
                SP.CODIGO ASC
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
        $this->otroServicioProductoId = -1;
        $this->codigo = null;
        $this->marcaId = null;
        $this->marca = null;
        $this->modelo = null;
        $this->descripcion = null;
        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (FACOTROSSERVICIOSPRODUCTOS) existente
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
            UPDATE FACOTROSSERVICIOSPRODUCTOS SET " . $updates . " WHERE OTROSERVICIOPRODUCTOID = ?
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
     * Agregar un nuevo registro (FACOTROSSERVICIOSPRODUCTOS)
     * 
     * @param int $marcaId ID de la marca del servicio/producto
     * @param string $modelo Modelo del producto
     * @param string $descripcion Descripción del servicio/producto
     * @param int $usuarioId ID del usuario que está creando el registro
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $marcaId, string $modelo, string $descripcion, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "";

        if ($marcaId == -1)
        {
            $sentenciaSql = "
            INSERT INTO FACOTROSSERVICIOSPRODUCTOS
                (MODELO, DESCRIPCION, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, GETDATE(), GETDATE(), ?, ?)
            ";
            $datoResultado = $this->conn->insert($sentenciaSql,
            [
                $modelo, $descripcion, $usuarioId, $usuarioId
            ],
            true);
        }
        else
        {
            $sentenciaSql = "
            INSERT INTO FACOTROSSERVICIOSPRODUCTOS
                (MARCAID, MODELO, DESCRIPCION, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, GETDATE(), GETDATE(), ?, ?)
            ";
            $datoResultado = $this->conn->insert($sentenciaSql,
            [
                $marcaId, $modelo, $descripcion, $usuarioId, $usuarioId
            ],
            true);
        }

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->otroServicioProductoId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (FACOTROSSERVICIOSPRODUCTOS)
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
            DELETE FROM FACOTROSSERVICIOSPRODUCTOS WHERE OTROSERVICIOPRODUCTOID = ?
        ";
        $eliminado = $this->conn->delete($sentenciaSql, [$id]);
        
        if (!$eliminado)
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $eliminado;
    }

    //-------------------------------------------
}