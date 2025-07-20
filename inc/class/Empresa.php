<?php

require_once("SQLSrvBD.php");

class Empresa
{
    //-------------------------------------------

    private $conn;

    public $empresaId;
    public $nombre;
    public $direccion;
    public $telefono;
    
    public $fechaModificacion;
    public $usuarioIdModificacion;
    public $usuarioModifica;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto Empresa
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
     * Obtener datos de la empresa (CONFEMPRESA), solo hay una empresa en la base de datos
     * 
     * @param void
     * 
     * @return void No se retorna dato, pero se guardan los datos del registro en las propiedades del objeto
     * 
     */
    public function getDatos(): void
    {
        $sentenciaSql = "
            SELECT TOP 1
                E.EMPRESAID
                ,E.NOMBRE
                ,E.DIRECCION
                ,E.TELEFONO
                ,E.FECHAMODIFICACION
                ,E.USUARIOIDMODIFICACION
                ,UM.USUARIO AS USUARIOMODIFICA
            FROM
                CONFEMPRESAS E
                LEFT JOIN ACCUSUARIOS UM ON UM.USUARIOID=E.USUARIOIDMODIFICACION
            ORDER BY
                E.EMPRESAID
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        $this->resetPropiedades();
        foreach ($datos as $dato)
        {
            $this->empresaId = $dato["EMPRESAID"];
            $this->nombre = $dato["NOMBRE"];
            $this->direccion = $dato["DIRECCION"];
            $this->telefono = $dato["TELEFONO"];
            $this->fechaModificacion = $dato["FECHAMODIFICACION"];
            $this->usuarioIdModificacion = $dato["USUARIOIDMODIFICACION"];
            $this->usuarioModifica = $dato["USUARIOMODIFICA"];
        }
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
        $this->empresaId = -1;
        $this->nombre = null;
        $this->direccion = null;
        $this->telefono = null;
        $this->fechaModificacion = null;
        $this->usuarioIdModificacion = null;
        $this->usuarioModifica = null;
    }

    //-------------------------------------------

    /**
     * Edita los datos de la empresa (solo hay una empresa en la base de datos)
     * 
     * @param array $camposValores Array que contiene campos y valores a ser actualizados [campo, valor, campo, valor...]
     * 
     * @return bool Resultado de actualizar el registro: true: fue editado, false: no fue editado
     * 
     */
    public function editarRegistro(array $camposValores): bool
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

        array_push($valores);

        $sentenciaSql = "
            UPDATE CONFEMPRESAS SET " . $updates . " WHERE EMPRESAID = (SELECT TOP 1 EMPRESAID FROM CONFEMPRESAS ORDER BY EMPRESAID)
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
     * Obtener el ID de la empresa guardada en la base de datos
     * 
     * @param void
     * 
     * @return int ID de la empresa registrada en la base de datos
     * 
     */
    public function getEmpresaId(): int
    {
        $sentenciaSql = "
            SELECT TOP 1
                E.EMPRESAID
            FROM
                CONFEMPRESAS E
            ORDER BY
                E.EMPRESAID
        ";
        $datos = $this->conn->select($sentenciaSql, []);

        $empresaId = -1;

        foreach ($datos as $dato)
        {
            $empresaId = $dato["EMPRESAID"];
        }

        return $empresaId;
    }

    //-------------------------------------------
}