<?php

require_once("SQLSrvBD.php");

class FacDevolucionesOtrosDetalles
{
    //-------------------------------------------

    private $conn;

    public $devolucionOtroDetalleId;
    public $devolucionId;
    public $OtroServicioProductoId;
    public $productoCodigo;
    public $descripcion;
    public $modelo;
    public $marca;
    public $precio;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto FacDevolucionesOtrosDetalles
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
     * Obtener datos de un registro (FACDEVOLUCIONESOTROSDETALLES) por medio de ID
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
                DOD.DEVOLUCIONOTRODETALLEID
                ,DOD.DEVOLUCIONID
                ,DOD.OTROSERVICIOPRODUCTOID
				,OP.CODIGO AS PRODUCTOCODIGO
                ,ISNULL(M.NOMBRE, '') AS MARCA
                ,OP.MODELO
                ,OP.DESCRIPCION
				,DOD.PRECIO
            FROM
                FACDEVOLUCIONESOTROSDETALLES DOD
                JOIN FACOTROSSERVICIOSPRODUCTOS OP ON OP.OTROSERVICIOPRODUCTOID=DOD.OTROSERVICIOPRODUCTOID
                LEFT JOIN INVMARCAS M ON M.MARCAID=OP.MARCAID
            WHERE
                DOD.DEVOLUCIONOTRODETALLEID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->devolucionOtroDetalleId = $dato["DEVOLUCIONOTRODETALLEID"];
            $this->devolucionId = $dato["DEVOLUCIONID"];
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
     * Obtener todos los registros de la tabla (FACDEVOLUCIONESOTROSDETALLES)
     * 
     * @param int $devolucionId Devolución a la que pertenecen las líneas de detalle
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAll(int $devolucionId): array
    {
        $sentenciaSql = "
            SELECT
                DOD.DEVOLUCIONOTRODETALLEID
                ,DOD.DEVOLUCIONID
                ,DOD.OTROSERVICIOPRODUCTOID
				,OP.CODIGO AS PRODUCTOCODIGO
                ,ISNULL(M.NOMBRE, '') AS MARCA
                ,OP.MODELO
                ,OP.DESCRIPCION
				,DOD.PRECIO
            FROM
                FACDEVOLUCIONESOTROSDETALLES DOD
                JOIN FACOTROSSERVICIOSPRODUCTOS OP ON OP.OTROSERVICIOPRODUCTOID=DOD.OTROSERVICIOPRODUCTOID
                LEFT JOIN INVMARCAS M ON M.MARCAID=OP.MARCAID
            WHERE
                DOD.DEVOLUCIONID = ?
            ORDER BY
                DOD.DEVOLUCIONOTRODETALLEID ASC
        ";
        $datos = $this->conn->select($sentenciaSql, [$devolucionId]);

        return $datos;
    }
    
    //-------------------------------------------

    /**
     * Obtener registros de la tabla (FACDEVOLUCIONESOTROSDETALLES) con filtros
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
                DOD.DEVOLUCIONOTRODETALLEID
                ,DOD.DEVOLUCIONID
                ,DOD.OTROSERVICIOPRODUCTOID
				,OP.CODIGO AS PRODUCTOCODIGO
                ,ISNULL(M.NOMBRE, '') AS MARCA
                ,OP.MODELO
                ,OP.DESCRIPCION
				,DOD.PRECIO
            FROM
                FACDEVOLUCIONESOTROSDETALLES DOD
                JOIN FACOTROSSERVICIOSPRODUCTOS OP ON OP.OTROSERVICIOPRODUCTOID=DOD.OTROSERVICIOPRODUCTOID
                LEFT JOIN INVMARCAS M ON M.MARCAID=OP.MARCAID
            WHERE
                $filtro
            ORDER BY
                DOD.DEVOLUCIONOTRODETALLEID ASC
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
        $this->devolucionOtroDetalleId = -1;
        $this->devolucionId = null;
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
     * Edita un registro (FACDEVOLUCIONESOTROSDETALLES) existente
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
            UPDATE FACDEVOLUCIONESOTROSDETALLES SET " . $updates . " WHERE DEVOLUCIONOTRODETALLEID = ?
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
     * Agregar un nuevo registro (FACDEVOLUCIONESOTROSDETALLES)
     * 
     * @param int $devolucionId Devolución a la que se le agrega un detalle
     * @param int $otroServicioProductoId Servicio/Otro producto que se está registrando en el detalle
     * @param float $precio Precio al que se facturó el servicio/otro producto
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $devolucionId, int $otroServicioProductoId, float $precio): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                FACDEVOLUCIONESOTROSDETALLES
                (DEVOLUCIONID, OTROSERVICIOPRODUCTOID, PRECIO)
            VALUES
                (?, ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $devolucionId, $otroServicioProductoId, $precio
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->devolucionOtroDetalleId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (FACDEVOLUCIONESOTROSDETALLES)
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
            DELETE FROM FACDEVOLUCIONESOTROSDETALLES WHERE DEVOLUCIONOTRODETALLEID = ?
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

    /**
     * Eliminar todos los otros detalles de una devolución
     * 
     * @param int $devolucionId ID de la devolución a la cual se le eliminarán todos sus otros detalles
     * 
     * @return bool Estado final de eliminación: true: fue eliminado, false: no fue eliminado
     * 
     */
    public function eliminarOtrosDetallesDeDevolucion(int $devolucionId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            DELETE FROM FACDEVOLUCIONESOTROSDETALLES WHERE DEVOLUCIONID = ?
        ";
        
        $eliminado = false;
        $eliminado = $this->conn->delete($sentenciaSql, [$devolucionId]);
        
        if (!$eliminado)
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $eliminado;
    }

    //-------------------------------------------
}