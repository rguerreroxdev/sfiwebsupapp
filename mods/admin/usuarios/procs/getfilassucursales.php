<?php
//-----------------------------------------------

function getFilasSucursales($conn, string $usuarioId): string
{
    require_once("inc/class/Usuario.php");
    $objUsuario = new Usuario($conn);

    $sucursales = $objUsuario->getSucursalesXUsuario($usuarioId);

    $filas = "";
    foreach ($sucursales as $sucursal)
    {
        $sucursalXUsuarioId = $sucursal["SUCURSALXUSUARIOID"];
        $sucursalId = $sucursal["SUCURSALID"];
        $nombreSucursal = $sucursal["SUCURSAL"];

        $filas .= "
            <tr>
                <td>
                    $nombreSucursal
                    <input type=\"hidden\" id=\"sucursalid[]\" name=\"sucursalid[]\" value=\"$sucursalId\">
                </td>
                <td>
                    <input type=\"hidden\" id=\"sucursalxusuarioid[]\" name=\"sucursalxusuarioid[]\" value=\"$sucursalXUsuarioId\">
                    <button class=\"btn btn-sm btn-outline-danger\" type=\"button\" onclick=\"eliminarFila(this)\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button>
                </td>
            </tr>
        ";
    }

    return $filas;
}

//-----------------------------------------------