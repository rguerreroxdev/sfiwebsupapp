<?php
//-----------------------------------------------

session_start();

//-----------------------------------------------

if (isset($_SESSION['sesion'])) {
    // Renovar la sesión extendiendo su duración
    session_regenerate_id(true);
}

//-----------------------------------------------