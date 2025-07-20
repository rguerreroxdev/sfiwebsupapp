<?php

require_once("SQLSrvBD.php");

class Accesos
{
    //-------------------------------------------

    private $conn;
    private $usuarioId;

    //-------------------------------------------

    /**
     * Instancia un objeto Accesos
     * 
     * @param SQLSrvBD $conn Conexión a base de datos para realizar acciones sobre registros
     * @param int $usuarioId Usuario al que se le obtendran los accesos
     * 
     */
    // Constructor: Recibe conexión a base de datos
    // para realizar acciones sobre tabla
    public function __construct(SQLSrvBD $conn, int $usuarioId)
    {
        $this->conn = $conn;
        $this->usuarioId = $usuarioId;
    }

    //-------------------------------------------

    /**
     * Se obtiene el valor de acceso a un módulo que tiene un usuario por medio de su perfil
     * 
     * @param string $moduloId Módulo al que se le va a obtener el acceso del usuario
     * 
     * @return bool Devuelve true si tiene acceso, false si no lo tiene
     */
    private function getAccesoAModulo(string $moduloId): bool
    {
        $acceso = false;

        $sentenciaSql = "
            SELECT
                U.USUARIOID,
                PO.CODIGO,
                PO.DESCRIPCION,
                PD.ESTADO AS ACCESO
            FROM
                ACCUSUARIOS U
                JOIN ACCPERFILESDETALLE PD ON PD.PERFILID=U.PERFILID
                JOIN ACCPERFILESOPCIONES PO ON PO.PERFILOPCIONID=PD.PERFILOPCIONID AND PO.MODULOID=? AND LEN(PO.CODIGO)=2
            WHERE
                USUARIOID = ?
        ";
        
        $resultado = $this->conn->select(
            $sentenciaSql,
            [$moduloId, $this->usuarioId]
        );

        if (count($resultado))
        {
            $acceso = $resultado[0]["ACCESO"] == 1;
        }

        return $acceso;
    }

    //-------------------------------------------

    /**
     * Se obtiene el valor de acceso a una opción del sistema
     * 
     * @param string $codigo Código de la opción a la que se le va a leer el acceso del usuario
     * 
     * @return bool Devuelve true si tiene acceso, false si no lo tiene
     */
    public function getAccesoAOpcion(string $codigo): bool
    {
        $acceso = false;

        $sentenciaSql = "
            SELECT
                U.USUARIOID,
                PO.CODIGO,
                PO.DESCRIPCION,
                PD.ESTADO AS ACCESO
            FROM
                ACCUSUARIOS U
                JOIN ACCPERFILESDETALLE PD ON PD.PERFILID=U.PERFILID
                JOIN ACCPERFILESOPCIONES PO ON PO.PERFILOPCIONID=PD.PERFILOPCIONID AND PO.CODIGO=?
            WHERE
                USUARIOID = ?
        ";
        
        $resultado = $this->conn->select(
            $sentenciaSql,
            [$codigo, $this->usuarioId]
        );

        if (count($resultado))
        {
            $acceso = $resultado[0]["ACCESO"] == 1;
        }

        return $acceso;
    }

    //-------------------------------------------

    /**
     * Valida el acceso de un usuario a una opción de menú, si no tiene acceso muestra pantalla de error
     * 
     * @param string $moduloId ID del módulo al que pertenece la opción de menú
     * @param string $codigo Código de la opción de menú a la que se le va a leer el acceso del usuario
     * 
     * @return bool Retorna si tiene o no acceso tanto al módulo como a la opción de menú
     */
    public function validarAccesoAOpcionDeMenu(string $moduloId, string $codigo): bool
    {
        $accesoAModulo = $this->getAccesoAModulo($moduloId);
        $accesoAOpcion = $this->getAccesoAOpcion($codigo);

        return ($accesoAModulo && $accesoAOpcion);
    }

    //-------------------------------------------

    /**
     * Se obtiene la lista de opciones que tiene acceso un usuario a partir de una opción padre
     * 
     * @param string $codigo Código de la opción a la que se le va a leer los accesos de ella y sus hijas
     * 
     * @return array Devuelve la lista de códigos a las que tiene acceso
     */
    public function getListaDeOpcionesConAcceso(string $codigo): array
    {
        $sentenciaSql = "
            SELECT
                PO.CODIGO
            FROM
                ACCUSUARIOS U
                JOIN ACCPERFILESDETALLE PD ON PD.PERFILID=U.PERFILID
                JOIN ACCPERFILESOPCIONES PO ON PO.PERFILOPCIONID=PD.PERFILOPCIONID
            WHERE
                U.USUARIOID = ?
                AND PO.CODIGO LIKE ?
                AND PD.ESTADO = 1
        ";
        
        $resultado = $this->conn->select(
            $sentenciaSql,
            [$this->usuarioId, $codigo . "%"]
        );

        $arraySucursales = array();
        foreach ($resultado as $acceso) {
            array_push($arraySucursales, $acceso["CODIGO"]);
        }

        return $arraySucursales;
    }

    //-------------------------------------------

    /**
     * Se obtiene la lista de sucursales a las que tiene acceso un usuario
     * 
     * @param void No necesita un dato, se usa el usuarioId que se creó al instanciar el objeto Accesos
     * 
     * @return array Devuelve la lista de sucursales a las que tiene acceso el usuario
     */
    public function getListaDeSucursalesDeUsuario(): array
    {
        $sentenciaSql = "
            SELECT DISTINCT
                SUCURSALID
            FROM
                ACCSUCURSALESXUSUARIO
            WHERE
                USUARIOID = ?
            ORDER BY
                SUCURSALID
        ";
        
        $resultado = $this->conn->select(
            $sentenciaSql,
            [$this->usuarioId]
        );

        $arraySucursales = array();
        foreach ($resultado as $sucursal) {
            array_push($arraySucursales, $sucursal["SUCURSALID"]);
        }

        return $arraySucursales;
    }

    //-------------------------------------------
}