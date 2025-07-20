<?php

require_once("SQLSrvBD.php");
require_once("Sesion.php");

class Usuario
{
    //-------------------------------------------

    private $conn;

    public $usuarioId;
    public $nombreCompleto;
    public $usuario;
    public $contrasena;
    public $activo;
    public $cambiarContrasena;
    public $perfilId;
    public $perfil;
    public $moduloDeInicioId;
    public $moduloDeInicio;
    
    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto Usuario
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
     * Obtener datos de un registro (ACCUSUARIOS) por medio de ID
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
                U.USUARIOID
               ,U.NOMBRECOMPLETO
               ,U.USUARIO
               ,U.CONTRASENA
               ,U.ACTIVO
               ,U.CAMBIARCONTRASENA
               ,U.PERFILID
               ,U.MODULODEINICIOID
               ,M.NOMBRE AS MODULODEINICIO
               ,P.NOMBRE AS PERFIL
               ,U.FECHACREACION
               ,U.FECHAMODIFICACION
               ,U.USUARIOIDCREACION
               ,U.USUARIOIDMODIFICACION
               ,UC.USUARIO AS USUARIOCREO
               ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                ACCUSUARIOS U
                JOIN ACCPERFILES P ON P.PERFILID=U.PERFILID
                LEFT JOIN ACCUSUARIOS UC ON UC.USUARIOID=U.USUARIOIDCREACION
                LEFT JOIN ACCUSUARIOS UM ON UM.USUARIOID=U.USUARIOIDMODIFICACION
                LEFT JOIN ACCMODULOS M ON M.MODULOID=U.MODULODEINICIOID
            WHERE
                U.USUARIOID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->usuarioId = $dato["USUARIOID"];
            $this->nombreCompleto = $dato["NOMBRECOMPLETO"];
            $this->usuario = $dato["USUARIO"];
            $this->contrasena = $dato["CONTRASENA"];
            $this->activo = $dato["ACTIVO"];
            $this->cambiarContrasena = $dato["CAMBIARCONTRASENA"];
            $this->perfilId = $dato["PERFILID"];
            $this->perfil = $dato["PERFIL"];
            $this->moduloDeInicioId = $dato["MODULODEINICIOID"];
            $this->moduloDeInicio = $dato["MODULODEINICIO"];
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
     * Obtener todos los registros de la tabla (ACCUSUARIOS) con paginación
     * 
     * @param string $buscar Texto a buscar en los campos de la tabla
     * @param int $activo Indica si se buscan activos "1", inactivos "0" o todos "-1"
     * @param int $perfilId Perfil para filtrar usuarios
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllConPaginacion(string $buscar, int $perfilId, int $activo, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "";
        if (trim($buscar) != "")
        {
            $condicion = "
                WHERE
                    (U.NOMBRECOMPLETO LIKE '%$buscar%'
                    OR U.USUARIO LIKE '%$buscar%'
                    OR P.NOMBRE LIKE '%$buscar%')
            ";
        }

        switch ($activo) {
            case 0:
                $condicion .= strlen($condicion) > 0 ? " AND U.ACTIVO=0 " : " WHERE U.ACTIVO=0 ";
                break;
            case 1:
                $condicion .= strlen($condicion) > 0 ? " AND U.ACTIVO=1 " : " WHERE U.ACTIVO=1 ";
                break;
        }

        if ($perfilId > -1)
        {
            $condicion .= strlen($condicion) > 0 ? " AND U.PERFILID=$perfilId " : " WHERE U.PERFILID=$perfilId ";
        }

        $sentenciaSql = "
            SELECT
                U.USUARIOID
               ,U.NOMBRECOMPLETO
               ,U.USUARIO
               ,U.CONTRASENA
               ,U.ACTIVO
               ,U.CAMBIARCONTRASENA
               ,U.PERFILID
               ,P.NOMBRE AS PERFIL
               ,U.FECHACREACION
            FROM
                ACCUSUARIOS U
                JOIN ACCPERFILES P ON P.PERFILID=U.PERFILID
            
            $condicion

            ORDER BY
                U.NOMBRECOMPLETO ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(U.USUARIOID) AS CONTEO
            FROM
                ACCUSUARIOS U
                JOIN ACCPERFILES P ON P.PERFILID=U.PERFILID

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
     * Obtener registros de la tabla (ACCUSUARIOS) con filtros
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
            U.USUARIOID
           ,U.NOMBRECOMPLETO
           ,U.USUARIO
           ,U.CONTRASENA
           ,U.ACTIVO
           ,U.CAMBIARCONTRASENA
           ,U.PERFILID
           ,U.FECHACREACION
        FROM
            ACCUSUARIOS U
            JOIN ACCPERFILES P ON P.PERFILID=U.PERFILID
        WHERE
            $filtro
        ORDER BY
            U.USUARIO ASC
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
        $this->usuarioId = -1;
        $this->nombreCompleto = null;
        $this->usuario = null;
        $this->contrasena = null;
        $this->activo = null;
        $this->cambiarContrasena = null;
        $this->perfilId = null;
        $this->perfil = null;
        $this->moduloDeInicioId = null;
        $this->moduloDeInicio = null;
        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (ACCUSUARIOS) existente
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
            UPDATE ACCUSUARIOS SET " . $updates . " WHERE USUARIOID = ?
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
     * Agregar un nuevo registro (ACCUSUARIOS)
     * 
     * @param string $nombreCompleto Nombre real del usuario
     * @param string $usuario Login o Alias
     * @param string $contrasena Contraseña de usuario
     * @param int $activo 0: No activo, 1: Activo
     * @param int $cambiarContrasena 0: No cambiar contraseña en próximo inicio de sesión, 1: Cambiar contraseña en próximo inicio de sesión
     * @param int $perfilId ID del perfil de acceso al que pertenece el usuario
     * @param int $usuarioId ID del usuario que crea el registro
     * @param string $moduloId ID del módulo de inicio que tendrá asignado el usuario
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(string $nombreCompleto, string $usuario, string $contrasena, int $activo, int $cambiarContrasena, int $perfilId, int $usuarioId, string $moduloId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO ACCUSUARIOS
                (NOMBRECOMPLETO, USUARIO, CONTRASENA, ACTIVO, CAMBIARCONTRASENA, PERFILID, MODULODEINICIOID, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $nombreCompleto, $usuario, $contrasena, $activo, $cambiarContrasena, $perfilId, $moduloId, $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->usuarioId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (ACCUSUARIOS)
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
            EXECUTE SPACCELIMINARUSUARIO ?
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
     * Realiza inicio de sesión en aplicación
     * Si se logra validar usuario y contraseña, se crean variables de sesión de aplicación
     * 
     * @param string $usuario Usuario con el que se intenta hacer inicio de sesión
     * @param string $contrasena Contraseña del usuario que intenta hacer inicio de sesión
     * 
     * @return bool Resultado de validar al usuario con su contraseña
     */
    public function login(string $usuario, string $contrasena)
    {
        $resultado = $this->conn->select(
            "SELECT * FROM ACCUSUARIOS WHERE USUARIO = ? AND ACTIVO=1",
            [$usuario]
        );

        if(count($resultado) && $resultado[0]["CONTRASENA"] == md5($contrasena))
        {
            Sesion::setVariableDeSesion("sesion", true);
            Sesion::setVariableDeSesion("usuario", $usuario);
            Sesion::setVariableDeSesion("usuarioId", $resultado[0]["USUARIOID"]);
            
            return true;
        }

        return false;
    }

    //-------------------------------------------

    /**
     * Obtener la lista de estado "activo", "inactivo", "todos" para mostrar en combo
     * 
     * @param void
     * 
     * @return array La lista de estados "activo", "inactivo" y "todos"
     * 
     */
    public function getEstadoParaCombo(): array
    {
        $sentenciaSql = "
            SELECT
                -1 AS ESTADO
                ,'- ALL -' AS NOMBRE

            UNION

            SELECT
                1 AS ESTADO
                ,'Active' AS NOMBRE

            UNION

            SELECT
                0 AS ESTADO
                ,'Deactivated' AS NOMBRE

            ORDER BY
                NOMBRE ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener la lista de sucursales a las que tiene acceso un usuario
     * 
     * @param int $usuarioId Usuario al que se le tomarán las sucursales a las que tiene acceso
     * 
     * @return array La lista de sucursales a las que tiene acceso un usuario
     * 
     */
    public function getSucursalesXUsuario(int $usuarioId): array
    {
        $sentenciaSql = "
            SELECT
                SU.SUCURSALXUSUARIOID
                ,SU.USUARIOID
                ,SU.SUCURSALID
                ,S.NOMBRE AS SUCURSAL
            FROM
                ACCSUCURSALESXUSUARIO SU
                JOIN CONFSUCURSALES S ON S.SUCURSALID=SU.SUCURSALID
            WHERE
                SU.USUARIOID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$usuarioId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Verifica si un usuario está marcado para cambiar contraseña
     * 
     * @param int $usuarioId Usuario al que se le verifica el estado de cambio de contraseña
     * 
     * @return bool True si debe cambiar, False si no debe cambiar
     * 
     */
    public function debeCambiarContrasena(int $usuarioId): bool
    {
        $sentenciaSql = "
            SELECT
                U.USUARIOID
                ,U.CAMBIARCONTRASENA
            FROM
                ACCUSUARIOS U
            WHERE
                U.USUARIOID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$usuarioId]);

        $cambiarContrasena = false;
        if (count($datos) > 0)
        {
            $cambiarContrasena = $datos[0]["CAMBIARCONTRASENA"] == 1;
        }

        return $cambiarContrasena;
    }

    //-------------------------------------------

    /**
     * Obtener la lista de módulos del sistema
     * (incluye fila de "SELECT")
     * 
     * @param void
     * 
     * @return array La lista de módulos del sistema
     * 
     */
    public function getListaDeModulos(string $primeraOpcion = "SELECT"): array
    {
        $sentenciaSql = "
            SELECT
                'AAA' AS MODULOID
                ,'- $primeraOpcion -' AS NOMBRE

            UNION

            SELECT
                M.MODULOID
                ,M.NOMBRE
            FROM
                ACCMODULOS M

            ORDER BY
                NOMBRE
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener listado para búsqueda en procesos, como vendedores en facturacion
     * 
     * @param string $buscar Texto a buscar en los campos de la tabla
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllActivosSinAdmin(string $buscar, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "
            WHERE
                U.ACTIVO=1
                AND U.USUARIOID<>1

        ";
        if (trim($buscar) != "")
        {
            $condicion .= "
                AND
                    (U.NOMBRECOMPLETO LIKE '%$buscar%'
                    OR U.USUARIO LIKE '%$buscar%')
            ";
        }

        $sentenciaSql = "
            SELECT
                U.USUARIOID
               ,U.NOMBRECOMPLETO
               ,U.USUARIO
            FROM
                ACCUSUARIOS U
            
            $condicion

            ORDER BY
                U.NOMBRECOMPLETO ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(U.USUARIOID) AS CONTEO
            FROM
                ACCUSUARIOS U

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
}