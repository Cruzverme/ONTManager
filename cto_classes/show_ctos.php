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
                      <label>Selecione a OLT</label>
                      <select class="form-control" name="olt">
                        <?php 
                          $sql_consulta_olt = "SELECT deviceName,pon_id FROM pon";
                          $executa_query = mysqli_query($conectar,$sql_consulta_olt);
                          while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                          {
                            echo "<option value=$ont[pon_id]>$ont[deviceName]</option>";
                          }
                        ?>
                      </select>
                      
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