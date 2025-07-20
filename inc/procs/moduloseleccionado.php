<?php
//-----------------------------------------------

session_start();
require_once("../class/Sesion.php");

//-----------------------------------------------

$moduloId = isset($_POST["mId"]) ? $_POST["mId"] : "";
Sesion::setVariableDeSesion("moduloId", $moduloId);

//-----------------------------------------------

exit();

//-----------------------------------------------
