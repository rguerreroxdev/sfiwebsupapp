<?php
    // $conn -> ya fue definido en encabezado.inc.php

    // Validar si necesita cambiar contraseña
    require_once("./inc/class/Usuario.php");
    $objUsuario = new Usuario($conn);

    if ($objUsuario->debeCambiarContrasena($_SESSION["usuarioId"]))
    {
        echo ("<script>window.location.href='?mod=admin&opc=cambiarcontrasena'</script>");
        exit();
    }

    // Crear objeto para obtener indicadores
    require_once("./inc/class/Indicadores.php");
    $objIndicadores = New Indicadores($conn);

    // Obtener conteos de recepciones de carga
    $conteoRecepciones = $objIndicadores->conteoDeRecepcionesDeCarga();

    // Obtener accesos para mostrar datos
    $usuarioAccesos = new Accesos($conn, $_SESSION["usuarioId"]);
    $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("02.02.01");
    $accesoRecepciones = in_array("02.02.01", $accesos) ? true : false;
    $accesos = $usuarioAccesos->getListaDeOpcionesConAcceso("02.02.02");
    $accesoTraslados = in_array("02.02.02", $accesos) ? true : false;

    // Obtener vínculos a accesos directos
    require_once("./inc/class/MenuDeSistema.php");
    $objMenu = New MenuDeSistema($conn, $_SESSION["usuarioId"]);
    $linkRecepcionesDeCarga = $objMenu->getVinculoDeOpcionDeMenu(15);   // 15 -> MenuId de recepciones de carga
    $linkTraslados = $objMenu->getVinculoDeOpcionDeMenu(16);            // 16 -> MenuId de traslados
    $linkConsultaEnTiendas = $objMenu->getVinculoDeOpcionDeMenu(20);    // 20 -> MenuId de consulta de existencias en tiendas
?>

<h3>Home</h3>

<div class="row">
    <?php if ($accesoRecepciones): ?>
    <div class="col-sm-2 d-flex">
        <div class="card shadow-sm flex-fill">
            <div class="card-header small">
                Bills of lading summary
            </div>
            <div class="card-body">
                <div class="indicadorBordeIzquierda border-start border-danger border-4">
                    <div class="border-start">
                        <h5 class="card-title fw-bold m-0"><?= $conteoRecepciones[0]["CONTEO"] ?></h5>
                    </div>
                    <div>
                        <p class="card-text text-danger small m-0">Formulation</p>
                    </div>
                </div>
                <div class="indicadorBordeIzquierda border-start border-success border-4">
                    <div>
                        <h5 class="card-title fw-bold m-0"><?= $conteoRecepciones[1]["CONTEO"] ?></h5>
                    </div>
                    <div>
                        <p class="card-text small text-success m-0">Closed</p>
                    </div>
                </div>
                <div class="indicadorBordeIzquierda border-start border-primary border-4">
                    <div>
                        <h5 class="card-title fw-bold m-0"><?= $conteoRecepciones[2]["CONTEO"] ?></h5>
                    </div>
                    <div>
                        <p class="card-text small text-primary m-0">Posted</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col d-flex">
        <div class="card shadow-sm flex-fill">
            <div class="card-header small">
                Shortcuts
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <div class="link-shortcut text-center">
                    <a href="<?= $linkRecepcionesDeCarga ?>" class="link-underline link-underline-opacity-0 link-dark">
                        <i class="bi bi-file-earmark-ruled fs-4 d-block"></i>
                        <span class="d-block small">Bills of lading</span>
                    </a>
                </div>
                <div class="link-shortcut text-center">
                    <a href="<?= $linkTraslados ?>" class="link-underline link-underline-opacity-0 link-dark">
                        <i class="bi bi-truck fs-4 d-block"></i>
                        <span class="d-block small">Inventory transfers</span>
                    </a>
                </div>
                <div class="link-shortcut text-center">
                    <a href="<?= $linkConsultaEnTiendas ?>" class="link-underline link-underline-opacity-0 link-dark">
                        <i class="bi bi-card-list fs-4 d-block"></i>
                        <span class="d-block small">Stock in stores</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($accesoTraslados): ?>
    <div class="col-sm-4 d-flex">
        <div class="card shadow-sm flex-fill">
            <div class="card-header small">
                Upcoming transfers
            </div>
            <div class="card-body">

                <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/inicio/procs/gettrasladosproximos.php"
                    data-icon-size="sm"
                    data-card-view="true"
                    class="table-sm small"
                    data-height="150"
                >
                    <thead>
                        <tr>
                            <th data-field="TRASLADOID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowIndexFormatter" data-visible="false">#</th>
                            <th data-field="FECHACREACIONVARCHAR">Creation date</th>
                            <th data-field="SUCURSALORIGEN">Store from</th>
                            <th data-field="SUCURSALDESTINO">Store to</th>
                            <th data-field="CORRELATIVO">Correlative</th>
                            <th data-field="ESTADO">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="mt-2"></div>
    <?php if ($accesoRecepciones): ?>
    <div class="col-sm-4 d-flex">
        <div class="card shadow-sm flex-fill">
            <div class="card-header small">
                <i class="bi bi-file-earmark-ruled fs-4"></i> Upcoming bills of lading
            </div>
            <div class="card-body">

                <table
                    id="tabledatos"
                    data-toggle="table"
                    data-url="./mods/inicio/procs/getrecepcionesproximas.php"
                    data-icon-size="sm"
                    data-card-view="true"
                    class="table-sm small"
                    data-height="150"
                >
                    <thead>
                        <tr>
                            <th data-field="RECEPCIONDECARGAID" data-visible="false">ID</th>
                            <th data-field="Index" data-formatter="rowIndexFormatter" data-visible="false">#</th>
                            <th data-field="FECHADERECEPCION">Reception date</th>
                            <th data-field="SUCURSAL">Store</th>
                            <th data-field="CORRELATIVO">Correlative</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Registros -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
<div class="row">

</div>



<!--div class="d-flex justify-content-center align-items-center" style="height: 70vh;">
    <div>
        <div class="p-3 bg-body rounded shadow-sm">
            <img src="imgs/logo.png" alt="Supreme Appliances LLC">
        </div>
        <div class="text-center fs-3 fw-bold">
            Inventory management
        </div>
    </div>
</div-->
