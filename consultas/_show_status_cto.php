<?php 

  include_once "../db/db_config_mysql.php";
  include_once "../u2000/tl1_sender.php";

  if($_SESSION["consulta_ctos"] == 0) 
  {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }

  $caixa_atendimento = filter_input(INPUT_POST,'ctoSelect');
  $deviceName = filter_input(INPUT_POST,'pon');
  $radioType = filter_input(INPUT_POST,'optionsRadiosConsulta');

  $area = filter_input(INPUT_POST,'area');
  $celulaInicial = filter_input(INPUT_POST,'celulaInicial');
  $celulaFinal = filter_input(INPUT_POST,'celulaFinal');

  if($caixa_atendimento || $deviceName || $area || $celulaInicial || $celulaFinal)
  {
    if($radioType == 'cto')
    {
      $deviceName = NULL;
      $celulaFinal = NULL;
      $celulaInicial = NULL;
      $area = NULL;
      
      echo "  
          <div class='row'>
            <div class='col-lg-16'>
              <div class='table-responsive'>
                <table class='table'>
                  <thead>
                    <tr>
                      <th>OLT</th>
                      <th>CTO</th>
                      <th>FRAME</th>
                      <th>SLOT</th>
                      <th>PON</th>
                      <th>Porta Atendimento</th>
                      <th>MAC do Equipamento</th>
                      <th>Contrato</th>
                    </tr>
                  </thead>
                  <tbody>";

                  
    
          $select_ont_infos = "SELECT ct.frame_slot_pon,ct.porta_atendimento,ct.porta_atendimento_disponivel,
          ct.serial,p.deviceName,p.olt_ip FROM ctos ct
            INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
            WHERE ct.caixa_atendimento = '$caixa_atendimento' ";
          
          $execute_ont_infos = mysqli_query($conectar,$select_ont_infos);
          
          while($info = mysqli_fetch_array($execute_ont_infos, MYSQLI_BOTH))
          {
            $porta_atendimento = $info['porta_atendimento'];
            list($frame,$slot,$pon) = explode('-',$info['frame_slot_pon']);
            $device = $info['deviceName'];
            $porta_disponivel = $info['porta_atendimento_disponivel'];
            $serial = $info['serial'];
            $ipOLT = $info['olt_ip'];

            echo "
                <tr>
                  <td>$device</td>
                  <td>$caixa_atendimento</td>
                  <td>$frame</td>
                  <td>$slot</td>
                  <td>$pon</td>
                  <td>$porta_atendimento</td>";
                  if($porta_disponivel == 0)
                  {
                    if($_SESSION["cadastrar_onu"] == 1)
                    {
                      echo "
                        <td><a href='../ont_classes/ont_registering.php?porta_atendimento=$porta_atendimento&frame=$frame&slot=$slot&pon=$pon&cto=$caixa_atendimento&device=$device'>DISPONÍVEL</a></td>
                        <td>----------------</td>
                      ";
                    }else{
                      echo "
                        <td>DISPONÍVEL</td>
                        <td>----------------</td>
                      ";
                    }
                  }
                  else{ 
                    $contrato_select = "SELECT contrato FROM ont WHERE serial = '$serial'";
                    $execute_contrato = mysqli_query($conectar,$contrato_select);
                    $contrato = mysqli_fetch_array($execute_contrato, MYSQLI_BOTH);
                    
                    echo "
                      <td>$serial</td>
                      <td>$contrato[contrato]</td>
                    ";
                  }
            echo"
                  
                </tr>";
          }
          echo "</tbody>
                </table>
              </div>
            </div>  
          </div>
          ";

    }elseif($radioType == 'pon')
    {
      $caixa_atendimento == NULL;
      $celulaFinal = NULL;
      $celulaInicial = NULL;
      $area = NULL;
      

      echo "  
          <div class='row'>
            <div class='col-lg-16'>
              <div class='table-responsive'>
                <table class='table'>
                  <thead>
                    <tr>
                      <th>Nome da OLT</th>
                      <th>IP</th>
                      <th>FRAME</th>
                      <th>SLOT</th>
                      <th>Quantidade de Portas</th>
                    </tr>
                  </thead>
                  <tbody>";
                  $select_ont_infos = "SELECT frame,slot,porta,deviceName, olt_ip FROM pon";
          
                  $execute_ont_infos = mysqli_query($conectar,$select_ont_infos);
          
                  while($info = mysqli_fetch_array($execute_ont_infos, MYSQLI_BOTH))
                  {
                    $frame = $info['frame'];
                    $slot = $info['slot'];
                    $quantidade_portas = $info['porta'];
                    $deviceName = $info['deviceName'];
                    $ipOLT = $info['olt_ip'];

                    echo "
                          <tr>
                            <td>$deviceName</td>
                            <td>$ipOLT</td>
                            <td>$frame</td>
                            <td>$slot</td>
                            <td>$quantidade_portas</td>
                          </tr>";
                  }

      echo "      </tbody>
                </table>
              </div>
            </div>  
          </div>
      ";
    }elseif($radioType == 'disponibilizaCTO'){
      $caixa_atendimento == NULL;
      $deviceName = NULL;

      echo "  
          <div class='row'>
            <div class='col-lg-10'>
              <!--<form method='post' action='altera_dispo.php' target='_blank'>-->
                <div class='table-responsive form-group'>
                  <table class='table'>
                    <thead>
                      <tr>
                        <th colspan=5>Celulas Ativadas na Área $area</th>
                      </tr>
                    </thead>
                    <tbody>";
                      $contador = 0;
                      for($celula = $celulaInicial; $celula <= $celulaFinal; $celula++)
                      {
                        $cto = $area."C".$celula;
                        $sql_olt_atendimento = "SELECT DISTINCT caixa_atendimento,disponivel
                          FROM ctos WHERE caixa_atendimento LIKE '$cto.%'
                          ORDER BY caixa_atendimento,porta_atendimento ASC LIMIT 1";
                        $executa_sql_olt_atendimento = mysqli_query($conectar,$sql_olt_atendimento);
                        
                        while ($celulas = mysqli_fetch_array($executa_sql_olt_atendimento, MYSQLI_BOTH))
                        {
                          $celula_sem_cto = explode('.',$celulas['caixa_atendimento']);
                          $valor_disponibilidade = $celulas['disponivel'] == 1? "checked": '';

                          if($contador == 0)
                            echo "<tr>";
                          
                            if($contador < 5) 
                          {
                            echo "
                                  <td>
                                    <input type='checkbox' class='cto_check' name='cto_ativa' value='$celula_sem_cto[0]-$celulas[disponivel]'  $valor_disponibilidade/> $celula_sem_cto[0]
                                  </td>
                                  ";
                            $contador++;
                          }
                          else{
                            echo "</tr> <br>";
                            $contador = 0;
                          }
                        }
                      }
      echo "        </tbody>
                  </table>
                </div> <!-- FIM RESPONSIVETABLE -->
                
                <div class=form-group>
                  <input type=submit class='checkArrayBox btn btn-secondary form-control' name='senderCto' onClick='return mudar_status_cto()' value=MODIFICAR />
                </div>
              <!-- </form> -->
            </div> <!-- FIM COL -->
          </div> <!-- FIM ROW -->
    ";
    }
  }else{
    echo "";
  }
?>
