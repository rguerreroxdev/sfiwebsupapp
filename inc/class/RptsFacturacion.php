<?php

require_once("SQLSrvBD.php");

class RptsFacturacion
{
    //-------------------------------------------

    private $conn;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto RptsFacturacion
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
     * Ventas completas
     * 
     * @param string $fechaInicial Fecha de inicio de emisión de facturas (formato yyyymmdd)
     * @param string $fechaFinal Fecha final de emisión de facturas (formato yyyymmdd)
     * @param int $sucursalId Sucursal a ser filtrada (-1 para todas las sucursales que tiene acceso el usuario)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function ventasCompletas(string $fechaInicial, string $fechaFinal, int $sucursalId): array
    {
        $sentenciaSql = "
            EXECUTE SPFACREPVENTASCOMPLETAS ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$fechaInicial, $fechaFinal, $sucursalId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Ventas por vendedor (resumen)
     * 
     * @param string $fechaInicial Fecha de inicio de emisión de facturas (formato yyyymmdd)
     * @param string $fechaFinal Fecha final de emisión de facturas (formato yyyymmdd)
     * @param int $sucursalId Sucursal a ser filtrada (-1 para todas las sucursales que tiene acceso el usuario)
     * @param int $usuarioVendedorId Vendedor a ser filtardo (-1 para todos los vendedores)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function ventasPorVendedorResumen(string $fechaInicial, string $fechaFinal, int $sucursalId, int $usuarioVendedorId): array
    {
        $sentenciaSql = "
            EXECUTE SPFACREPVENTASPORVENDEDOR ?, ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$fechaInicial, $fechaFinal, $sucursalId, $usuarioVendedorId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Ventas por vendedor (detalle)
     * 
     * @param string $fechaInicial Fecha de inicio de emisión de facturas (formato yyyymmdd)
     * @param string $fechaFinal Fecha final de emisión de facturas (formato yyyymmdd)
     * @param int $sucursalId Sucursal a ser filtrada (-1 para todas las sucursales que tiene acceso el usuario)
     * @param int $usuarioVendedorId Vendedor a ser filtardo (-1 para todos los vendedores)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function ventasPorVendedorDetalle(string $fechaInicial, string $fechaFinal, int $sucursalId, int $usuarioVendedorId): array
    {
        $sentenciaSql = "
            EXECUTE SPFACREPVENTASPORVENDEDORDETALLE ?, ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$fechaInicial, $fechaFinal, $sucursalId, $usuarioVendedorId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Ventas agrupadas por producto
     * 
     * @param string $fechaInicial Fecha de inicio de emisión de facturas (formato yyyymmdd)
     * @param string $fechaFinal Fecha final de emisión de facturas (formato yyyymmdd)
     * @param int $sucursalId Sucursal a ser filtrada (-1 para todas las sucursales que tiene acceso el usuario)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function ventasAgrupadasPorProducto(string $fechaInicial, string $fechaFinal, int $sucursalId): array
    {
        $sentenciaSql = "
            EXECUTE SPFACREPVENTASPORPRODUCTO ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$fechaInicial, $fechaFinal, $sucursalId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Ventas agrupadas por plataforma
     * 
     * @param string $fechaInicial Fecha de inicio de emisión de facturas (formato yyyymmdd)
     * @param string $fechaFinal Fecha final de emisión de facturas (formato yyyymmdd)
     * @param int $sucursalId Sucursal a ser filtrada (-1 para todas las sucursales que tiene acceso el usuario)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function ventasAgrupadasPorPlataforma(string $fechaInicial, string $fechaFinal, int $sucursalId): array
    {
        $sentenciaSql = "
            EXECUTE SPFACREPVENTASPORPLATAFORMA ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$fechaInicial, $fechaFinal, $sucursalId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Ventas agrupadas por tipo de pago
     * 
     * @param string $fechaInicial Fecha de inicio de emisión de facturas (formato yyyymmdd)
     * @param string $fechaFinal Fecha final de emisión de facturas (formato yyyymmdd)
     * @param int $sucursalId Sucursal a ser filtrada (-1 para todas las sucursales que tiene acceso el usuario)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function ventasAgrupadasPorTipoDePago(string $fechaInicial, string $fechaFinal, int $sucursalId): array
    {
        $sentenciaSql = "
            EXECUTE SPFACREPVENTASPORTIPODEPAGO ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$fechaInicial, $fechaFinal, $sucursalId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Ganancias sobre ventas
     * 
     * @param string $fechaInicial Fecha de inicio de emisión de facturas (formato yyyymmdd)
     * @param string $fechaFinal Fecha final de emisión de facturas (formato yyyymmdd)
     * @param int $sucursalId Sucursal a ser filtrada (-1 para todas las sucursales que tiene acceso el usuario)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function gananciasSobreVentas(string $fechaInicial, string $fechaFinal, int $sucursalId): array
    {
        $sentenciaSql = "
            EXECUTE SPFACREPGANANCIASOBREVENTAS ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$fechaInicial, $fechaFinal, $sucursalId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Ganancias sobre ventas (Con detalle por producto)
     * 
     * @param string $fechaInicial Fecha de inicio de emisión de facturas (formato yyyymmdd)
     * @param string $fechaFinal Fecha final de emisión de facturas (formato yyyymmdd)
     * @param int $sucursalId Sucursal a ser filtrada (-1 para todas las sucursales que tiene acceso el usuario)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function gananciasSobreVentasDetalle(string $fechaInicial, string $fechaFinal, int $sucursalId): array
    {
        $sentenciaSql = "
            EXECUTE SPFACREPGANANCIASOBREVENTASDETALLE ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$fechaInicial, $fechaFinal, $sucursalId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Resumen de impuestos por declarar
     * 
     * @param string $fechaInicial Fecha de inicio de emisión de facturas (formato yyyymmdd)
     * @param string $fechaFinal Fecha final de emisión de facturas (formato yyyymmdd)
     * @param int $sucursalId Sucursal a ser filtrada (-1 para todas las sucursales que tiene acceso el usuario)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function resumenDeImpuestosPorDeclarar(string $fechaInicial, string $fechaFinal, int $sucursalId): array
    {
        $sentenciaSql = "
            EXECUTE SPFACREPRESUMENIMPUESTOSPORDECLARAR ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$fechaInicial, $fechaFinal, $sucursalId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Detalle de impuestos por declarar
     * 
     * @param string $fechaInicial Fecha de inicio de emisión de facturas (formato yyyymmdd)
     * @param string $fechaFinal Fecha final de emisión de facturas (formato yyyymmdd)
     * @param int $sucursalId Sucursal a ser filtrada (-1 para todas las sucursales que tiene acceso el usuario)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function detalleDeImpuestosPorDeclarar(string $fechaInicial, string $fechaFinal, int $sucursalId): array
    {
        $sentenciaSql = "
            EXECUTE SPFACREPDETALLEIMPUESTOSPORDECLARAR ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$fechaInicial, $fechaFinal, $sucursalId]);

        return $datos;
    }

    //-------------------------------------------

    /**
     * Ventas por proveedor (detalle)
     * 
     * @param string $fechaInicial Fecha de inicio de emisión de facturas (formato yyyymmdd)
     * @param string $fechaFinal Fecha final de emisión de facturas (formato yyyymmdd)
     * @param int $sucursalId Sucursal a ser filtrada (-1 para todas las sucursales que tiene acceso el usuario)
     * @param int $proveedorId Proveedor a ser filtardo (-1 para todos los proveedores)
     * 
     * @return array Todos los registros encontrados
     * 
     */
    public function ventasPorProveedorDetalle(string $fechaInicial, string $fechaFinal, int $sucursalId, int $proveedorId): array
    {
        $sentenciaSql = "
            EXECUTE SPFACREPVENTASPORPROVEEDORDETALLE ?, ?, ?, ?
        ";
        $datos = $this->conn->execute($sentenciaSql, [$fechaInicial, $fechaFinal, $sucursalId, $proveedorId]);

        return $datos;
    }

    //-------------------------------------------
}