<?php 
    include "../classes/html_inicio.php"; 
    include "../db/db_config_mysql.php"; 

    if($_SESSION["remover_cto"] == 0) {
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
            <h3 class="panel-title">Remover CTO</h3>
        </div>
        <div class="panel-body">
          <form role="form" action="../classes/deletar_cto.php" method="post">
            <div class="form-group">
              <label>PON</label> 
              <select class="selectpicker form-control" name="cto" data-show-subtext="true" data-live-search="true">
                <?php 
                  $sql_consulta_cto = "SELECT DISTINCT caixa_atendimento FROM ctos";
                  $executa_query = mysqli_query($conectar,$sql_consulta_cto);
                  while ($cto = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                  {
                    echo "<option value=$cto[caixa_atendimento]> $cto[caixa_atendimento] </option>";
                  }
                ?>
              </select>
            </div>
            <button class="btn btn-lg btn-success btn-block">Remover</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "../classes/html_fim.php";?>