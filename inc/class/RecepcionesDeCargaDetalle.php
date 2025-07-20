<?php

require_once("SQLSrvBD.php");

class RecepcionesDeCargaDetalle
{
    //-------------------------------------------

    private $conn;

    public $recepcionDeCargaDetalleId;
    public $recepcionDeCargaId;
    public $cantidad;
    public $productoId;
    public $categoria;
    public $codigoProducto;
    public $producto;
    public $color;
    public $marca;
    public $descripcion;
    public $tipoDeStockOrigenId;
    public $tipoDeStockDistId;
    public $tipoDeStockOrigen;
    public $tipoDeStockDist;
    public $porcentajeTipoDeStockOrigen;
    public $porcentajeTipoDeStockDist;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto RecepcionesDeCargaDetalle
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
     * Obtener datos de un registro (INVRECEPCIONESDECARGADETALLE) por medio de ID
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
                RCD.RECEPCIONDECARGADETALLEID
                ,RCD.RECEPCIONDECARGAID
                ,RCD.CANTIDAD
                ,RCD.PRODUCTOID
                ,C.NOMBRE AS CATEGORIA
                ,P.CODIGO AS CODIGOPRODUCTO
                ,P.MODELO AS PRODUCTO
                ,COL.NOMBRE AS COLOR
                ,M.NOMBRE AS MARCA
                ,P.DESCRIPCION
                ,RCD.TIPODESTOCKORIGENID
                ,RCD.TIPODESTOCKDISTID
                ,TSO.NOMBRECORTO AS TIPODESTOCKORIGEN
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,RCD.PORCENTAJETIPODESTOCKORIGEN
                ,RCD.PORCENTAJETIPODESTOCKDIST
                ,CASE
                    WHEN RC.ESTADO IN ('FOR', 'CER') THEN (SELECT P2.MSRP FROM INVPRODUCTOS P2 WHERE P2.PRODUCTOID=P.PRODUCTOID)
                    WHEN RC.ESTADO IN ('PRO') THEN (SELECT TOP 1 IE.MSRP FROM INVINVENTARIOESTADOS IE WHERE IE.RECEPCIONDECARGADETALLEID=RCD.RECEPCIONDECARGADETALLEID)
                END AS MSRP
                ,CASE
                    WHEN RC.ESTADO IN ('FOR', 'CER') THEN (SELECT P2.MSRP * RCD.PORCENTAJETIPODESTOCKORIGEN / 100 FROM INVPRODUCTOS P2 WHERE P2.PRODUCTOID=P.PRODUCTOID)
                    WHEN RC.ESTADO IN ('PRO') THEN (SELECT TOP 1 IE.COSTOORIGEN FROM INVINVENTARIOESTADOS IE WHERE IE.RECEPCIONDECARGADETALLEID=RCD.RECEPCIONDECARGADETALLEID)
                END AS COSTOORIGEN
                ,CASE
                    WHEN RC.ESTADO IN ('FOR', 'CER') THEN (SELECT P2.MSRP * RCD.PORCENTAJETIPODESTOCKDIST / 100 FROM INVPRODUCTOS P2 WHERE P2.PRODUCTOID=P.PRODUCTOID)
                    WHEN RC.ESTADO IN ('PRO') THEN (SELECT TOP 1 IE.COSTODIST FROM INVINVENTARIOESTADOS IE WHERE IE.RECEPCIONDECARGADETALLEID=RCD.RECEPCIONDECARGADETALLEID)
                END AS COSTODIST
            FROM
                INVRECEPCIONESDECARGADETALLE RCD
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=RCD.PRODUCTOID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=RCD.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=RCD.TIPODESTOCKDISTID
                JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
            WHERE
                RCD.RECEPCIONDECARGADETALLEID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->recepcionDeCargaDetalleId = $dato["RECEPCIONDECARGADETALLEID"];
            $this->recepcionDeCargaId = $dato["RECEPCIONDECARGAID"];
            $this->cantidad = $dato["CANTIDAD"];
            $this->productoId = $dato["PRODUCTOID"];
            $this->codigoProducto = $dato["CODIGOPRODUCTO"];
            $this->categoria = $dato["CATEGORIA"];
            $this->producto = $dato["PRODUCTO"];
            $this->color = $dato["COLOR"];
            $this->marca = $dato["MARCA"];
            $this->descripcion = $dato["DESCRIPCION"];
            $this->tipoDeStockOrigenId = $dato["TIPODESTOCKORIGENID"];
            $this->tipoDeStockDistId = $dato["TIPODESTOCKDISTID"];
            $this->tipoDeStockOrigen = $dato["TIPODESTOCKORIGEN"];
            $this->tipoDeStockDist = $dato["TIPODESTOCKDIST"];
            $this->porcentajeTipoDeStockOrigen = $dato["PORCENTAJETIPODESTOCKORIGEN"];
            $this->porcentajeTipoDeStockDist = $dato["PORCENTAJETIPODESTOCKDIST"];
        }
    }

    //-------------------------------------------

    /**
     * Obtener todos los registros de la tabla (INVRECEPCIONESDECARGADETALLE)
     * 
     * @param int $recepcionDeCargaId Recepción a la que pertenecen las líneas de detalle
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAll(int $recepcionDeCargaId): array
    {
        $sentenciaSql = "
            SELECT
                RCD.RECEPCIONDECARGADETALLEID
                ,RCD.RECEPCIONDECARGAID
                ,RCD.CANTIDAD
                ,RCD.PRODUCTOID
                ,P.CODIGO AS CODIGOPRODUCTO
                ,C.NOMBRE AS CATEGORIA
                ,P.MODELO AS PRODUCTO
                ,COL.NOMBRE AS COLOR
                ,M.NOMBRE AS MARCA
                ,P.DESCRIPCION
                ,RCD.TIPODESTOCKORIGENID
                ,RCD.TIPODESTOCKDISTID
                ,TSO.NOMBRECORTO AS TIPODESTOCKORIGEN
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,RCD.PORCENTAJETIPODESTOCKORIGEN
                ,RCD.PORCENTAJETIPODESTOCKDIST
                ,CASE
                    WHEN RC.ESTADO IN ('FOR', 'CER') THEN (SELECT P2.MSRP FROM INVPRODUCTOS P2 WHERE P2.PRODUCTOID=P.PRODUCTOID)
                    WHEN RC.ESTADO IN ('PRO') THEN (SELECT TOP 1 IE.MSRP FROM INVINVENTARIOESTADOS IE WHERE IE.RECEPCIONDECARGADETALLEID=RCD.RECEPCIONDECARGADETALLEID)
                END AS MSRP
                ,CASE
                    WHEN RC.ESTADO IN ('FOR', 'CER') THEN (SELECT P2.MSRP * RCD.PORCENTAJETIPODESTOCKORIGEN / 100 FROM INVPRODUCTOS P2 WHERE P2.PRODUCTOID=P.PRODUCTOID)
                    WHEN RC.ESTADO IN ('PRO') THEN (SELECT TOP 1 IE.COSTOORIGEN FROM INVINVENTARIOESTADOS IE WHERE IE.RECEPCIONDECARGADETALLEID=RCD.RECEPCIONDECARGADETALLEID)
                END AS COSTOORIGEN
                ,CASE
                    WHEN RC.ESTADO IN ('FOR', 'CER') THEN (SELECT P2.MSRP * RCD.PORCENTAJETIPODESTOCKDIST / 100 FROM INVPRODUCTOS P2 WHERE P2.PRODUCTOID=P.PRODUCTOID)
                    WHEN RC.ESTADO IN ('PRO') THEN (SELECT TOP 1 IE.COSTODIST FROM INVINVENTARIOESTADOS IE WHERE IE.RECEPCIONDECARGADETALLEID=RCD.RECEPCIONDECARGADETALLEID)
                END AS COSTODIST
            FROM
                INVRECEPCIONESDECARGADETALLE RCD
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=RCD.PRODUCTOID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=RCD.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=RCD.TIPODESTOCKDISTID
                JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
                JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
            WHERE
                RCD.RECEPCIONDECARGAID = ?
            ORDER BY
                RCD.RECEPCIONDECARGADETALLEID ASC
        ";
        $datos = $this->conn->select($sentenciaSql, [$recepcionDeCargaId]);

        return $datos;
    }
    
    //-------------------------------------------

    /**
     * Obtener registros de la tabla (INVRECEPCIONESDECARGADETALLE) con filtros
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
                RCD.RECEPCIONDECARGADETALLEID
                ,RCD.RECEPCIONDECARGAID
                ,RCD.CANTIDAD
                ,RCD.PRODUCTOID
                ,C.NOMBRE AS CATEGORIA
                ,P.MODELO AS PRODUCTO
                ,RCD.TIPODESTOCKORIGENID
                ,RCD.TIPODESTOCKDISTID
                ,TSO.NOMBRECORTO AS TIPODESTOCKORIGEN
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,RCD.PORCENTAJETIPODESTOCKORIGEN
                ,RCD.PORCENTAJETIPODESTOCKDIST
                ,CASE
                    WHEN RC.ESTADO IN ('FOR', 'CER') THEN (SELECT P2.MSRP FROM INVPRODUCTOS P2 WHERE P2.PRODUCTOID=P.PRODUCTOID)
                    WHEN RC.ESTADO IN ('FOR', 'CER') THEN (SELECT P2.MSRP * RCD.PORCENTAJETIPODESTOCK / 100 FROM INVPRODUCTOS P2 WHERE P2.PRODUCTOID=P.PRODUCTOID)
                END AS MSRP
                ,CASE
                    WHEN RC.ESTADO IN ('FOR', 'CER') THEN (SELECT P2.MSRP * RCD.PORCENTAJETIPODESTOCKORIGEN / 100 FROM INVPRODUCTOS P2 WHERE P2.PRODUCTOID=P.PRODUCTOID)
                    WHEN RC.ESTADO IN ('PRO') THEN (SELECT TOP 1 IE.COSTOORIGEN FROM INVINVENTARIOESTADOS IE WHERE IE.RECEPCIONDECARGADETALLEID=RCD.RECEPCIONDECARGADETALLEID)
                END AS COSTOORIGEN
                ,CASE
                    WHEN RC.ESTADO IN ('FOR', 'CER') THEN (SELECT P2.MSRP * RCD.PORCENTAJETIPODESTOCKDIST / 100 FROM INVPRODUCTOS P2 WHERE P2.PRODUCTOID=P.PRODUCTOID)
                    WHEN RC.ESTADO IN ('PRO') THEN (SELECT TOP 1 IE.COSTODIST FROM INVINVENTARIOESTADOS IE WHERE IE.RECEPCIONDECARGADETALLEID=RCD.RECEPCIONDECARGADETALLEID)
                END AS COSTODIST
            FROM
                INVRECEPCIONESDECARGADETALLE RCD
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=RCD.PRODUCTOID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=RCD.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=RCD.TIPODESTOCKDISTID
                JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
            WHERE
                $filtro
            ORDER BY
                RCD.RECEPCIONDECARGADETALLEID ASC
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
        $this->recepcionDeCargaDetalleId = -1;
        $this->recepcionDeCargaId = null;
        $this->cantidad = null;
        $this->productoId = null;
        $this->categoria = null;
        $this->producto = null;
        $this->codigoProducto = null;
        $this->color = null;
        $this->marca = null;
        $this->descripcion = null;
        $this->tipoDeStockOrigenId = null;
        $this->tipoDeStockDistId = null;
        $this->tipoDeStockOrigen = null;
        $this->tipoDeStockDist = null;
        $this->porcentajeTipoDeStockOrigen = null;
        $this->porcentajeTipoDeStockDist = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (INVRECEPCIONESDECARGADETALLE) existente
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
            UPDATE INVRECEPCIONESDECARGADETALLE SET " . $updates . " WHERE RECEPCIONDECARGADETALLEID = ?
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
     * Agregar un nuevo registro (INVRECEPCIONESDECARGADETALLE)
     * 
     * @param int $recepcionDeCargaId Recepción de carga a la que se le agrega un detalle
     * @param int $cantidad Cantidad de ítems
     * @param int $productoId Producto que se está registrando en el detalle
     * @param int $tipoDeStockOrigenId Tipo de Stock de origen aplicado al producto
     * @param int $tipoDeStockDistId Tipo de Stock de distribución en tiendas aplicado al producto
     * @param float $porcentajeDeTipoDeStockOrigen Porcentaje del tipo de stock de origen que está siendo aplicado
     * @param float $porcentajeDeTipoDeStockDist Porcentaje del tipo de stock de distribución en tienda que está siendo aplicado
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $recepcionDeCargaId, int $cantidad, int $productoId, int $tipoDeStockOrigenId, int $tipoDeStockDistId, float $porcentajeDeTipoDeStockOrigen, float $porcentajeDeTipoDeStockDist): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                INVRECEPCIONESDECARGADETALLE
                (RECEPCIONDECARGAID, CANTIDAD, PRODUCTOID, TIPODESTOCKORIGENID, TIPODESTOCKDISTID, PORCENTAJETIPODESTOCKORIGEN, PORCENTAJETIPODESTOCKDIST)
            VALUES
                (?, ?, ?, ?, ?, ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $recepcionDeCargaId, $cantidad, $productoId,
                                                $tipoDeStockOrigenId, $tipoDeStockDistId, $porcentajeDeTipoDeStockOrigen, $porcentajeDeTipoDeStockDist
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->recepcionDeCargaDetalleId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (INVRECEPCIONESDECARGADETALLE)
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
            DELETE FROM INVRECEPCIONESDECARGADETALLE WHERE RECEPCIONDECARGADETALLEID = ?
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