<?php
/**
 *      /index.php recibe dos parámetros:
 *      - mod: módulo que se va a cargar, se encuentra en: /mods/[mod]
 *        el nombre del archivo a cargar es igual a [mod]: /mods/[mod]/[mod].php
 *        también carga un .js con el mismo nombre en: /mods/[mod]/js/[mod].js
 *      - opc: es una opción dentro de un módulo, por ejemplo: módulo inventario, opción productos
 *        el nombre del archivo a cargar es: /mods/[mod]/[opc]/[opc].php
 *        y carga un .js con el mismo nombre: /mods/[mod]/[opc]/js/[opc].js
 *      - subopc: es opcional, y siempre debe ir después de un [mod]/[opc]/, es una subopción dentro de una opción
 *        el nombre del archivo a cargar es: /mods/[mod]/[opc]/[subopc].php
 *        y carga un .js con el mismo nombre: /mods/[mod]/[opc]/js/[subopc].js
 * 
 *      Si hay una sesión iniciada, y no se ha definido un [mod], redirige a inicio:
 *          /mods/inicio/inicio.php
 *      y carga su respectivo .js
 *          /mods/inicio/js/inicio.js
 * 
 *      Si no hay una sesión iniciada, se redirige a login:
 *          /mods/login/login.php
 *      y carga su respectivo .js
 *          /mods/login/js/login.js
 * 
 *      Los includes generales que necesita la aplicación están concentrados en:
 *          /inc/config.inc.php
 */

//-----------------------------------------------

session_start();

//-----------------------------------------------

// Un solo require que llame a las librerías php a utilizar de forma general
require_once("./inc/includes.inc.php");

//-----------------------------------------------

// Valida si existe sesión, en caso de no existir, redirige a login
Sesion::validarSesion();

//-----------------------------------------------

// Se ha definido entrar a un módulo
if (isset($_GET["mod"]))
{
    // Si se está tratando de entrar a pantalla de inicio de sesión, pero
    // ya existe sesión: enviar a inicio
    if ($_GET["mod"] == "login" && Sesion::existeSesion())
    {
        header("Location: ?mod=inicio");
    }

    // Variable que guarda la ruta del encabezado a cargar
    $requireEncabezado = "";

    // Carga encabezado HTML dependiendo de si está en login o dentro de la aplicación
    if ($_GET["mod"] != "login")
    {
        $requireEncabezado = "inc/encabezado.inc.php";
    }
    else
    {
        $requireEncabezado = "inc/encabezadologin.inc.php";
    }

    // Si existe una opción con subopción dentro del módulo, cargarla
    // Si solo existe una opción, cargarla
    // De lo contrario cargar solo el módulo
    if (isset($_GET["opc"]) && isset($_GET["subopc"])) {
        $destino = "mods/" . $_GET["mod"] . "/" . $_GET["opc"] . "/" . $_GET["subopc"] . ".php";
    }
    else if (isset($_GET["opc"]))
    {
        $destino = "mods/" . $_GET["mod"] . "/" . $_GET["opc"] . "/" . $_GET["opc"] . ".php";
    }
    else
    {
        $destino = "mods/" . $_GET["mod"] . "/" . $_GET["mod"] . ".php";
    }

    // Verificar si el destino existe
    if (!file_exists($destino))
    {
        $destino = "mods/error404/error404.php";
    }

    // Se carga finalmente el encabezado correspondiente y el destino
    require_once($requireEncabezado);
    require_once($destino);
}
else
{
    // No hay un módulo definido, entonces verificar si hay sesión para
    // dirigir a inicio, y, si no hay sesión, enviar a pantalla de login
    if (Sesion::existeSesion())
    {
        header("Location: ?mod=inicio");
    }
    else
    {
        header("Location: ?mod=login");
    }
}

//-----------------------------------------------

// Pié de HTML
require_once("inc/pie.inc.php");

//-----------------------------------------------