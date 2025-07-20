<?php

require_once("SQLSrvBD.php");

class TiposDePago
{
    //-------------------------------------------

    private $conn;

    public $tipoDePagoId;
    public $nombre;
    public $sumaImpuesto;
    public $permitePagoSinImpuesto;
    
    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto TiposDePago
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
     * Obtener datos de un registro (FACTIPOSDEPAGO) por medio de ID
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
                 TP.TIPODEPAGOID
                ,TP.NOMBRE
                ,TP.SUMAIMPUESTO
                ,TP.PERMITEPAGOSINIMPUESTO
                ,TP.FECHACREACION
                ,TP.FECHAMODIFICACION
                ,TP.USUARIOIDCREACION
                ,TP.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACTIPOSDEPAGO TP
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=TP.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=TP.USUARIOIDMODIFICACION
            WHERE
                TP.TIPODEPAGOID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->tipoDePagoId = $dato["TIPODEPAGOID"];
            $this->nombre = $dato["NOMBRE"];
            $this->sumaImpuesto = $dato["SUMAIMPUESTO"];
            $this->permitePagoSinImpuesto = $dato["PERMITEPAGOSINIMPUESTO"];
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
     * Obtener todos los registros de la tabla (FACTIPOSDEPAGO) con paginación
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
                    TP.NOMBRE LIKE '%$buscar%'
            ";
        }

        $sentenciaSql = "
            SELECT
                 TP.TIPODEPAGOID
                ,TP.NOMBRE
                ,TP.SUMAIMPUESTO
                ,TP.PERMITEPAGOSINIMPUESTO
                ,TP.FECHACREACION
                ,TP.FECHAMODIFICACION
                ,TP.USUARIOIDCREACION
                ,TP.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACTIPOSDEPAGO TP
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=TP.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=TP.USUARIOIDMODIFICACION
            
            $condicion

            ORDER BY
                TP.NOMBRE ASC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(TIPODEPAGOID) AS CONTEO
            FROM
                FACTIPOSDEPAGO TP
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=TP.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=TP.USUARIOIDMODIFICACION

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
     * Obtener registros de la tabla (FACTIPOSDEPAGO) con filtros
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
                 TP.TIPODEPAGOID
                ,TP.NOMBRE
                ,TP.SUMAIMPUESTO
                ,TP.PERMITEPAGOSINIMPUESTO
                ,TP.FECHACREACION
                ,TP.FECHAMODIFICACION
                ,TP.USUARIOIDCREACION
                ,TP.USUARIOIDMODIFICACION
                ,UC.USUARIO AS USUARIOCREO
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACTIPOSDEPAGO TP
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=TP.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=TP.USUARIOIDMODIFICACION
            WHERE
                $filtro
            ORDER BY
                TP.NOMBRE ASC 
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
        $this->tipoDePagoId = -1;
        $this->nombre = null;
        $this->sumaImpuesto = null;
        $this->permitePagoSinImpuesto = null;
        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (FACTIPOSDEPAGO) existente
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
            UPDATE FACTIPOSDEPAGO SET " . $updates . " WHERE TIPODEPAGOID = ?
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
     * Agregar un nuevo registro (FACTIPOSDEPAGO)
     * 
     * @param string $nombre Nombre del tipo de pago
     * @param int $sumaImpuesto Define si el tipo de pago suma impuesto en reportes
     * @param int $permitePagoSinImpuesto Define si el tipo de pago permite pagos que no se calcula impuesto
     * @param int $usuarioId ID del ussuario que está creando el registro
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(string $nombre, int $sumaImpuesto, int $permitePagoSinImpuesto, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO FACTIPOSDEPAGO
                (NOMBRE, SUMAIMPUESTO, PERMITEPAGOSINIMPUESTO, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $nombre, $sumaImpuesto, $permitePagoSinImpuesto, $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->tipoDePagoId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (FACTIPOSDEPAGO)
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
            DELETE FROM FACTIPOSDEPAGO WHERE TIPODEPAGOID = ?
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
     * Obtener todos los registros de la tabla (FACTIPOSDEPAGO) para mostrar en combo
     * (incluye fila de "SELECT")
     * 
     * @param bool $soloMostrarPagoSinImpuesto Muestra solamente los tipos de pago que permiten pagos sin impuesto
     * @param string $primeraOpcion Primera opción que se muestra en el combo, por defecto es: SELECT
     * 
     * @return array Todos los registros encontrados en la tabla en orden alfabético con la primer opción "SELECT"
     * 
     */
    public function getListaParaCombo(bool $soloMostrarPagoSinImpuesto, string $primeraOpcion = "SELECT"): array
    {
        $sentenciaSql = "";
        if ($soloMostrarPagoSinImpuesto)
        {
            $sentenciaSql = "
                SELECT
                    -1 AS TIPODEPAGOID
                    ,'- $primeraOpcion -' AS NOMBRE

                UNION

                SELECT
                    TIPODEPAGOID
                    ,NOMBRE
                FROM
                    FACTIPOSDEPAGO
                WHERE
                    PERMITEPAGOSINIMPUESTO = 1
                ORDER BY
                    NOMBRE ASC
            ";
        }
        else
        {
            $sentenciaSql = "
                SELECT
                    -1 AS TIPODEPAGOID
                    ,'- $primeraOpcion -' AS NOMBRE

                UNION

                SELECT
                    TIPODEPAGOID
                    ,NOMBRE
                FROM
                    FACTIPOSDEPAGO
                ORDER BY
                    NOMBRE ASC
            ";
        }
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

}