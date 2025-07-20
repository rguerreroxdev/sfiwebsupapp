<?php

require_once("SQLSrvBD.php");

class SucursalesXUsuario
{
    //-------------------------------------------

    private $conn;

    public $sucursalXUsuarioId;
    public $sucursalId;
    public $usuarioId;

    public $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto SucursalesXUsuario
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
        $this->sucursalXUsuarioId = -1;
        $this->sucursalId = null;
        $this->usuarioId = null;
    }

    //-------------------------------------------

    /**
     * Agregar un nuevo registro (ACCSUCURSALESXUSUARIO)
     * 
     * @param int $usuarioId Usuario al que se le está asignando una sucursal
     * @param int $sucursalId Sucursal que está siendo asignada a un usuario
     * 
     * @return bool Resultado de guardar registro: true: fue guardado, false: no fue guardado
     * 
     */
    public function agregarRegistro(int $usuarioId, int $sucursalId): bool
    {
        $this->resetPropiedades();

        $sentenciaSql = "
            INSERT INTO ACCSUCURSALESXUSUARIO
                (USUARIOID, SUCURSALID)
            VALUES
                (?, ?)
        ";
        $datoResultado = $this->conn->insert($sentenciaSql,
                                            [
                                                $usuarioId, $sucursalId
                                            ],
                                            true);

        $agregado = !$this->conn->getExisteError();

        if ($agregado)
        {
            $this->sucursalXUsuarioId = $datoResultado[0]["ID"];
        }
        else
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $agregado;
    }

    //-------------------------------------------

    /**
     * Eliminar un registro (ACCSUCURSALESXUSUARIO)
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
            DELETE FROM ACCSUCURSALESXUSUARIO WHERE SUCURSALXUSUARIOID = ?
        ";
        $eliminado = $this->conn->delete($sentenciaSql, [$id]);
        
        if (!$eliminado)
        {
            $this->mensajeError = $this->conn->getMensajeError();
        }

        return $eliminado;
    }

    //-------------------------------------------
}