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

  $olt = filter_input(INPUT_POST,"olt");
  $nomeDispositivo = filter_input(INPUT_POST,"dispositivo");
  $porta_pon = filter_input(INPUT_POST,"pon");
  
  list($frame,$slot,$pon,$quantidadeCTOs) = explode('-',$porta_pon); //se quantidadeDeCTOs for 16 ela esta completa, devido ao limite de 2 Celulas por PON
  
  $ctosNaMesmaPON = array();

  $sql_caixa_limite = "SELECT DISTINCT substring(caixa_atendimento,1,4) as caixa,disponivel
                    FROM ctos WHERE frame_slot_pon = '$frame-$slot-$pon' AND pon_id_fk = $olt";

  $executa_caixa_limite = mysqli_query($conectar,$sql_caixa_limite);

  while($linha_limite = mysqli_fetch_array($executa_caixa_limite,MYSQLI_NUM))
  {
    array_push($ctosNaMesmaPON,$linha_limite[0]);
  }
  
?>

<script>

</script>
<div id="page-wrapper">
  <div class="container">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <div class="login-panel panel panel-default">
          
          <div class="panel-heading">
            <h3 class="panel-title">
              Selecione a Célula a Ser Transferida para a pon <?php echo "$pon"; ?> do slot <?php echo "$slot"; ?> na OLT <?php echo "$nomeDispositivo"; ?>
            </h3>
          </div>
          
          <div class="panel-body">
            <form action="../classes/transferir_celula_pon.php" method="post">
              <input type="hidden" name="olt" value=<?php echo "$olt"; ?> >
              <input type="hidden" name="dispositivo" value=<?php echo "$nomeDispositivo";?> >
              <input type="hidden" name="frame" value=<?php echo "$frame";?> >
              <input type="hidden" name="slot" value=<?php echo "$slot";?> >
              <input type="hidden" name="pon" value=<?php echo "$pon";?> >
              
              <table>
                <?php 
                  $contador = 0;

                  $sql_olt_atendimento = "SELECT DISTINCT substring(caixa_atendimento,1,5) as caixa,disponivel
                    FROM ctos WHERE pon_id_fk = $olt
                    ORDER BY caixa;";
                                      
                  $executa_sql_olt_atendimento = mysqli_query($conectar,$sql_olt_atendimento);
                  
                  while ($celulas = mysqli_fetch_array($executa_sql_olt_atendimento, MYSQLI_BOTH))
                  {
                    

                    $celula_sem_cto = explode('.',$celulas['caixa']);
                    
                    if(in_array($celulas['caixa'],$ctosNaMesmaPON))
                    {
                      echo "<p class='alert alert-info' role=alert>Celula já presente na PON: $celulas[caixa]</p>";
                      $idTransfer = "cto_transfer_desativada";
                      $valor_disponibilidade = "disabled";
                    }else{
                      $idTransfer = "cto_transfer_padrao";
                      $valor_disponibilidade = "";
                    }
                    
                    if($contador == 0)
                      echo "<tr>";
                    
                    if($contador < 6)
                    {
                      echo "
                            <td>
                              <div class='form-check form-check-inline'>
                                <input type='checkbox' id='$celula_sem_cto[0]' class='form-check-input cto_check cto_transfer' name='cto_ativa[]' value='$celula_sem_cto[0]-$celulas[disponivel]' $valor_disponibilidade/>
                                <label class='form-check-label' for='$celula_sem_cto[0]'>$celula_sem_cto[0]</label>
                              </div>
                            </td>
                            ";
                      $contador++;
                    }
                    else{
                      echo "
                          <td>
                            <input type='checkbox' $idTransfer class='form-check-input cto_check cto_transfer' name='cto_ativa[]' value='$celula_sem_cto[0]-$celulas[disponivel]' $valor_disponibilidade/> $celula_sem_cto[0]
                          </td>
                        </tr> <br>";
                      $contador = 0;
                    }
                  }
                ?>
              </table>

              <div>
                <button type="submit" class="btn btn-sucess">OK</button>
              </div>
            </form>
          </div> <!-- fim panelbody -->

        </div> <!-- fim panel -->
      </div> <!-- fim col-->
    </div> <!-- fim row-->
  </div><!-- fim container -->
</div> <!-- fim wrapper-->

<?php include "../classes/html_fim.php";?>
