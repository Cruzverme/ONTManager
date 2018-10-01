<?php
  include "../classes/html_inicio.php"; 
  include_once "../db/db_config_mysql.php";
  set_time_limit(0);
  //define numero de row por pagina e valor da pagina atual
  $itens_por_pagina = 20;
  $pagina = intval($_GET['pagina']);

  //Consulta BD
  $sql = "SELECT sin.cto,sin.porta_atendimento,sin.porta_pon,sin.olt,sin.sinal,sin.data_registro 
          FROM todos_sinais sin 
          ORDER BY sin.porta_pon";
  $executaSQL = mysqli_query($conectar,$sql);
  $numero = mysqli_num_rows($executaSQL);

?>

  <div id="page-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-md-11 col-md-offset-0">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Analise de Porta PON</h3>
            </div>
            <div class="panel-body">
              <div class='table-responsive'>
                <table class='table'>

                  <thead>
                    <tr>
                      <th>DEVICE</th>
                      <th>FRAME-SLOT-PON</th>
                      <th>Menor</th>
                      <th>Maior</th>
                      <th>DIFERENCA</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                    $menor = 0;
                    $maior = -9999;
                    $diferenca = "";
                    $ponAtual = "";
                    
                    while($linha = mysqli_fetch_array($executaSQL,MYSQLI_BOTH))
                    {

                      $frameSlotPon = $linha['porta_pon'];
                      $nomeOLT = $linha['olt'];
                      $sinalRX = $linha['sinal'];

                      if($ponAtual != $frameSlotPon )
                      { 
                        if($ponAtual != "")
                        {
                          $diferenca = $menor - $maior;
                          echo "<tr>
                            <td>$ponAtual</td>
                            <td>$nomeOLT</td>
                            <td>$menor</td>
                            <td>$maior</td>
                            <td>$diferenca</td>
                          </tr>";
                        }

                        $maior = $sinalRX;
                        $menor = $sinalRX;
                        $diferenca = "";
                        $ponAtual = $frameSlotPon;
                      }else{
                      
                        if($sinalRX > $maior)
                        {
                          $maior = $sinalRX;
                        }
                        if($sinalRX < $menor)
                        {
                          $menor = $sinalRX;
                        }
                      }
                      
                    }
                  ?> 
                  </tbody>
                </table>
                 
              </div> <!-- FIM TABLE -->
            </div> <!-- FIM PANEL BODY -->
          </div><!-- login-panel panel panel-default -->
        </div><!--COL -->
      </div><!-- ROW -->
    </div><!--CONTAINER -->
  </div><!-- PAGE WRAPPER -->

<?php include "../classes/html_fim.php"; ?>