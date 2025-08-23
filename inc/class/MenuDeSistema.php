<?php
//-----------------------------------------------

class MenuDeSistema
{
    //-------------------------------------------

    private $conn;
    private $usuarioId;

    //-------------------------------------------

    /**
     * Instancia de un objeto de MenuDeSistema
     * 
     * @param SQLSrvBD $conn Conexión a base de datos para realizar acciones sobre registros
     * @param int $usuarioId Usuario que ha realizado login en la aplicación y que se filtran accesos para menú
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
     * Obtener menú de un módulo para un usuario según sus accesos
     * 
     * @param string $moduloId ID del módulo que se va a generar menú
     * 
     * @return string Menú en formato HTML, en formato de menú de Bootstrap
     * 
     */
    public function getMenuDeSistema(string $moduloId): string
    {
        $menu = "
                    <li class=\"nav-item\">
                        <a class=\"nav-link\" aria-current=\"page\" href=\"?mod=inicio\">Home</a>
                    </li>
        ";

        // Obtener las opciones principales del menú
        $opcionesPrincipales = $this->getOpcionesPrincipales($moduloId);

        // Recorrer las opciones principales
        foreach ($opcionesPrincipales as $opcion) {
            if($opcion["ITEMSDEMENU"] > 0)
            {
                $menu .= $this->crearItemPrincipalConHijos($opcion);
            }
            else
            {
                $menu .= $this->crearItemPrincipalSinHijos($opcion);
            }
        }

        return $menu;
    }    

    //-------------------------------------------

     /**
     * Obtener las opciones principales del menú
     * 
     * @param string $moduloId ID del módulo que se va a obtener las opciones principales
     * 
     * @return array Resultado de obtener las opciones principales del mòdulo de la tabla ACCMENU
     * 
     */   
    private function  getOpcionesPrincipales(string $moduloId): array
    {
        $sentenciaSql = "
            SELECT
                MENUID
                ,NOMBRE
                ,VINCULO
                ,MENUPADREID
                ,(SELECT COUNT(*) FROM ACCMENU WHERE MENUPADREID=M.MENUID) AS ITEMSDEMENU
                ,PD.ESTADO AS ACCESO
            FROM
                ACCMENU M
                JOIN ACCPERFILESOPCIONES O ON O.PERFILOPCIONID=M.PERFILOPCIONID
                JOIN ACCPERFILESDETALLE PD ON PD.PERFILOPCIONID=O.PERFILOPCIONID AND PERFILID=(SELECT PERFILID FROM ACCUSUARIOS WHERE USUARIOID=?)
            WHERE
                M.MODULOID=?
                AND M.MENUPADREID IS NULL
            ORDER BY
                M.ORDEN
        ";
        $opciones = $this->conn->select($sentenciaSql, [$this->usuarioId, $moduloId]);

        return $opciones;
    }

    //-------------------------------------------

     /**
     * Crea el blque HTML para una opción de menú principal sin hijos
     * 
     * @param array $item Ítem de menú con los datos para generar el vínculo
     * 
     * @return string Bloque HTML para mostrar una opción de menú principal sin hijos
     * 
     */ 
    private function crearItemPrincipalSinHijos(array $item): string
    {
        $texto = $item["NOMBRE"];
        $vinculo = $item["VINCULO"];

        $opcion = "";
        if ($item["ACCESO"] == 1)
        {
            $opcion = "
                <li class=\"nav-item\">
                    <a class=\"nav-link\" href=\"$vinculo\">$texto</a>
                </li>
            ";
        }
        else
        {
            // Para no mostrar la opción
            $opcion = "";

            // Para mostrar la opción tenue y sin vínculo
            // $opcion = "
            //     <li class=\"nav-item\">
            //         <a class=\"nav-link disabled\" href=\"#\">$texto</a>
            //     </li>
            // ";
        }

        return $opcion;
    }

    //-------------------------------------------

     /**
     * Crea el blque HTML para una opción de menú principal con hijos
     * 
     * @param array $item Ítem de menú con los datos para generar el vínculo
     * 
     * @return string Bloque HTML para mostrar una opción de menú principal con hijos
     * 
     */ 
    private function crearItemPrincipalConHijos(array $item): string
    {
        $menuId = $item["MENUID"];
        $texto = $item["NOMBRE"];

        if ($item["ACCESO"] == 1)
        {
            $opcion = "
                <li class=\"nav-item dropdown\">
                    <a class=\"nav-link dropdown-toggle\" href=\"#\" role=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">$texto</a>
                    <ul class=\"dropdown-menu\">
            ";
        }
        else
        {
            // Para no mostrar la opción
            $opcion = "";

            // Para mostrar la opción tenue y sin vínculo
            // $opcion = "
            //     <li class=\"nav-item\">
            //         <a class=\"nav-link dropdown-toggle disabled\" aria-disabled=\"true\">$texto</a>
            //     </li>
            // ";
            return $opcion;
        }


        // Obtener hijos del menú principal y crear opciones
        $hijos = $this->getOpcionesHijas($menuId);
        foreach ($hijos as $hijo) {
            $texto = $hijo["NOMBRE"];
            $vinculo = $hijo["VINCULO"];

            if ($hijo["ACCESO"] == 1)
            {
                $opcion .= "
                    <li><a class=\"dropdown-item small\" href=\"$vinculo\">$texto</a></li>
                ";
            }
            else
            {
                // Para no mostrar la opción
                $opcion .= "";

                // Para mostrar la opción tenue y sin vínculo
                // $opcion .= "
                //     <li><a class=\"dropdown-item disabled small\" href=\"#\">$texto</a></li>
                // ";
            }
        }
        
        $opcion .= "
                </ul>
            </li>
        ";

        return $opcion;
    }

    //-------------------------------------------

     /**
     * Obtener las opciones hijas de un menú principal
     * 
     * @param int $menuId ID de la opción de menú principal a la que se le buscarán las hijas
     * 
     * @return array Resultado de obtener las opciones hijas de un menú principal
     * 
     */   
    private function  getOpcionesHijas(string $menuId): array
    {
        $sentenciaSql = "
            SELECT
                M.NOMBRE
                ,M.VINCULO
                ,PD.ESTADO AS ACCESO
            FROM
                ACCMENU M
                JOIN ACCPERFILESOPCIONES O ON O.PERFILOPCIONID=M.PERFILOPCIONID
                JOIN ACCPERFILESDETALLE PD ON PD.PERFILOPCIONID=O.PERFILOPCIONID AND PERFILID=(SELECT PERFILID FROM ACCUSUARIOS WHERE USUARIOID=?)
            WHERE
                M.MENUPADREID = ?
            ORDER BY
                M.ORDEN
        ";
        $opciones = $this->conn->select($sentenciaSql, [$this->usuarioId, $menuId]);

        return $opciones;
    }

    //-------------------------------------------

    /**
     * Obtener lista de módulos a los que tiene acceso un usuario
     * 
     * @param int $usuarioId ID del usuarios que se tomarán sus accesos para obtener módulos
     * 
     * @return array Lista de módulos a los que tiene acceso el usuario
     * 
     */
    public function getModulos(): array
    {
        $sentenciaSql = "
            SELECT
                M.MODULOID
                ,M.NOMBRE
                ,PO.CODIGO
                ,PD.ESTADO AS ACCESO
            FROM
                ACCMODULOS M
                JOIN ACCPERFILESOPCIONES PO ON PO.MODULOID=M.MODULOID AND LEN(PO.CODIGO)=2
                JOIN ACCPERFILESDETALLE PD ON PD.PERFILOPCIONID=PO.PERFILOPCIONID AND PD.PERFILID=(SELECT PERFILID FROM ACCUSUARIOS WHERE USUARIOID=?)
            WHERE
                PD.ESTADO = 1
            ORDER BY
                M.NOMBRE
        ";
        $modulos = $this->conn->select($sentenciaSql, [$this->usuarioId]);

        return $modulos;
    }   

    //-------------------------------------------

    /**
     * Obtener el vínculo de una opción de menú
     * 
     * @param int $menuId Opción de menú de la que se va a obtener el vínculo
     * 
     * @return string Vínculo de la opción de menú
     * 
     */
    public function getVinculoDeOpcionDeMenu(int $menuId): string
    {
        $sentenciaSql = "
            SELECT
                NOMBRE,
                VINCULO
            FROM
                ACCMENU
            WHERE
                MENUID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$menuId]);

        $vinculo = "";
        if (count($datos) >= 1)
        {
            $vinculo = $datos[0]["VINCULO"];
        }
        return $vinculo;
    }   

    //-------------------------------------------

    /**
     * Obtener el módulo de inicio del usuario
     * 
     * @param void
     * 
     * @return string ID del módulo de inicio del usuario
     * 
     */
    public function GetModuloDeInicioDeUsuario(): string
    {
        $sentenciaSql = "
            SELECT
                ISNULL(U.MODULODEINICIOID, '') AS MODULODEINICIOID
            FROM
                ACCUSUARIOS U
            WHERE
                USUARIOID=1
        ";
        $datos = $this->conn->select($sentenciaSql, [$this->usuarioId]);

        $moduloDeInicio = "";
        if (count($datos) >= 1)
        {
            $moduloDeInicio = $datos[0]["MODULODEINICIOID"];
        }
        return $moduloDeInicio;
    }   

    //-------------------------------------------
}