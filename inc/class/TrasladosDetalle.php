<?php

require_once("SQLSrvBD.php");

class TrasladosDetalle
{
    //-------------------------------------------

    private $conn;

    public $trasladoDetalleId;
    public $trasladoId;
    public $inventarioId;
    //QUITAR DE LA BASE                   public $tipoDeStockId;
    //QUITAR DE LA BASE                   public $porcentajeTipoDeStock;
    public $categoria;
    public $marca;
    public $modelo;
    public $descripcion;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto TrasladosDetalle
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
     * Obtener datos de un registro (INVTRASLADOSDETALLE) por medio de ID
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
                TD.TRASLADODETALLEID
                ,TD.TRASLADOID
                ,TD.INVENTARIOID
                ,C.NOMBRE AS CATEGORIA
                ,M.NOMBRE AS MARCA
                ,P.MODELO
                ,P.DESCRIPCION
            FROM
                INVTRASLADOSDETALLE TD
                JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
            WHERE
                TD.TRASLADODETALLEID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->trasladoDetalleId = $dato["TRASLADODETALLEID"];
            $this->trasladoId = $dato["TRASLADOID"];
            $this->inventarioId = $dato["INVENTARIOID"];
            $this->categoria = $dato["CATEGORIA"];
            $this->marca = $dato["MARCA"];
            $this->modelo = $dato["MODELO"];
            $this->descripcion = $dato["DESCRIPCION"];
        }
    }

    //-------------------------------------------

    /**
     * Obtener todos los registros de la tabla (INVTRASLADOSDETALLE)
     * 
     * @param int $trasladoId Traslado al que pertenecen las líneas de detalle
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAll(int $trasladoId): array
    {
        $sentenciaSql = "
            SELECT
                TD.TRASLADODETALLEID
                ,TD.TRASLADOID
                ,TD.INVENTARIOID
                ,I.CODIGOINVENTARIO
                ,C.NOMBRE AS CATEGORIA
                ,M.NOMBRE AS MARCA
                ,P.MODELO
                ,P.DESCRIPCION
                ,I.MSRP
                ,I.PORCENTAJETIPODESTOCKDIST
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
            FROM
                INVTRASLADOSDETALLE TD
                JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
            WHERE
                TD.TRASLADOID = ?
            ORDER BY
                TD.TRASLADODETALLEID ASC
        ";
        $datos = $this->conn->select($sentenciaSql, [$trasladoId]);

        return $datos;
    }
    
    //-------------------------------------------

    /**
     * Obtener registros de la tabla (INVTRASLADOSDETALLE) con filtros
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
                TD.TRASLADODETALLEID
                ,TD.TRASLADOID
                ,TD.INVENTARIOID
                ,C.NOMBRE AS CATEGORIA
                ,M.NOMBRE AS MARCA
                ,P.MODELO
                ,P.DESCRIPCION
            FROM
                INVTRASLADOSDETALLE TD
                JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
            WHERE
                $filtro
            ORDER BY
                TD.TRASLADODETALLEID ASC
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
        $this->trasladoDetalleId = -1;
        $this->trasladoId = null;
        $this->inventarioId = null;
        $this->categoria = null;
        $this->marca = null;
        $this->modelo = null;
        $this->descripcion = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (INVTRASLADOSDETALLE) existente
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
            UPDATE INVTRASLADOSDETALLE SET " . $updates . " WHERE TRASLADODETALLEID = ?
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
     * Agregar un nuevo registro (INVTRASLADOSDETALLE)
     * 
     * @param int $trasladoId Traslado al que se le agrega un detalle
     * @param int $inventarioId Ítem de inventario que se está registrando en el detalle
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $trasladoId, int $inventarioID): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                INVTRASLADOSDETALLE
                (TRASLADOID, INVENTARIOID)
            VALUES
                (?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $trasladoId, $inventarioID
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->trasladoDetalleId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (INVTRASLADODETALLE)
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
            DELETE FROM INVTRASLADOSDETALLE WHERE TRASLADODETALLEID = ?
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