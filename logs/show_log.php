<?php

include_once "../classes/html_inicio.php";
if (!$_SESSION["consulta_log"]) {
    echo '
        <script language= "JavaScript">
            alert("Sem Permissão de Acesso!");
            location.href="../classes/redirecionador_pagina.php";
        </script>
    ';
}

?>
<div id="page-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-11 col-md-offset-0">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">LOGs</h3>
                    </div>
                    <div class="panel-body">
                        <div class='table-responsive'>
                        <table class='table display' id='tabelaLog' data-link='row'>
                            <thead>
                                <tr>
                                    <th>contrato</th>
                                    <th>registro</th>
                                    <th>codigo_usuario</th>
                                    <th>mac</th>
                                    <th>cto</th>
                                    <th>horario</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../classes/html_fim.php"; ?>