<?php

require_once("SQLSrvBD.php");

class Facturas
{
    //-------------------------------------------

    private $conn;

    public $facturaId;
    public $sucursalId;
    public $clienteId;
    public $usuarioIdVendedor;
    public $usuarioVendedor;
    public $usuarioVendedorNombre;
    public $plataformaDeReferenciaId;
    public $plataformaDeReferencia;
    public $formaDeRetiroId;
    public $formaDeRetiro;
    public $fecha;
    public $fechadt;
    public $prefijoDeCorrelativo;
    public $correlativo;
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
    public $totalAntesDeImpuesto;
    public $impuestoPorcentaje;
    public $impuesto;
    public $totalConImpuesto;
    public $impuestoFinanciera;
    public $totalFinal;
    public $notas;
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
     * Instancia un objeto Facturas
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
     * Obtener todos los registros de la tabla (FACFACTURAS) con paginación, filtrado por sucursal
     * 
     * @param int $sucursalId Sucursal que se filtrará para mostrar documentos (-1 muestra todas)
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
                F.SUCURSALID = $sucursalId
                AND F.FECHA >= '$fechaDesde' ";

        if (is_numeric($correlativo))
        {
            $condicion .= " AND F.CORRELATIVO = $correlativo";
        }

        if ($nombreCliente != "")
        {
            $condicion .= " AND C.NOMBRE LIKE '%$nombreCliente%'";
        }

        if ($estado != "")
        {
            $condicion .= " AND F.ESTADO = '$estado' ";
        }

        $sentenciaSql = "
            SELECT
                F.FACTURAID,
				SUC.NOMBRE AS SUCURSAL,
                CONVERT(VARCHAR, F.FECHA, 101) AS FECHA,
				F.PREFIJODECORRELATIVO + '-' + CONVERT(VARCHAR, F.CORRELATIVO) AS NUMEROFACTURA,
				C.CODIGO + ' - ' + C.NOMBRE AS CLIENTE,
                F.TOTALCONIMPUESTO AS TOTALCONIMPUESTO,
                F.TOTALFINAL,
                F.ESTADO,
                CASE
                    WHEN F.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN F.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN F.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN F.ESTADO = 'ANU' THEN 'CANCELED'
                    WHEN F.ESTADO = 'DEV' THEN 'RETURNED'
                END AS NOMBREDEESTADO,
                UV.NOMBRECOMPLETO AS VENDEDOR,
                F.FECHACREACION,
                F.FECHAMODIFICACION,
                F.USUARIOIDCREACION,
                UC.USUARIO AS USUARIOCREO,
                F.USUARIOIDMODIFICACION,
                UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACFACTURAS F
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=F.SUCURSALID
				JOIN FACCLIENTES C ON C.CLIENTEID=F.CLIENTEID
                JOIN ACCUSUARIOS UV ON UV.USUARIOID=F.USUARIOIDVENDEDOR
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=F.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=F.USUARIOIDMODIFICACION

            $condicion
            
            ORDER BY
                F.FECHA DESC,
                F.CORRELATIVO DESC

            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY
        ";
        $datos = $this->conn->select($sentenciaSql, [$offset, $tamanoDePagina]);

        $sentenciaSql = "
            SELECT
                COUNT(F.FACTURAID) AS CONTEO
            FROM
                FACFACTURAS F
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=F.SUCURSALID
				JOIN FACCLIENTES C ON C.CLIENTEID=F.CLIENTEID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=F.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=F.USUARIOIDMODIFICACION

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
     * Obtener datos de un registro (FACFACTURAS) por medio de ID
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
                F.FACTURAID,
                F.SUCURSALID,
				F.CLIENTEID,
				F.USUARIOIDVENDEDOR,
				UV.USUARIO AS USUARIOVENDEDOR,
                UV.NOMBRECOMPLETO AS USUARIOVENDEDORNOMBRE,
				F.PLATAFORMADEREFERENCIAID,
				PF.NOMBRE AS PLATAFORMADEREFERENCIA,
                F.FORMADERETIROID,
				FR.NOMBRE AS FORMADERETIRO,
                CONVERT(VARCHAR, F.FECHA, 101) AS FECHA,
                FECHA AS FECHADT,
				F.PREFIJODECORRELATIVO,
				F.CORRELATIVO,
				F.SUCURSALNOMBRE,
				F.SUCURSALDIRECCION,
				F.SUCURSALDIRECCIONCOMPLEMENTO,
				F.SUCURSALCODIGOPOSTAL,
				F.SUCURSALTELEFONO,
                F.SUCURSALTELEFONOSERVICIO,
				C.CODIGO AS CLIENTECODIGO,
				F.CLIENTENOMBRE,
				F.CLIENTEDIRECCION,
				F.CLIENTEDIRECCIONCOMPLEMENTO,
				F.CLIENTECODIGOPOSTAL,
				F.CLIENTETELEFONO,
				F.CLIENTECORREOELECTRONICO,
				F.PERSONADEREFERENCIA,
				F.ESCLIENTEPREVIO,
				CONVERT(VARCHAR, F.FECHADERETIRO, 101) AS FECHADERETIRO,
				F.FECHADERETIRO AS FECHADERETIRODT,
				F.AGREGARINSTALACION,
				F.AGREGARACCESORIOS,
                F.NOCALCULARIMPUESTO,
				F.TOTALANTESDEIMPUESTO,
				F.IMPUESTOPORCENTAJE,
				F.IMPUESTO,
				F.TOTALCONIMPUESTO,
                F.IMPUESTOFINANCIERA,
                F.TOTALFINAL,
                F.NOTAS,
                F.ESTADO,
                CASE
                    WHEN F.ESTADO = 'FOR' THEN 'FORMULATION'
                    WHEN F.ESTADO = 'CER' THEN 'CLOSED'
                    WHEN F.ESTADO = 'PRO' THEN 'POSTED'
                    WHEN F.ESTADO = 'ANU' THEN 'CANCELED'
                    WHEN F.ESTADO = 'DEV' THEN 'RETURNED'
                END AS NOMBREDEESTADO,
                F.FECHACREACION,
                F.FECHAMODIFICACION,
                F.USUARIOIDCREACION,
                UC.USUARIO AS USUARIOCREO,
                UC.NOMBRECOMPLETO AS USUARIOCREONOMBRE,
                F.USUARIOIDMODIFICACION,
                UM.USUARIO AS USUARIOMODIFICA
            FROM
                FACFACTURAS F
                JOIN CONFSUCURSALES SUC ON SUC.SUCURSALID=F.SUCURSALID
				JOIN FACCLIENTES C ON C.CLIENTEID=F.CLIENTEID
				JOIN ACCUSUARIOS UV ON UV.USUARIOID=F.USUARIOIDVENDEDOR
				JOIN FACPLATAFORMASDEREFERENCIA PF ON PF.PLATAFORMADEREFERENCIAID=F.PLATAFORMADEREFERENCIAID
                JOIN FACFORMASDERETIRO FR ON FR.FORMADERETIROID=F.FORMADERETIROID
                JOIN ACCUSUARIOS UC ON UC.USUARIOID=F.USUARIOIDCREACION
                JOIN ACCUSUARIOS UM ON UM.USUARIOID=F.USUARIOIDMODIFICACION
            WHERE
                F.FACTURAID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$id]);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->facturaId = $dato["FACTURAID"];
            $this->sucursalId = $dato["SUCURSALID"];
            $this->clienteId = $dato["CLIENTEID"];
            $this->usuarioIdVendedor = $dato["USUARIOIDVENDEDOR"];
            $this->usuarioVendedor = $dato["USUARIOVENDEDOR"];
            $this->usuarioVendedorNombre = $dato["USUARIOVENDEDORNOMBRE"];
            $this->plataformaDeReferenciaId = $dato["PLATAFORMADEREFERENCIAID"];
            $this->plataformaDeReferencia = $dato["PLATAFORMADEREFERENCIA"];
            $this->formaDeRetiroId = $dato["FORMADERETIROID"];
            $this->formaDeRetiro = $dato["FORMADERETIRO"];
            $this->fecha = $dato["FECHA"];
            $this->fechadt = $dato["FECHADT"];
            $this->prefijoDeCorrelativo = $dato["PREFIJODECORRELATIVO"];
            $this->correlativo = $dato["CORRELATIVO"];
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
            $this->totalAntesDeImpuesto = $dato["TOTALANTESDEIMPUESTO"];
            $this->impuestoPorcentaje = $dato["IMPUESTOPORCENTAJE"];
            $this->impuesto = $dato["IMPUESTO"];
            $this->totalConImpuesto = $dato["TOTALCONIMPUESTO"];
            $this->impuestoFinanciera = $dato["IMPUESTOFINANCIERA"];
            $this->totalFinal = $dato["TOTALFINAL"];
            $this->notas = $dato["NOTAS"];
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

        $this->facturaId = -1;
        $this->sucursalId = -1;
        $this->clienteId = -1;
        $this->usuarioIdVendedor = -1;
        $this->usuarioVendedor = null;
        $this->usuarioVendedorNombre = null;
        $this->plataformaDeReferenciaId = -1;
        $this->plataformaDeReferencia = null;
        $this->formaDeRetiroId = -1;
        $this->formaDeRetiro = null;
        $this->fecha = $fecha;
        $this->fechadt = $fecha;
        $this->prefijoDeCorrelativo = null;
        $this->correlativo = -1;
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
        $this->fechaDeRetiro = $fecha;
        $this->fechaDeRetirodt = $fecha;
        $this->agregarInstalacion = 0;
        $this->agregarAccesorios = 0;
        $this->noCalcularImpuesto = 0;
        $this->totalAntesDeImpuesto = 0.00;
        $this->impuestoPorcentaje = 0.00;
        $this->impuesto = 0.00;
        $this->totalConImpuesto = 0.00;
        $this->impuestoFinanciera = 0.000;
        $this->totalFinal = 0.00;
        $this->notas = null;
        $this->notas = null;
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
     * Agregar un nuevo registro (FACFACTURAS)
     * 
     * @param int $sucursalId Sucursal en la que se registra la factura
     * @param int $clienteId Cliente al que se le emite la factura
     * @param int $usuarioIdVendedor Usuario vendedor que emite la factura
     * @param int $plataformaDeReferenciaId Plataforma de referencia con la que el cliente supo de la tienda
     * @param string $formaDeRetiroId Forma en que se va a retirar los ítems facturados
     * @param string $fecha Fecha de emisión de la factura
     * @param string $prefijoDeCorrelativo Prefijo del correlativo de la factura según sucursal
     * @param int $correlativo Número correlativo de la factura
     * @param string $sucursalNombre Nombre de sucursal en el momento de emitir la factura
     * @param string $sucursalDireccion Dirección de sucursal en el momento de emitir la factura
     * @param string $sucursalDireccionComplemento Complemento de la dirección de la sucursal
     * @param string $sucursalCodigoPostal Código postal de la dirección de la sucursal
     * @param string $sucursalTelefono Teléfono de la sucursal
     * @param string $sucursalTelefonoServicio Teléfono de servicio de la sucursal
     * @param string $clienteNombre Nombre del cliente a que se emite la factura
     * @param string $clienteDireccion Dirección del cliente
     * @param string $clienteDireccionComplemento Complemento de la dirección del cliente
     * @param string $clienteCodigoPostal Código postal de la dirección del cliente
     * @param string $clienteTelefono Número de teléfono de cliente
     * @param string $clienteCorreo Correo electrónico del cliente
     * @param string $personaDeReferencia Persona que fue referencia del cliente
     * @param int $esClientePrevio Define si el cliente es nuevo o si ya tiene transacciones
     * @param string $fechaDeRetiro Fecha de retiro de los ítems facturados
     * @param int $agregarInstalacion Define si se va a agregar instalación de los ítems facturados
     * @param int $agregarAccesorios Define si se agregan accesorios de los ítems facturados
     * @param int $noCalcularImpuesto Define si en la factura no se calcula el impuesto
     * @param float $totalAntesDeImpuesto Total de la factura antes de sumar impuesto
     * @param float $impuestoPorcentaje Porcentaje de impuesto aplicado a la factura
     * @param float $impuesto Total de impuesto aplicado a la factura
     * @param float $totalConImpuesto Total antes de impuesto + impuesto
     * @param float $impuestoFinanciera Impuesto calculado de pagos por medio de financieras
     * @param float $totalFinal total final de la factura
     * @param string $notas Notas u observaciones que se le agregan a la factura
     * @param string $estado Estado con el que se crea la factura
     * @param int $usuarioId Usuario que está registrando la factura
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(
        int $sucursalId, int $clienteId, int $usuarioIdVendedor, int $plataformaDeReferenciaId, string $formaDeRetiroId, string $fecha,
        string $prefijoDeCorrelativo, int $correlativo, string $sucursalNombre, string $sucursalDireccion, string $sucursalDireccionComplemento,
        string $sucursalCodigoPostal, string $sucursalTelefono, string $sucursalTelefonoServicio, string $clienteNombre, string $clienteDireccion, string $clienteDireccionComplemento,
        string $clienteCodigoPostal, string $clienteTelefono, string $clienteCorreo, string $personaDeReferencia, int $esClientePrevio,
        string $fechaDeRetiro, int $agregarInstalacion, int $agregarAccesorios, int $noCalcularImpuesto, float $totalAntesDeImpuesto, float $impuestoPorcentaje,
        float $impuesto, float $totalConImpuesto, float $impuestoFinanciera, float $totalFinal, string $notas, string $estado, int $usuarioId
    ): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO
                FACFACTURAS
                (SUCURSALID, CLIENTEID, USUARIOIDVENDEDOR, PLATAFORMADEREFERENCIAID, FORMADERETIROID, FECHA,
                PREFIJODECORRELATIVO, CORRELATIVO, SUCURSALNOMBRE, SUCURSALDIRECCION, SUCURSALDIRECCIONCOMPLEMENTO,
                SUCURSALCODIGOPOSTAL, SUCURSALTELEFONO, SUCURSALTELEFONOSERVICIO, CLIENTENOMBRE, CLIENTEDIRECCION, CLIENTEDIRECCIONCOMPLEMENTO,
                CLIENTECODIGOPOSTAL, CLIENTETELEFONO, CLIENTECORREOELECTRONICO, PERSONADEREFERENCIA, ESCLIENTEPREVIO,
                FECHADERETIRO, AGREGARINSTALACION, AGREGARACCESORIOS, NOCALCULARIMPUESTO, TOTALANTESDEIMPUESTO, IMPUESTOPORCENTAJE,
                IMPUESTO, TOTALCONIMPUESTO, IMPUESTOFINANCIERA, TOTALFINAL, NOTAS, ESTADO, FECHACREACION, FECHAMODIFICACION, USUARIOIDCREACION, USUARIOIDMODIFICACION)
            VALUES
                (?, ?, ?, ?, ?, ?,
                 ?, ?, ?, ?, ?,
                 ?, ?, ?, ?, ?, ?,
                 ?, ?, ?, ?, ?,
                 ?, ?, ?, ?, ?, ?,
                 ?, ?, ?, ?, ?, ?, GETDATE(), GETDATE(), ?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $sucursalId, $clienteId, $usuarioIdVendedor, $plataformaDeReferenciaId, $formaDeRetiroId, $fecha,
                                                $prefijoDeCorrelativo, $correlativo, $sucursalNombre, $sucursalDireccion, $sucursalDireccionComplemento,
                                                $sucursalCodigoPostal, $sucursalTelefono, $sucursalTelefonoServicio, $clienteNombre, $clienteDireccion, $clienteDireccionComplemento,
                                                $clienteCodigoPostal, $clienteTelefono, $clienteCorreo, $personaDeReferencia, $esClientePrevio,
                                                $fechaDeRetiro, $agregarInstalacion, $agregarAccesorios, $noCalcularImpuesto, $totalAntesDeImpuesto, $impuestoPorcentaje,
                                                $impuesto, $totalConImpuesto, $impuestoFinanciera, $totalFinal, $notas, $estado, $usuarioId, $usuarioId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->facturaId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Edita un registro (FACFACTURAS) existente
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
            UPDATE FACFACTURAS SET " . $updates . " WHERE FACTURAID = ?
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
     * Eliminar un registro (FACFACTURAS)
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
            EXECUTE SPFACELIMINARFACTURA ?
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
        $this->facturaId = -1;
        $this->sucursalId = null;
        $this->clienteId = null;
        $this->usuarioIdVendedor = null;
        $this->usuarioVendedor = null;
        $this->usuarioVendedorNombre = null;
        $this->plataformaDeReferenciaId = null;
        $this->plataformaDeReferencia = null;
        $this->formaDeRetiroId = null;
        $this->formaDeRetiro = null;
        $this->fecha = null;
        $this->fechadt = null;
        $this->prefijoDeCorrelativo = null;
        $this->correlativo = null;
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
        $this->totalAntesDeImpuesto = null;
        $this->impuestoPorcentaje = null;
        $this->impuesto = null;
        $this->totalConImpuesto = null;
        $this->impuestoFinanciera = null;
        $this->totalFinal = null;
        $this->notas = null;
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
     * Obtener la lista de estados que puede tomar una factura para mostrar en combo
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
            UNION
            
            SELECT
                5 AS ORDEN
                ,'DEV' AS ESTADO
                ,'RETURNED' AS NOMBRE   
                
            ORDER BY
                ORDEN ASC
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Cambiar de estado una factura: CER, FOR, PRO, ANU
     * 
     * @param int $facturaId Factura que va a cambiar de estado
     * @param int $usuarioId Usuario que realiza el cambio de estado
     * @param string $estado Estado al que va a cambiar la factura
     * @param string $descripcion Descripción personalizada para el cambio de estado
     * 
     * @return bool Estado final del cambio de estado: true: se cambió el estado, false: no fue cambiado
     * 
     */
    public function cambiarEstado(int $facturaId, int $usuarioId, string $estado, string $descripcion = ""): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            EXECUTE SPFACCAMBIARESTADOFACTURA ?, ?, ?, ?
        ";
        $resultado = $this->conn->execute($sentenciaSql, [$facturaId, $estado, $usuarioId, $descripcion]);
        
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
     * Obtener la lista de formas de retiro de los ítems facturados
     * (incluye fila de "SELECT")
     * 
     * @param string @primeraOpcion Primer opción a mostrar en el combo, por defecto es "SELECT"
     * 
     * @return array Lista de formas de retiro, con el primer elemento "SELECT"  o personalizado
     * 
     */
    public function getFormasDeRetiroParaCombo(string $primeraOpcion = "SELECT"): array
    {
        $sentenciaSql = "
            SELECT
                '' AS FORMADERETIROID,
                '- $primeraOpcion -' AS NOMBRE

            UNION

            SELECT
                FORMADERETIROID,
                NOMBRE
            FROM
                FACFORMASDERETIRO

            ORDER BY
                FORMADERETIROID
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Obtener el número de ítems que tiene la factura: detalles y otrosdetalles
     * 
     * @param int @facturaId Factura a la que se le contarán los ítems
     * 
     * @return int Cantidad de ítems que tiene la factura
     * 
     */
    public function getTotalDeItems(int $facturaId): int
    {
        $sentenciaSql = "
            SELECT
                COUNT(*) AS CONTEO
            FROM
                FACFACTURASDETALLE
            WHERE
                FACTURAID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        $conteoDetalle = $datos[0]["CONTEO"];

        $sentenciaSql = "
            SELECT
                COUNT(*) AS CONTEO
            FROM
                FACFACTURASOTROSDETALLES
            WHERE
                FACTURAID = ?
        ";
        $datos = $this->conn->select($sentenciaSql, [$facturaId]);

        $conteoOtroDetalle = $datos[0]["CONTEO"];
        
        return ($conteoDetalle + $conteoOtroDetalle);
    }
    
    //-------------------------------------------
}