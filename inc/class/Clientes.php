<?php

require_once("SQLSrvBD.php");

class Clientes
{
    //-------------------------------------------

    private $conn;

    public $clienteId;
    public $codigo;
    public $nombre;
    public $direccion;
    public $direccionComplemento;
    public $codigoPostal;
    public $telefono;
    public $correoElectronico;

    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto Clientes
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
     * Obtener datos de un registro (FACCLIENTES) por medio de ID
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
                C.CLIENTEID
                ,C.CODIGO
                ,C.NOMBRE
                ,C.DIRECCION
                ,C.DIRECCIONCOMPLEMENTO
                ,C.CODIGOPOSTAL
                ,C.TELEFONO
                ,C.CORREOELECTRONICO
                ,C.FECHACREACION
                ,C.FECHAMODIFICACION
                ,C.USUARIOIDCREACION
                ,C.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACCLIENTES C
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=C.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=C.USUARIOIDMODIFICACION
            WHERE
                C.CLIENTEID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {

            $this->clienteId = $dato["CLIENTEID"];
            $this->codigo = $dato["CODIGO"];
            $this->nombre = $dato["NOMBRE"];
            $this->direccion = $dato["DIRECCION"];
            $this->direccionComplemento = $dato["DIRECCIONCOMPLEMENTO"];
            $this->codigoPostal = $dato["CODIGOPOSTAL"];
            $this->telefono = $dato["TELEFONO"];
            $this->correoElectronico = $dato["CORREOELECTRONICO"];
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
     * Obtener todos los registros de la tabla (FACCLIENTES) con paginación
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
                    (C.CODIGO LIKE '%$buscar%'
                    OR C.NOMBRE LIKE '%$buscar%')
            ";
        }

        $sentenciaSql = "
            SELECT
                C.CLIENTEID
                ,C.CODIGO
                ,C.NOMBRE
                ,C.DIRECCION
                ,C.DIRECCIONCOMPLEMENTO
                ,C.CODIGOPOSTAL
                ,C.TELEFONO
                ,C.CORREOELECTRONICO
                ,C.FECHACREACION
                ,C.FECHAMODIFICACION
                ,C.USUARIOIDCREACION
                ,C.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
                ,(SELECT COUNT(*) FROM FACFACTURAS WHERE CLIENTEID=C.CLIENTEID) AS FACTURAS
            FROM
                FACCLIENTES C
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=C.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=C.USUARIOIDMODIFICACION

            $condicion
            
            ORDER BY
                C.NOMBRE ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(C.CLIENTEID) AS CONTEO
            FROM
                FACCLIENTES C
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=C.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=C.USUARIOIDMODIFICACION

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
     * Obtener registros de la tabla (FACCLIENTES) con filtros
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
                C.CLIENTEID
                ,C.CODIGO
                ,C.NOMBRE
                ,C.DIRECCION
                ,C.DIRECCIONCOMPLEMENTO
                ,C.CODIGOPOSTAL
                ,C.TELEFONO
                ,C.CORREOELECTRONICO
                ,C.FECHACREACION
                ,C.FECHAMODIFICACION
                ,C.USUARIOIDCREACION
                ,C.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
                ,(SELECT COUNT(*) FROM FACFACTURAS WHERE CLIENTEID=C.CLIENTEID) AS FACTURAS
            FROM
                FACCLIENTES C
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=C.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=C.USUARIOIDMODIFICACION
            WHERE
                $filtro
            ORDER BY
                C.NOMBRE ASC
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
        $this->clienteId = -1;
        $this->codigo = null;
        $this->nombre = null;
        $this->direccion = null;
        $this->direccionComplemento = null;
        $this->codigoPostal = null;
        $this->telefono = null;
        $this->correoElectronico = null;
        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (FACCLIENTES) existente
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
            UPDATE FACCLIENTES SET " . $updates . " WHERE CLIENTEID = ?
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
     * Agregar un nuevo registro (FACCLIENTES)
     * 
     * @param string $nombre Nombre completo del cliente
     * @param string $direccion Dirección del cliente
     * @param string $direccionComplemento Complemento de la dirección del cliente
     * @param string $codigoPostal Código postal de la dirección del cliente
     * @param string $telefono Número de teléfono del cliente
     * @param string $correoElectronico Correo electrónico del cliente
     * @param int $usuarioId ID del usuario que está creando el registro
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(string $nombre, string $direccion, string $direccionComplemento, string $codigoPostal, string $telefono, string $correoElectronico, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO FACCLIENTES
                (NOMBRE, DIRECCION, DIRECCIONCOMPLEMENTO, CODIGOPOSTAL, TELEFONO, CORREOELECTRONICO, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, ?, ?, ?, GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $nombre, $direccion, $direccionComplemento, $codigoPostal, $telefono, $correoElectronico, $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->clienteId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (FACCLIENTES)
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
            DELETE FROM FACCLIENTES WHERE CLIENTEID = ?
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