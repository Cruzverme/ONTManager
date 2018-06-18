<?php 
    
  include "../classes/html_inicio.php";
  include_once "../db/db_config_mysql.php";
  
  if($_SESSION["consulta_ctos"] == 0) 
  {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }

?>

<script type="text/javascript">
  $().ready(function() {
    $("#course").autocomplete("autoComplete.php", {
        width: 260,
        matchContains: true,
        //mustMatch: true,
        //minChars: 0,
        //multiple: true,
        //highlight: false,
        //multipleSeparator: ",",
        selectFirst: false
    });
  });
</script>

<div id="page-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-md-11 col-md-offset-0">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Cadastro de ONT</h3>
            </div>
            <div class="panel-body">
            <form method="post">
              <div class=form-group>
                <label>CTO</label>
              
                <select class="form-control selectpicker" name=ctoSelect data-show-subtext="true" data-live-search="true">
                <?php
                    $sql_caixa_atendimento = "SELECT DISTINCT caixa_atendimento FROM ctos";
                    $executa_sql_caixa_atendimento = mysqli_query($conectar,$sql_caixa_atendimento);
                    
                    while ($caixa_atendimento = mysqli_fetch_array($executa_sql_caixa_atendimento, MYSQLI_BOTH))
                    {
                      if($_POST['ctoSelect'] == $caixa_atendimento['caixa_atendimento'])
                      {
                        $selecionado = "selected";
                      }else{
                        $selecionado = "";
                      }
                      echo "<option name='cto' value=$caixa_atendimento[caixa_atendimento] $selecionado>$caixa_atendimento[caixa_atendimento]</option>";
                    }
                ?>
                </select>
                <span class="input-group-btn">
                  <button class="btn btn-secondary form-control" type="submit">Buscar</button>
                </span>
              </div>
              
            </form>
            <?php include "_ont_show_ctos.php"?>
          </div><!-- fim panel -->
        </div>
      </div><!-- fim row -->
    </div>
  </div> <!-- fim pagewrapper -->

<?php include "../classes/html_fim.php"; ?>