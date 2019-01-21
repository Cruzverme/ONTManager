<?php 
  include "../classes/html_inicio.php"; 
  include "../db/db_config_mysql.php"; 
  
  if($_SESSION["transferir_celula"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";    
    </script>
    ';
  }

  $array_sucesso = $_SESSION['sucesso'];
  $array_falha = $_SESSION['falha'];
  $totaldeCTOsMigradas = $_SESSION['ctosMigradas'];
  
?>



<div id="page-wrapper">
  <div class="container">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">RESUMO DA MIGRAÇÃO</h3>
          </div>
          <div class="panel-body">
            <div class="alert alert-info">
              Em caso contratos que não migraram, é indicado salvar os que deram erro em um bloco de notas e depois tratá-los.
            </div>
              <ul class="list-group">
                <li class="list-group-item">
                  <h4 class="list-group-item-heading">Total de CTOs Migradas</h4>
                  <p class="list-group-item-text"><?php echo $totaldeCTOsMigradas; ?></p>
                </li>

                <li class="list-group-item">
                  <h4 class="list-group-item-heading">Migrados</h4>
                  <?php 
                    foreach($array_sucesso as $contratos_migrados)
                      echo "<p class=list-group-item-text>$contratos_migrados</p>"
                  ?>
                  
                </li>

                <li class="list-group-item">
                  <h4 class="list-group-item-heading">Não Migrados</h4>
                  <?php
                    if(!empty($array_falha))
                    {
                      foreach($array_falha as $contratos_falhados)
                        echo "<p class=list-group-item-text>$contratos_falhados</p>";
                    }else{
                      echo "<p>Todos Clientes Foram Migrados Com Sucesso! </p>";
                    }
                    unset($_SESSION['sucesso']);
                    unset($_SESSION['falha']);
                    unset($_SESSION['ctosMigradas']);
                  ?>
                </li>
              </ul>
              <a href="./transfer_olt_select.php"><button class='btn col-md-12'>Concluir</button></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include_once "../classes/html_fim.php"; ?>