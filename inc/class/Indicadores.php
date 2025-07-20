<?php

require_once("SQLSrvBD.php");

class Indicadores
{
    //-------------------------------------------

    private $conn;

    //-------------------------------------------

    /**
     * Instancia un objeto Indicadores
     * 
     * @param SQLSrvBD $conn Conexión a base de datos para realizar acciones sobre registros
     * 
     */
    // Constructor: Recibe conexión a base de datos
    // para realizar acciones sobre tabla
    public function __construct(SQLSrvBD $conn)
    {
        $this->conn = $conn;
    }

    //-------------------------------------------

    /**
     * Conteo de Recepciones de Carga para mostrar en Home
     * 
     * @param void No necesita parámetros
     * 
     * @return array Se retorna los conteos de recepciones
     * 
     */
    public function conteoDeRecepcionesDeCarga(): array
    {
        $sentenciaSql = "
            SELECT
                1 AS ORDEN,
                'FORMULATION' AS ESTADO,
                COUNT(*) AS CONTEO

            FROM
                INVRECEPCIONESDECARGA
            WHERE
                ESTADO = 'FOR'

            UNION

            SELECT
                2 AS ORDEN,
                'CLOSED' AS ESTADO,
                COUNT(*) AS CONTEO

            FROM
                INVRECEPCIONESDECARGA
            WHERE
                ESTADO = 'CER'

            UNION

            SELECT
                3 AS ORDEN,
                'POSTED' AS ESTADO,
                COUNT(*) AS CONTEO

            FROM
                INVRECEPCIONESDECARGA
            WHERE
                ESTADO = 'PRO'

            ORDER BY
                ORDEN
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * lista de recepciones de carga próximas a ingresar
     * 
     * @param void No necesita parámetros
     * 
     * @return array Se retorna lista de recepciones
     * 
     */
    public function recepcionesDeCargaProximas(): array
    {
        $sentenciaSql = "
            SELECT
                R.RECEPCIONDECARGAID,
                CONVERT(VARCHAR, R.FECHADEEMISION, 101) AS FECHADEEMISION,
                CONVERT(VARCHAR, R.FECHADERECEPCION, 101) AS FECHADERECEPCION,
                CASE
                    WHEN R.ESTADO = 'FOR' THEN 'Formulated'
                    WHEN R.ESTADO = 'CER' THEN 'Closed'
                    ELSE '-'
                END AS ESTADO,
                R.CORRELATIVO,
                R.NUMERODEDOCUMENTO,
                P.CODIGO + ' - ' + P.NOMBRE AS PROVEEDOR,
                S.NOMBRE AS SUCURSAL
            FROM 
                INVRECEPCIONESDECARGA R
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=R.PROVEEDORID
                JOIN CONFSUCURSALES S ON S.SUCURSALID=R.SUCURSALID
            WHERE
                R.ESTADO NOT IN ('ANU', 'PRO')
                AND FECHADERECEPCION >= CONVERT(DATE, GETDATE())
            ORDER BY
                R.FECHADERECEPCION ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * lista de traslados a ingresar (procesar destino o anular por rechazo)
     * 
     * @param int $usuarioId Usuario con acceso a sucursales para obtener los traslados
     * 
     * @return array Se retorna lista de traslados
     * 
     */
    public function trasladosProximos(int $usuarioId): array
    {
        $sentenciaSql = "
            SELECT
                T.TRASLADOID,
                T.FECHACREACION,
	            CONVERT(VARCHAR, T.FECHACREACION, 101) AS FECHACREACIONVARCHAR,
                SO.NOMBRE AS SUCURSALORIGEN,
                SD.NOMBRE AS SUCURSALDESTINO,
                T.CORRELATIVO,
                'Post origin' AS ESTADO
            FROM
                INVTRASLADOS T
                JOIN CONFSUCURSALES SO ON SO.SUCURSALID=T.SUCURSALORIGENID
                JOIN CONFSUCURSALES SD ON SD.SUCURSALID=T.SUCURSALDESTINOID
            WHERE
                T.ESTADO = 'PRO'
                AND T.SUCURSALDESTINOID IN (
                    SELECT
                        SXU.SUCURSALID
                    FROM
                        ACCSUCURSALESXUSUARIO SXU
                    WHERE
                        SXU.USUARIOID = ?
                )

            UNION

            SELECT
                T.TRASLADOID,
                T.FECHACREACION,
	            CONVERT(VARCHAR, T.FECHACREACION, 101) AS FECHACREACIONVARCHAR,
                SD.NOMBRE AS SUCURSALDESTINO,
                SO.NOMBRE AS SUCURSALORIGEN,
                T.CORRELATIVO,
                'Rejected by destination' AS ESTADO
            FROM
                INVTRASLADOS T
                JOIN CONFSUCURSALES SD ON SD.SUCURSALID=T.SUCURSALDESTINOID
                JOIN CONFSUCURSALES SO ON SO.SUCURSALID=T.SUCURSALORIGENID
            WHERE
                T.ESTADO = 'LIB'
                AND T.SUCURSALORIGENID IN (
                    SELECT
                        SXU.SUCURSALID
                    FROM
                        ACCSUCURSALESXUSUARIO SXU
                    WHERE
                        SXU.USUARIOID = ?
                )

            ORDER BY
	            T.FECHACREACION
        ";
        $datos = $this->conn->select($sentenciaSql, [$usuarioId, $usuarioId]);

        return $datos;
    }

    //-------------------------------------------
}