<?php
//-----------------------------------------------

class Sesion
{
    //-------------------------------------------

    /**
     * Valida que exista una sesión activa en la aplicación para cargar el módulo
     * que se está intentando abrir, de lo contrario envía a login
     * 
     * @param void
     * @return void
     * 
     */
    public static function validarSesion()
    {
        if (Sesion::existeSesion()) {
            // PASS
        } else if (isset($_GET["mod"]) && $_GET["mod"] == "login") {
            // PASS
        } else {
            header("Location: ?mod=login");
        }
    }

    //-------------------------------------------

    /**
     * Verifica si existe una sesión activa en la aplicación
     * 
     * @param void
     * @return bool Resultado de verificar si existe una sesión activa en la aplicación
     * 
     */
    public static function existeSesion()
    {
        return (isset($_SESSION["sesion"]) && $_SESSION["sesion"] == true);
    }

    //-------------------------------------------

    /**
     * Define una variable de sesión con un valor definido
     * 
     * @param string $nombreVariable Nombre de variable que será definida
     * @param $valor Valor que tomará la variable (puede ser de cualquier tipo)
     * 
     */
    public static function setVariableDeSesion(string $nombreVariable, $valor)
    {
        $_SESSION[$nombreVariable] = $valor;
    }

    //-------------------------------------------
}