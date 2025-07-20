<?php

require_once("SQLSrvBD.php");

class RecepcionesDeCarga
{
    //-------------------------------------------

    private $conn;

    public $recepcionDeCargaId;
    public $sucursalId;
    public $sucursal;
    public $proveedorId;
    public $codigoProveedor;
    public $proveedor;
    public $tipoDeStockOrigenId;
    public $tipoDeStockDistId;
    public $tipoDeStockOrigen;
    public $tipoDeStockDist;
    public $tipoDeGarantiaId;
    public $tipoDeGarantia;
    public $fechaDeEmision;
    public $fechaDeRecepcion;
    public $correlativo;
    public $numeroDeDocumento;
    public $porcentajeTipoDeStockOrigen;
    public $porcentajeTipoDeStockDist;
    public $estado;
    public $nombreDeEstado;

    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto RecepcionesDeCarga
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
     * Obtener datos de un registro (INVRECEPCIONESDECARGA) por medio de ID
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
                RC.RECEPCIONDECARGAID
                ,RC.SUCURSALID
                ,S.NOMBRE AS SUCURSAL
                ,RC.PROVEEDORID
                ,P.CODIGO AS CODIGOPROVEEDOR
                ,P.NOMBRE AS PROVEEDOR
                ,RC.TIPODESTOCKORIGENID
                ,TSO.NOMBRECORTO AS TIPODESTOCKORIGEN
                ,RC.TIPODESTOCKDISTID
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,RC.FECHADEEMISION
                ,RC.FECHADERECEPCION
                ,RC.CORRELATIVO
                ,RC.NUMERODEDOCUMENTO
                ,RC.PORCENTAJETIPODESTOCKORIGEN
                ,RC.PORCENTAJETIPODESTOCKDIST
                ,RC.ESTADO
                ,CASE
                    WHEN RC.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN RC.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN RC.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN RC.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO
                ,RC.FECHACREACION
                ,RC.FECHAMODIFICACION
                ,RC.USUARIOIDCREACION
                ,UC.USUARIO AS USUARIOCREO
                ,RC.USUARIOIDMODIFICACION
                ,UM.USUARIO AS USUARIOMODIFICA
                ,RC.TIPODEGARANTIAID
                ,TG.NOMBRE AS TIPODEGARANTIA
            FROM
                INVRECEPCIONESDECARGA RC
                JOIN CONFSUCURSALES S ON S.SUCURSALID=RC.SUCURSALID
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=RC.PROVEEDORID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=RC.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=RC.TIPODESTOCKDISTID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=RC.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=RC.USUARIOIDMODIFICACION
                JOIN INVTIPOSDEGARANTIA TG ON TG.TIPODEGARANTIAID=RC.TIPODEGARANTIAID
            WHERE
                RC.RECEPCIONDECARGAID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->recepcionDeCargaId = $dato["RECEPCIONDECARGAID"];
            $this->sucursalId = $dato["SUCURSALID"];
            $this->sucursal = $dato["SUCURSAL"];
            $this->proveedorId = $dato["PROVEEDORID"];
            $this->codigoProveedor = $dato["CODIGOPROVEEDOR"];
            $this->proveedor = $dato["PROVEEDOR"];
            $this->tipoDeStockOrigenId = $dato["TIPODESTOCKORIGENID"];
            $this->tipoDeStockOrigen = $dato["TIPODESTOCKORIGEN"];
            $this->tipoDeStockDistId = $dato["TIPODESTOCKDISTID"];
            $this->tipoDeStockDist = $dato["TIPODESTOCKDIST"];
            $this->tipoDeGarantiaId = $dato["TIPODEGARANTIAID"];
            $this->tipoDeGarantia = $dato["TIPODEGARANTIA"];
            $this->fechaDeEmision = $dato["FECHADEEMISION"];
            $this->fechaDeRecepcion = $dato["FECHADERECEPCION"];
            $this->correlativo = $dato["CORRELATIVO"];
            $this->numeroDeDocumento = $dato["NUMERODEDOCUMENTO"];
            $this->porcentajeTipoDeStockOrigen = $dato["PORCENTAJETIPODESTOCKORIGEN"];
            $this->porcentajeTipoDeStockDist = $dato["PORCENTAJETIPODESTOCKDIST"];
            $this->estado = $dato["ESTADO"];
            $this->nombreDeEstado = $dato["NOMBREDEESTADO"];

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
     * Obtener todos los registros de la tabla (INVRECEPCIONESDECARGA) con paginación
     * 
     * @param int $sucursalId Sucursal que se filtrará para mostrar documentos (-1 muestra todas)
     * @param string $buscar Texto a buscar en campos de tipo VARCHAR
     * @param string $fechaDesde Fecha inicial para filtrar registros
     * @param string $estado Estado de los documentos para filtrar registros
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllConPaginacion(int $sucursalId, string $buscar,  string $fechaDesde, string $estado, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "";
        if (trim($buscar) != "")
        {
            if (is_numeric($buscar))
            {
                $condicion = "
                WHERE
                    (RC.CORRELATIVO = $buscar OR P.NOMBRE LIKE '%$buscar%' OR RC.NUMERODEDOCUMENTO LIKE '%$buscar%')
                ";
            }
            else
            {
                $condicion = "
                WHERE
                    (P.NOMBRE LIKE '%$buscar%' OR RC.NUMERODEDOCUMENTO LIKE '%$buscar%')
                ";                
            }
        }

        if ($sucursalId != -1)
        {
            $condicion .= strlen($condicion) > 0 ? " AND RC.SUCURSALID = $sucursalId" : " WHERE RC.SUCURSALID = $sucursalId";
        }

        if ($fechaDesde != "")
        {
            $condicion .= strlen($condicion) > 0 ? " AND RC.FECHADERECEPCION >= '$fechaDesde' " : " WHERE RC.FECHADERECEPCION >= '$fechaDesde' ";
        }

        if ($estado != "")
        {
            $condicion .= strlen($condicion) > 0 ? " AND RC.ESTADO = '$estado' " : " WHERE RC.ESTADO = '$estado' ";
        }

        $sentenciaSql = "
            SELECT
                RC.RECEPCIONDECARGAID
                ,RC.SUCURSALID
                ,S.NOMBRE AS SUCURSAL
                ,RC.PROVEEDORID
                ,P.CODIGO AS CODIGOPROVEEDOR
                ,P.NOMBRE AS PROVEEDOR
                ,RC.TIPODESTOCKORIGENID
                ,TSO.NOMBRECORTO AS TIPODESTOCKORIGEN
                ,RC.TIPODESTOCKDISTID
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,CONVERT(VARCHAR, RC.FECHADEEMISION, 101) AS FECHADEEMISION
                ,CONVERT(VARCHAR, RC.FECHADERECEPCION, 101) AS FECHADERECEPCION
                ,RC.CORRELATIVO
                ,RC.NUMERODEDOCUMENTO
                ,RC.PORCENTAJETIPODESTOCKORIGEN
                ,RC.PORCENTAJETIPODESTOCKDIST
                ,RC.ESTADO
                ,CASE
                    WHEN RC.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN RC.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN RC.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN RC.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO
                ,RC.FECHACREACION
                ,RC.FECHAMODIFICACION
                ,RC.USUARIOIDCREACION
                ,UC.USUARIO AS USUARIOCREO
                ,RC.USUARIOIDMODIFICACION
                ,UM.USUARIO AS USUARIOMODIFICA
                ,RC.TIPODEGARANTIAID
                ,TG.NOMBRE AS TIPODEGARANTIA
            FROM
                INVRECEPCIONESDECARGA RC
                JOIN CONFSUCURSALES S ON S.SUCURSALID=RC.SUCURSALID
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=RC.PROVEEDORID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=RC.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=RC.TIPODESTOCKDISTID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=RC.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=RC.USUARIOIDMODIFICACION
                JOIN INVTIPOSDEGARANTIA TG ON TG.TIPODEGARANTIAID=RC.TIPODEGARANTIAID

            $condicion
            
            ORDER BY
                RC.FECHADEEMISION DESC
                ,RC.CORRELATIVO DESC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(RC.RECEPCIONDECARGAID) AS CONTEO
            FROM
                INVRECEPCIONESDECARGA RC
                JOIN CONFSUCURSALES S ON S.SUCURSALID=RC.SUCURSALID
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=RC.PROVEEDORID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=RC.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=RC.TIPODESTOCKDISTID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=RC.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=RC.USUARIOIDMODIFICACION
                JOIN INVTIPOSDEGARANTIA TG ON TG.TIPODEGARANTIAID=RC.TIPODEGARANTIAID

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
     * Obtener todos los registros de la tabla (INVRECEPCIONESDECARGA) con paginación, filtrando las sucursales a que tiene acceso el usuario
     * 
     * @param int $usuarioId Usuario al que se le van a filtrar las sucursales a las que tiene acceso
     * @param int $sucursalId Sucursal que se filtrará para mostrar documentos (-1 muestra todas)
     * @param string $loadId Número de documento (LOAD ID) a buscar
     * @param string $correlativo Correlativo que se está buscando de forma directa
     * @param int $proveedorId ID de proveedor que se selecciona de forma directa
     * @param string $fechaDesde Fecha inicial para filtrar registros
     * @param string $estado Estado de los documentos para filtrar registros
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllSucursalXUsuarioConPaginacion(int $usuarioId, int $sucursalId, string $loadId, string $correlativo, int $proveedorId, string $fechaDesde, string $estado, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "";

        if ($loadId != "")
        {
            $condicion .= strlen($condicion) > 0 ? " AND RC.NUMERODEDOCUMENTO LIKE '%$loadId%' " : " WHERE RC.NUMERODEDOCUMENTO LIKE '%$loadId%' ";
        }

        if (is_numeric($correlativo))
        {
            $condicion .= strlen($condicion) > 0 ? " AND RC.CORRELATIVO = $correlativo" : " WHERE RC.CORRELATIVO = $correlativo ";
        }

        if ($sucursalId != -1)
        {
            $condicion .= strlen($condicion) > 0 ? " AND RC.SUCURSALID = $sucursalId" : " WHERE RC.SUCURSALID = $sucursalId ";
        }

        if ($proveedorId != -1)
        {
            $condicion .= strlen($condicion) > 0 ? " AND RC.PROVEEDORID = $proveedorId" : " WHERE RC.PROVEEDORID = $proveedorId ";
        }

        if ($fechaDesde != "")
        {
            $condicion .= strlen($condicion) > 0 ? " AND RC.FECHADERECEPCION >= '$fechaDesde' " : " WHERE RC.FECHADERECEPCION >= '$fechaDesde' ";
        }

        if ($estado != "")
        {
            $condicion .= strlen($condicion) > 0 ? " AND RC.ESTADO = '$estado' " : " WHERE RC.ESTADO = '$estado' ";
        }

        $sentenciaSql = "
            SELECT
                RC.RECEPCIONDECARGAID
                ,RC.SUCURSALID
                ,S.NOMBRE AS SUCURSAL
                ,RC.PROVEEDORID
                ,P.CODIGO AS CODIGOPROVEEDOR
                ,P.NOMBRE AS PROVEEDOR
                ,RC.TIPODESTOCKORIGENID
                ,TSO.NOMBRECORTO AS TIPODESTOCKORIGEN
                ,RC.TIPODESTOCKDISTID
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,CONVERT(VARCHAR, RC.FECHADEEMISION, 101) AS FECHADEEMISION
                ,CONVERT(VARCHAR, RC.FECHADERECEPCION, 101) AS FECHADERECEPCION
                ,RC.CORRELATIVO
                ,RC.NUMERODEDOCUMENTO
                ,RC.PORCENTAJETIPODESTOCKORIGEN
                ,RC.PORCENTAJETIPODESTOCKDIST
                ,RC.ESTADO
                ,CASE
                    WHEN RC.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN RC.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN RC.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN RC.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO
                ,RC.FECHACREACION
                ,RC.FECHAMODIFICACION
                ,RC.USUARIOIDCREACION
                ,UC.USUARIO AS USUARIOCREO
                ,RC.USUARIOIDMODIFICACION
                ,UM.USUARIO AS USUARIOMODIFICA
                ,RC.TIPODEGARANTIAID
                ,TG.NOMBRE AS TIPODEGARANTIA
                ,(SELECT SUM(CANTIDAD) FROM INVRECEPCIONESDECARGADETALLE RCD WHERE RCD.RECEPCIONDECARGAID=RC.RECEPCIONDECARGAID) AS PIEZAS
            FROM
                INVRECEPCIONESDECARGA RC
                JOIN CONFSUCURSALES S ON S.SUCURSALID=RC.SUCURSALID
                JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=S.SUCURSALID AND SXU.USUARIOID=?
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=RC.PROVEEDORID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=RC.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=RC.TIPODESTOCKDISTID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=RC.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=RC.USUARIOIDMODIFICACION
                JOIN INVTIPOSDEGARANTIA TG ON TG.TIPODEGARANTIAID=RC.TIPODEGARANTIAID

            $condicion
            
            ORDER BY
                RC.FECHADEEMISION DESC
                ,RC.CORRELATIVO DESC
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$usuarioId, $offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(RC.RECEPCIONDECARGAID) AS CONTEO
            FROM
                INVRECEPCIONESDECARGA RC
                JOIN CONFSUCURSALES S ON S.SUCURSALID=RC.SUCURSALID
                JOIN ACCSUCURSALESXUSUARIO SXU ON SXU.SUCURSALID=S.SUCURSALID AND SXU.USUARIOID=?
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=RC.PROVEEDORID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=RC.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=RC.TIPODESTOCKDISTID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=RC.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=RC.USUARIOIDMODIFICACION
                JOIN INVTIPOSDEGARANTIA TG ON TG.TIPODEGARANTIAID=RC.TIPODEGARANTIAID

            $condicion
        ";
        $datoConteo = $this->conn->select($sentenciaSql, [$usuarioId]);

        $resultado = [
            "total" => $datoConteo[0]["CONTEO"],
            "rows" => $datos
        ];

        return $resultado;
    }

    //-------------------------------------------

    /**
     * Obtener registros de la tabla (INVRECEPCIONESDECARGA) con filtros
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
                RC.RECEPCIONDECARGAID
                ,RC.SUCURSALID
                ,S.NOMBRE AS SUCURSAL
                ,RC.PROVEEDORID
                ,P.CODIGO AS CODIGOPROVEEDOR
                ,P.NOMBRE AS PROVEEDOR
                ,RC.TIPODESTOCKORIGENID
                ,TSO.NOMBRECORTO AS TIPODESTOCKORIGEN
                ,RC.TIPODESTOCKDISTID
                ,TSD.NOMBRECORTO AS TIPODESTOCKDIST
                ,RC.FECHADEEMISION
                ,RC.FECHADERECEPCION
                ,RC.CORRELATIVO
                ,RC.NUMERODEDOCUMENTO
                ,RC.PORCENTAJETIPODESTOCKORIGEN
                ,RC.PORCENTAJETIPODESTOCKDIST
                ,RC.ESTADO
                ,CASE
                    WHEN RC.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN RC.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN RC.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN RC.ESTADO = 'ANU' THEN 'CANCELED'
                END AS NOMBREDEESTADO
                ,RC.FECHACREACION
                ,RC.FECHAMODIFICACION
                ,RC.USUARIOIDCREACION
                ,UC.USUARIO AS USUARIOCREO
                ,RC.USUARIOIDMODIFICACION
                ,UM.USUARIO AS USUARIOMODIFICA
                ,RC.TIPODEGARANTIAID
                ,TG.NOMBRE AS TIPODEGARANTIA
            FROM
                INVRECEPCIONESDECARGA RC
                JOIN CONFSUCURSALES S ON S.SUCURSALID=RC.SUCURSALID
                JOIN INVPROVEEDORES P ON P.PROVEEDORID=RC.PROVEEDORID
                JOIN INVTIPOSDESTOCK TSO ON TSO.TIPODESTOCKID=RC.TIPODESTOCKORIGENID
                JOIN INVTIPOSDESTOCK TSD ON TSD.TIPODESTOCKID=RC.TIPODESTOCKDISTID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=RC.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=RC.USUARIOIDMODIFICACION
                JOIN INVTIPOSDEGARANTIA TG ON TG.TIPODEGARANTIAID=RC.TIPODEGARANTIAID
            WHERE
                $filtro
            ORDER BY
                RC.FECHADEEMISION DESC
                ,RC.CORRELATIVO DESC
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
        $this->recepcionDeCargaId = -1;
        $this->sucursalId = null;
        $this->sucursal = null;
        $this->proveedorId = null;
        $this->codigoProveedor = null;
        $this->proveedor = null;
        $this->tipoDeStockOrigenId = null;
        $this->tipoDeStockDistId = null;
        $this->tipoDeStockOrigen = null;
        $this->tipoDeStockDist = null;
        $this->tipoDeGarantiaId = null;
        $this->tipoDeGarantia = null;
        $this->fechaDeEmision = null;
        $this->fechaDeRecepcion = null;
        $this->correlativo = null;
        $this->numeroDeDocumento = null;
        $this->porcentajeTipoDeStockOrigen = null;
        $this->porcentajeTipoDeStockDist = null;
        $this->estado = null;
        $this->nombreDeEstado = null;

        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Edita un registro (INVRECEPCIONESDECARGA) existente
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
            UPDATE INVRECEPCIONESDECARGA SET " . $updates . " WHERE RECEPCIONDECARGAID = ?
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
     * Agregar un nuevo registro (INVRECEPCIONESDECARGA)
     * 
     * @param int $sucursalId Sucursal en la que se está registrando la recepción
     * @param int $proveedorId Proveedor que realiza la entrega de ítems
     * @param int $tipoDeStockOrigenId Tipo de stock de origen aplicado a la recepción
     * @param int $tipoDeStockDistId Tipo de stock para distribución en tienda aplicado a la recepción
     * @param string $fechaDeEmision Fecha de emisión del documento de recepción
     * @param string $fechaDeRecepcion Fecha en que se está registrando la recepción
     * @param int $correlativo Número correlativo de recepción (correlativo interno)
     * @param string $numeroDocumento Número de documento que se está recibiendo (número de origen)
     * @param float $porcentajeTipoDeStockOrigen Porcentaje de origen aplicado según el tipo de stock
     * @param float $porcentajeTipoDeStockDist Porcentaje para distribución en tienda aplicado según el tipo de stock
     * @param int $tipoDeGarantiaId Tipo de garantía aplicada a los productos del bill of lading
     * @param string $estado Estado con el que quedará guardado la recepción
     * @param int $usuarioId Usuario que está registrando la recepción
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $sucursalId, int $proveedorId, int $tipoDeStockOrigenId, int $tipoDeStockDistId, string $fechaDeEmision, string $fechaDeRecepcion, int $correlativo, string $numeroDocumento,
                                    float $porcentajeTipoDeStockOrigen, float $porcentajeTipoDeStockDist, int $tipoDeGarantiaId, string $estado, int $usuarioId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                INVRECEPCIONESDECARGA
                (SUCURSALID, PROVEEDORID, TIPODESTOCKORIGENID, TIPODESTOCKDISTID, FECHADEEMISION, FECHADERECEPCION, CORRELATIVO, NUMERODEDOCUMENTO,
                PORCENTAJETIPODESTOCKORIGEN, PORCENTAJETIPODESTOCKDIST, ESTADO, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION,
                TIPODEGARANTIAID)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, GETDATE(), GETDATE(), ?, ?,
                ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $sucursalId, $proveedorId, $tipoDeStockOrigenId, $tipoDeStockDistId, $fechaDeEmision, $fechaDeRecepcion, $correlativo, $numeroDocumento,
                                                $porcentajeTipoDeStockOrigen, $porcentajeTipoDeStockDist, $estado, $usuarioId, $usuarioId,
                                                $tipoDeGarantiaId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->recepcionDeCargaId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (INVRECEPCIONESDECARGA)
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
            EXECUTE SPINVELIMINARRECEPCIONDECARGA ?
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
     * Obtener la lista de estados que puede tomar una recepción para mostrar en combo
     * (incluye fila de "SELECT")
     * 
     * @param string @primeraOpcion Primer opción a mostrar en el combo, por defecto es "SELECT"
     * 
     * @return array Lista de estados, con el primer elemento "SELECT"  o personalizado
     * 
     */
    public function getListaDeEstadosParaCombo(string $primeraOpcion = "SELECT"): array
    {
        $sentenciaSql = "
            SELECT
                1 AS ORDEN
                ,'' AS ESTADO
                ,'- $primeraOpcion -' AS NOMBRE

            UNION

            SELECT
                2 AS ORDEN
                ,'FOR' AS ESTADO
                ,'FORMULATION' AS NOMBRE

            UNION

            SELECT
                3 AS ORDEN
                ,'CER' AS ESTADO
                ,'CLOSED' AS NOMBRE
            
            UNION

            SELECT
                4 AS ORDEN
                ,'PRO' AS ESTADO
                ,'POSTED' AS NOMBRE

            UNION
            
            SELECT
                5 AS ORDEN
                ,'ANU' AS ESTADO
                ,'CANCELED' AS NOMBRE
                
            ORDER BY
                ORDEN ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }
    
    //-------------------------------------------

    /**
     * Iniciar datos para crear un nuevo registro
     * 
     * @param void No necesita parámetros
     * 
     * @return void No retorna valor sino que quedan actualizadas las propiedades del objeto
     * 
     */
    // Resetear a valores neutros las propiedades del objeto
    public function iniciarDatosParaNuevoRegistro(): void
    {
        require_once("Sucursales.php");
        $objSucursales = New Sucursales($this->conn);
        $resCasaMatriz = $objSucursales->getWithFilters("ESCASAMATRIZ=1");
        $casaMatrizId = count($resCasaMatriz) > 0 ? $resCasaMatriz[0]["SUCURSALID"] : -1;

        $fechaDeEmision = new DateTime();
        $fechaDeRecepcion = new DateTime();

        $this->recepcionDeCargaId = -1;
        $this->sucursalId = $casaMatrizId;
        $this->sucursal = null;
        $this->proveedorId = null;
        $this->codigoProveedor = null;
        $this->proveedor = null;
        $this->tipoDeStockOrigenId = null;
        $this->tipoDeStockDistId = null;
        $this->tipoDeStockOrigen = null;
        $this->tipoDeStockDist = null;
        $this->tipoDeGarantiaId = null;
        $this->tipoDeGarantia = null;
        $this->fechaDeEmision = $fechaDeEmision;
        $this->fechaDeRecepcion = $fechaDeRecepcion;
        $this->correlativo = -1;
        $this->numeroDeDocumento = null;
        $this->porcentajeTipoDeStockOrigen = null;
        $this->porcentajeTipoDeStockDist = null;
        $this->estado = "FOR";
        $this->nombreDeEstado = "FORMULATION";

        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Cambiar de estado una recepción: CER, FOR, PRO, ANU
     * 
     * @param int $recepcionId Recepción que va a cambiar de estado
     * @param int $usuarioId Usuario que realiza el cambio de estado
     * @param string $estado Estado al que va a cambiar la recepción
     * 
     * @return bool Estado final del cambio de estado: true: se cambió el estado, false: no fue cambiado
     * 
     */
    public function cambiarEstado(int $recepcionId, int $usuarioId, string $estado): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            EXECUTE SPINVCAMBIARESTADORECEPCIONDECARGA ?, ?, ?
        ";
        $resultado = $this->conn->execute($sentenciaSql, [$recepcionId, $estado, $usuarioId]);
        
        $cambiado = false;
        if (count($resultado) > 0)
        {
            $cambiado = $resultado[0]["EXISTEERROR"] == 0;
            $this->mensajeError = $resultado[0]["MENSAJEDEERROR"];
        }

        return $cambiado;
    }

    //-------------------------------------------
}