<?php 

  include_once "../db/db_config_mysql.php";
  include_once "../u2000/tl1_sender.php";
  include_once "../classes/funcoes.php";

  // if($_SESSION["consulta_onts"] == 0) 
  // {
  //   echo '
  //   <script language= "JavaScript">
  //     alert("Sem Permissão de Acesso!");
  //     location.href="../classes/redirecionador_pagina.php";
  //   </script>
  //   ';
  // }

  $contrato = filter_input(INPUT_POST,'contrato');
  $mac = filter_input(INPUT_POST,'mac');
  
  if($mac)
  {
    $get_contrato = "SELECT contrato FROM ont WHERE serial = '$mac' ";
    $executa_contrato = mysqli_query($conectar,$get_contrato);

    $contrato = mysqli_fetch_array($executa_contrato)[0];
  }
  
  if($contrato)
  {
    ######## CHECK THE CONTRACT NUMBER
    if(checar_contrato($contrato) == null)
    {
      echo "<p style='text-align:center'>Contrato Inexistente ou Cancelado!</p>";
      mysqli_close($conectar);
      exit;
    }

    $sql_ont_info = "SELECT serial FROM ont WHERE contrato=$contrato";
    $execute_olt_info = mysqli_query($conectar,$sql_ont_info);
    $onu_info = mysqli_fetch_assoc($execute_olt_info);
    
    $serial = $onu_info['serial'];
    if($serial != 0)
    {
    ########### GET INFORMATION IN LOCAL DB
      $select_ont_infos = "SELECT onu.ontID,onu.cto,onu.porta,onu.perfil,ct.frame_slot_pon,p.deviceName,
        onu.service_port_l2l,onu.service_port_internet,onu.service_port_iptv,onu.service_port_telefone
        FROM ont onu 
        INNER JOIN ctos ct ON ct.serial='$serial' AND ct.caixa_atendimento= onu.cto 
        INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
        WHERE onu.serial='$serial' AND onu.contrato='$contrato'";
      
      $execute_ont_infos = mysqli_query($conectar,$select_ont_infos);
      $info = mysqli_fetch_assoc($execute_ont_infos);

      $ontID = $info['ontID'];
      list($frame,$slot,$pon) = explode('-',$info['frame_slot_pon']);
      $device = $info['deviceName'];
      $vasProfile = $info['perfil'];
      $cto = $info['cto'];
      $porta_atendimento = $info['porta'];
      $service_port_l2l = $info['service_port_l2l'];
      $service_port_internet = $info['service_port_internet'];
      $service_port_iptv = $info['service_port_iptv'];
      $service_port_telefone = $info['service_port_telefone'];


    ##### GET U2000 INFORMATIONs
      $status = get_status_ont($device,$frame,$slot,$pon,$ontID);
      $status_signal = get_signal_ont($device,$frame,$slot,$pon,$ontID);
      $wan = verificar_wan($device,$frame,$slot,$pon,$ontID);
      $status_service_port = verificar_service_port($device,$frame,$slot,$pon,$ontID);

      //ONT
      $tira_ponto_virgula = explode(";",$status);
      $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
      $remove_desc = explode("ENDESC=",$check_sucesso[1]);
      $errorCode = trim($remove_desc[0]);
      // FIM ONT

      //SINAL
      $tira_ponto_virgula_signal = explode(";",$status_signal);
      $check_sucesso_signal = explode("EN=",$tira_ponto_virgula_signal[1]);
      $remove_desc_signal = explode("ENDESC=",$check_sucesso_signal[1]);
      $errorCode_signal = trim($remove_desc_signal[0]);
      $errorCode_sip = "0";
      //SIGNAL

      // WANs
      $tira_ponto_virgula_wan = explode(";",$wan);
      $check_sucesso_wan = explode("EN=",$tira_ponto_virgula_wan[1]);
      $remove_desc_wan = explode("ENDESC=",$check_sucesso_wan[1]);
      $errorCode_wan = trim($remove_desc_wan[0]);
      //FIm WANs

      //SERVICE PORT
      $status_service_port = verificar_service_port($device,$frame,$slot,$pon,$ontID);
      $tira_ponto_virgula_service_port = explode(";",$status_service_port);
      $check_sucesso_service_port = explode("EN=",$tira_ponto_virgula_service_port[1]);
      $remove_desc_service_port = explode("ENDESC=",$check_sucesso_service_port[1]);
      $errorCode_service_port = trim($remove_desc_service_port[0]);
      //FIM SERVICE PORT


      //LISTA DE PLANOS COM TELEFONE
      $lista_planos_telefone = ["VAS_Internet-VoIP","VAS_IPTV-VoIP","VAS_Internet-VoIP-IPTV",
                                "VAS_Internet-twoVoIP-IPTV","VAS_Internet-twoVoIP",
                                "VAS_Internet-VoIP-CORP-IP","VAS_Internet-VoIP-IPTV-CORP-IP",
                                "VAS_Internet-VoIP-IPTV-CORP-IP-B","VAS_Internet-VoIP-CORP-IP-Bridge"];

      if(in_array($vasProfile,$lista_planos_telefone))
      {
        $status_sip = get_status_sip($device,$frame,$slot,$pon,$ontID);
        //SIP
        $tira_ponto_virgula_sip = explode(";",$status_sip);
        $check_sucesso_sip = explode("EN=",$tira_ponto_virgula_sip[1]);
        $remove_desc_sip = explode("ENDESC=",$check_sucesso_sip[1]);
        $errorCode_sip = trim($remove_desc_sip[0]);

        $remove_barra_sip = explode("-----------------------------------------------------------------------------------------",$remove_desc_sip[1]);
        $filtra_enter_sip = explode(PHP_EOL,$remove_barra_sip[1]);
        $filtra_resultados_sip = preg_split('/\s+/', $filtra_enter_sip[2]);//explode('',$filtra_enter[2]);
        // FIM SIP
      }
      if($errorCode == "0" || $errorCode_sip == "0" || $errorCode_signal == "0" || $errorCode_wan == "0")
      {
        ##### Eliminar Caracteres Indesejaveis
        $remove_barra = explode("-------------------------------------------------------------------------------------------------------------------",$remove_desc[1]);
        
        $remove_barra_signal = explode("-----------------------------------------------------------------------------------------",$remove_desc_signal[1]);

        $remove_barra_wan = explode("----------------------------------------------------------------------------------------------",$remove_desc_wan[1]);

        $remove_barra_service_port = explode("-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------",$remove_desc_service_port[1]);
        
        $filtra_enter = explode(PHP_EOL,$remove_barra[1]);
        
        $filtra_enter_signal = explode(PHP_EOL,$remove_barra_signal[1]);

        $filtra_enter_wan = explode(PHP_EOL,$remove_barra_wan[1]);
                
        $filtra_resultados = preg_split('/\s+/', $filtra_enter[2]);
        
        $filtra_resultados_signal = preg_split('/\s+/', $filtra_enter_signal[2]);

        $filtra_enter_service_port = explode(PHP_EOL,$remove_barra_service_port[1]);

        #### CORES DE LINHA
        //caso o sinal esteja ruim
        if("-2400" <= $filtra_resultados_signal[7] )
          $tr_inicial = "<tr id=consulta_ont_positivo>";
        else
          $tr_inicial = "<tr id=consulta_ont_negativo>";

        //caso a ONT esteja down
        if($filtra_resultados[7] == "Down")
          $color = "#ff3333";
        else
          $color = "#b3d1ff";

        #### FILTRA SIP 

        if(in_array($vasProfile,$lista_planos_telefone))
        {
          // pegará status do telefone
          switch($filtra_resultados_sip[9])
          {
            case 'REGISTERING':
              $statusSip = "<td>Tentando registrar </td>";
              break;
            case 'IDLE':
              $statusSip = "<td>Registrado e aguardando</td>";
              break;
            case 'DIALING':
              $statusSip = "<td>Telefone Fora do Gancho</td>";
              break;
            case 'RINGING':
              $statusSip = "<td>Telefone Tocando</td>";
              break;
            case'DEACTIVED':
              $statusSip = "<td>Desativado</td>";
              break;
            case 'CONNECTED':
              $statusSip = "<td>Conectado</td>";
              break;
            case'FAILED-REGISTRATTION':
              $statusSip = "<td>A autenticação falhou</td>";
              break;
            default:
              $statusSip = "<td>$filtra_resultados_sip[9]</td>";
          }
          //pegara status do serviço
          switch($filtra_resultados_sip[10])
          {
            case 'REMOTE-BLOCKED':
              $serviceStatusSip = "<td>Bloqueado Remotamente</td>";
              break;
            case 'NORMAL':
              $serviceStatusSip = "<td>Funcionamento normal</td>";
              break;
            case 'REMOTE-FAULT':
              $serviceStatusSip = "<td>Ocorreu um erro na autenticação.</td>";
              break;
            default:
              $serviceStatusSip = "<td>$filtra_resultados_sip[10]</td>";
          }
        }else{
          $statusSip = "<td>NAO HA TELEFONE </td>";
          $serviceStatusSip = "<td>NAO HA TELEFONE </td>";
        }

?>
        <!-- HTML COMEÇA AQUI | HTML START HERE-->
        <div class='row'>
          <div class='col-lg-12'>
            <div class='table-responsive'>
              
              <table class='table'>

                <thead>
                  <h4 style='text-align:center' class="">INFORMAÇÕES ONT</h4>
                  <tr>
                    <th>CONTRATO</th>
                    <th>MAC</th>
                    <th>CTO-Porta de Atendimento</th>
                    <th>STATUS</th>
                    <th>Ultima Vez Offline</th>
                  </tr>
                </thead>
                <tbody>
                  <tr style='background-color:<?php echo $color;?>'>
                  <td><?php echo $contrato; ?></td>
                  <td><?php echo $serial; ?></td>
                  <td><?php echo "$cto-$porta_atendimento"; ?></td>
          
                  <?php 
                    if($filtra_resultados[7] == "Down")
                      echo "<td style='font-weight: bold;color:#ff8c1a'>OFFLINE</td>";
                    elseif($filtra_resultados[7] == "Up")
                      echo "<td style='color:#2e9700'>ONLINE</td>";
                    else{
                      echo "<td>$filtra_resultados[7]</td>";
                    }
                  ?> 
          
                  <?php 
                    if($filtra_resultados[12] != '--')
                      echo "<td style='font-weight: bold'>$filtra_resultados[12]-$filtra_resultados[13]</td>";
                    else
                      echo "<td>Sem Registro</td>";
                  ?>
                </tbody>
              </table>
                            
              
              <table class='table'>
                <thead>
                  <h4 style='text-align:center'>INFORMAÇÕES SINAL</h4>
                  <tr>
                    <th>RX</th>
                    <th>TX</th>
                    <th>RX By OLT</th>
                    <th>SIP STATUS</th>
                    <th>SIP SERVICE STATUS</th>  
                  </tr>
                </thead>
                <tbody>
                  <?php echo $tr_inicial ?>
                    <td><?php echo $filtra_resultados_signal[7];?> Dbm</td>
                    <td><?php echo $filtra_resultados_signal[8];?> Dbm</td>
                    <td><?php echo $filtra_resultados_signal[13]; ?>Dbm</td>
                    <?php echo $statusSip;?>
                    <?php echo $serviceStatusSip;?>
                  </tr>
                </tbody>
              </table>

              <table class='table'>
                
                <thead>
                  <h4 style='text-align:center'>INFORMAÇÕES OLT</h4>
                  <tr>
                    <th>ONT ID</th>
                    <th>OLT</th>
                    <th>SLOT</th>
                    <th>PON</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><?php echo $ontID?></td>
                    <td><?php echo $device?></td>
                    <td><?php echo $filtra_resultados[2]?></td>
                    <td><?php echo $filtra_resultados[3]?></td>
                  </tr>
                </tbody>
              </table>
              
              <!-- TRATAMENTO DE WAN -->
              
              <h4 class='informacoes_legend' style='text-align:center' onclick='levanta();'>
                INFORMAÇÕES ADICIONAIS<i class='fa fa-chevron-down'></i>
              </h4>
              
              <div class='hider_infos' style='display:none'>
              <?php 
                for($inicio = 2; $inicio < (count($filtra_enter_wan) - 1);$inicio++)
                {
                  $filtra_resultados_wan = preg_split('/\s+/', $filtra_enter_wan[$inicio]); 
              ?>
                  <table class='table'>
                    <thead>
                      <tr>
                        <th>TIPO WAN</th>
                        <th>IPV4</th>
                        <th>WAN MASK</th>
                        <th>WAN GATEWAY</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><?php echo $filtra_resultados_wan[5]?></td>
                        <td><a target=_blank href=<?php echo "https://$filtra_resultados_wan[6]:80"?>><?php echo $filtra_resultados_wan[6]?></a></td>
                        <td><?php echo $filtra_resultados_wan[7]?></td>
                        <td><?php echo $filtra_resultados_wan[9]?></td>
                      </tr>
                    </tbody>
                  </table> 
              <?php 
                }
              ?>
              <!-- FIM TRATAMENTO WAN     
              TRATAMENTO DE SERVICE PORT -->
              <table class=table>
                <legend>Service Ports</legend>
                <thead>
                  <tr>
                  <?php
                    for($inicio = 2; $inicio < (count($filtra_enter_service_port) - 1);$inicio++)
                    {
                      $filtra_resultados_service_port = preg_split('/\s+/', $filtra_enter_service_port[$inicio]);
                      echo "<th>$filtra_resultados_service_port[10]</th>";
                    }
                  ?>
                  </tr>
                </thead>
              </table>
            </div> <!-- fim div de hidder -->
            
            <!-- FIM TRATAMENTO DE SERVICE PORT
              TRATAMENTO PORTA STATUS -->
              <table class=table>
                <legend>Status de Porta</legend>
                <thead>
                  <tr>
                    <th>Porta 1</th>
                    <th>Porta 2</th>
                    <th>Porta 3</th>
                    <th>Porta 4</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <?php 
                      foreach(range(1,4) as $porta) //itera o numero de portas, caso chegue alguma ONT com numero acima de 4 portas tera que rever
                      {
                        //PORTA ETH
                        $status_porta_eth = verificar_portas_ont($device,$frame,$slot,$pon,$ontID,$porta);
                        $tira_ponto_virgula_status_porta = explode(";",$status_porta_eth);
                        $check_sucesso_status_porta = explode("EN=",$tira_ponto_virgula_status_porta[1]);
                        $remove_desc_status_porta = explode("ENDESC=",$check_sucesso_status_porta[1]);
                        $errorCode_status_porta = trim($remove_desc_status_porta[0]);
      
                        $remove_barra_status_porta = explode("-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------",$remove_desc_status_porta[1]);
                        $filtra_enter_status_porta = explode(PHP_EOL,$remove_barra_status_porta[1]);
                        $filtra_resultados_status_porta = preg_split('/\s+/', $filtra_enter_status_porta[2]);
                        //FIM PORTA ETH
                        if($filtra_resultados_status_porta[8] == "Active")
                        {
                          echo "<td style='background:green'>CONECTADA</td>";
                        }elseif($filtra_resultados_status_porta[8]){
                          echo "<td style='background:red'>NÃO CONECTADA</td>";
                        }else{
                          echo "<td style='background:red'>$filtra_resultados_status_porta[8]</td>";
                        }
                      }
                    ?>
                  </tr>
                </tbody>
              </table>
              <!-- FIM TRATAMENTO DE STATUS PORTA -->
              <div style='text-align:center'>
                <button class='btn btn-secondary' onClick="consultar();">Atualizar Dados</button>
                <button class='btn btn-secondary' onClick="return acordaONT(<?php echo "'$device','$frame','$slot','$pon','$ontID','reset'" ?>);">
                  <i class='fa fa-spinner fa-fw'></i>REINICIAR
                </button>
                <button class='btn btn-secondary' onClick="return acordaONT(<?php echo "'$device','$frame','$slot','$pon','$ontID','fabric'" ?>);">
                  <i class='fa fa-cogs fa-fw'></i>PADRÃO DE FÁBRICA
                </button>
              </div>

            </div> <!-- end table-responsive -->
          </div> <!-- end col -->
        </div><!-- End Row -->      

        <!-- HTML TERMINA AQUI | HTML END HERE-->
<?php
      }else{
        echo "<p style='text-align:center' background-color>Houve erro ao Consultar no u2000.\r Codigo: $errorCode</p>";
        mysqli_close($conectar);
        exit;
      }
    }else{
      echo "<p style='text-align:center'>Não existe equipamento Cadastrado no contrato $contrato !</p>";
      mysqli_close($conectar);
      exit;
    }
  
  }

?>