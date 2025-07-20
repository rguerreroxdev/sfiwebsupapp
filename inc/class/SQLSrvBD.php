<?php

class SQLSrvBD
{
    //-------------------------------------------

    private $serverName;
    private $dataBase;
    private $user;
    private $password;

    private $conn;

    private bool $existeError;
    private string $mensajeError;

    //-------------------------------------------

    /**
     * Instancia un objeto SQLSrvBD
     * 
     * @param string $serverName Nombre o IP de servidor
     * @param string $dataBase Nombre de base de datos a usar
     * @param string $user Usuario con acceso a base de datos seleccionada
     * @param string $password Contraseña de usuario
     * 
     */
    public function __construct(string $serverName, string $dataBase, string $user, string $password)
    {
        $this->serverName = $serverName;
        $this->dataBase = $dataBase;
        $this->user = $user;
        $this->password = $password;

        $this->defineError(false, "");
    }

    //-------------------------------------------

    /**
     * Realiza conexión a base de datos, y la guarda en una propiedad del objeto
     * 
     * @param void No necesita parámetros
     * 
     * @return bool Estado final de conexión: true: se realizó conexión, false: no se realizó conexión
     * 
     */
    public function conectar(): bool
    {
        $returnValue = false;

        $connectionInfo = array(
            "Database" => $this->dataBase,
            "UID"      => $this->user,
            "PWD"      => $this->password,
            "CharacterSet"           => "UTF-8",
            "TrustServerCertificate" => "yes"
        );
        $this->conn = sqlsrv_connect($this->serverName, $connectionInfo);

        if($this->conn)
        {
            $this->defineError(false, "");
            $returnValue = true;
        }
        else
        {
            $errorMsg = $this->leerErrores();
            $this->defineError(true, $errorMsg);
        }

        return $returnValue;
    }

    //-------------------------------------------

    /**
     * Para crear sentencias SELECT y obtener array con datos
     * 
     * Ejemplo de uso de parámetros:
     * - $sentenciaSQL    = "SELECT * FROM TABLA WHERE CAMPO1=? AND CAMPO2=?"
     * - $arrayParametros = ["texto", 35]
     * 
     * @param string $sentenciaSQL Sentencia SELECT a ejecutar, los parámetros se definen con signo ?
     * @param array $arrayParametros Lista de parámetros que se incluyen en la sentencia (los signos ?)
     * 
     * @return array Datos obtenidos por la consulta
     * 
     */
    public function select(string $sentenciaSQL, array $arrayParametros = array()): array
    {
        if(!$this->sqlIniciaCon($sentenciaSQL, "SELECT"))
            return [];

        $this->defineError(false, "");

        $stmt = sqlsrv_query($this->conn, $sentenciaSQL, $arrayParametros);
        
        if(!$stmt)
        {
            $errorMsg = $this->leerErrores();
            $this->defineError(true, $errorMsg);

            return [];
        }

        $resultado = array();
        while($row = sqlsrv_fetch_array($stmt))
        {
            array_push($resultado, $row);
        }

        return $resultado;
    }

    //-------------------------------------------

    /**
     * Para crear sentencias WITHJ - SELECT y obtener array con datos
     * 
     * Ejemplo de uso de parámetros:
     * - $sentenciaSQL    = "WITHD DATOS (CAMPO1, CAMPO2) AS (SELECT * FROM TABLA WHERE CAMPO1=? AND CAMPO2=?)"
     * - $arrayParametros = ["texto", 35]
     * 
     * @param string $sentenciaSQL Sentencia WITH - SELECT a ejecutar, los parámetros se definen con signo ?
     * @param array $arrayParametros Lista de parámetros que se incluyen en la sentencia (los signos ?)
     * 
     * @return array Datos obtenidos por la consulta
     * 
     */
    public function with(string $sentenciaSQL, array $arrayParametros = array()): array
    {
        if(!$this->sqlIniciaCon($sentenciaSQL, "WITH"))
            return [];

        $this->defineError(false, "");

        $stmt = sqlsrv_query($this->conn, $sentenciaSQL, $arrayParametros);
        
        if(!$stmt)
        {
            $errorMsg = $this->leerErrores();
            $this->defineError(true, $errorMsg);

            return [];
        }

        $resultado = array();
        while($row = sqlsrv_fetch_array($stmt))
        {
            array_push($resultado, $row);
        }

        return $resultado;
    }

    //-------------------------------------------

    /**
     * Para crear sentencias INSERT
     * 
     * Ejemplo de uso de parámetros:
     * - $sentenciaSQL    = "INSERT INTO TABLA (CAMPO1, CAMPO2) VALUES (?, ?)"
     * - $arrayParametros = ["texto", 35]
     * 
     * @param string $sentenciaSQL Sentencia INSERT a ejecutar, los parámetros se definen con signo ?
     * @param array $arrayParametros Lista de parámetros que se incluyen en la sentencia (los signos ?)
     * @param bool $retornaId Para definir si se retorna o no el ID (autoincremental) al insertar registro
     * 
     * @return array Vacío: no se insertó o no se solicitó ID, Con datos: El ID del nuevo registro (arreglo[0]["ID"])
     * 
     */
    public function insert(string $sentenciaSQL, array $arrayParametros = array(), bool $retornaId = false): array
    {
        if(!$this->sqlIniciaCon($sentenciaSQL, "INSERT"))
            return [];

        $resultado = array();

        $this->defineError(false, "");

        $stmt = sqlsrv_query($this->conn, $sentenciaSQL, $arrayParametros);
        
        if(!$stmt)
        {
            $errorMsg = $this->leerErrores();
            $this->defineError(true, $errorMsg);
        }
        else if ($retornaId)
        {
            $resultado = $this->select("SELECT @@IDENTITY AS ID");
        }

        return $resultado;
    }

    //-------------------------------------------

    /**
     * Para crear sentencias UPDATE
     * 
     * Ejemplo de uso de parámetros:
     * - $sentenciaSQL    = "UPDATE TABLA SET CAMPO1=? WHERE ID=?"
     * - $arrayParametros = ["texto", 35]
     * 
     * @param string $sentenciaSQL Sentencia UPDATE a ejecutar, los parámetros se definen con signo ?
     * @param array $arrayParametros Lista de parámetros que se incluyen en la sentencia (los signos ?)
     * 
     * @return bool Resultado de actualización de dato: true: se actualizó, false: no se actualizó
     * 
     */
    public function update(string $sentenciaSQL, array $arrayParametros = array()): bool
    {
        if(!$this->sqlIniciaCon($sentenciaSQL, "UPDATE"))
            return false;

        $this->updateDeleteExecute($sentenciaSQL, $arrayParametros);
        return !$this->getExisteError();
    }

    //-------------------------------------------

    /**
     * Para crear sentencias EXECUTE (Procedimientos almacenados)
     * 
     * Ejemplo de uso de parámetros:
     * - $sentenciaSQL    = "EXECUTE PROCEDIMIENTO ?, ?"
     * - $arrayParametros = ["texto", 35]
     * 
     * @param string $sentenciaSQL Sentencia EXECUTE a ejecutar, los parámetros se definen con signo ?
     * @param array $arrayParametros Lista de parámetros que se incluyen en la sentencia (los signos ?)
     * 
     * @return array Conjunto de datos que se obtiene con la sentencia
     * 
     */
    public function execute(string $sentenciaSQL, array $arrayParametros = array()): array
    {
        if(!$this->sqlIniciaCon($sentenciaSQL, "EXEC"))
            return false;

        return $this->updateDeleteExecute($sentenciaSQL, $arrayParametros);
    }
    
    //-------------------------------------------

    /**
     * Para crear sentencias DELETE
     * 
     * Ejemplo de uso de parámetros:
     * - $sentenciaSQL    = "DELETE FROM TABLA WHERE ID=?"
     * - $arrayParametros = [35]
     * 
     * @param string $sentenciaSQL Sentencia DELETE a ejecutar, los parámetros se definen con signo ?
     * @param array $arrayParametros Lista de parámetros que se incluyen en la sentencia (los signos ?)
     * 
     * @return bool Resultado de eliminación de registro: true: se eliminó, false: no se eliminó
     * 
     */
    public function delete(string $sentenciaSQL, array $arrayParametros = array()): bool
    {
        if(!$this->sqlIniciaCon($sentenciaSQL, "DELETE"))
            return false;

        $this->updateDeleteExecute($sentenciaSQL, $arrayParametros);
        return !$this->getExisteError();
    }

    //-------------------------------------------

    /**
     * Para crear sentencias UPDATE, DELETE y EXECUTE
     * 
     * Ejemplo de uso de parámetros:
     * - $sentenciaSQL    = "UPDATE TABLA SET CAMPO1=? WHERE ID=?"
     * - $arrayParametros = ["texto", 35]
     * 
     * @param string $sentenciaSQL Sentencia UPDATE, DELETE o EXECUTE a ejecutar, los parámetros se definen con signo ?
     * @param array $arrayParametros Lista de parámetros que se incluyen en la sentencia (los signos ?)
     * 
     * @return array Conjunto de datos resultado que retorna la sentencia
     * 
     */
    private function updateDeleteExecute(string $sentenciaSQL, array $arrayParametros = array()): array
    {
        $resultado = array();

        $this->defineError(false, "");

        $stmt = sqlsrv_query($this->conn, $sentenciaSQL, $arrayParametros);
        
        if(!$stmt)
        {
            $errorMsg = $this->leerErrores();
            $this->defineError(true, $errorMsg);
        }
        else
        {
            while($row = sqlsrv_fetch_array($stmt))
            {
                array_push($resultado, $row);
            }
        }

        return $resultado;
    }

    //-------------------------------------------

    /**
     * Para definir si existe o no un error generado por una acción en la base de datos
     * 
     * @param bool $existeError True: Existe error, False: No existe error (valor de propiedad de objeto $existeError)
     * @param string $mensajeError Mensaje de descripción de error (valor de propiedad de objeto $mensajeError)
     * 
     * @return void
     * 
     */
    // Para definir si existe un error generado por una acción en la base de datos y su mensaje
    private function defineError(bool $existeError, string $mensajeError): void
    {
        $this->existeError = $existeError;
        $this->mensajeError = $mensajeError;
    }

    //-------------------------------------------

    /**
     * Retorna valor de propiedad $existeError, que describe si ha ocurrido un error en acción en base de datos
     * 
     * @param void
     * 
     * @return bool Valor de propiedad $existeError: True: ha ocurrido un error, False: no ha ocurrido error
     * 
     */
    public function getExisteError(): bool
    {
        return $this->existeError;
    }


    /**
     * Retorna valor de propiedad $mensajeError, que describe un error ocurrido en acción en base de datos
     * 
     * @param void
     * 
     * @return string Valor de propiedad $mensajeError, descripción del error ocurrido en acción en base de datos
     * 
     */
    public function getMensajeError(): string
    {
        return $this->mensajeError;
    }

    //-------------------------------------------

    /**
     * Obiene los errores detectados en última ejecución en base de datos
     * 
     * @param void
     * 
     * @return string Cadena de texto con los errores detectados en última ejecución en base de datos
     * 
     */
    // Obtener mensajes de error de la última ejecución en base de datos
    private function leerErrores(): string
    {
        $errorMsg = "";

        if(($errors = sqlsrv_errors()) != null) {
            $salirDeForEach = false;
            foreach($errors as $error) {

                switch ($error['code']) {
                    case 2627:
                        //$errorMsg = "Error code " . $error['code'] . "<br />";
                        $errorMsg .= "An attempt was made to save a duplicate data.<br>";
                        $salirDeForEach = true;
                        break;
                    
                    case 547:
                        //$errorMsg = "Código de error " . $error['code'] . "<br />";
                        $errorMsg .= "An attempt was made to delete a record that is associated with other data.<br>";
                        $salirDeForEach = true;
                        break;

                    default:
                        //$errorMsg .= "SQLSTATE: " . $error['SQLSTATE'] . "<br />";
                        $errorMsg .= "code: " .     $error['code'] .     "<br />";
                        $errorMsg .= "message: " .  $error['message'] .  "<br />";
                        break;
                }

                if ($salirDeForEach)
                {
                    break;
                }
            }
        }

        return $errorMsg;
    }

    //-------------------------------------------

    /**
     * Valida que una cadena inicie con una palabra en específico
     * 
     * La función realizá una búsqueda de una subcadena en una cadena específica,
     * la cadena se le aplica TRIM para eliminar espacios, tabulaciones o saltos de línea
     * y poder buscar la subcadena en la posición [0]
     * 
     * @param string $strCadena Cadena en la que va a ser buscado un texto
     * @param string $strSubCadenaABuscar SubCadena que será buscada dentro de $strCadena
     * 
     * @return bool True: la cadena inicia con la subcadena, False: la cadena no inicia con la sucbadena
     * 
     */
    private function sqlIniciaCon(string $strCadena, string $strSubCadenaABuscar): bool
    {
        $this->defineError(false, "");

        $rs = strpos(strtoupper(trim($strCadena)), strtoupper($strSubCadenaABuscar));
        if (!(is_numeric($rs) && $rs == 0))
            $this->defineError(true, "La sentencia no inicia con $strSubCadenaABuscar");
        
        return !$this->getExisteError();
    }

    //-------------------------------------------
}