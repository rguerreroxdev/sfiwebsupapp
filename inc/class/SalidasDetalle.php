<?php

require_once("SQLSrvBD.php");

class SalidasDetalle
{
    //-------------------------------------------

    private $conn;

    public $salidaDetalleId;
    public $salidaId;
    public $inventarioId;

    public $categoria;
    public $marca;
    public $modelo;
    public $descripcion;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto SalidasDetalle
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
     * Obtener datos de un registro (INVSALIDASDETALLE) por medio de ID
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
                SD.SALIDADETALLEID
                ,SD.SALIDAID
                ,SD.INVENTARIOID
                ,C.NOMBRE AS CATEGORIA
                ,M.NOMBRE AS MARCA
                ,P.MODELO
                ,P.DESCRIPCION
            FROM
                INVSALIDASDETALLE SD
                JOIN INVINVENTARIO I ON I.INVENTARIOID=SD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
            WHERE
                SD.SALIDADETALLEID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->salidaDetalleId = $dato["SALIDADETALLEID"];
            $this->salidaId = $dato["SALIDAID"];
            $this->inventarioId = $dato["INVENTARIOID"];
            $this->categoria = $dato["CATEGORIA"];
            $this->marca = $dato["MARCA"];
            $this->modelo = $dato["MODELO"];
            $this->descripcion = $dato["DESCRIPCION"];
        }
    }

    //-------------------------------------------

    /**
     * Obtener todos los registros de la tabla (INVSALIDASDETALLE)
     * 
     * @param int $salidaId Salida a la que pertenecen las líneas de detalle
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAll(int $salidaId): array
    {
        $sentenciaSql = "
            SELECT
                SD.SALIDADETALLEID
                ,SD.SALIDAID
                ,SD.INVENTARIOID
                ,I.CODIGOINVENTARIO
                ,C.NOMBRE AS CATEGORIA
                ,M.NOMBRE AS MARCA
                ,P.MODELO
                ,P.DESCRIPCION
                ,I.MSRP
                ,TS.NOMBRECORTO AS TIPODESTOCK
                ,I.COSTODIST AS COSTODIST
				,TSO.NOMBRECORTO AS TIPODESTOCKORIGEN
				,I.COSTOORIGEN AS COSTOORIGEN
            FROM
                INVSALIDASDETALLE SD
                JOIN INVINVENTARIO I ON I.INVENTARIOID=SD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TS ON TS.TIPODESTOCKID=I.TIPODESTOCKDISTID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=I.TIPODESTOCKORIGENID
            WHERE
                SD.SALIDAID = ?
            ORDER BY
                SD.SALIDADETALLEID ASC
        ";
        $datos = $this->conn->select($sentenciaSql, [$salidaId]);

        return $datos;
    }
    
    //-------------------------------------------

    /**
     * Obtener registros de la tabla (INVSALIDASDETALLE) con filtros
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
                SD.SALIDADETALLEID
                ,SD.SALIDAID
                ,SD.INVENTARIOID
                ,C.NOMBRE AS CATEGORIA
                ,M.NOMBRE AS MARCA
                ,P.MODELO
                ,P.DESCRIPCION
            FROM
                INVSALIDASDETALLE SD
                JOIN INVINVENTARIO I ON I.INVENTARIOID=SD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
            WHERE
                $filtro
            ORDER BY
                SD.SALIDADETALLEID ASC
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
        $this->salidaDetalleId = -1;
        $this->salidaId = null;
        $this->inventarioId = null;
        $this->categoria = null;
        $this->marca = null;
        $this->modelo = null;
        $this->descripcion = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (INVSALIDASDETALLE) existente
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
            UPDATE INVSALIDASDETALLE SET " . $updates . " WHERE SALIDADETALLEID = ?
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
     * Agregar un nuevo registro (INVSALIDASDETALLE)
     * 
     * @param int $salidaId Salida a la que se le agrega un detalle
     * @param int $inventarioId Ítem de inventario que se está registrando en el detalle
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $salidaId, int $inventarioID): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                INVSALIDASDETALLE
                (SALIDAID, INVENTARIOID)
            VALUES
                (?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $salidaId, $inventarioID
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->salidaDetalleId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (INVSALIDASDETALLE)
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
            DELETE FROM INVSALIDASDETALLE WHERE SALIDADETALLEID = ?
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