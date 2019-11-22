<?php

include_once "../classes/html_inicio.php";
include_once "../db/db_config_mysql.php";
include_once "../classes/funcoes.php";

// if($_SESSION["cadastrar_ip"] == 0) {
//   echo '
//   <script language= "JavaScript">
//     alert("Sem Permissão de Acesso!");
//     location.href="../classes/redirecionador_pagina.php";
//   </script>
//   ';
// }

#$sqlShowIP = "select * from ips_valido";
#$executeShowIP = mysqli_query($conectar,$sqlShowIP);
#$listaIp = mysqli_fetch_all($executeShowIP,MYSQLI_ASSOC);

?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Lista de IP</h3>
                </div>
                
            </div>
        </div>
    </div>
</div>