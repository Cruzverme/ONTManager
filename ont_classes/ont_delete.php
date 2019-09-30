<?php 
  include_once "../classes/html_inicio.php";

  if($_SESSION["deletar_onu"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }
?>

  <div id="page-wrapper">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
              <h3 class="panel-title">Remoção de ONT</h3>
          </div>
          <div class="panel-body">
            <form role="form" method='post'>
              <div class="form-group">
                <div class="input-group">
                  <label for="contrato-remocao">Contrato</label>
                  <input id="contrato-remocao" class="form-control" placeholder="Contrato" name="contrato" pattern='[0-9]' type="text" autofocus required>
                  <span class="input-group-btn">
                    <button class="btn btn-secondary" type="button" onclick='check_contrato();'>Buscar</button>
                  </span>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include_once "../classes/html_fim.php";   ?>