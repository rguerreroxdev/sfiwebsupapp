
</div><!-- container -->
    
    <script src="./libs/jquery/jquery.min.js"></script>
    <script src="./libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./libs/bootstrap-table/bootstrap-table.min.js"></script>
    <!--script src="./libs/bootstrap-table/locale/bootstrap-table-es-ES.min.js"></!--script-->
    <!--script src="./libs/jQueryTableExport/tableExport.min.js"></script-->
    <!--script src="./libs/bootstrap-table-1.21.0-dist/extensions/export/bootstrap-table-export.min.js"></script-->
    <script src="./js/main.js"></script>
<?php

if ($_GET["mod"] != "login")
{
    echo '    <script src="js/opts.js"></script>';
}

if (isset($_GET["mod"])) {
    if (isset($_GET["opc"])) {
        if (isset($_GET["subopc"])) {
            $destino = "mods/" . $_GET["mod"] . "/" . $_GET["opc"] . "/js/" . $_GET["subopc"] . ".js";
        } else {
            $destino = "mods/" . $_GET["mod"] . "/" . $_GET["opc"] . "/js/" . $_GET["opc"] . ".js";
        }
    } else {
        $destino = "mods/" . $_GET["mod"] . "/js/" . $_GET["mod"] . ".js";
    }

    if (@file_exists($destino)) {
        echo '    <script src="' . $destino .'"></script>';
    }
}

?>
</body>
</html>