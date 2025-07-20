<?php

require_once("SQLSrvBD.php");

class Perfiles
{
    //-------------------------------------------

    private $conn;

    public $perfilId;
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
     * Instancia un objeto Perfiles
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
     * Obtener datos de un registro (ACCPERFILES) por medio de ID
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
                P.PERFILID
                ,P.NOMBRE
                ,P.FECHACREACION
                ,P.FECHAMODIFICACION
                ,P.USUARIOIDCREACION
                ,P.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                ACCPERFILES P
                LEFT JOIN ACCUSUARIOS UC ON UC.USUARIOID=P.USUARIOIDCREACION
                LEFT JOIN ACCUSUARIOS UM ON UM.USUARIOID=P.USUARIOIDMODIFICACION
            WHERE
                P.PERFILID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->perfilId = $dato["PERFILID"];
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
     * Obtener todos los registros de la tabla (ACCPERFILES) con paginación
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
                PERFILID
                ,NOMBRE
                ,FECHACREACION
                ,FECHAMODIFICACION
                ,USUARIOIDCREACION
                ,USUARIOIDMODIFICACION
            FROM
                ACCPERFILES
            
            $condicion

            ORDER BY
                NOMBRE ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(PERFILID) AS CONTEO
            FROM
                ACCPERFILES

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
     * Obtener todos los accesos de un perfil
     * 
     * @param int $perfilId El perfil al cuál se le van a obtener los accesos
     * @param string $moduloId El módulo seleccionado para mostrar sus accesos
     * @param string $codigoMenu Código del menú al que se le van alistar sus opciones
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAccesosDePerfil(int $perfilId, string $moduloId, string $codigoMenu): array
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            SELECT
                PD.PERFILDETALLEID,
                PO.PERFILOPCIONID,
                M.MODULOID,
                M.NOMBRE AS MODULO,
                PO.CODIGO,
                PO.DESCRIPCION,
                PD.ESTADO
            FROM
                ACCPERFILESOPCIONES PO
                JOIN ACCPERFILESDETALLE PD ON PD.PERFILOPCIONID=PO.PERFILOPCIONID
                JOIN ACCMODULOS M ON M.MODULOID=PO.MODULOID
            WHERE
                PD.PERFILID = ?
                AND M.MODULOID = ?
                AND (
                    PO.CODIGO = SUBSTRING('$codigoMenu', 1, 2)
                    OR PO.CODIGO LIKE '$codigoMenu%'
                )
            ORDER BY
                PO.CODIGO
        ";
        $datos = $this->conn->select($sentenciaSql, [$perfilId, $moduloId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener registros de la tabla (INVPERFILES) con filtros
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
                PERFILID
                ,NOMBRE
                ,FECHACREACION
                ,FECHAMODIFICACION
                ,USUARIOIDCREACION
                ,USUARIOIDMODIFICACION
            FROM
                ACCPERFILES
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
        $this->perfilId = -1;
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
     * Edita un registro (ACCPERFILES) existente
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
            UPDATE ACCPERFILES SET " . $updates . " WHERE PERFILID = ?
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
     * Agregar un nuevo registro (ACCPERFILES)
     * 
     * @param string $nombre Nombre del perfil
     * @param int $usuarioId ID del usuario que está creando el registro
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(string $nombre, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            EXECUTE SPACCCREARPERFIL ?, ?
        ";
        $datoResultado = $this->conn->execute($sentenciaSql,
                                            [
                                                $nombre, $usuarioId
                                            ],
                                            true);

        $agregado = false;
        if (count($datoResultado) > 0)
        {
            $agregado = $datoResultado[0]["EXISTEERROR"] == 0;
            $this->mensajeError = $datoResultado[0]["MENSAJEDEERROR"];
            $this->perfilId = $datoResultado[0]["ID"];
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (INVPERFILES)
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
            EXECUTE SPACCELIMINARPERFIL ?
        ";
        $resultado = $this->conn->execute($sentenciaSql, [$id]);
        
        $eliminado = false;
        if (count($resultado) > 0)
        {
            $eliminado = $resultado[0]["EXISTEERROR"] == 0;
            $this->mensajeError = $resultado[0]["MENSAJEDEERROR"];
        }

        return $eliminado;
    }

    //-------------------------------------------

    /**
     * Obtener todos los registros de la tabla (ACCPERFILES) para mostrar en combo
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
                -1 AS PERFILID
                ,'- $primeraOpcion -' AS NOMBRE

            UNION

            SELECT
                PERFILID
                ,NOMBRE
            FROM
                ACCPERFILES
            ORDER BY
                NOMBRE ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Edita el estado de una opción de perfil (ACCPERFILESDETALLE)
     * 
     * @param int $perfilDetalleId Opción de perfil en tabla ACCPERFILESDETALLE que va a cambiar de estado
     * @param int $estado Estado al que va a cambar la opción del perfil 0: sin acceso, 1: con acceso
     * 
     * @return bool Resultado de actualizar el registro: true: fue editado, false: no fue editado
     * 
     */
    public function cambiarEstadoPerfilDetalle(int $perfilDetalleId, int $estado): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            UPDATE ACCPERFILESDETALLE SET ESTADO = ? WHERE PERFILDETALLEID = ?
        ";
        $editado = $this->conn->update($sentenciaSql, [$estado, $perfilDetalleId]);

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
     * Obtener la lista de módulos del sistema para mostrarlos en combo
     * 
     * @param void
     * 
     * @return array Todos los módulos orden alfabético
     * 
     */
    public function getModulosParaCombo(): array
    {
        $sentenciaSql = "
            SELECT
                MODULOID,
                NOMBRE
            FROM
                ACCMODULOS
            ORDER BY
                NOMBRE
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener la lista de opciones principales de menú de un módulo para mostrarlos en combo
     * 
     * @param string $moduloId Módulo al cual se le van a listar sus opciones principales de menú
     * 
     * @return array Todos las opciones principales de menú en orden alfabético
     * 
     */
    public function getMenusPrincipalesParaCombo(string $moduloId): array
    {
        $sentenciaSql = "
            SELECT
                PO.MODULOID,
                PO.CODIGO,
                REPLACE(PO.DESCRIPCION, 'Menu: ', '') AS DESCRIPCION
            FROM
                ACCPERFILESOPCIONES PO
            WHERE
                MODULOID = ?
                AND (LEN(CODIGO) - LEN(REPLACE(CODIGO, '.', ''))) = 1
        ";
        $datos = $this->conn->select($sentenciaSql, [$moduloId]);

        return $datos;
    }

    //-------------------------------------------
}