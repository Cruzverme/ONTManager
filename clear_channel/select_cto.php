<?php 
    
  include "../classes/html_inicio.php";
  include_once "../db/db_config_mysql.php";

  if($_SESSION['gerenciar_l2l'] == 0) //$_SESSION["cadastrar_onu"] == 0 &&
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
  <div class="container">
    <div class="row">
      <div class="col-md-10 col-md-offset-0">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Cadastro de L2L ONT</h3>
          </div>
          <div class="panel-body">
          <form method="post">
            <div class=form-group>
              <label>CTO</label>
              <select class="form-control selectpicker" name=ctoSelect data-show-subtext="true" data-size=5 data-live-search="true">
              <?php
                  $sql_caixa_atendimento = "SELECT DISTINCT caixa_atendimento, disponivel FROM ctos";
                  $executa_sql_caixa_atendimento = mysqli_query($conectar,$sql_caixa_atendimento);
                  
                  while ($caixa_atendimento = mysqli_fetch_array($executa_sql_caixa_atendimento, MYSQLI_BOTH))
                  {
                    if($caixa_atendimento['disponivel'] == 0)
                    {
                      $disponivel = "disabled";
                    }else{
                      $disponivel = "";
                    }

                    if($_POST['ctoSelect'] == $caixa_atendimento['caixa_atendimento'])
                    {
                      $selecionado = "selected";
                    }else{
                      $selecionado = "";
                    }
                      if($_SESSION['nome_usuario'] != 'Charles Pereira' || $_SESSION['nome_usuario'] != 'Administrador') {
                        echo "<option name='cto' value=$caixa_atendimento[caixa_atendimento] $selecionado $disponivel>$caixa_atendimento[caixa_atendimento]</option>";
                      } else{
                        echo "<option>EM MANUTENCAO!, VOLTE LOGO MAIS!</option>";break;}
                  }
              ?>
              </select>
              <span class="input-group-btn">
                <?php if($_SESSION['nome_usuario'] != 'Charles Pereira' || $_SESSION['nome_usuario'] != 'Administrador') { ?>
                <button class="btn btn-secondary form-control" type="submit" >Buscar</button>
                <?php } else {?>
                <button class="btn btn-secondary form-control" type="submit" disabled>Buscar</button>
                <?php } ?>
              </span>
            </div>
            
          </form>
          <?php include "_show_ctos_ports.php"?>
        </div><!-- fim panel -->
      </div>
    </div><!-- fim row -->
  </div>
</div> <!-- fim pagewrapper -->

<?php include "../classes/html_fim.php"; ?>
