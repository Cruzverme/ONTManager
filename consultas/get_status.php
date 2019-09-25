<?php 
    
  include "../classes/html_inicio.php"; 
  
  if($_SESSION["consulta_onts"] == 0) 
  {
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
      <div class="col-md-14 col-md-offset-0">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Consulta de Informações de ONT</h3>
          </div>
          <div class="panel-body">

          <form method="post">
            <div class='form-group'>
              <div class="input-group">
                <label>Digite o Contrato</label>
                <input id="contrato" placeholder="Insira o contrato" class="form-control" type="text" name="contrato">
                <span class="input-group-btn">
                  <button class="btn btn-secondary" type="submit">Buscar</button>
                </span>
              </div>

              <div class="input-group">
                <label>Digite o MAC</label>
                <input id="mac_pon" placeholder="Insira o MAC do equipamento" class="form-control" type="text" minlength="16" maxlength="16" name="mac">
                <span class="input-group-btn">
                  <button class="btn btn-secondary" type="submit">Buscar</button>
                </span>
              </div>
              
            </div>
          </form>
            <?php include "_show_status.php"?>
        </div><!-- fim panel -->
      </div>
    </div><!-- fim row -->
  </div> <!-- fim pagewrapper -->

<?php include "../classes/html_fim.php"; ?>
