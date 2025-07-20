<?php

require_once("SQLSrvBD.php");

class TiposDeDevolucionInv
{
    //-------------------------------------------

    private $conn;

    public $tipoDeDevolucionId;
    public $nombre;

    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto TiposDeDevolucionInv
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
     * Obtener todos los registros de la tabla (INVTIPOSDEDEVOLUCION) con paginación
     * 
     * @param string $nombre Nombre con el que se realiza la búsqueda
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllConPaginacion(string $nombre, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "";

        if ($nombre != "")
        {
            $condicion .= " WHERE TD.NOMBRE LIKE '%$nombre%' ";
        }

        $sentenciaSql = "
            SELECT
                TD.TIPODEDEVOLUCIONID,
                TD.NOMBRE
            FROM
                INVTIPOSDEDEVOLUCION TD

            $condicion
            
            ORDER BY
                TS.NOMBRE

            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(TS.TIPODEDEVOLUCION) AS CONTEO
            FROM
                INVTIPOSDEDEVOLUCION TD

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
     * Obtener datos de un registro (INVTIPOSDEDEVOLUCION) por medio de ID
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
                TD.TIPODEDEVOLUCIONID,
                TD.NOMBRE,
                TD.FECHACREACION,
                TD.FECHAMODIFICACION,
                TD.USUARIOIDCREACION,
                UC.USUARIO AS USUARIOCREO,
                TD.USUARIOIDMODIFICACION,
                UM.USUARIO AS USUARIOMODIFICA
            FROM
                INVTIPOSDEDEVOLUCION TD
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=TD.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=TD.USUARIOIDMODIFICACION
            WHERE
                TD.TIPODEDEVOLUCIONID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->tipoDeDevolucionId = $dato["TIPODEDEVOLUCIONID"];
            $this->nombre = $dato["NOMBRE"];
        
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
     * Iniciar datos para crear un nuevo registro
     * 
     * @param void No necesita parámetros
     * 
     * @return void No retorna valor sino que quedan actualizadas las propiedades del objeto
     * 
     */
    // Resetear a valores neutros las propiedades del objeto
    public function iniciarDatosParaNuevoRegistro(): void
    {
        $fecha = new DateTime();

        $this->tipoDeDevolucionId = -1;
        $this->nombre = null;
    
        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Agregar un nuevo registro (INVTIPOSDEDEVOLUCION)
     * 
     * @param string $nombre Nombre del tipo de devolución
     * @param int $usuarioId Usuario que está creando el registro
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(string $nombre, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                INVTIPOSDEDEVOLUCION
                (NOMBRE,
                FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?,
                GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $nombre,
                                                $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->tipoDeDevolucionId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Edita un registro (INVTIPOSDEDEVOLUCION) existente
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
            UPDATE INVTIPOSDEDEVOLUCION SET " . $updates . " WHERE TIPODEDEVOLUCIONID = ?
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
     * Eliminar un registro (INVTIPOSDEDEVOLUCION)
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
            DELETE FROM INVTIPOSDEDEVOLUCION WHERE TIPODEDEVOLUCIONID = ?
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
        $this->tipoDeDevolucionId = -1;
        $this->nombre = null;

        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Obtener todos los registros de la tabla (INVTIPOSDEDEVOLUCION) para mostrar en combo
     * (incluye fila de "SELECT")
     * 
     * @param void
     * 
     * @return array Todos los registros encontrados en la tabla en orden alfabético con la primer opción "SELECT"
     * 
     */
    public function getListaParaCombo(string $primeraOpcion = "SELECT"): array
    {
        $sentenciaSql = "
            SELECT
                -1 AS TIPODEDEVOLUCIONID
                ,'- $primeraOpcion -' AS NOMBRE

            UNION

            SELECT
                TIPODEDEVOLUCIONID
                ,NOMBRE
            FROM
                INVTIPOSDEDEVOLUCION
            ORDER BY
                NOMBRE ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    } 
    
    //-------------------------------------------
}