<?php

require_once("SQLSrvBD.php");

class Inventario
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
     * Obtener lista de inventario detallado, por sucursal y categoría
     * 
     * @param int $usuarioId Usuario al que se le filtran las sucursales
     * @param int $sucursalId Sucursal a la cual se le obtiene el inventario
     * @param int $categoriaId Filtro de categoría (-1 es para mostrar todas)
     * @param int $colorId Filtro de color (-1 es para mostrar todos)
     * @param int $stockTypeId Filtro para Stock Type (-1 es para mostrar todos)
     * @param bool $soloExistenciaMayorDeCero Para mostrar solamente inventario de ítems con existencia mayor que cero
     * @param int $numeroRecepcion Para filtrar por un número de recepción
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function invGeneralXSucursalYCategoria(int $usuarioId, int $sucursalId, int $categoriaId, int $colorId, int $stockTypeId, bool $soloExistenciaMayorDeCero, int $numeroRecepcion, string $buscar, int $numeroDePagina = 1, int $tamanoDePagina = 15): array
    {
        $offset = $numeroDePagina * $tamanoDePagina;

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

        if ($colorId != -1)
        {
            $condicionInventario .= " AND P.COLORID = $colorId";
            $condicionTransito .= " AND P.COLORID = $colorId";
            $condicionTransitoRechazo .= " AND P.COLORID = $colorId";
        }

        if ($stockTypeId != -1)
        {
            $condicionInventario .= " AND TSD.TIPODESTOCKID = $stockTypeId";
            $condicionTransito .= " AND TSD.TIPODESTOCKID = $stockTypeId";
            $condicionTransitoRechazo .= " AND TSD.TIPODESTOCKID = $stockTypeId";
        }
        
        if ($numeroRecepcion != -1)
        {
            $condicionInventario .= " AND RC.CORRELATIVO = $numeroRecepcion";
            $condicionTransito .= " AND RC.CORRELATIVO = $numeroRecepcion";
            $condicionTransitoRechazo .= " AND RC.CORRELATIVO = $numeroRecepcion";
        }

        if ($soloExistenciaMayorDeCero)
        {
            $condicionInventario .= " AND I.EXISTENCIA > 0";
            //$condicionTransito .= " AND I.EXISTENCIA > 0";
            //$condicionTransitoRechazo .= " AND I.EXISTENCIA > 0";
        }

        if (trim($buscar) != "")
        {
            $condicionInventario .= " AND (I.CODIGOINVENTARIO LIKE '%$buscar%' OR P.MODELO LIKE '%$buscar%' OR I.SERIE LIKE '%$buscar%' OR P.DESCRIPCION LIKE '%$buscar%' OR M.NOMBRE LIKE '%$buscar%')";
            $condicionTransito .= " AND (I.CODIGOINVENTARIO LIKE '%$buscar%' OR P.MODELO LIKE '%$buscar%' OR I.SERIE LIKE '%$buscar%' OR P.DESCRIPCION LIKE '%$buscar%' OR M.NOMBRE LIKE '%$buscar%')";
            $condicionTransitoRechazo .= " AND (I.CODIGOINVENTARIO LIKE '%$buscar%' OR P.MODELO LIKE '%$buscar%' OR I.SERIE LIKE '%$buscar%' OR P.DESCRIPCION LIKE '%$buscar%' OR M.NOMBRE LIKE '%$buscar%')";
        }

        $sentenciaSql = "
            WITH
                DATOS (INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, EXISTENCIA, ENTRANSITO, MSRP,
                PORCENTAJETIPODESTOCKDIST, TIPODESTOCKDIST, SUCURSAL, RECEPCIONCORRELATIVO)
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
                    S.NOMBRE AS SUCURSAL,
                    RC.CORRELATIVO AS RECEPCIONCORRELATIVO
                FROM
                    INVINVENTARIO I
                    JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                    JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                    JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                    JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
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
                    I.PORCENTAJETIPODESTOCKDIST,
                    TSD.NOMBRECORTO AS TIPODESTOCKDIST,
                    SD.NOMBRE AS SUCURSAL,
                    RC.CORRELATIVO AS RECEPCIONCORRELATIVO
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
                    I.PORCENTAJETIPODESTOCKDIST,
                    TSD.NOMBRECORTO AS TIPODESTOCKDIST,
                    SO.NOMBRE AS SUCURSAL,
                    RC.CORRELATIVO AS RECEPCIONCORRELATIVO
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
                    JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                    JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
                
                $condicionTransitoRechazo
            )

            SELECT
                INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, MSRP, PORCENTAJETIPODESTOCKDIST, TIPODESTOCKDIST, SUCURSAL,
                RECEPCIONCORRELATIVO, SUM(EXISTENCIA) AS EXISTENCIA, SUM(ENTRANSITO) AS ENTRANSITO
            FROM
                DATOS
            GROUP BY
                INVENTARIOID, CODIGOINVENTARIO, CATEGORIA, MARCA, MODELO, SERIE, COLOR, DESCRIPCION, MSRP, PORCENTAJETIPODESTOCKDIST, TIPODESTOCKDIST, SUCURSAL,
                RECEPCIONCORRELATIVO
            ORDER BY
                SUCURSAL,
                CODIGOINVENTARIO

            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->with($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(I.INVENTARIOID) AS CONTEO
            FROM
                INVINVENTARIO I
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID

            $condicionInventario
        ";
        $datoConteoInventario = $this->conn->select($sentenciaSql, []);

        $sentenciaSql = "
            SELECT
                COUNT(I.INVENTARIOID) AS CONTEO
            FROM
                INVTRASLADOS T
                JOIN INVTRASLADOSDETALLE TD ON TD.TRASLADOID=T.TRASLADOID
                JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID

            $condicionTransito
        ";
        $datoConteoTransito = $this->conn->select($sentenciaSql, []);

        $sentenciaSql = "
            SELECT
                COUNT(I.INVENTARIOID) AS CONTEO
            FROM
                INVTRASLADOS T
                JOIN INVTRASLADOSDETALLE TD ON TD.TRASLADOID=T.TRASLADOID
                JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID

            $condicionTransitoRechazo
        ";
        $datoConteoTransitoRechazo = $this->conn->select($sentenciaSql, []);
        
        $resultado = [
            "total" => $datoConteoInventario[0]["CONTEO"] + $datoConteoTransito[0]["CONTEO"] + $datoConteoTransitoRechazo[0]["CONTEO"],
            "rows" => $datos
        ];

        return $resultado;
    }

    //-------------------------------------------

   /**
     * Obtener lista de inventario para selección de etiquetas a emitir
     * 
     * @param int $categoriaId Filtro de categoría (-1 es para mostrar todas)
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function invGeneralParaEtiquetas(int $categoriaId, string $buscar, int $numeroDePagina = 1, int $tamanoDePagina = 15): array
    {
        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "";

        if ($categoriaId != -1)
        {
            $condicion .= " WHERE C.CATEGORIAID = $categoriaId ";
        }

        if (trim($buscar) != "")
        {
            if (strlen($condicion) > 0)
            {
                $condicion .= " AND (I.CODIGOINVENTARIO LIKE '%$buscar%' OR P.MODELO LIKE '%$buscar%' OR M.NOMBRE LIKE '%$buscar%') ";
            }
            else
            {
                $condicion .= " WHERE I.CODIGOINVENTARIO LIKE '%$buscar%' OR P.MODELO LIKE '%$buscar%' OR M.NOMBRE LIKE '%$buscar%' ";
            }
        }

        $sentenciaSql = "
            SELECT
                I.INVENTARIOID,
                I.CODIGOINVENTARIO,
                C.NOMBRE AS CATEGORIA,
                M.NOMBRE AS MARCA,
                P.MODELO,
                I.SERIE,
                P.DESCRIPCION,
                I.EXISTENCIA
            FROM
                INVINVENTARIO I
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
            
            $condicion

            ORDER BY
                I.CODIGOINVENTARIO
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(I.INVENTARIOID) AS CONTEO
            FROM
                INVINVENTARIO I
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID

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
     * Obtener lista de inventario generado por una fila de RecepciónDetalle
     * 
     * @param int $recepcionDetalleId Fila de RecepciónDetalle que generó los ítems de inventario
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function itemsDeRecepcionDetalleId(int $recepcionDetalleId): array
    {
        $sentenciaSql = "
            SELECT
                I.INVENTARIOID,
                I.CODIGOINVENTARIO,
                I.SERIE
            FROM
                INVINVENTARIO I
            WHERE
                I.RECEPCIONDECARGADETALLEID = ?
            ORDER BY
                I.CODIGOINVENTARIO
        ";
        $datos = $this->conn->select($sentenciaSql, [$recepcionDetalleId]);

        return $datos;
    }

    //-------------------------------------------

   /**
     * Obtener historial de un ítem de inventario
     * 
     * @param int $inventarioId Ítem de inventario al que se le va a listar el historial
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function historialDeItem(int $inventarioId): array
    {
        $sentenciaSql = "
            EXECUTE SPINVHISTORIALDEITEM ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$inventarioId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Actualiza el número de serie de un ítem de inventario
     * 
     * @param int $id El id del registro a editar
     * @param string $serie Número de serie a actualizar
     * 
     * @return bool Resultado de actualizar el registro: true: fue editado, false: no fue editado
     * 
     */
    public function editarSerie(int $id, string $serie): bool
    {
        $camposValores = ["SERIE", $serie];
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
            UPDATE INVINVENTARIO SET " . $updates . " WHERE INVENTARIOID = ?
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
     * Obtener datos de ítem de inventario por su Id
     * 
     * @param int $inventarioId Ítem de inventario al que se le van a leer sus datos
     * 
     * @return array Los datos del ítem de inventario
     * 
     */
    public function getByInventarioId(int $inventarioId): array
    {
        $sentenciaSql = "
            SELECT
                I.INVENTARIOID
                ,I.PRODUCTOID
                ,I.CODIGOINVENTARIO
                ,C.NOMBRE AS CATEGORIA
                ,P.MODELO
                ,M.NOMBRE AS MARCA
                ,I.SERIE
                ,I.EXISTENCIA
                ,I.ESTADO
                ,PROV.CODIGO + ' - ' + PROV.NOMBRE AS PROVEEDOR
                ,TSO.NOMBRECORTO AS TIPODESTOCKORIGEN
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,I.PORCENTAJETIPODESTOCKORIGEN
                ,I.PORCENTAJETIPODESTOCKDIST
                ,I.COSTOORIGEN
                ,I.COSTODIST
                ,I.MSRP
            FROM
                INVINVENTARIO I
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
                JOIN INVPROVEEDORES PROV ON PROV.PROVEEDORID=RC.PROVEEDORID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=I.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
            WHERE
                I.INVENTARIOID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$inventarioId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener datos de ítem de inventario por su código
     * 
     * @param string $codigo Código del Ítem de inventario al que se le van a leer sus datos
     * 
     * @return array Los datos del ítem de inventario
     * 
     */
    public function getByCodigo(string $codigo): array
    {
        $sentenciaSql = "
            SELECT
                I.INVENTARIOID
                ,I.PRODUCTOID
                ,I.SUCURSALID
                ,I.CODIGOINVENTARIO
                ,C.NOMBRE AS CATEGORIA
                ,P.MODELO
                ,P.DESCRIPCION
                ,M.NOMBRE AS MARCA
                ,I.SERIE
                ,I.EXISTENCIA
                ,I.ESTADO
                ,PROV.CODIGO + ' - ' + PROV.NOMBRE AS PROVEEDOR
                ,TSO.NOMBRECORTO AS TIPODESTOCKORIGEN
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,I.PORCENTAJETIPODESTOCKORIGEN
                ,I.PORCENTAJETIPODESTOCKDIST
                ,I.COSTOORIGEN
                ,I.COSTODIST
                ,I.MSRP
                ,RC.TIPODEGARANTIAID
				,TG.NOMBRE AS TIPODEGARANTIA
            FROM
                INVINVENTARIO I
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
                JOIN INVPROVEEDORES PROV ON PROV.PROVEEDORID=RC.PROVEEDORID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=I.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                LEFT JOIN INVTIPOSDEGARANTIA TG ON TG.TIPODEGARANTIAID=RC.TIPODEGARANTIAID
            WHERE
                I.CODIGOINVENTARIO = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$codigo]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener inventario resumido por categorías
     * 
     * @param int $sucursalId Sucursal a la que se le tomará el inventario
     * 
     * @return array Listado de categorías con su existencia
     * 
     */
    public function getInvResumenGeneral(int $sucursalId): array
    {
        $sentenciaSql = "
            SELECT
                C.CATEGORIAID, C.NOMBRE AS CATEGORIA,
                (
                    SELECT
                        ISNULL(SUM(I.EXISTENCIA), 0)
                    FROM
                        INVINVENTARIO I
                        LEFT JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                    WHERE
                        I.SUCURSALID = ?
                        AND P.CATEGORIAID=C.CATEGORIAID
                ) AS EXISTENCIA,
                (
                    SELECT
                        ISNULL(SUM(1), 0)
                    FROM
                        INVTRASLADOS T
                        JOIN INVTRASLADOSDETALLE TD ON TD.TRASLADOID=T.TRASLADOID
                        JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                        JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                        JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                        JOIN INVCATEGORIAS C2 ON C2.CATEGORIAID=P.CATEGORIAID
                    WHERE
                        (
                            (
                                T.SUCURSALDESTINOID = ?
                                AND T.ESTADO = 'PRO'
                            )
                            OR
                            (
                                T.SUCURSALORIGENID = ?
                                AND T.ESTADO = 'LIB'
                            )
                        )
                        AND P.CATEGORIAID=C.CATEGORIAID
                ) AS ENTRANSITO
            FROM
                INVCATEGORIAS C
            ORDER BY
                C.NOMBRE
        ";
        $datos = $this->conn->select($sentenciaSql, [$sucursalId, $sucursalId, $sucursalId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener inventario resumido por sucursal y categoría
     * 
     * @param int $sucursalId Sucursal a la que se le tomará el inventario
     * @param int $categoriaId Categoría a la que se le tomará el inventario
     * @param int $soloConStock 1: Mostrar solo con stock, 0: Mostrar con o sin stock
     * 
     * @return array Listado de productos con su existencia
     * 
     */
    public function getInvResumenPorCategoria(int $sucursalId, int $categoriaId, int $soloConStock): array
    {
        $sentenciaSql = "
            EXECUTE SPINVINVENTARIOPORCATEGORIAS ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$sucursalId, $categoriaId, $soloConStock]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener existencias de un prodcuto en sucursales a las que tiene acceso un usuario
     * 
     * @param int $usuarioId Usuario al que se le van a filtrar las sucursales que tiene acceso
     * @param int $productoId Producto al que se le va a buscar su inventario
     * 
     * @return array Listado de sucursales con la existencia del producto
     * 
     */
    public function getExistenciasDeProductoXSucursal(int $usuarioId, int $productoId): array
    {
        $sentenciaSql = "
            SELECT
                S.SUCURSALID
                ,S.NOMBRE AS SUCURSAL
                ,(
                    SELECT
                        ISNULL(SUM(EXISTENCIA), 0)
                    FROM
                        INVINVENTARIO I
                    WHERE
                        I.PRODUCTOID = ?
                        AND I.SUCURSALID=S.SUCURSALID
                ) AS EXISTENCIA,
                (
                    SELECT
                        ISNULL(SUM(1), 0)
                    FROM
                        INVTRASLADOS T
                        JOIN INVTRASLADOSDETALLE TD ON TD.TRASLADOID=T.TRASLADOID
                        JOIN INVINVENTARIO I ON I.INVENTARIOID=TD.INVENTARIOID
                        JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                        JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                        JOIN INVCATEGORIAS C2 ON C2.CATEGORIAID=P.CATEGORIAID
                    WHERE
                        (
                            (
                                T.SUCURSALDESTINOID = S.SUCURSALID
                                AND T.ESTADO = 'PRO'
                            )
                            OR
                            (
                                T.SUCURSALORIGENID = S.SUCURSALID
                                AND T.ESTADO = 'LIB'
                            )
                        )
                        AND I.PRODUCTOID = ?
                ) AS ENTRANSITO
            FROM
                CONFSUCURSALES S
                --JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=S.SUCURSALID AND SXU.USUARIOID = ?
            ORDER BY
                S.NOMBRE
        ";
        $datos = $this->conn->select($sentenciaSql, [$productoId, $productoId, $usuarioId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener lista de inventario para emitir etiquetas a partir de una recepción de carga
     * 
     * @param int $recepcionDeCargaId Recepción que generó ítems de inventario
     * 
     * @return array Listado de ítems de inventario con datos para emitir etiquetas
     * 
     */
    public function getItemsParaEtiquetasDesdeRecepcion(int $recepcionDeCargaId): array
    {
        $sentenciaSql = "
            SELECT
                I.CODIGOINVENTARIO,
                P.MODELO,
                IE.MSRP,
                TSO.NOMBRECORTO AS TIPODESTOCKORIGEN,
                TSD.NOMBRECORTO AS TIPODESTOCKDIST,
                CONVERT(VARCHAR, RC.FECHADERECEPCION, 101) AS FECHADERECEPCION
            FROM
                INVINVENTARIO I
                JOIN INVINVENTARIOESTADOS IE ON IE.INVENTARIOID=I.INVENTARIOID AND I.RECEPCIONDECARGADETALLEID=IE.RECEPCIONDECARGADETALLEID
                JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=IE.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=IE.TIPODESTOCKDISTID
            WHERE
                RC.RECEPCIONDECARGAID = ?
            ORDER BY
                I.CODIGOINVENTARIO
        ";
        $datos = $this->conn->select($sentenciaSql, [$recepcionDeCargaId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener lista de inventario para emitir etiquetas a partir de una serie de ids de inventario
     * 
     * @param string $stringInventarioIDs Lista de ids de inventario separados por comas
     * 
     * @return array Listado de ítems de inventario con datos para emitir etiquetas
     * 
     */
    public function getItemsParaEtiquetasDesdeListaDeInventarioIDs(string $stringInventarioIDs): array
    {
        $sentenciaSql = "
            SELECT
                I.CODIGOINVENTARIO,
                P.MODELO,
                IE.MSRP,
                TSO.NOMBRECORTO AS TIPODESTOCKORIGEN,
                TSD.NOMBRECORTO AS TIPODESTOCKDIST,
                CONVERT(VARCHAR, RC.FECHADERECEPCION, 101) AS FECHADERECEPCION
            FROM
                INVINVENTARIO I
                JOIN INVINVENTARIOESTADOS IE ON IE.INVENTARIOID=I.INVENTARIOID AND I.RECEPCIONDECARGADETALLEID=IE.RECEPCIONDECARGADETALLEID
                JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=IE.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=IE.TIPODESTOCKDISTID
            WHERE
                I.INVENTARIOID IN ($stringInventarioIDs)
            ORDER BY
                I.CODIGOINVENTARIO
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

   /**
     * Obtener lista de inventario con existencias por sucursal
     * 
     * @param int $sucursalId Sucursal a la cual se le obtiene el inventario
     * @param int $categoriaId Categoría para aplicar en filtro
     * @param int $buscar Filtro para aplicar en la búsqueda de ítems
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getInventarioDeSucursalConPaginacion(int $sucursalId, int $categoriaId, string $buscar, int $numeroDePagina = 1, int $tamanoDePagina = 15): array
    {
        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "
            WHERE
                I.SUCURSALID = $sucursalId
                AND I.EXISTENCIA > 0
        ";

        if ($categoriaId != -1)
        {
            $condicion .= " AND C.CATEGORIAID = " . $categoriaId;
        }

        if (trim($buscar) != "")
        {
            $condicion .= " AND (I.CODIGOINVENTARIO LIKE '%$buscar%' OR P.MODELO LIKE '%$buscar%' OR P.DESCRIPCION LIKE '%$buscar%' OR M.NOMBRE LIKE '%$buscar%')";
        }

        $sentenciaSql = "
            SELECT
                I.INVENTARIOID
                ,I.CODIGOINVENTARIO
                ,C.NOMBRE AS CATEGORIA
                ,M.NOMBRE AS MARCA
                ,P.MODELO
                ,P.DESCRIPCION
                ,I.MSRP
                ,I.PORCENTAJETIPODESTOCKDIST
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,RC.TIPODEGARANTIAID
				,TG.NOMBRE AS TIPODEGARANTIA
            FROM
                INVINVENTARIO I
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
				JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
				LEFT JOIN INVTIPOSDEGARANTIA TG ON TG.TIPODEGARANTIAID=RC.TIPODEGARANTIAID
            
            $condicion

            ORDER BY
                I.CODIGOINVENTARIO
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(I.INVENTARIOID) AS CONTEO
            FROM
                INVINVENTARIO I
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID

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
     * Obtener lista de inventario que su último movimiento es una salida por sucursal
     * 
     * @param int $sucursalId Sucursal a la cual se le obtiene el inventario que su último movimiento fueron salidas
     * @param int $categoriaId Categoría para aplicar en filtro
     * @param int $buscar Filtro para aplicar en la búsqueda de ítems
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getInventarioEnSalidasPorSucursalConPaginacion(int $sucursalId, int $categoriaId, string $buscar, int $numeroDePagina = 1, int $tamanoDePagina = 15): array
    {
        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "
            WHERE
                I.SUCURSALID = $sucursalId
                AND I.EXISTENCIA = 0
        ";

        if ($categoriaId != -1)
        {
            $condicion .= " AND C.CATEGORIAID = " . $categoriaId;
        }

        if (trim($buscar) != "")
        {
            $condicion .= " AND (I.CODIGOINVENTARIO LIKE '%$buscar%' OR P.MODELO LIKE '%$buscar%' OR P.DESCRIPCION LIKE '%$buscar%' OR M.NOMBRE LIKE '%$buscar%')";
        }

        $sentenciaSql = "
            SELECT
                I.INVENTARIOID
                ,I.CODIGOINVENTARIO
                ,C.NOMBRE AS CATEGORIA
                ,M.NOMBRE AS MARCA
                ,P.MODELO
                ,P.DESCRIPCION
                ,I.MSRP
                ,I.PORCENTAJETIPODESTOCKDIST
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,S.CORRELATIVO AS CORRELATIVOSALIDA
	            ,CONVERT(VARCHAR, S.FECHA, 101) AS FECHASALIDA
                ,SD.SALIDADETALLEID
                ,TSAL.NOMBRE AS TIPODESALIDA
                ,S.CONCEPTO
            FROM
                INVINVENTARIO I
                JOIN (
                    SELECT
                        IE1.INVENTARIOID, IE1.SALIDADETALLEID
                    FROM
                        INVINVENTARIOESTADOS IE1
                    WHERE
                        IE1.INVENTARIOESTADOID IN (
                            SELECT
                                MAX(IE2.INVENTARIOESTADOID)
                            FROM
                                INVINVENTARIOESTADOS IE2
                            GROUP BY
                                IE2.INVENTARIOID
                        )
                        AND IE1.SALIDADETALLEID IS NOT NULL
                ) IE ON IE.INVENTARIOID=I.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                JOIN INVSALIDASDETALLE SD ON SD.SALIDADETALLEID=IE.SALIDADETALLEID
	            JOIN INVSALIDAS S ON S.SALIDAID=SD.SALIDAID
                JOIN INVTIPOSDESALIDA TSAL ON TSAL.TIPODESALIDAID=S.TIPODESALIDAID
            
            $condicion

            ORDER BY
                I.CODIGOINVENTARIO
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(I.INVENTARIOID) AS CONTEO
            FROM
                INVINVENTARIO I
                JOIN (
                    SELECT
                        IE1.INVENTARIOID, IE1.SALIDADETALLEID
                    FROM
                        INVINVENTARIOESTADOS IE1
                    WHERE
                        IE1.INVENTARIOESTADOID IN (
                            SELECT
                                MAX(IE2.INVENTARIOESTADOID)
                            FROM
                                INVINVENTARIOESTADOS IE2
                            GROUP BY
                                IE2.INVENTARIOID
                        )
                        AND IE1.SALIDADETALLEID IS NOT NULL
                ) IE ON IE.INVENTARIOID=I.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                JOIN INVSALIDASDETALLE SD ON SD.SALIDADETALLEID=IE.SALIDADETALLEID
	            JOIN INVSALIDAS S ON S.SALIDAID=SD.SALIDAID

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
     * Obtener datos de ítem de inventario que su último movimiento es una salida, búsqueda por su código
     * 
     * @param string $codigo Código del Ítem de inventario al que se le van a leer sus datos
     * 
     * @return array Los datos del ítem de inventario
     * 
     */
    public function getInventarioEnSalidaByCodigo(string $codigo): array
    {
        $sentenciaSql = "
            SELECT
                I.INVENTARIOID
                ,I.PRODUCTOID
                ,I.SUCURSALID
                ,I.CODIGOINVENTARIO
                ,C.NOMBRE AS CATEGORIA
                ,P.MODELO
                ,P.DESCRIPCION
                ,M.NOMBRE AS MARCA
                ,I.SERIE
                ,I.EXISTENCIA
                ,I.ESTADO
                ,PROV.CODIGO + ' - ' + PROV.NOMBRE AS PROVEEDOR
                ,TSO.NOMBRECORTO AS TIPODESTOCKORIGEN
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,I.PORCENTAJETIPODESTOCKORIGEN
                ,I.PORCENTAJETIPODESTOCKDIST
                ,I.COSTOORIGEN
                ,I.COSTODIST
                ,I.MSRP
                ,S.CORRELATIVO AS CORRELATIVOSALIDA
	            ,CONVERT(VARCHAR, S.FECHA, 101) AS FECHASALIDA
                ,SD.SALIDADETALLEID
                ,TSAL.NOMBRE AS TIPODESALIDA
            FROM
                INVINVENTARIO I
                JOIN (
                    SELECT
                        IE1.INVENTARIOID, IE1.SALIDADETALLEID
                    FROM
                        INVINVENTARIOESTADOS IE1
                    WHERE
                        IE1.INVENTARIOESTADOID IN (
                            SELECT
                                MAX(IE2.INVENTARIOESTADOID)
                            FROM
                                INVINVENTARIOESTADOS IE2
                            GROUP BY
                                IE2.INVENTARIOID
                        )
                        AND IE1.SALIDADETALLEID IS NOT NULL
                ) IE ON IE.INVENTARIOID=I.INVENTARIOID
                JOIN INVPRODUCTOS P ON P.PRODUCTOID=I.PRODUCTOID
                JOIN INVMARCAS M ON M.MARCAID=P.MARCAID
                JOIN INVCATEGORIAS C ON C.CATEGORIAID=P.CATEGORIAID
                JOIN INVRECEPCIONESDECARGADETALLE RCD ON RCD.RECEPCIONDECARGADETALLEID=I.RECEPCIONDECARGADETALLEID
                JOIN INVRECEPCIONESDECARGA RC ON RC.RECEPCIONDECARGAID=RCD.RECEPCIONDECARGAID
                JOIN INVPROVEEDORES PROV ON PROV.PROVEEDORID=RC.PROVEEDORID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=I.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=I.TIPODESTOCKDISTID
                JOIN INVSALIDASDETALLE SD ON SD.SALIDADETALLEID=IE.SALIDADETALLEID
	            JOIN INVSALIDAS S ON S.SALIDAID=SD.SALIDAID
                JOIN INVTIPOSDESALIDA TSAL ON TSAL.TIPODESALIDAID=S.TIPODESALIDAID
            WHERE
                I.CODIGOINVENTARIO = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$codigo]);

        return $datos;
    }

    //-------------------------------------------
}