<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= HTML_TITULO ?></title>

    <link rel="stylesheet" href="./libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="./libs/bootstrap-table/bootstrap-table.min.css">
    <link rel="stylesheet" href="./libs/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="bg-body-tertiary">
<?php
//-----------------------------------------------

// Se utilizan las clases MenuDeSistema y Sesion
// que se obtienen de inc/includes.inc.php
// que se llama desde index.php

//-----------------------------------------------

$conn = new SQLSrvBD(DB_SERVER, DB_DATABASE, DB_USER, DB_PASSWORD);
$conn->conectar();

// Se crea un objeto de tipo MenuDeSistema, y se le envía el usuarioId logueado para obtener sus accesos según su perfil
// Primero se obtienen los módulos a los que tiene acceso y el módulo de inicio del usuario
$objMenu = new MenuDeSistema($conn, $_SESSION["usuarioId"]);
$modulos = $objMenu->getModulos();
$moduloDeUsuario = $objMenu->GetModuloDeInicioDeUsuario();

// Se crea un arreglo para insertar los módulos a los que tiene acceso, será ocupado posteriormente para validar el módulo
// que tenga seleccionado durante su sesión
$modulosConAcceso = array();
// Se crea la lista de opciones que tendrá el comboBox de módulos
// Acá se llena el arreglo $modulosConAcceso
$listaOptions = "";
foreach($modulos as $modulo)
{
    $texto = $modulo["NOMBRE"];
    $valor = $modulo["MODULOID"];

    $isSelected = "";
    if (isset($_SESSION["moduloId"]))
    {
        $isSelected = $_SESSION["moduloId"] == $valor ? " selected" : "";
    }
    else
    {
        $isSelected = $moduloDeUsuario == $valor ? " selected" : "";
    }

    $listaOptions .= "
        <option value=\"$valor\"$isSelected>$texto</option>
    ";

    array_push($modulosConAcceso, $valor);
}

// Si la lista de opciones de módulos está vacía, es porque no tiene accesos a ningún módulo
// Se crea una sola opción que indique que no tiene accesos (NM -> No módulos)
// Se agrega un ítem al arreglo que también está vacío para realizar una validación posterior
if ($listaOptions == "")
{
    $listaOptions .= "
        <option value=\"NM\">No access</option>
    ";

    array_push($modulosConAcceso, "NM");
}

// Se define el módulo seleccionado, obteniéndolo de una variable de sesión si es que existe
// Si no existe la variable de sesión, se toma el primer valor de la lista de módulos con acceso, que será la que se muestra en el comboBox
$moduloSeleccionado = $moduloDeUsuario != "" ? $moduloDeUsuario : $modulosConAcceso[0];
$moduloSeleccionado = isset($_SESSION["moduloId"]) ? $_SESSION["moduloId"] : $moduloSeleccionado;
// En caso que el módulo que estaba en variable de sesión ya no es accesible (por posible cambio en perfil del usuario),
// se define entonces que el módulo seleccionado es el que se encuentra en la primer posición de mósulos con acceso, que se mostrará en el comboBox
$moduloSeleccionado = in_array($moduloSeleccionado, $modulosConAcceso) ? $moduloSeleccionado : $modulosConAcceso[0];

//-----------------------------------------------

$menu = $objMenu->getMenuDeSistema($moduloSeleccionado, $_SESSION["usuarioId"]);

//-----------------------------------------------
?>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="?mod=inicio">
                <span class="bs-icon-sm bs-icon-rounded bs-icon-primary d-flex justify-content-center align-items-center me-2 bs-icon">
                    <img src="imgs/logomini.png" style="max-width: 40px;">
                </span>
                <span><?= APP_TITULO ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 small" id="menuPrincipal">
                    <!-- Menú -->
                    <?php echo $menu; ?>
                </ul>
                <div class="d-flex">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 small">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person"></i> <?= $_SESSION["usuario"] ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item small" href="?mod=admin&opc=cambiarcontrasena">Change password</a></li>
                                <li><a class="dropdown-item small" href="#" id="linkLogout">Logout</a></li>
                            </ul>
                        </li>
                    </ul>

                    <div>
                        <select name="selectModulos" id="selectModulos" class="form-select form-select-sm">
                            <!-- Opciones -->
                            <?php echo $listaOptions; ?>
                        </select>
                    </div>

                    <input type="hidden" name="uId" id="uId" value="<?= $_SESSION["usuarioId"] ?>">
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">