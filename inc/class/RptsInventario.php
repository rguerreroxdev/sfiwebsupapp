<?php

require_once("SQLSrvBD.php");

class RptsInventario
{
    //-------------------------------------------

    private $conn;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto Inventario
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
     * Inventario general (por sucursal y categoría)
     * 
     * @param int $usuarioId Usuario al que se le filtran las sucursales
     * @param int $sucursalId Sucursal a la cual se le obtiene el inventario (-1 es para mostrar todas a las que tiene acceso el usuario)
     * @param int $categoriaId Filtro de categoría (-1 es para mostrar todas)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function inventarioGeneral(int $usuarioId, int $sucursalId, int $categoriaId): array
    {
        $condicionInventario = "";
        $condicionTransito = "";
        $condicionTransitoRechazo = "";

        if ($sucursalId == -1)
        {
            // Se solicitó ver todas las sucursales

            $condicionTransito = "
                WHERE
                    T.ESTADO = 'PRO'
            ";

            $condicionTransitoRechazo = "
                WHERE
                    T.ESTADO = 'LIB'
            ";
        }
        else
        {
            // Se solicitó ver una sucursal en específico
            $condicionInventario = "
                WHERE
                    I.SUCURSALID = $sucursalId
            ";

            $condicionTransito = "
                WHERE
                    T.SUCURSALDESTINOID = $sucursalId
                    AND T.ESTADO = 'PRO'
            ";

            $condicionTransitoRechazo = "
                WHERE
                    T.SUCURSALORIGENID = $sucursalId
                    AND T.ESTADO = 'LIB'
            ";
        }

        if ($categoriaId != -1)
        {
            $condicionInventario .= " AND C.CATEGORIAID = $categoriaId";
            $condicionTransito .= " AND C.CATEGORIAID = $categoriaId";
            $condicionTransitoRechazo .= " AND C.CATEGORIAID = $categoriaId";
        }

        // Mostrar solamente con existencias
        $condicionInventario .= " AND I.EXISTENCIA > 0";

        $sentenciaSql = "
            WITH
                DATOS (INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, EXISTENCIA, ENTRANSITO, MSRP,
                PORCENTAJETIPODESTOCKDIST, TIPODESTOCKDIST, SUCURSAL)
            AS
            (
                -- Existencias de inventario	
                SELECT
                    I.INVENTARIOID,
                    I.CODIGOINVENTARIO,
                    C.NOMBRE AS CATEGORIA,
                    M.NOMBRE AS MARCA,
                    P.MODELO,
                    I.SERIE,
                    COL.NOMBRE AS COLOR,
                    P.DESCRIPCION,
                    I.EXISTENCIA,
                    0 AS ENTRANSITO,
                    I.MSRP,
                    I.PORCENTAJETIPODESTOCKDIST,
                    TSD.NOMBRECORTO AS TIPODESTOCKDIST,
                    S.NOMBRE AS SUCURSAL
                FROM
                    INVINVENTARIO I
                    JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                    JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                    JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                    JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                    JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                    JOIN CONFSUCURSALES S ON S.SUCURSALID=I.SUCURSALID
                    JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=S.SUCURSALID AND SXU.USUARIOID=$usuarioId
                
                $condicionInventario

                UNION

                -- En traslado por llegar
                SELECT
                    I.INVENTARIOID,
                    I.CODIGOINVENTARIO,
                    C.NOMBRE AS CATEGORIA,
                    M.NOMBRE AS MARCA,
                    P.MODELO,
                    I.SERIE,
                    COL.NOMBRE AS COLOR,
                    P.DESCRIPCION,
                    0 AS EXISTENCIA,
                    1 AS ENTRANSITO,
                    I.MSRP,
                    I.PORCENTAJETIPODESTOCKDIST,
                    TSD.NOMBRECORTO AS TIPODESTOCKDIST,
                    SD.NOMBRE AS SUCURSAL
                FROM
                    INVTRASLADOS T
                    JOIN INVTRASLADOSDETALLE TD ON TD.TRASLADOID=T.TRASLADOID
                    JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                    JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                    JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                    JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                    JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                    JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                    JOIN CONFSUCURSALES SD ON SD.SUCURSALID=T.SUCURSALDESTINOID
                    JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=T.SUCURSALDESTINOID AND SXU.USUARIOID=$usuarioId
                
                $condicionTransito

                UNION

                -- En traslado rechazado
                SELECT
                    I.INVENTARIOID,
                    I.CODIGOINVENTARIO,
                    C.NOMBRE AS CATEGORIA,
                    M.NOMBRE AS MARCA,
                    P.MODELO,
                    I.SERIE,
                    COL.NOMBRE AS COLOR,
                    P.DESCRIPCION,
                    0 AS EXISTENCIA,
                    1 AS ENTRANSITO,
                    I.MSRP,
                    I.PORCENTAJETIPODESTOCKDIST,
                    TSD.NOMBRECORTO AS TIPODESTOCKDIST,
                    SO.NOMBRE AS SUCURSAL
                FROM
                    INVTRASLADOS T
                    JOIN INVTRASLADOSDETALLE TD ON TD.TRASLADOID=T.TRASLADOID
                    JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                    JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                    JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                    JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                    JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                    JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                    JOIN CONFSUCURSALES SO ON SO.SUCURSALID=T.SUCURSALORIGENID
                    JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=T.SUCURSALORIGENID AND SXU.USUARIOID=$usuarioId
                
                $condicionTransitoRechazo
            )

            SELECT
                INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, MSRP, PORCENTAJETIPODESTOCKDIST, TIPODESTOCKDIST, SUCURSAL,
                SUM(EXISTENCIA) AS EXISTENCIA, SUM(ENTRANSITO) AS ENTRANSITO
            FROM
                DATOS
            GROUP BY
                INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, MSRP, PORCENTAJETIPODESTOCKDIST, TIPODESTOCKDIST, SUCURSAL
            ORDER BY
                SUCURSAL,
                CATEGORIA,
                MARCA,
                CODIGOINVENTARIO
        ";
        $datos = $this->conn->with($sentenciaSql, []);
        
        return $datos;
    }

    //-------------------------------------------

   /**
     * Inventario valorizado (por sucursal y categoría)
     * 
     * @param int $usuarioId Usuario al que se le filtran las sucursales
     * @param int $sucursalId Sucursal a la cual se le obtiene el inventario (-1 es para mostrar todas a las que tiene acceso el usuario)
     * @param int $categoriaId Filtro de categoría (-1 es para mostrar todas)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function inventarioValorizado(int $usuarioId, int $sucursalId, int $categoriaId): array
    {
        $condicionInventario = "";
        $condicionTransito = "";
        $condicionTransitoRechazo = "";

        if ($sucursalId == -1)
        {
            // Se solicitó ver todas las sucursales

            $condicionTransito = "
                WHERE
                    T.ESTADO = 'PRO'
            ";

            $condicionTransitoRechazo = "
                WHERE
                    T.ESTADO = 'LIB'
            ";
        }
        else
        {
            // Se solicitó ver una sucursal en específico
            $condicionInventario = "
                WHERE
                    I.SUCURSALID = $sucursalId
            ";

            $condicionTransito = "
                WHERE
                    T.SUCURSALDESTINOID = $sucursalId
                    AND T.ESTADO = 'PRO'
            ";

            $condicionTransitoRechazo = "
                WHERE
                    T.SUCURSALORIGENID = $sucursalId
                    AND T.ESTADO = 'LIB'
            ";
        }

        if ($categoriaId != -1)
        {
            $condicionInventario .= " AND C.CATEGORIAID = $categoriaId";
            $condicionTransito .= " AND C.CATEGORIAID = $categoriaId";
            $condicionTransitoRechazo .= " AND C.CATEGORIAID = $categoriaId";
        }

        // Mostrar solamente con existencias
        $condicionInventario .= " AND I.EXISTENCIA > 0";

        $sentenciaSql = "
            WITH
                DATOS (INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, EXISTENCIA, ENTRANSITO, MSRP,
                PORCENTAJETIPODESTOCKORIGEN, TIPODESTOCKORIGEN, SUCURSAL, COSTOORIGEN)
            AS
            (
                -- Existencias de inventario	
                SELECT
                    I.INVENTARIOID,
                    I.CODIGOINVENTARIO,
                    C.NOMBRE AS CATEGORIA,
                    M.NOMBRE AS MARCA,
                    P.MODELO,
                    I.SERIE,
                    COL.NOMBRE AS COLOR,
                    P.DESCRIPCION,
                    I.EXISTENCIA,
                    0 AS ENTRANSITO,
                    I.MSRP,
                    I.PORCENTAJETIPODESTOCKORIGEN,
                    TSO.NOMBRECORTO AS TIPODESTOCKORIGEN,
                    S.NOMBRE AS SUCURSAL,
                    I.COSTOORIGEN
                FROM
                    INVINVENTARIO I
                    JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                    JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                    JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                    JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=I.TIPODESTOCKORIGENID
                    JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                    JOIN CONFSUCURSALES S ON S.SUCURSALID=I.SUCURSALID
                    JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=S.SUCURSALID AND SXU.USUARIOID=$usuarioId
                
                $condicionInventario

                UNION

                -- En traslado por llegar
                SELECT
                    I.INVENTARIOID,
                    I.CODIGOINVENTARIO,
                    C.NOMBRE AS CATEGORIA,
                    M.NOMBRE AS MARCA,
                    P.MODELO,
                    I.SERIE,
                    COL.NOMBRE AS COLOR,
                    P.DESCRIPCION,
                    0 AS EXISTENCIA,
                    1 AS ENTRANSITO,
                    I.MSRP,
                    I.PORCENTAJETIPODESTOCKORIGEN,
                    TSO.NOMBRECORTO AS TIPODESTOCKORIGEN,
                    SD.NOMBRE AS SUCURSAL,
                    I.COSTOORIGEN
                FROM
                    INVTRASLADOS T
                    JOIN INVTRASLADOSDETALLE TD ON TD.TRASLADOID=T.TRASLADOID
                    JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                    JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                    JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                    JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                    JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=I.TIPODESTOCKORIGENID
                    JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                    JOIN CONFSUCURSALES SD ON SD.SUCURSALID=T.SUCURSALDESTINOID
                    JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=T.SUCURSALDESTINOID AND SXU.USUARIOID=$usuarioId
                
                $condicionTransito

                UNION

                -- En traslado rechazado
                SELECT
                    I.INVENTARIOID,
                    I.CODIGOINVENTARIO,
                    C.NOMBRE AS CATEGORIA,
                    M.NOMBRE AS MARCA,
                    P.MODELO,
                    I.SERIE,
                    COL.NOMBRE AS COLOR,
                    P.DESCRIPCION,
                    0 AS EXISTENCIA,
                    1 AS ENTRANSITO,
                    I.MSRP,
                    I.PORCENTAJETIPODESTOCKORIGEN,
                    TSO.NOMBRECORTO AS TIPODESTOCKORIGEN,
                    SO.NOMBRE AS SUCURSAL,
                    I.COSTOORIGEN
                FROM
                    INVTRASLADOS T
                    JOIN INVTRASLADOSDETALLE TD ON TD.TRASLADOID=T.TRASLADOID
                    JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                    JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                    JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                    JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                    JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=I.TIPODESTOCKORIGENID
                    JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                    JOIN CONFSUCURSALES SO ON SO.SUCURSALID=T.SUCURSALORIGENID
                    JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=T.SUCURSALORIGENID AND SXU.USUARIOID=$usuarioId
                
                $condicionTransitoRechazo
            )

            SELECT
                INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, MSRP, PORCENTAJETIPODESTOCKORIGEN, TIPODESTOCKORIGEN, SUCURSAL, COSTOORIGEN,
                SUM(EXISTENCIA) AS EXISTENCIA, SUM(ENTRANSITO) AS ENTRANSITO
            FROM
                DATOS
            GROUP BY
                INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, MSRP, PORCENTAJETIPODESTOCKORIGEN, TIPODESTOCKORIGEN, SUCURSAL, COSTOORIGEN
            ORDER BY
                SUCURSAL,
                CATEGORIA,
                MARCA,
                CODIGOINVENTARIO
        ";
        $datos = $this->conn->with($sentenciaSql, []);
        
        return $datos;
    }

    //-------------------------------------------

   /**
     * Inventario, antigüedad de inventario (por sucursal y categoría)
     * 
     * @param int $usuarioId Usuario al que se le filtran las sucursales
     * @param int $sucursalId Sucursal a la cual se le obtiene el inventario (-1 es para mostrar todas a las que tiene acceso el usuario)
     * @param int $categoriaId Filtro de categoría (-1 es para mostrar todas)
     * @param int $dias Días de antigüedad de inventario (se buscarán los ítems que sean mayor o igual a estos días)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function inventarioAntiguedad(int $usuarioId, int $sucursalId, int $categoriaId, int $dias): array
    {
        $condicionInventario = "";
        $condicionTransito = "";
        $condicionTransitoRechazo = "";

        if ($sucursalId == -1)
        {
            // Se solicitó ver todas las sucursales

            $condicionTransito = "
                WHERE
                    T.ESTADO = 'PRO'
            ";

            $condicionTransitoRechazo = "
                WHERE
                    T.ESTADO = 'LIB'
            ";
        }
        else
        {
            // Se solicitó ver una sucursal en específico
            $condicionInventario = "
                WHERE
                    I.SUCURSALID = $sucursalId
                    AND DATEDIFF(DAY, RC.FECHADERECEPCION, GETDATE()) >= $dias
            ";

            $condicionTransito = "
                WHERE
                    T.SUCURSALDESTINOID = $sucursalId
                    AND T.ESTADO = 'PRO'
                    AND DATEDIFF(DAY, RC.FECHADERECEPCION, GETDATE()) >= $dias
            ";

            $condicionTransitoRechazo = "
                WHERE
                    T.SUCURSALORIGENID = $sucursalId
                    AND T.ESTADO = 'LIB'
                    AND DATEDIFF(DAY, RC.FECHADERECEPCION, GETDATE()) >= $dias
            ";
        }

        if ($categoriaId != -1)
        {
            $condicionInventario .= " AND C.CATEGORIAID = $categoriaId";
            $condicionTransito .= " AND C.CATEGORIAID = $categoriaId";
            $condicionTransitoRechazo .= " AND C.CATEGORIAID = $categoriaId";
        }

        // Mostrar solamente con existencias
        $condicionInventario .= " AND I.EXISTENCIA > 0";

        $sentenciaSql = "
            WITH
                DATOS (INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, EXISTENCIA, ENTRANSITO, MSRP,
                PORCENTAJETIPODESTOCKORIGEN, TIPODESTOCKORIGEN, SUCURSAL, FECHADERECEPCIONORDEN, FECHADERECEPCION, DIAS)
            AS
            (
                -- Existencias de inventario	
                SELECT
                    I.INVENTARIOID,
                    I.CODIGOINVENTARIO,
                    C.NOMBRE AS CATEGORIA,
                    M.NOMBRE AS MARCA,
                    P.MODELO,
                    I.SERIE,
                    COL.NOMBRE AS COLOR,
                    P.DESCRIPCION,
                    I.EXISTENCIA,
                    0 AS ENTRANSITO,
                    I.MSRP,
                    I.PORCENTAJETIPODESTOCKORIGEN,
                    TSO.NOMBRECORTO AS TIPODESTOCKORIGEN,
                    S.NOMBRE AS SUCURSAL,
                    RC.FECHADERECEPCION AS FECHADERECEPCIONORDEN,
                    CONVERT(VARCHAR, RC.FECHADERECEPCION, 101) AS FECHADERECEPCION,
                    DATEDIFF(DAY, RC.FECHADERECEPCION, GETDATE()) AS DIAS
                FROM
                    INVINVENTARIO I
                    JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                    JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                    JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                    JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=I.TIPODESTOCKORIGENID
                    JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                    JOIN CONFSUCURSALES S ON S.SUCURSALID=I.SUCURSALID
                    JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=S.SUCURSALID AND SXU.USUARIOID=$usuarioId
                    JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                    JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
                
                $condicionInventario

                UNION

                -- En traslado por llegar
                SELECT
                    I.INVENTARIOID,
                    I.CODIGOINVENTARIO,
                    C.NOMBRE AS CATEGORIA,
                    M.NOMBRE AS MARCA,
                    P.MODELO,
                    I.SERIE,
                    COL.NOMBRE AS COLOR,
                    P.DESCRIPCION,
                    0 AS EXISTENCIA,
                    1 AS ENTRANSITO,
                    I.MSRP,
                    I.PORCENTAJETIPODESTOCKORIGEN,
                    TSO.NOMBRECORTO AS TIPODESTOCKORIGEN,
                    SD.NOMBRE AS SUCURSAL,
                    RC.FECHADERECEPCION AS FECHADERECEPCIONORDEN,
                    CONVERT(VARCHAR, RC.FECHADERECEPCION, 101) AS FECHADERECEPCION,
                    DATEDIFF(DAY, RC.FECHADERECEPCION, GETDATE()) AS DIAS
                FROM
                    INVTRASLADOS T
                    JOIN INVTRASLADOSDETALLE TD ON TD.TRASLADOID=T.TRASLADOID
                    JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                    JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                    JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                    JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                    JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=I.TIPODESTOCKORIGENID
                    JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                    JOIN CONFSUCURSALES SD ON SD.SUCURSALID=T.SUCURSALDESTINOID
                    JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=T.SUCURSALDESTINOID AND SXU.USUARIOID=$usuarioId
                    JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                    JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
                
                $condicionTransito

                UNION

                -- En traslado rechazado
                SELECT
                    I.INVENTARIOID,
                    I.CODIGOINVENTARIO,
                    C.NOMBRE AS CATEGORIA,
                    M.NOMBRE AS MARCA,
                    P.MODELO,
                    I.SERIE,
                    COL.NOMBRE AS COLOR,
                    P.DESCRIPCION,
                    0 AS EXISTENCIA,
                    1 AS ENTRANSITO,
                    I.MSRP,
                    I.PORCENTAJETIPODESTOCKORIGEN,
                    TSO.NOMBRECORTO AS TIPODESTOCKORIGEN,
                    SO.NOMBRE AS SUCURSAL,
                    RC.FECHADERECEPCION AS FECHADERECEPCIONORDEN,
                    CONVERT(VARCHAR, RC.FECHADERECEPCION, 101) AS FECHADERECEPCION,
                    DATEDIFF(DAY, RC.FECHADERECEPCION, GETDATE()) AS DIAS
                FROM
                    INVTRASLADOS T
                    JOIN INVTRASLADOSDETALLE TD ON TD.TRASLADOID=T.TRASLADOID
                    JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                    JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                    JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                    JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                    JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=I.TIPODESTOCKORIGENID
                    JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                    JOIN CONFSUCURSALES SO ON SO.SUCURSALID=T.SUCURSALORIGENID
                    JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=T.SUCURSALORIGENID AND SXU.USUARIOID=$usuarioId
                    JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                    JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
                
                $condicionTransitoRechazo
            )

            SELECT
                INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, MSRP, PORCENTAJETIPODESTOCKORIGEN, TIPODESTOCKORIGEN, SUCURSAL,
                FECHADERECEPCIONORDEN, FECHADERECEPCION, DIAS, SUM(EXISTENCIA) AS EXISTENCIA, SUM(ENTRANSITO) AS ENTRANSITO
            FROM
                DATOS
            GROUP BY
                INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, MSRP, PORCENTAJETIPODESTOCKORIGEN, TIPODESTOCKORIGEN, SUCURSAL,
                FECHADERECEPCIONORDEN, FECHADERECEPCION, DIAS
            ORDER BY
                FECHADERECEPCIONORDEN,
                SUCURSAL,
                CATEGORIA,
                MARCA,
                CODIGOINVENTARIO
        ";

        $datos = $this->conn->with($sentenciaSql, []);
        
        return $datos;
    }

    //-------------------------------------------

   /**
     * Inventario, ítems a los que se le ha dado salida
     * 
     * @param int $usuarioId Usuario al que se le filtran las sucursales
     * @param int $sucursalId Sucursal a la cual se le obtiene el inventario (-1 es para mostrar todas a las que tiene acceso el usuario)
     * @param int $categoriaId Filtro de categoría (-1 es para mostrar todas)
     * @param string $fechaInicial Fecha de inicio para filtrar documentos salidas
     * @param string $fechaFinal Fecha final para filtrar documentos salidas
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function inventarioSalidas(int $usuarioId, int $sucursalId, int $categoriaId, string $fechaInicial, string $fechaFinal): array
    {
        $condicion = "";

        if ($sucursalId != -1)
        {
            // Se solicitó ver todas las sucursales
            $condicion .= "
                AND S.SUCURSALID = $sucursalId
            ";
        }

        if ($categoriaId != -1)
        {
            $condicion .= "
                AND C.CATEGORIAID = $categoriaId
            ";
        }

        $sentenciaSql = "
            SELECT
                CONVERT(VARCHAR, S.FECHA, 101) AS FECHA,
                S.CORRELATIVO,
                TS.NOMBRE AS TIPODESALIDA,
                SUC.NOMBRE AS SUCURSAL,
                I.CODIGOINVENTARIO,
                C.NOMBRE AS CATEGORIA,
                M.NOMBRE AS MARCA,
                P.MODELO,
                COL.NOMBRE AS COLOR,
                P.DESCRIPCION
            FROM
                INVSALIDAS S
                JOIN INVSALIDASDETALLE SD ON S.SALIDAID=SD.SALIDAID
                JOIN INVTIPOSDESALIDA TS ON TS.TIPODESALIDAID=S.TIPODESALIDAID
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=S.SUCURSALID
                JOIN INVINVENTARIO I ON I.INVENTARIOID=SD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCOLORES COL ON COL.COLORID=P.COLORID
                JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=S.SUCURSALID AND SXU.USUARIOID=$usuarioId
            WHERE
                S.FECHA >= '$fechaInicial'
                AND CONVERT(DATE, S.FECHA) <= '$fechaFinal'
                AND S.ESTADO = 'PRO'
            
            $condicion
            
            ORDER BY
                S.FECHA,
                S.CORRELATIVO,
                I.CODIGOINVENTARIO
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

   /**
     * Inventario, Recepciones por proveedor
     * 
     * @param int $usuarioId Usuario al que se le filtran las sucursales
     * @param int $sucursalId Sucursal a la cual se le obtiene el inventario (-1 es para mostrar todas a las que tiene acceso el usuario)
     * @param int $proveedorId Filtro de proveedor (-1 es para mostrar todos)
     * @param string $fechaInicial Fecha de inicio para filtrar documentos
     * @param string $fechaFinal Fecha final para filtrar documentos
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function recepcionesPorProveedor(int $usuarioId, int $sucursalId, int $proveedorId, string $fechaInicial, string $fechaFinal): array
    {
        $condicion = "";

        if ($sucursalId != -1)
        {
            $condicion .= "
                AND RC.SUCURSALID = $sucursalId
            ";
        }

        if ($proveedorId != -1)
        {
            $condicion .= "
                AND RC.PROVEEDORID = $proveedorId
            ";
        }

        $sentenciaSql = "
            SELECT
                RC.FECHADERECEPCION,
                CONVERT(VARCHAR, RC.FECHADERECEPCION, 101) AS FECHADERECEPCIONVARCHAR,
                P.NOMBRE AS PROVEEDOR,
                TSO.NOMBRECORTO AS TIPODESTOCKORIGEN,
                SUM(RCD.CANTIDAD) AS CANTIDAD,
                SUM(I.MSRP) AS TOTALMSRP,
                SUM(I.COSTOORIGEN) AS TOTALCOSTO
            FROM
                INVRECEPCIONESDECARGA RC
                JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGAID=RC.RECEPCIONDECARGAID
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=RC.PROVEEDORID
                JOIN INVINVENTARIO I ON I.RECEPCIONDECARGADETALLEID=RCD.RECEPCIONDECARGADETALLEID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=RC.TIPODESTOCKORIGENID
                JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=RC.SUCURSALID AND SXU.USUARIOID=$usuarioId
            WHERE
                RC.ESTADO = 'PRO'
                AND RC.FECHADERECEPCION >= '$fechaInicial'
                AND CONVERT(DATE, RC.FECHADERECEPCION) <= '$fechaFinal'
            
                $condicion

            GROUP BY
                RC.FECHADERECEPCION,
                P.NOMBRE,
                TSO.NOMBRECORTO
            ORDER BY
                RC.FECHADERECEPCION,
                P.NOMBRE
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

   /**
     * Inventario, Traslados de ítems a tiendas
     * 
     * @param int $usuarioId Usuario al que se le filtran las sucursales
     * @param int $sucursalId Sucursal a la cual se le enviaron los ítems (-1 es para mostrar todas a las que tiene acceso el usuario)
     * @param int $categoriaId Filtro de categoría (-1 es para mostrar todas)
     * @param string $fechaInicial Fecha de inicio para filtrar documentos
     * @param string $fechaFinal Fecha final para filtrar documentos
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function trasladoDeItemsATiendas(int $usuarioId, int $sucursalId, int $categoriaId, string $fechaInicial, string $fechaFinal): array
    {
        $condicion = "";

        if ($sucursalId != -1)
        {
            $condicion .= "
                AND T.SUCURSALDESTINOID = $sucursalId
            ";
        }

        if ($categoriaId != -1)
        {
            $condicion .= "
                AND C.CATEGORIAID = $categoriaId
            ";
        }

        $sentenciaSql = "
            SELECT
                T.FECHACREACION,
                CONVERT(VARCHAR, T.FECHACREACION, 101) AS FECHACREACIONVARCHAR,
                SD.NOMBRE AS DESTINO,
                C.NOMBRE AS CATEGORIA,
                P.MODELO AS MODELO,
                I.MSRP,
                TSO.NOMBRECORTO AS TIPODESTOCKORIGEN,
                TSD.NOMBRECORTO AS TIPODESTOCKDISTR,
                COUNT(I.INVENTARIOID) AS CANTIDAD,
                SUM(I.COSTOORIGEN) AS TOTALCOSTOORIGEN,
                SUM(I.COSTODIST) AS TOTALCOSTODISTR
            FROM
                INVTRASLADOS T
                JOIN INVTRASLADOSDETALLE TD ON TD.TRASLADOID=T.TRASLADOID
                JOIN CONFSUCURSALES SD ON SD.SUCURSALID=T.SUCURSALDESTINOID
                JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=I.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=T.SUCURSALDESTINOID AND SXU.USUARIOID=$usuarioId

            WHERE
                T.ESTADO =  'PRD'
                AND T.FECHACREACION >= '$fechaInicial'
                AND CONVERT(DATE, T.FECHACREACION) <= '$fechaFinal'

                $condicion

            GROUP BY
                T.FECHACREACION,
                SD.NOMBRE,
                C.NOMBRE,
                P.MODELO,
                I.MSRP,
                TSO.NOMBRECORTO,
                TSD.NOMBRECORTO
            ORDER BY
                SD.NOMBRE,
                T.FECHACREACION,
                C.NOMBRE
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------
}