<?php
//-----------------------------------------------

session_start();
session_unset();
session_destroy();

//-----------------------------------------------

$resultado = array();
$resultado["error"] = 0;

// Mostrar resultado de proceso
header('Content-type: application/json; charset=utf-8');
echo json_encode($resultado);
exit();

//-----------------------------------------------