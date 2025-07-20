<?php

require_once("SQLSrvBD.php");

class FacturasDetalle
{
    //-------------------------------------------

    private $conn;

    public $facturaDetalleId;
    public $facturaId;
    public $inventarioId;
    public $tipoDeGarantiaId;
    public $tipoDeGarantia;
    public $precio;

    public $categoria;
    public $marca;
    public $modelo;
    public $descripcion;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto FacturasDetalle
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
     * Obtener datos de un registro (FACFACTURASDETALLE) por medio de ID
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
                FD.FACTURADETALLEID
                ,FD.FACTURAID
                ,FD.INVENTARIOID
                ,C.NOMBRE AS CATEGORIA
                ,M.NOMBRE AS MARCA
                ,P.MODELO
                ,P.DESCRIPCION
				,FD.TIPODEGARANTIAID
				,TG.NOMBRE AS TIPODEGARANTIA
				,FD.PRECIO
            FROM
                FACFACTURASDETALLE FD
                JOIN INVINVENTARIO I ON I.INVENTARIOID=FD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
				JOIN INVTIPOSDEGARANTIA TG ON TG.TIPODEGARANTIAID=FD.TIPODEGARANTIAID
            WHERE
                FD.FACTURADETALLEID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->facturaDetalleId = $dato["FACTURADETALLEID"];
            $this->facturaId = $dato["FACTURAID"];
            $this->inventarioId = $dato["INVENTARIOID"];
            $this->categoria = $dato["CATEGORIA"];
            $this->marca = $dato["MARCA"];
            $this->modelo = $dato["MODELO"];
            $this->descripcion = $dato["DESCRIPCION"];
            $this->tipoDeGarantiaId = $dato["TIPODEGARANTIAID"];
            $this->tipoDeGarantia = $dato["TIPODEGARANTIA"];
            $this->precio = $dato["PRECIO"];
        }
    }

    //-------------------------------------------

    /**
     * Obtener todos los registros de la tabla (FACFACTURASDETALLE)
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
                FD.FACTURADETALLEID
                ,FD.FACTURAID
                ,FD.INVENTARIOID
                ,I.CODIGOINVENTARIO
                ,C.NOMBRE AS CATEGORIA
                ,M.NOMBRE AS MARCA
                ,P.MODELO
                ,P.DESCRIPCION
                ,I.MSRP
				,FD.TIPODEGARANTIAID
				,TG.NOMBRE AS TIPODEGARANTIA
				,FD.PRECIO
            FROM
                FACFACTURASDETALLE FD
                JOIN INVINVENTARIO I ON I.INVENTARIOID=FD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
				JOIN INVTIPOSDEGARANTIA TG ON TG.TIPODEGARANTIAID=FD.TIPODEGARANTIAID
            WHERE
                FD.FACTURAID = ?
            ORDER BY
                FD.FACTURADETALLEID ASC
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        return $datos;
    }
    
    //-------------------------------------------

    /**
     * Obtener registros de la tabla (FACFACTURASDETALLE) con filtros
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
                FD.FACTURADETALLEID
                ,FD.FACTURAID
                ,FD.INVENTARIOID
                ,I.CODIGOINVENTARIO
                ,C.NOMBRE AS CATEGORIA
                ,M.NOMBRE AS MARCA
                ,P.MODELO
                ,P.DESCRIPCION
                ,I.MSRP
				,FD.TIPODEGARANTIAID
				,TG.NOMBRE AS TIPODEGARANTIA
				,FD.PRECIO
            FROM
                FACFACTURASDETALLE FD
                JOIN INVINVENTARIO I ON I.INVENTARIOID=FD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
				JOIN INVTIPOSDEGARANTIA TG ON TG.TIPODEGARANTIAID=FD.TIPODEGARANTIAID
            WHERE
                $filtro
            ORDER BY
                FD.FACTURADETALLEID ASC
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
        $this->facturaDetalleId = -1;
        $this->facturaId = null;
        $this->inventarioId = null;
        $this->categoria = null;
        $this->marca = null;
        $this->modelo = null;
        $this->descripcion = null;
        $this->tipoDeGarantiaId = null;
        $this->tipoDeGarantia = null;
        $this->precio = null;
        $this->mensajeError = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (FACFACTURASDETALLE) existente
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
            UPDATE FACFACTURASDETALLE SET " . $updates . " WHERE FACTURADETALLEID = ?
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
     * Agregar un nuevo registro (FACFACTURASDETALLE)
     * 
     * @param int $facturaId Factura a la que se le agrega un detalle
     * @param int $inventarioId Ítem de inventario que se está registrando en el detalle
     * @param int $tipoDeGarantiaId Tipo de garantía aplicado al ítem
     * @param float $precio Precio al que se está facturando el ítem
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $facturaId, int $inventarioId, int $tipoDeGarantiaId, float $precio): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                FACFACTURASDETALLE
                (FACTURAID, INVENTARIOID, TIPODEGARANTIAID, PRECIO)
            VALUES
                (?, ?, ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $facturaId, $inventarioId, $tipoDeGarantiaId, $precio
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->facturaDetalleId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (FACFACTURASDETALLE)
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
            DELETE FROM FACFACTURASDETALLE WHERE FACTURADETALLEID = ?
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