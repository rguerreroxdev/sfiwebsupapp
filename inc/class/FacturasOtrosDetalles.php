<?php

require_once("SQLSrvBD.php");

class FacturasOtrosDetalles
{
    //-------------------------------------------

    private $conn;

    public $facturaOtroDetalleId;
    public $facturaId;
    public $OtroServicioProductoId;
    public $productoCodigo;
    public $descripcion;
    public $modelo;
    public $marca;
    public $precio;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto FacturasOtrosDetalles
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
     * Obtener datos de un registro (FACFACTURASOTROSDETALLES) por medio de ID
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
                FOD.FACTURAOTRODETALLEID
                ,FOD.FACTURAID
                ,FOD.OTROSERVICIOPRODUCTOID
				,OP.CODIGO AS PRODUCTOCODIGO
                ,ISNULL(M.NOMBRE, '') AS MARCA
                ,OP.MODELO
                ,OP.DESCRIPCION
				,FOD.PRECIO
            FROM
                FACFACTURASOTROSDETALLES FOD
                JOIN FACOTROSSERVICIOSPRODUCTOS OP ON OP.OTROSERVICIOPRODUCTOID=FOD.OTROSERVICIOPRODUCTOID
                LEFT JOIN INVMARCAS M ON M.MARCAID=OP.MARCAID
            WHERE
                FOD.FACTURAOTRODETALLEID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->facturaOtroDetalleId = $dato["FACTURAOTRODETALLEID"];
            $this->facturaId = $dato["FACTURAID"];
            $this->OtroServicioProductoId = $dato["OTROSERVICIOPRODUCTOID"];
            $this->productoCodigo = $dato["PRODUCTOCODIGO"];
            $this->marca = $dato["MARCA"];
            $this->modelo = $dato["MODELO"];
            $this->descripcion = $dato["DESCRIPCION"];
            $this->precio = $dato["PRECIO"];
        }
    }

    //-------------------------------------------

    /**
     * Obtener todos los registros de la tabla (FACFACTURASOTROSDETALLES)
     * 
     * @param int $facturaId Factura a la que pertenecen las líneas de detalle
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAll(int $facturaId): array
    {
        $sentenciaSql = "
            SELECT
                FOD.FACTURAOTRODETALLEID
                ,FOD.FACTURAID
                ,FOD.OTROSERVICIOPRODUCTOID
				,OP.CODIGO AS PRODUCTOCODIGO
                ,ISNULL(M.NOMBRE, '') AS MARCA
                ,OP.MODELO
                ,OP.DESCRIPCION
				,FOD.PRECIO
            FROM
                FACFACTURASOTROSDETALLES FOD
                JOIN FACOTROSSERVICIOSPRODUCTOS OP ON OP.OTROSERVICIOPRODUCTOID=FOD.OTROSERVICIOPRODUCTOID
                LEFT JOIN INVMARCAS M ON M.MARCAID=OP.MARCAID
            WHERE
                FOD.FACTURAID = ?
            ORDER BY
                FOD.FACTURAOTRODETALLEID ASC
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        return $datos;
    }
    
    //-------------------------------------------

    /**
     * Obtener registros de la tabla (FACFACTURASOTROSDETALLES) con filtros
     * 
     * @param string $filtro Filtros a aplicar, por ejemplo: $filtro = "CAMPO1=0 AND CAMPO2='ALGO'"
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
                FOD.FACTURAOTRODETALLEID
                ,FOD.FACTURAID
                ,FOD.OTROSERVICIOPRODUCTOID
				,OP.CODIGO AS PRODUCTOCODIGO
                ,ISNULL(M.NOMBRE, '') AS MARCA
                ,OP.MODELO
                ,OP.DESCRIPCION
				,FOD.PRECIO
            FROM
                FACFACTURASOTROSDETALLES FOD
                JOIN FACOTROSSERVICIOSPRODUCTOS OP ON OP.OTROSERVICIOPRODUCTOID=FOD.OTROSERVICIOPRODUCTOID
                LEFT JOIN INVMARCAS M ON M.MARCAID=OP.MARCAID
            WHERE
                $filtro
            ORDER BY
                FOD.FACTURAOTRODETALLEID ASC
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
        $this->facturaOtroDetalleId = -1;
        $this->facturaId = null;
        $this->OtroServicioProductoId = null;
        $this->productoCodigo = null;
        $this->marca = null;
        $this->modelo = null;
        $this->descripcion = null;
        $this->precio = null;
        $this->mensajeError = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (FACFACTURASOTROSDETALLES) existente
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
            UPDATE FACFACTURASOTROSDETALLES SET " . $updates . " WHERE FACTURAOTRODETALLEID = ?
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
     * Agregar un nuevo registro (FACFACTURASOTROSDETALLES)
     * 
     * @param int $facturaId Factura a la que se le agrega un detalle
     * @param int $otroServicioProductoId Servicio/Otro producto que se está registrando en el detalle
     * @param float $precio Precio al que se está facturando el servicio/otro producto
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $facturaId, int $otroServicioProductoId, float $precio): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                FACFACTURASOTROSDETALLES
                (FACTURAID, OTROSERVICIOPRODUCTOID, PRECIO)
            VALUES
                (?, ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $facturaId, $otroServicioProductoId, $precio
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->facturaOtroDetalleId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (FACFACTURASOTROSDETALLES)
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
            DELETE FROM FACFACTURASOTROSDETALLES WHERE FACTURAOTRODETALLEID = ?
        ";
        
        $eliminado = false;
        $eliminado = $this->conn->delete($sentenciaSql, [$id]);
        
        if (!$eliminado)
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $eliminado;
    }

    //-------------------------------------------
}