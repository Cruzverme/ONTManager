<?php

include_once "../classes/html_inicio.php";
include_once "../db/db_config_mysql.php";

if ($_SESSION["gerenciar_l2l"] == 0) {
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
        <div class="col-md-12">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Gerenciador Erros U2000</h3>
                </div>
                <div class="panel-body">
                    <form id="errorForm" role="form">
                        <div class="form-group">
                            <label for="code" class="control-label ">Código de Erro:</label>
                            <input type="text" id="code" name="code" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="description" class="control-label">Descrição:</label>
                            <input id="description" name="description" class="form-control" maxlength="60" required />
                        </div>
                        <button type="submit" class="btn btn-primary form-control">Cadastrar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-title">
                    <h4>Lista de Códigos de Erro</h4>
                </div>
                <div class="panel-body">
                    <div class='table-responsive'>
                        <table class="table" id="errorTable">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Descrição</th>
                                    <th>Número de Ocorrencias</th>
                                    <th>Atualizado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once "../classes/html_fim.php"; ?>
