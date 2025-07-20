<?php

require_once("SQLSrvBD.php");

class Sucursales
{
    //-------------------------------------------

    private $conn;

    public $sucursalId;
    public $nombre;
    public $esCasaMatriz;
    public $direccion;
    public $direccionComplemento;
    public $codigoPostal;
    public $telefono;
    public $telefonoServicio;
    
    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto Sucursales
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
     * Obtener datos de un registro (CONFSUCURSALES) por medio de ID
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
                 S.SUCURSALID
                ,S.NOMBRE
                ,S.ESCASAMATRIZ
                ,ISNULL(S.DIRECCION, '') AS DIRECCION
                ,ISNULL(S.DIRECCIONCOMPLEMENTO, '') AS DIRECCIONCOMPLEMENTO
                ,ISNULL(S.CODIGOPOSTAL, '') AS CODIGOPOSTAL
                ,ISNULL(S.TELEFONO, '') AS TELEFONO
                ,ISNULL(S.TELEFONOSERVICIO, '') AS TELEFONOSERVICIO
                ,S.FECHACREACION
                ,S.FECHAMODIFICACION
                ,S.USUARIOIDCREACION
                ,S.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                CONFSUCURSALES S
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=S.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=S.USUARIOIDMODIFICACION
            WHERE
                S.SUCURSALID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->sucursalId = $dato["SUCURSALID"];
            $this->nombre = $dato["NOMBRE"];
            $this->esCasaMatriz = $dato["ESCASAMATRIZ"];
            $this->direccion = $dato["DIRECCION"];
            $this->direccionComplemento = $dato["DIRECCIONCOMPLEMENTO"];
            $this->codigoPostal = $dato["CODIGOPOSTAL"];
            $this->telefono = $dato["TELEFONO"];
            $this->telefonoServicio = $dato["TELEFONOSERVICIO"];
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
     * Obtener todos los registros de la tabla (CONFSUCURSALES) con paginación
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
                 SUCURSALID
                ,NOMBRE
                ,ESCASAMATRIZ
            FROM
                CONFSUCURSALES
            
            $condicion

            ORDER BY
                NOMBRE ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(SUCURSALID) AS CONTEO
            FROM
                CONFSUCURSALES

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
     * Obtener registros de la tabla (CONFSUCURSALES) con filtros
     * 
     * @param string $filtro Sección de sentencia SQL con los filtros, ejemplo: $filtro = "CAMPO1=0 AND CAMPO2='ALGO'"
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
                 SUCURSALID
                ,NOMBRE
                ,ESCASAMATRIZ
                ,ISNULL(DIRECCION, '') AS DIRECCION
                ,ISNULL(DIRECCIONCOMPLEMENTO, '') AS DIRECCIONCOMPLEMENTO
                ,ISNULL(CODIGOPOSTAL, '') AS CODIGOPOSTAL
                ,ISNULL(TELEFONO, '') AS TELEFONO
            FROM
                CONFSUCURSALES
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
        $this->sucursalId = -1;
        $this->nombre = null;
        $this->esCasaMatriz = null;
        $this->direccion = null;
        $this->direccionComplemento = null;
        $this->codigoPostal = null;
        $this->telefono = null;
        $this->telefonoServicio = null;
        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (CONFSUCURSALES) existente
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
            UPDATE CONFSUCURSALES SET " . $updates . " WHERE SUCURSALID = ?
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
     * Agregar un nuevo registro (CONFSUCURSALES)
     * 
     * @param int Empresa a la que se está agregando la sucursal
     * @param string $nombre Nombre de la sucursal
     * @param int $esCasaMatriz Define si la sucursal es casa matriz de la empresa valores: [0, 1]
     * @param string $direccion Dirección de la sucursal
     * @param string $direccionComplemento Complemento de la dirección de la sucursal
     * @param string $codigoPostal Código postal de la dirección de la sucursal
     * @param string $telefono Número de teléfono de la sucursal
     * @param string $telefonoServicio Número de teléfono de servicio de la sucursal
     * @param int $usuarioId ID del ussuario que está creando el registro
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $empresaId, string $nombre, int $esCasaMatriz, string $direccion, string $direccionComplemento, string $codigoPostal, string $telefono, string $telefonoServicio, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO CONFSUCURSALES
                (EMPRESAID, NOMBRE, ESCASAMATRIZ, DIRECCION, DIRECCIONCOMPLEMENTO, CODIGOPOSTAL, TELEFONO, TELEFONOSERVICIO,
                FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?,
                 GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $empresaId, $nombre, $esCasaMatriz, $direccion, $direccionComplemento, $codigoPostal, $telefono, $telefonoServicio,
                                                $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->sucursalId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (CONFSUCURSALES)
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
            DELETE FROM CONFSUCURSALES WHERE SUCURSALID = ?
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
     * Obtener todos los registros de la tabla (CONFSUCURSALES) para mostrar en combo
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
                -1 AS SUCURSALID
                ,0 AS ESCASAMATRIZ
                ,'- $primeraOpcion -' AS NOMBRE

            UNION

            SELECT
                SUCURSALID
                ,ESCASAMATRIZ
                ,NOMBRE
            FROM
                CONFSUCURSALES
            ORDER BY
                NOMBRE ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener todos los registros de la tabla (CONFSUCURSALES) para mostrar en combo filtrando por acceso de usuario
     * (incluye fila de "SELECT")
     * 
     * @param int $usuarioId Usuario al que se le tomarán las sucursales a las que tiene acceso
     * @param string $primeraOpcion Opción que se muestra al inicio del combo, por defecto es "SELECT"
     * 
     * @return array Todos los registros encontrados en la tabla en orden alfabético con la primer opción "SELECT"
     * 
     */
    public function getListaParaComboDeUsuario(int $usuarioId, string $primeraOpcion = "SELECT"): array
    {
        $sentenciaSql = "
            SELECT
                -1 AS SUCURSALID
                ,0 AS ESCASAMATRIZ
                ,'- $primeraOpcion -' AS NOMBRE

            UNION

            SELECT
                S.SUCURSALID
                ,S.ESCASAMATRIZ
                ,S.NOMBRE
            FROM
                CONFSUCURSALES S
                JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=S.SUCURSALID AND SXU.USUARIOID=?
            ORDER BY
                NOMBRE ASC
        ";
        $datos = $this->conn->select($sentenciaSql, [$usuarioId]);

        return $datos;
    }

    //-------------------------------------------
}