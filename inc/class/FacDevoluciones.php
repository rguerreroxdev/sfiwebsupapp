<?php

require_once("SQLSrvBD.php");

class FacDevoluciones
{
    //-------------------------------------------

    private $conn;

    public $devolucionId;
    public $sucursalId;

    // Datos de factura devuelta
    public $facturaDevueltaId;
    public $clienteId;
    public $usuarioIdVendedor;
    public $usuarioVendedor;
    public $usuarioVendedorNombre;
    public $plataformaDeReferenciaId;
    public $plataformaDeReferencia;
    public $formaDeRetiroId;
    public $formaDeRetiro;
    public $fechaDeFactura;
    public $fechaDeFacturadt;
    public $prefijoDeCorrelativoDeFactura;
    public $correlativoDeFactura;
    public $sucursalNombre;
    public $sucursalDireccion;
    public $sucursalDireccionComplemento;
    public $sucursalCodigoPostal;
    public $sucursalTelefono;
    public $sucursalTelefonoServicio;
    public $clienteCodigo;
    public $clienteNombre;
    public $clienteDireccion;
    public $clienteDireccionComplemento;
    public $clienteCodigoPostal;
    public $clienteTelefono;
    public $clienteCorreoElectronico;
    public $personaDeReferencia;
    public $esClientePrevio;
    public $fechaDeRetiro;
    public $fechaDeRetirodt;
    public $agregarInstalacion;
    public $agregarAccesorios;
    public $noCalcularImpuesto;
    public $notas;

    // Datos de factura sustituye
    public $facturaSustituyeId;
    public $prefijoDeCorrelativoSustituye;
    public $CorrelativoFacturaSustituye;
    public $fechaFacturaSustituye;
    public $fechaFacturaSustituyedt;

    // Datos de devolución
    public $fechaDevolucion;
    public $fechaDevoluciondt;
    public $prefijoCorrelativoDevolucion;
    public $correlativoDevolucion;
    public $totalAntesDeImpuesto;
    public $impuestoPorcentaje;
    public $impuesto;
    public $totalConImpuesto;
    public $impuestoFinanciera;
    public $totalFinal;
    public $concepto;
    public $estado;
    public $nombreDeEstado;

    public $fechaCreacion;
    public $fechaModificacion;
    public $usuarioIdCreacion;
    public $usuarioIdModificacion;
    public $usuarioCreo;
    public $usuarioCreoNombre;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto FacDevoluciones
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
     * Obtener todos los registros de la tabla (FACDEVOLUCIONES) con paginación, filtrado por sucursal
     * 
     * @param int $sucursalId Sucursal que se filtrará para mostrar documentos
     * @param string $correlativo Correlativo que se está buscando de forma directa
     * @param string $fechaDesde Fecha inicial para filtrar registros
     * @param string $nombreCliente Cliente por el que se quiere filtrar los documentos
     * @param string $estado Estado de los documentos para filtrar registros
     * @param int $numeroDePagina Indica el número de página a mostrar, debe ser un valor mayor o igual a cero
     * @param int $tamanoDePagina Indica la cantidad de filas que se van a retornar
     * 
     * @return array Todos los registros encontrados en la tabla
     * 
     */
    public function getAllXSucursalConPaginacion(int $sucursalId, string $correlativo, string $fechaDesde, string $nombreCliente, string $estado, int $numeroDePagina = 1, int $tamanoDePagina = 25): array
    {
        $this->resetPropiedades();

        $offset = $numeroDePagina * $tamanoDePagina;

        $condicion = "
            WHERE
                D.SUCURSALID = $sucursalId
                AND D.FECHA >= '$fechaDesde' ";

        if (is_numeric($correlativo))
        {
            $condicion .= " AND D.CORRELATIVO = $correlativo";
        }

        if ($nombreCliente != "")
        {
            $condicion .= " AND C.NOMBRE LIKE '%$nombreCliente%'";
        }

        if ($estado != "")
        {
            $condicion .= " AND D.ESTADO = '$estado' ";
        }

        $sentenciaSql = "
            SELECT
                D.DEVOLUCIONID,
                SUC.NOMBRE AS SUCURSAL,
                CONVERT(VARCHAR, D.FECHA, 101) AS FECHA,
                D.PREFIJODECORRELATIVO + '-' + CONVERT(VARCHAR, D.CORRELATIVO) AS NUMERODEVOLUCION,
                FD.PREFIJODECORRELATIVO + '-' + CONVERT(VARCHAR, FD.CORRELATIVO) AS NUMEROFACTURADEVUELTA,
                ISNULL(FS.PREFIJODECORRELATIVO + '-' + CONVERT(VARCHAR, FS.CORRELATIVO), '') AS NUMEROFACTURASUSTITUYE,
                C.CODIGO + ' - ' + C.NOMBRE AS CLIENTE,
                D.TOTALFINAL,
                D.ESTADO,
                CASE
                    WHEN D.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN D.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN D.ESTADO = 'PRO' THEN 'POSTED'
                END AS NOMBREDEESTADO,
                D.FECHACREACION,
                D.FECHAMODIFICACION,
                D.USUARIOIDCREACION,
                UC.USUARIO AS USUARIOCREO
            FROM
                FACDEVOLUCIONES D
                JOIN FACFACTURAS FD ON FD.FACTURAID=D.FACTURADEVUELTAID
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=FD.SUCURSALID
                JOIN FACCLIENTES C ON C.CLIENTEID=FD.CLIENTEID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=D.USUARIOIDCREACION
                LEFT JOIN FACFACTURAS FS ON FS.FACTURAID=D.FACTURASUSTITUYEID

            $condicion
            
            ORDER BY
                D.FECHA DESC,
                D.CORRELATIVO DESC

            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(D.DEVOLUCIONID) AS CONTEO
            FROM
                FACDEVOLUCIONES D
                JOIN FACFACTURAS FD ON FD.FACTURAID=D.FACTURADEVUELTAID
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=FD.SUCURSALID
                JOIN FACCLIENTES C ON C.CLIENTEID=FD.CLIENTEID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=D.USUARIOIDCREACION
                LEFT JOIN FACFACTURAS FS ON FS.FACTURAID=D.FACTURASUSTITUYEID

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
     * Obtener datos de un registro (FACDEVOLUCIONES) por medio de ID
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
				D.DEVOLUCIONID,
                D.SUCURSALID,
                D.FACTURADEVUELTAID,
				D.FACTURASUSTITUYEID,
                CONVERT(VARCHAR, D.FECHA, 101) AS FECHA,
                D.FECHA AS FECHADT,
				D.PREFIJODECORRELATIVO,
				D.CORRELATIVO,
				FD.CLIENTEID,
				FD.USUARIOIDVENDEDOR,
				UV.USUARIO AS USUARIOVENDEDOR,
                UV.NOMBRECOMPLETO AS USUARIOVENDEDORNOMBRE,
				FD.PLATAFORMADEREFERENCIAID,
				PF.NOMBRE AS PLATAFORMADEREFERENCIA,
                FD.FORMADERETIROID,
				FR.NOMBRE AS FORMADERETIRO,
                CONVERT(VARCHAR, FD.FECHA, 101) AS FECHAFACTURADEVUELTA,
                FD.FECHA AS FECHAFACTURADEVUELTADT,
				FD.PREFIJODECORRELATIVO AS PREFIJOFACTURADEVUELTA,
				FD.CORRELATIVO AS CORRELATIVOFACTURADEVUELTA,
				FD.SUCURSALNOMBRE,
				FD.SUCURSALDIRECCION,
				FD.SUCURSALDIRECCIONCOMPLEMENTO,
				FD.SUCURSALCODIGOPOSTAL,
				FD.SUCURSALTELEFONO,
                FD.SUCURSALTELEFONOSERVICIO,
				C.CODIGO AS CLIENTECODIGO,
				FD.CLIENTENOMBRE,
				FD.CLIENTEDIRECCION,
				FD.CLIENTEDIRECCIONCOMPLEMENTO,
				FD.CLIENTECODIGOPOSTAL,
				FD.CLIENTETELEFONO,
				FD.CLIENTECORREOELECTRONICO,
				FD.PERSONADEREFERENCIA,
				FD.ESCLIENTEPREVIO,
				CONVERT(VARCHAR, FD.FECHADERETIRO, 101) AS FECHADERETIRO,
				FD.FECHADERETIRO AS FECHADERETIRODT,
				FD.AGREGARINSTALACION,
				FD.AGREGARACCESORIOS,
                FD.NOCALCULARIMPUESTO,
				D.TOTALANTESDEIMPUESTO,
				D.IMPUESTOPORCENTAJE,
				D.IMPUESTO,
				D.TOTALCONIMPUESTO,
                D.IMPUESTOFINANCIERA,
                D.TOTALFINAL,
                FD.NOTAS,
                D.ESTADO,
				D.CONCEPTO,
				FS.PREFIJODECORRELATIVO AS PREFIJOFACTURASUSTITUYE,
				FS.CORRELATIVO AS CORRELATIVOFACTURASUSTITUYE,
				CONVERT(VARCHAR, FS.FECHA, 101) AS FECHAFACTURASUSTITUYE,
                FS.FECHA AS FECHAFACTURASUSTITUYEDT,
                CASE
                    WHEN D.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN D.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN D.ESTADO = 'PRO' THEN 'POSTED'
                END AS NOMBREDEESTADO,
                D.FECHACREACION,
                D.FECHAMODIFICACION,
                D.USUARIOIDCREACION,
                UC.USUARIO AS USUARIOCREO,
                UC.NOMBRECOMPLETO AS USUARIOCREONOMBRE,
                D.USUARIOIDMODIFICACION,
                UM.USUARIO AS USUARIOMODIFICA
            FROM
				FACDEVOLUCIONES D
                JOIN FACFACTURAS FD ON FD.FACTURAID=D.FACTURADEVUELTAID
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=D.SUCURSALID
				JOIN FACCLIENTES C ON C.CLIENTEID=FD.CLIENTEID
				JOIN ACCUSUARIOS UV ON UV.USUARIOID=FD.USUARIOIDVENDEDOR
				JOIN FACPLATAFORMASDEREFERENCIA PF ON PF.PLATAFORMADEREFERENCIAID=FD.PLATAFORMADEREFERENCIAID
                JOIN FACFORMASDERETIRO FR ON FR.FORMADERETIROID=FD.FORMADERETIROID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=D.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=D.USUARIOIDMODIFICACION
				LEFT JOIN FACFACTURAS FS ON FS.FACTURAID=D.FACTURASUSTITUYEID
            WHERE
                D.DEVOLUCIONID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->devolucionId = $dato["DEVOLUCIONID"];
            $this->sucursalId = $dato["SUCURSALID"];
            $this->facturaDevueltaId = $dato["FACTURADEVUELTAID"];
            $this->clienteId = $dato["CLIENTEID"];
            $this->usuarioIdVendedor = $dato["USUARIOIDVENDEDOR"];
            $this->usuarioVendedor = $dato["USUARIOVENDEDOR"];
            $this->usuarioVendedorNombre = $dato["USUARIOVENDEDORNOMBRE"];
            $this->plataformaDeReferenciaId = $dato["PLATAFORMADEREFERENCIAID"];
            $this->plataformaDeReferencia = $dato["PLATAFORMADEREFERENCIA"];
            $this->formaDeRetiroId = $dato["FORMADERETIROID"];
            $this->formaDeRetiro = $dato["FORMADERETIRO"];
            $this->fechaDeFactura = $dato["FECHAFACTURADEVUELTA"];
            $this->fechaDeFacturadt = $dato["FECHAFACTURADEVUELTADT"];
            $this->prefijoDeCorrelativoDeFactura = $dato["PREFIJOFACTURADEVUELTA"];
            $this->correlativoDeFactura = $dato["CORRELATIVOFACTURADEVUELTA"];
            $this->sucursalNombre = $dato["SUCURSALNOMBRE"];
            $this->sucursalDireccion = $dato["SUCURSALDIRECCION"];
            $this->sucursalDireccionComplemento = $dato["SUCURSALDIRECCIONCOMPLEMENTO"];
            $this->sucursalCodigoPostal = $dato["SUCURSALCODIGOPOSTAL"];
            $this->sucursalTelefono = $dato["SUCURSALTELEFONO"];
            $this->sucursalTelefonoServicio = $dato["SUCURSALTELEFONOSERVICIO"];
            $this->clienteCodigo = $dato["CLIENTECODIGO"];
            $this->clienteNombre = $dato["CLIENTENOMBRE"];
            $this->clienteDireccion = $dato["CLIENTEDIRECCION"];
            $this->clienteDireccionComplemento = $dato["CLIENTEDIRECCIONCOMPLEMENTO"];
            $this->clienteCodigoPostal = $dato["CLIENTECODIGOPOSTAL"];
            $this->clienteTelefono = $dato["CLIENTETELEFONO"];
            $this->clienteCorreoElectronico = $dato["CLIENTECORREOELECTRONICO"];
            $this->personaDeReferencia = $dato["PERSONADEREFERENCIA"];
            $this->esClientePrevio = $dato["ESCLIENTEPREVIO"];
            $this->fechaDeRetiro = $dato["FECHADERETIRO"];
            $this->fechaDeRetirodt = $dato["FECHADERETIRODT"];
            $this->agregarInstalacion = $dato["AGREGARINSTALACION"];
            $this->agregarAccesorios = $dato["AGREGARACCESORIOS"];
            $this->noCalcularImpuesto = $dato["NOCALCULARIMPUESTO"];
            $this->notas = $dato["NOTAS"];

            $this->facturaSustituyeId = $dato["FACTURASUSTITUYEID"];
            $this->prefijoDeCorrelativoSustituye = $dato["PREFIJOFACTURASUSTITUYE"];
            $this->CorrelativoFacturaSustituye = $dato["CORRELATIVOFACTURASUSTITUYE"];
            $this->fechaFacturaSustituye = $dato["FECHAFACTURASUSTITUYE"];
            $this->fechaFacturaSustituyedt = $dato["FECHAFACTURASUSTITUYEDT"];

            $this->fechaDevolucion = $dato["FECHA"];
            $this->fechaDevoluciondt = $dato["FECHADT"];
            $this->prefijoCorrelativoDevolucion = $dato["PREFIJODECORRELATIVO"];
            $this->correlativoDevolucion = $dato["CORRELATIVO"];
            $this->totalAntesDeImpuesto = $dato["TOTALANTESDEIMPUESTO"];
            $this->impuestoPorcentaje = $dato["IMPUESTOPORCENTAJE"];
            $this->impuesto = $dato["IMPUESTO"];
            $this->totalConImpuesto = $dato["TOTALCONIMPUESTO"];
            $this->impuestoFinanciera = $dato["IMPUESTOFINANCIERA"];
            $this->totalFinal = $dato["TOTALFINAL"];
            $this->concepto = $dato["CONCEPTO"];
            
            $this->estado = $dato["ESTADO"];
            $this->nombreDeEstado = $dato["NOMBREDEESTADO"];
        
            $this->fechaCreacion = $dato["FECHACREACION"];
            $this->fechaModificacion = $dato["FECHAMODIFICACION"];
            $this->usuarioIdCreacion = $dato["USUARIOIDCREACION"];
            $this->usuarioIdModificacion = $dato["USUARIOIDMODIFICACION"];
            $this->usuarioCreo = $dato["USUARIOCREO"];
            $this->usuarioCreoNombre = $dato["USUARIOCREONOMBRE"];
            $this->usuarioModifica = $dato["USUARIOMODIFICA"];
        }
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
        $fecha = new DateTime();

        $this->devolucionId = -1;
        $this->sucursalId = -1;

        $this->facturaDevueltaId = -1;
        $this->clienteId = -1;
        $this->usuarioIdVendedor = -1;
        $this->usuarioVendedor = null;
        $this->usuarioVendedorNombre = null;
        $this->plataformaDeReferenciaId = -1;
        $this->plataformaDeReferencia = null;
        $this->formaDeRetiroId = -1;
        $this->formaDeRetiro = null;
        $this->fechaDeFactura = null;
        $this->fechaDeFacturadt = null;
        $this->prefijoDeCorrelativoDeFactura = null;
        $this->correlativoDeFactura = null;
        $this->sucursalNombre = null;
        $this->sucursalDireccion = null;
        $this->sucursalDireccionComplemento = null;
        $this->sucursalCodigoPostal = null;
        $this->sucursalTelefono = null;
        $this->sucursalTelefonoServicio = null;
        $this->clienteCodigo = null;
        $this->clienteNombre = null;
        $this->clienteDireccion = null;
        $this->clienteDireccionComplemento = null;
        $this->clienteCodigoPostal = null;
        $this->clienteTelefono = null;
        $this->clienteCorreoElectronico = null;
        $this->personaDeReferencia = null;
        $this->esClientePrevio = 0;
        $this->fechaDeRetiro = null;
        $this->fechaDeRetirodt = null;
        $this->agregarInstalacion = 0;
        $this->agregarAccesorios = 0;
        $this->noCalcularImpuesto = 0;
        $this->notas = null;

        $this->facturaSustituyeId = -1;
        $this->prefijoDeCorrelativoSustituye = null;
        $this->CorrelativoFacturaSustituye = null;
        $this->fechaFacturaSustituye = null;
        $this->fechaFacturaSustituyedt = null;

        $this->fechaDevolucion = $fecha;
        $this->fechaDevoluciondt = $fecha;
        $this->prefijoCorrelativoDevolucion = "";
        $this->correlativoDevolucion = -1;
        $this->totalAntesDeImpuesto = 0.00;
        $this->impuestoPorcentaje = 0.00;
        $this->impuesto = 0.00;
        $this->totalConImpuesto = 0.00;
        $this->impuestoFinanciera = 0.000;
        $this->totalFinal = 0.00;
        $this->concepto = null;
        
        $this->estado = "FOR";
        $this->nombreDeEstado = "FORMULATION";

        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioCreoNombre = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Agregar un nuevo registro (FACDEVOLUCION)
     * 
     * @param int $sucursalId Sucursal en la que se registra la devolución
     * @param int $facturaDevueltaId Factura a la que se le está creando la devolución
     * @param string $fecha Fecha de emisión de la devolución
     * @param string $prefijoDeCorrelativo Prefijo del correlativo de la devolución según sucursal
     * @param int $correlativo Número correlativo de la devolución
     * @param float $totalAntesDeImpuesto Total de la devolución antes de sumar impuesto
     * @param float $impuestoPorcentaje Porcentaje de impuesto aplicado a la devolución
     * @param float $impuesto Total de impuesto aplicado a la devolución
     * @param float $totalConImpuesto Total antes de impuesto + impuesto
     * @param float $impuestoFinanciera Impuesto calculado de pagos por medio de financieras
     * @param float $totalFinal total final de la devolución
     * @param string $concepto Concepto por el que se está creando la devolución
     * @param string $estado Estado con el que se crea la factura
     * @param int $usuarioId Usuario que está registrando la factura
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(
        int $sucursalId, int $facturaDevueltaId, string $fecha,
        string $prefijoDeCorrelativo, int $correlativo,
        float $totalAntesDeImpuesto, float $impuestoPorcentaje, float $impuesto, float $totalConImpuesto,
        float $impuestoFinanciera, float $totalFinal, string $concepto, string $estado, int $usuarioId
    ): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                FACDEVOLUCIONES
                (SUCURSALID, FACTURADEVUELTAID, FACTURASUSTITUYEID, FECHA, PREFIJODECORRELATIVO, CORRELATIVO,
                TOTALANTESDEIMPUESTO, IMPUESTOPORCENTAJE, IMPUESTO, TOTALCONIMPUESTO, IMPUESTOFINANCIERA,
                TOTALFINAL, CONCEPTO, ESTADO, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, NULL, ?, ?, ?,
                 ?, ?, ?, ?, ?,
                 ?, ?, ?, GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $sucursalId, $facturaDevueltaId, $fecha, $prefijoDeCorrelativo, $correlativo,
                                                $totalAntesDeImpuesto, $impuestoPorcentaje, $impuesto, $totalConImpuesto, $impuestoFinanciera,
                                                $totalFinal, $concepto, $estado, $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->devolucionId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Edita un registro (FACDEVOLUCIONES) existente
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
            UPDATE FACDEVOLUCIONES SET " . $updates . " WHERE DEVOLUCIONID = ?
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
     * Eliminar un registro (FACDEVOLUCIONES)
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
            EXECUTE SPFACELIMINARDEVOLUCION ?
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
        $this->devolucionId = -1;
        $this->sucursalId = null;

        $this->facturaDevueltaId = null;
        $this->clienteId = null;
        $this->usuarioIdVendedor = null;
        $this->usuarioVendedor = null;
        $this->usuarioVendedorNombre = null;
        $this->plataformaDeReferenciaId = null;
        $this->plataformaDeReferencia = null;
        $this->formaDeRetiroId = null;
        $this->formaDeRetiro = null;
        $this->fechaDeFactura = null;
        $this->fechaDeFacturadt = null;
        $this->prefijoDeCorrelativoDeFactura = null;
        $this->correlativoDeFactura = null;
        $this->sucursalNombre = null;
        $this->sucursalDireccion = null;
        $this->sucursalDireccionComplemento = null;
        $this->sucursalCodigoPostal = null;
        $this->sucursalTelefono = null;
        $this->sucursalTelefonoServicio = null;
        $this->clienteCodigo = null;
        $this->clienteNombre = null;
        $this->clienteDireccion = null;
        $this->clienteDireccionComplemento = null;
        $this->clienteCodigoPostal = null;
        $this->clienteTelefono = null;
        $this->clienteCorreoElectronico = null;
        $this->personaDeReferencia = null;
        $this->esClientePrevio = null;
        $this->fechaDeRetiro = null;
        $this->fechaDeRetirodt = null;
        $this->agregarInstalacion = null;
        $this->agregarAccesorios = null;
        $this->noCalcularImpuesto = null;
        $this->notas = null;

        $this->facturaSustituyeId = null;
        $this->prefijoDeCorrelativoSustituye = null;
        $this->CorrelativoFacturaSustituye = null;
        $this->fechaFacturaSustituye = null;
        $this->fechaFacturaSustituyedt = null;

        $this->fechaDevolucion = null;
        $this->fechaDevoluciondt = null;
        $this->prefijoCorrelativoDevolucion = null;
        $this->correlativoDevolucion = null;
        $this->totalAntesDeImpuesto = null;
        $this->impuestoPorcentaje = null;
        $this->impuesto = null;
        $this->totalConImpuesto = null;
        $this->impuestoFinanciera = null;
        $this->totalFinal = null;
        
        $this->concepto = null;
        $this->estado = null;
        $this->nombreDeEstado = null;

        $this->fechaCreacion = null;
        $this->fechaModificacion = null;
        $this->usuarioIdCreacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioCreo = null;
        $this->usuarioCreoNombre = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Obtener la lista de estados que puede tomar una devolución para mostrar en combo
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

            ORDER BY
                ORDEN ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Cambiar de estado una devolución: CER, FOR, PRO
     * 
     * @param int $devolucionId Devolución que va a cambiar de estado
     * @param int $usuarioId Usuario que realiza el cambio de estado
     * @param string $estado Estado al que va a cambiar la devolución
     * 
     * @return bool Estado final del cambio de estado: true: se cambió el estado, false: no fue cambiado
     * 
     */
    public function cambiarEstado(int $devolucionId, int $usuarioId, string $estado): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            EXECUTE SPFACCAMBIARESTADODEVOLUCION ?, ?, ?
        ";
        $resultado = $this->conn->execute($sentenciaSql, [$devolucionId, $estado, $usuarioId]);
        
        $cambiado = false;
        if (count($resultado) > 0)
        {
            $cambiado = $resultado[0]["EXISTEERROR"] == 0;
            $this->mensajeError = $resultado[0]["MENSAJEDEERROR"];
        }

        return $cambiado;
    }

    //-------------------------------------------

    /**
     * Obtener el número de ítems que tiene la devolucion: detalles y otrosdetalles
     * 
     * @param int @devolucionId Devolución a la que se le contarán los ítems
     * 
     * @return int Cantidad de ítems que tiene la devolución
     * 
     */
    public function getTotalDeItems(int $devolucionId): int
    {
        $sentenciaSql = "
            SELECT
                COUNT(*) AS CONTEO
            FROM
                FACDEVOLUCIONESDETALLE
            WHERE
                DEVOLUCIONID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$devolucionId]);

        $conteoDetalle = $datos[0]["CONTEO"];

        $sentenciaSql = "
            SELECT
                COUNT(*) AS CONTEO
            FROM
                FACDEVOLUCIONESOTROSDETALLES
            WHERE
                DEVOLUCIONID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$devolucionId]);

        $conteoOtroDetalle = $datos[0]["CONTEO"];
        
        return ($conteoDetalle + $conteoOtroDetalle);
    }
    
    //-------------------------------------------

    /**
     * Busca una factura sustituta en devoluciones
     * 
     * @param int $facturaId ID de la factura que será buscada como sustituta en devoluciones
     * 
     * @return bool false: no encontrada, true: encontrada
     * 
     */
    public function existeFacturaSustitutaEnDevolucion(int $facturaId): bool
    {
        $sentenciaSql = "
            SELECT
				*
            FROM
				FACDEVOLUCIONES D
            WHERE
                D.FACTURASUSTITUYEID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        $this->resetPropiedades();
        $encontrada = false;
        foreach ($datos as $dato)
        {
            $encontrada = true;
        }

        return $encontrada;
    }

    //-------------------------------------------

    /**
     * Busca una devolución por medio de la factura sustituta
     * 
     * @param int $facturaSustitutaId ID de la factura sustituta con la que se busca su devolución
     * 
     * @return void No retorna valores pero se toman los datos en los atributos del objeto
     * 
     */
    public function getByFacturaSustituta(int $facturaSustitutaId): void
    {
        $sentenciaSql = "
            SELECT
                D.DEVOLUCIONID,
                D.PREFIJODECORRELATIVO,
                D.CORRELATIVO,
                CONVERT(VARCHAR, D.FECHA, 101) AS FECHA
            FROM
                FACDEVOLUCIONES D
            WHERE
                FACTURASUSTITUYEID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaSustitutaId]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->devolucionId = $dato["DEVOLUCIONID"];
            $this->prefijoCorrelativoDevolucion = $dato["PREFIJODECORRELATIVO"];
            $this->correlativoDevolucion = $dato["CORRELATIVO"];
            $this->fechaDevolucion = $dato["FECHA"];
        }
    }

    //-------------------------------------------
}