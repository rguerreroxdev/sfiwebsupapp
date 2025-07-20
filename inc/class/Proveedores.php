<?php

require_once("SQLSrvBD.php");

class Proveedores
{
    //-------------------------------------------

    private $conn;

    public $proveedorId;
    public $codigo;
    public $nombre;
    public $direccion;
    public $telefono;

    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto Proveedores
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
     * Obtener datos de un registro (INVPROVEEDORES) por medio de ID
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
                P.PROVEEDORID
                ,P.CODIGO
                ,P.NOMBRE
                ,P.DIRECCION
                ,P.TELEFONO
                ,P.FECHACREACION
                ,P.FECHAMODIFICACION
                ,P.USUARIOIDCREACION
                ,P.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                INVPROVEEDORES P
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=P.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=P.USUARIOIDMODIFICACION
            WHERE
                P.PROVEEDORID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->proveedorId = $dato["PROVEEDORID"];
            $this->codigo = $dato["CODIGO"];
            $this->nombre = $dato["NOMBRE"];
            $this->direccion = $dato["DIRECCION"];
            $this->telefono = $dato["TELEFONO"];
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
     * Obtener todos los registros de la tabla (INVPROVEEDORES) con paginación
     * 
     * @param string $buscar Texto a buscar en campos de tipo VARCHAR
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
            $condicion .= "
                WHERE
                    (P.CODIGO LIKE '%$buscar%'
                    OR P.NOMBRE LIKE '%$buscar%'
                    OR P.TELEFONO LIKE '%$buscar%')
            ";
        }

        $sentenciaSql = "
            SELECT
                P.PROVEEDORID
                ,P.CODIGO
                ,P.NOMBRE
                ,P.DIRECCION
                ,P.TELEFONO
            FROM
                INVPROVEEDORES P

            $condicion
            
            ORDER BY
                P.CODIGO ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(P.PROVEEDORID) AS CONTEO
            FROM
                INVPROVEEDORES P

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
     * Obtener registros de la tabla (INVPROVEEDORES) con filtros
     * 
     * @param void
     * @param string $filtro Serie de filtros a aplicar en la consulta, por ejemplo: $filtro = "CAMPO1=0 AND CAMPO2='ALGO'"
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
                P.PROVEEDORID
                ,P.CODIGO
                ,P.NOMBRE
                ,P.DIRECCION
                ,P.TELEFONO
            FROM
                INVPROVEEDORES P
            WHERE
                $filtro
            ORDER BY
                P.NOMBRE ASC
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
        $this->proveedorId = -1;
        $this->codigo = null;
        $this->nombre = null;
        $this->direccion = null;
        $this->telefono = null;
        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (INVPROVEEDORES) existente
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
            UPDATE INVPROVEEDORES SET " . $updates . " WHERE PROVEEDORID = ?
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
     * Agregar un nuevo registro (INVPROVEEDORES)
     * 
     * @param string $nombre Nombre del proveedor
     * @param string $direccion Dirección del proveedor
     * @param string $telefono Número de teléfono del proveedor
     * @param int $usuarioId ID del usuario que está creando el registro
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(string $nombre, string $direccion, string $telefono, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO INVPROVEEDORES
                (NOMBRE, DIRECCION, TELEFONO, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $nombre, $direccion, $telefono, $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->proveedorId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (INVPROVEEDORES)
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
            DELETE FROM INVPROVEEDORES WHERE PROVEEDORID = ?
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
     * Obtener todos los registros de la tabla (INVPROVEEDORES) para mostrar en combo
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
                -1 AS PROVEEDORID
                ,'- $primeraOpcion -' AS NOMBRE

            UNION

            SELECT
                PROVEEDORID
                ,NOMBRE
            FROM
                INVPROVEEDORES
            ORDER BY
                NOMBRE ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------
}