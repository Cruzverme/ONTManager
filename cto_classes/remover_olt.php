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
            <h3 class="panel-title">Remover OLT</h3>
        </div>
        <div class="panel-body">
          <form role="form" action="../classes/deletar_olt.php" method="post">
            <div class="form-group">
              <label>OLT</label> 
              <select class="form-control selectpicker" name="pon" data-live-search="true">
                <?php 
                  $sql_consulta_olt = "SELECT DISTINCT deviceName FROM pon";
                  $executa_query = mysqli_query($conectar,$sql_consulta_olt);
                  while ($olt = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                  {
                    echo "<option value=$olt[deviceName]> $olt[deviceName] </option>";
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