<?php

include_once "../classes/html_inicio.php";
if (!$_SESSION["block_customer_changes"]) {
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
                            <h3 class="panel-title">Clientes Bloqueados para Edição</h3>
                        </div>
                        <div id="blockedContractButton">
                            <button id="add_blocker" class="btn">Adicionar Bloqueio</button>
                            <button id="remove_block" class="btn" disabled>Desbloquear</button>
                        </div>
                        <div class="panel-body">
                            <div class='table-responsive'>
                                <table class='table display' id='blocked_changes_customer_table' data-link='row'>
                                    <thead>
                                        <tr>
                                            <th>CONTRATO</th>
                                            <th>HORARIO BLOQUEIO</th>
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