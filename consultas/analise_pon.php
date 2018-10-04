<?php
  include "../classes/html_inicio.php"; 
  include_once "../db/db_config_mysql.php";
  set_time_limit(0);
  
  if($_SESSION["consulta_relatorio_sinal"] == 0) 
  {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }
  
  
  //Consulta BD
  $sql = "SELECT sin.cto,sin.porta_atendimento,sin.porta_pon,sin.olt,sin.sinal,sin.data_registro 
          FROM todos_sinais sin 
          ORDER BY sin.olt,sin.porta_pon ASC";
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
                <table class='table table-hover display' id='tabelaSinais' data-link='row'>
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
                          list($frame,$slot,$pon) = explode('-',$ponAtual);
                          echo "
                            <tr data-toggle=modal data-pon=$ponAtual data-target=#listaSinaisModal>
                              <td>$nomeOLT</td>
                              <td>Slot: $slot Pon: $pon</td>
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

              
                <div id="listaSinaisModal" class="modal fade" role="dialog" aria-labelledby="listaSinaisModal" aria-hidden="true" style='height:100%;width:100%;overflow-y:scroll;'>
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                        <h3>Sinais da PON</h3>
                      </div>
                      <div id="listaSinaisDetails" class="modal-body"></div>
                      <div id="listaSinaisItems" class="modal-body"></div>
                      <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
                
              </div> <!-- FIM TABLE -->
            </div> <!-- FIM PANEL BODY -->
          </div><!-- login-panel panel panel-default -->
        </div><!--COL -->
      </div><!-- ROW -->
    </div><!--CONTAINER -->
  </div><!-- PAGE WRAPPER -->

<?php include "../classes/html_fim.php"; ?>