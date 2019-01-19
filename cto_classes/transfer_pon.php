<?php 
  include "../classes/html_inicio.php"; 
  include "../db/db_config_mysql.php"; 

  // if($_SESSION["remover_cto"] == 0) {
  //   echo '
  //   <script language= "JavaScript">
  //     alert("Sem Permissão de Acesso!");
  //     location.href="../classes/redirecionador_pagina.php";    
  //   </script>
  //   ';
  // }
  
  $olt = filter_input(INPUT_POST,"olt");

  $sql_nome = "SELECT deviceName, frame, slot FROM pon WHERE pon_id = $olt";

  $executa_nome = mysqli_query($conectar,$sql_nome);
  $nomeDispositivo = mysqli_fetch_array($executa_nome,MYSQLI_BOTH);

?>

<div id="page-wrapper">
  <div class="container">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Para onde a celula será transferida?</h3>
          </div>
          
          <div class="panel-body">
            <form action="transferir_celula.php" method="post">
              <input type="hidden" name="olt" value=<?php echo $olt;?> />
              <input type="hidden" name="dispositivo" value=<?php echo $nomeDispositivo[0];?> />
              
              <label for="selecionarPON">Selecione a Porta PON</label>
              <select class="form-control selectpicker" id="selecionarPON" name="pon" data-size=5 data-live-search="true">
                <?php 
                  $sql_check = "SELECT DISTINCT * FROM ctos WHERE pon_id_fk = $olt";
                  $executa_check = mysqli_query($conectar,$sql_check);
                  if(mysqli_num_rows($executa_check) > 0) //checa se ja existe CTO cadastrada na pon
                  {
                    
                    $sql_consulta_serial = "SELECT DISTINCT olt.frame,olt.slot,olt.porta FROM pon olt 
                      INNER JOIN ctos cto ON cto.pon_id_fk = $olt
                      WHERE olt.pon_id = $olt";//"SELECT frame,slot,porta FROM pon WHERE pon_id = $olt";
                    
                    $array_ctos = array();
                    //echo "$olt";
                    $executa_query = mysqli_query($conectar,$sql_consulta_serial) or die(mysqli_error($conectar));
                    
                    $sql_get_fsp = "SELECT DISTINCT caixa_atendimento,frame_slot_pon FROM `ctos` 
                      WHERE pon_id_fk = $olt" ;
                    $executa_get_fsp = mysqli_query($conectar,$sql_get_fsp);

                    while ($porta_pon_cadastrada = mysqli_fetch_array($executa_get_fsp,MYSQLI_BOTH))
                    {
                      array_push($array_ctos,$porta_pon_cadastrada['frame_slot_pon']);
                    }
                    
                    $conta = array_count_values($array_ctos);//conta quantas CTOs tem cadastradas na PON, devido a ter apenas 2 celulas em cada porta PON
                    
                    while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                    {
                      for($porta = 0;$porta < $ont['porta'];$porta++)
                      {
                        $numeroCTOS = $conta["$ont[frame]-$ont[slot]-$porta"];
                        
                        if($numeroCTOS < 16) // se a porta estiver com mais de 16 portas, significa que todas as portas foram alokadas
                          echo "<option value=$ont[frame]-$ont[slot]-$porta-$numeroCTOS> Slot: $ont[slot]  Porta: $porta </option>";
                      }
                    }
                  }else{
                    $sql_consulta_serial = "SELECT frame, slot, porta FROM pon WHERE pon_id = $olt";

                    $executa_query = mysqli_query($conectar,$sql_consulta_serial) or die(mysqli_error($conectar));
                    
                    while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                    {
                      for($porta = 0;$porta < $ont['porta'];$porta++)
                      {
                        //if(!in_array("$ont[frame]-$ont[slot]-$porta",$array_ctos))
                        echo "<option value=$olt-$ont[frame]-$ont[slot]-$porta> Slot: $ont[slot]  Porta: $porta </option>";
                      }
                    }
                  }
                ?>
              </select>                 
              <div>
                <br>
                <button type="submit" class="col-md-12 btn ">Prosseguir</button>
              </div>
              
            </form>
          </div> <!-- fim panelbody -->

        </div> <!-- fim panel -->
      </div> <!-- fim col-->
    </div> <!-- fim row-->
  </div><!-- fim container -->
</div> <!-- fim wrapper-->





</div>


<?php include "../classes/html_fim.php";?>