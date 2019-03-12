<?php
  
  include "../classes/html_inicio.php"; 
  include "../db/db_config_mysql.php";
  
  if($_SESSION["cadastrar_cto"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }

  $olt = filter_input(INPUT_POST,'olt');

?>

<div id="page-wrapper">
      <div class="container">
        <div class="row">
          <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
              <div class="panel-heading">
                  <h3 class="panel-title">Cadastro de CTO</h3>
              </div>
              <div class="panel-body">
                <form role="form" action="cto_create.php" method="post">
                  <div class="form-group">
                      <label>Selecione o tipo de CTO deseja criar</label>
                      <div class="row">
                        <div class="col-md-12">
                          <input type="radio" id="tipoGrupo" name="tipoCTO" value="range" required/> <label for="tipoGrupo">Grupo de CTOs </label>
                          <input type="radio" id="tipoEspecifico" name="tipoCTO" value="especifica" required disabled/> <label for="tipoEspecifico">CTO Específica </label>
                          <input type="radio" id="tipoExpansao" name="tipoCTO" value="expansao" required disabled/> <label for="tipoExpansao">CTO Expansão </label>
                          <input type="radio" id="tipoAssociada" name="tipoCTO" value="associada" required /> <label for="tipoAssociada">CTO Associada </label>
                        </div>
                      </div>
                      <input type="hidden" name="olt" value=<?php echo $olt ?>>
                  </div>                  
                  <button class="btn btn-lg btn-success btn-block">Avançar</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php include_once "../classes/html_fim.php"; ?>