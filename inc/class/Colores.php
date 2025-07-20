<?php

require_once("SQLSrvBD.php");

class Colores
{
    //-------------------------------------------

    private $conn;

    public $colorId;
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
     * Instancia un objeto Colores
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
     * Obtener datos de un registro (INVCOLORES) por medio de ID
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
                 C.COLORID
                ,C.NOMBRE
                ,C.FECHACREACION
                ,C.FECHAMODIFICACION
                ,C.USUARIOIDCREACION
                ,C.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                INVCOLORES C
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=C.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=C.USUARIOIDMODIFICACION
            WHERE
                C.COLORID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->colorId = $dato["COLORID"];
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
     * Obtener todos los registros de la tabla (INVCOLORES) con paginación
     * 
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllConPaginacion(string $buscar, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "";
        if (trim($buscar) != "")
        {
            $condicion = "
                WHERE
                    NOMBRE LIKE '%$buscar%'
            ";
        }

        $sentenciaSql = "
            SELECT
                COLORID
                ,NOMBRE
                ,FECHACREACION
                ,FECHAMODIFICACION
                ,USUARIOIDCREACION
                ,USUARIOIDMODIFICACION
            FROM
                INVCOLORES
            
            $condicion

            ORDER BY
                NOMBRE ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(COLORID) AS CONTEO
            FROM
                INVCOLORES

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
     * Obtener registros de la tabla (INVCOLORES) con filtros
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
                 COLORID
                ,NOMBRE
                ,FECHACREACION
                ,FECHAMODIFICACION
                ,USUARIOIDCREACION
                ,USUARIOIDMODIFICACION
            FROM
                INVCOLORES
            WHERE
                $filtro
            ORDER BY
                NOMBRE ASC 
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
        $this->colorId = -1;
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
     * Edita un registro (INVCOLORES) existente
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
            UPDATE INVCOLORES SET " . $updates . " WHERE COLORID = ?
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
     * Agregar un nuevo registro (INVCOLORES)
     * 
     * @param string $nombre Nombre del color
     * @param int $usuarioId ID del ussuario que está creando el registro
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(string $nombre, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO INVCOLORES
                (NOMBRE, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $nombre, $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->colorId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (INVCOLORES)
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
            DELETE FROM INVCOLORES WHERE COLORID = ?
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
     * Obtener todos los registros de la tabla (INVCOLORES) para mostrar en combo
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
                -1 AS COLORID
                ,'- $primeraOpcion -' AS NOMBRE

            UNION

            SELECT
                COLORID
                ,NOMBRE
            FROM
                INVCOLORES
            ORDER BY
                NOMBRE ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------
}