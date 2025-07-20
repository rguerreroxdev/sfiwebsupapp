<?php

require_once("SQLSrvBD.php");

class Productos
{
    //-------------------------------------------

    private $conn;

    public $productoId;
    public $codigo;
    public $categoriaId;
    public $categoria;
    public $marcaId;
    public $marca;
    public $colorId;
    public $color;
    public $modelo;
    public $descripcion;
    public $msrp;

    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto Productos
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
     * Obtener datos de un registro (INVPRODUCTOS) por medio de ID
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
                P.PRODUCTOID
                ,P.CODIGO
                ,P.CATEGORIAID
                ,CA.NOMBRE AS CATEGORIA
                ,P.MARCAID
                ,M.NOMBRE AS MARCA
                ,P.COLORID
                ,CO.NOMBRE AS COLOR
                ,P.MODELO
                ,P.DESCRIPCION
                ,P.MSRP
                ,P.FECHACREACION
                ,P.FECHAMODIFICACION
                ,P.USUARIOIDCREACION
                ,P.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                INVPRODUCTOS P
                JOIN INVCATEGORIAS CA ON CA.CATEGORIAID=P.CATEGORIAID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCOLORES CO ON CO.COLORID=P.COLORID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=P.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=P.USUARIOIDMODIFICACION
            WHERE
                P.PRODUCTOID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->productoId = $dato["PRODUCTOID"];
            $this->codigo = $dato["CODIGO"];
            $this->categoriaId = $dato["CATEGORIAID"];
            $this->categoria = $dato["CATEGORIA"];
            $this->marcaId = $dato["MARCAID"];
            $this->marca = $dato["MARCA"];
            $this->colorId = $dato["COLORID"];
            $this->color = $dato["COLOR"];
            $this->modelo = $dato["MODELO"];
            $this->descripcion = $dato["DESCRIPCION"];
            $this->msrp = $dato["MSRP"];
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
     * Obtener todos los registros de la tabla (INVPRODUCTOS) con paginación
     * 
     * @param string $buscar Texto a buscar en campos de tipo VARCHAR
     * @param int $categoriaId ID de categoría a filtrar
     * @param int $marcaId ID de marca a filtrar
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllConPaginacion(string $buscar, int $categoriaId, int $marcaId, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "";
        if (trim($buscar) != "")
        {
            $condicion .= "
                WHERE
                    (P.CODIGO LIKE '%$buscar%'
                    OR P.MODELO LIKE '%$buscar%'
                    OR P.DESCRIPCION LIKE '%$buscar%')
            ";
        }

        if ($categoriaId != -1)
        {
            $condicion .= strlen($condicion) > 0 ? " AND P.CATEGORIAID = $categoriaId " : " WHERE P.CATEGORIAID = $categoriaId ";
        }

        if ($marcaId != -1)
        {
            $condicion .= strlen($condicion) > 0 ? " AND P.MARCAID = $marcaId " : " WHERE P.MARCAID = $marcaId ";
        }

        $sentenciaSql = "
            SELECT
                P.PRODUCTOID
                ,P.CODIGO
                ,P.CATEGORIAID
                ,CA.NOMBRE AS CATEGORIA
                ,P.MARCAID
                ,M.NOMBRE AS MARCA
                ,P.COLORID
                ,CO.NOMBRE AS COLOR
                ,P.MODELO
                ,P.DESCRIPCION
                ,P.MSRP
                ,P.FECHACREACION
                ,P.FECHAMODIFICACION
                ,P.USUARIOIDCREACION
                ,P.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                INVPRODUCTOS P
                JOIN INVCATEGORIAS CA ON CA.CATEGORIAID=P.CATEGORIAID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCOLORES CO ON CO.COLORID=P.COLORID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=P.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=P.USUARIOIDMODIFICACION

            $condicion
            
            ORDER BY
                P.CODIGO ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(P.PRODUCTOID) AS CONTEO
            FROM
                INVPRODUCTOS P
                JOIN INVCATEGORIAS CA ON CA.CATEGORIAID=P.CATEGORIAID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCOLORES CO ON CO.COLORID=P.COLORID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=P.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=P.USUARIOIDMODIFICACION

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
     * Obtener registros de la tabla (INVPRODUCTOS) con filtros
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
                P.PRODUCTOID
                ,P.CODIGO
                ,P.CATEGORIAID
                ,CA.NOMBRE AS CATEGORIA
                ,P.MARCAID
                ,M.NOMBRE AS MARCA
                ,P.COLORID
                ,CO.NOMBRE AS COLOR
                ,P.MODELO
                ,P.DESCRIPCION
                ,P.MSRP
                ,P.FECHACREACION
                ,P.FECHAMODIFICACION
                ,P.USUARIOIDCREACION
                ,P.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                INVPRODUCTOS P
                JOIN INVCATEGORIAS CA ON CA.CATEGORIAID=P.CATEGORIAID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCOLORES CO ON CO.COLORID=P.COLORID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=P.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=P.USUARIOIDMODIFICACION
            WHERE
                $filtro
            ORDER BY
                P.CODIGO ASC
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
        $this->productoId = -1;
        $this->codigo = null;
        $this->categoriaId = null;
        $this->categoria = null;
        $this->marcaId = null;
        $this->marca = null;
        $this->colorId = null;
        $this->color = null;
        $this->modelo = null;
        $this->descripcion = null;
        $this->msrp = null;
        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (INVPRODUCTOS) existente
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
            UPDATE INVPRODUCTOS SET " . $updates . " WHERE PRODUCTOID = ?
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
     * Agregar un nuevo registro (INVPRODUCTOS)
     * 
     * @param int $categoriaId ID de la categoría a que pertenece el producto
     * @param int $marcaId ID de la marca del producto
     * @param int $colorId ID del color del producto
     * @param string $modelo Modelo del producto
     * @param string $descripcion Descripciòn del producto
     * @param float $msrp Precio de venta al público sugerido por el fabricante
     * @param int $usuarioId ID del usuario que está creando el registro
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $categoriaId, int $marcaId, int $colorId, string $modelo, string $descripcion, float $msrp, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO INVPRODUCTOS
                (CATEGORIAID, MARCAID, COLORID, MODELO, DESCRIPCION, MSRP, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, ?, ?, ?, GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $categoriaId, $marcaId, $colorId, $modelo, $descripcion, $msrp, $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->productoId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (INVPRODUCTOS)
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
            DELETE FROM INVPRODUCTOS WHERE PRODUCTOID = ?
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