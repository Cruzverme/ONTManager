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

  <div id="page-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-md-11 col-md-offset-0">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Consulta de OLT e CTO</h3>
            </div>
            <div class="panel-body">
            <form method="post">
              <div class="form-group">
                <label>Tipo de Consulta</label>
                <div class="radio">
                  <label>
                    <?php 
                      if(empty($_POST['optionsRadiosConsulta']))
                      {
                        $_POST['optionsRadiosConsulta'] = null;
                      }

                      if($_POST['optionsRadiosConsulta'] == 'cto' || $_POST['optionsRadiosConsulta'] == null) 
                      {
                        echo "<input type='radio' name='optionsRadiosConsulta' id='ctoRadio' value='cto' checked>CTO";
                      }else{
                        echo "<input type='radio' name='optionsRadiosConsulta' id='ctoRadio' value='cto'>CTO";
                      } 
                    ?>  

                  </label>
                </div>
                <div class="radio">
                  <label>
                  <?php 
                    if($_POST['optionsRadiosConsulta'] == 'pon') 
                    {
                      echo  "<input type='radio' name='optionsRadiosConsulta' id='ponRadio' value='pon' checked>OLT";
                    }else{
                      echo  "<input type='radio' name='optionsRadiosConsulta' id='ponRadio' value='pon'>OLT";
                    }
                  ?>   
                  </label>
                </div>
              </div> <!-- fim form group radio -->
              
              <?php 
                if($_POST['optionsRadiosConsulta'] == "cto" || $_POST['optionsRadiosConsulta'] == null)
                {
                  $visivel = "style=display:visible;";
                }else{
                  $visivel = "style=display:none;";
                }
              ?>
              <div class="campoCto" <?php echo $visivel ?>>
                <div class=form-group>
                  <label>CTO</label>

                  <select class=form-control name=ctoSelect>
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
                    // }
                  ?>
                  </select>
                  
                </div>
              </div>
              <div class="form-group">
                <span class="input-group-btn">
                  <button class="btn btn-secondary" type="submit">Buscar</button>
                </span>
              </div>
          </form>
          <?php include "_show_status_cto.php"?>
          </div><!-- fim panel -->
        </div>
      </div><!-- fim row -->
    </div>
  </div> <!-- fim pagewrapper -->

<?php include "../classes/html_fim.php"; ?>