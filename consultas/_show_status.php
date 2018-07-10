<?php 

  include_once "../db/db_config_mysql.php";
  include_once "../u2000/tl1_sender.php";
  include_once "../classes/funcoes.php";

  if($_SESSION["consulta_onts"] == 0) 
  {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }

  $contrato = filter_input(INPUT_POST,'contrato');
  $mac = filter_input(INPUT_POST,'mac');

  if(array_key_exists('reiniciar',$_POST))
  {
    echo "<script>alert('Favor entrar no contrato novamente');</script>";
  }

  if($mac)
  {
    $get_contrato = "SELECT contrato FROM ont WHERE serial = '$mac' ";
    $executa_contrato = mysqli_query($conectar,$get_contrato);

    $contrato = mysqli_fetch_array($executa_contrato)[0];
  }
      

  if($contrato)
  {
    if(checar_contrato($contrato) == null)
    {
      mysqli_close($conectar);
      echo '
        <script language= "JavaScript">
          alert("Contrato Inexistente ou Cancelado");
          location.href="../consultas/get_status.php";
        </script>
      ';
    }

    $sql_ont_info = "SELECT serial FROM ont WHERE contrato=$contrato";
    $execute_olt_info = mysqli_query($conectar,$sql_ont_info);
    $onu_info = mysqli_fetch_array($execute_olt_info, MYSQLI_BOTH);

    $serial = $onu_info['serial'];

    if($serial != 0)
    {
      $select_ont_infos = "SELECT onu.ontID,onu.cto,onu.porta,onu.perfil,onu.service_port_iptv,onu.service_port_internet,onu.equipamento,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip FROM ont onu 
        INNER JOIN ctos ct ON ct.serial='$serial' AND ct.caixa_atendimento= onu.cto 
        INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
        WHERE onu.serial='$serial' AND onu.contrato='$contrato'";

      $execute_ont_infos = mysqli_query($conectar,$select_ont_infos);
      while($info = mysqli_fetch_array($execute_ont_infos, MYSQLI_BOTH))
      {
        $ontID = $info['ontID'];
        list($frame,$slot,$pon) = explode('-',$info['frame_slot_pon']);
        $device = $info['deviceName'];
        $vasProfile = $info['perfil'];
        $cto = $info['cto'];
        $porta_atendimento = $info['porta'];
      }

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
      
      if($vasProfile == "VAS_Internet-VoIP" || $vasProfile == "VAS_Internet-VoIP-IPTV")
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
        

        echo "
        <div class='row'>
          <div class='col-lg-16'>
            <div class='table-responsive'>
              <table class='table'>
                <thead>
                  <tr>
                    <th>CONTRATO</th>
                    <th>MAC</th>
                    <th>OLT</th>
                    <th>SLOT</th>
                    <th>PON</th>
                    <th>CTO-Porta de Atendimento</th>
                    <th>STATUS</th>
                    <th>Ultima Vez Offline</th>
                    <th>RX</th>
                    <th>TX</th>
                    <th>RX By OLT</th>
                    <th>SIP STATUS</th>
                    <th>SIP SERVICE STATUS</th>  
                  </tr>
                </thead>
                <tbody>";
                if("-2400" <= $filtra_resultados_signal[7] )
                {
                  echo "<tr id=consulta_ont_positivo>";
                }else{
                  echo "<tr id=consulta_ont_negativo>";
                }
                  echo"
                    <td>$contrato</td>
                    <td>$serial</td>
                    <td>$device</td>
                    <td>$filtra_resultados[2]</td>
                    <td>$filtra_resultados[3]</td>
                    <td>$cto-$porta_atendimento</td>
                    <td>$filtra_resultados[7]</td>";
                    
                    if($filtra_resultados[12] != '--')
                    {
                      echo "<td>$filtra_resultados[12]-$filtra_resultados[13]</td>";
                    }
                    else{
                      echo "<td>Sem Registro</td>";
                    }
                    echo "<td>$filtra_resultados_signal[7]Dbm</td>
                    <td>$filtra_resultados_signal[8]Dbm</td>
                    <td>$filtra_resultados_signal[13]Dbm</td>";
                    if($vasProfile == "VAS_Internet-VoIP" || $vasProfile == "VAS_Internet-VoIP-IPTV")
                    {
                      switch($filtra_resultados_sip[9])
                      {
                        case 'REGISTERING':
                          echo "<td>Tentando registrar </td>";
                          break;
                        case 'IDLE':
                          echo "<td>Registrado e aguardando</td>";
                          break;
                        case 'DIALING':
                          echo "<td>Telefone Fora do Gancho</td>";
                          break;
                        case 'RINGING':
                          echo "<td>Telefone Tocando</td>";
                          break;
                        case'DEACTIVED':
                          echo "<td>Desativado</td>";
                          break;
                        case 'CONNECTED':
                          echo "<td>Conectado</td>";
                          break;
                        case'FAILED-REGISTRATTION':
                          echo "<td>A autenticação falhou</td>";
                          break;
                        default:
                          echo "<td>$filtra_resultados_sip[9]</td>";
                      }
                      switch($filtra_resultados_sip[10])
                      {
                        case 'REMOTE-BLOCKED':
                          echo "<td>aeae</td>";
                          break;
                        case 'NORMAL':
                          echo "<td>Funcionamento normal</td>";
                          break;
                        case 'REMOTE-FAULT':
                          echo "<td>O algum erro na autenticação.</td>";
                          break;
                        default:
                          echo "<td>$filtra_resultados_sip[10]</td>";
                      }
                    }else{
                      echo "<td>NAO HA TELEFONE </td>";
                      echo "<td>NAO HA TELEFONE </td>";
                    }
                echo "
                  </tr>
                </tbody>
              </table>";
              // TRATAMENTO DE WAN
              
              echo " <center><h3 class='h4'>INFORMAÇÕES ADICIONAIS</h3></center>";
              for($inicio = 2; $inicio < (count($filtra_enter_wan) - 1);$inicio++)
              {
                $filtra_resultados_wan = preg_split('/\s+/', $filtra_enter_wan[$inicio]);

                echo " 
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
                        <td>$filtra_resultados_wan[5]</td>
                        <td><a target=_blank href=https://$filtra_resultados_wan[6]:80>$filtra_resultados_wan[6]</a></td>
                        <td>$filtra_resultados_wan[7]</td>
                        <td>$filtra_resultados_wan[9]</td>
                      </tr>
                    </tbody>
                  </table> 
                  ";
              }        
              // FIM TRATAMENTO WAN     
              //TRATAMENTO DE SERVICE PORT
        echo "
            <table class=table>
              <legend>Service Ports</legend>
              <thead>
                <tr>
                  <th>Service Ports</th>
                </tr>
              </thead>
              <tbody>
                <tr>";
                for($inicio = 2; $inicio < (count($filtra_enter_service_port) - 1);$inicio++)
                {
                  $filtra_resultados_service_port = preg_split('/\s+/', $filtra_enter_service_port[$inicio]);
                  echo "<td>$filtra_resultados_service_port[10]</td>";
                }
        echo"  </tr>
              </tbody>
            </table>
                
                ";//FIM TRATAMENTO DE SERVICE PORT

              //TRATAMENTO PORTA STATUS
              echo "
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
                <tr>";
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
                  }elseif($filtra_resultados_status_porta[8])
                  {
                    echo "<td style='background:red'>NÃO CONECTADA</td>";
                  }else
                  {
                    echo "<td style='background:red'>$filtra_resultados_status_porta[8]</td>";
                  }
                  
                }              
              echo"  </tr>
              </tbody>
              </table>
              
              ";//FIM TRATAMENTO DE STATUS PORTA

              echo "<center>
                  <button class='btn btn-secondary' onClick='location.reload();' name=reload><i class='fa fa-retweet fa-fw'></i> Atualizar Dados</button>
                </center>
            </div><!-- fim responsive -->
          </div><!-- fim col lg -->
        </div><!-- fim row body --> ";
      }else{
        $_SESSION['menssagem'] = "Houve erro ao inserir no u2000 SQL: $errorCode";
        header('Location: get_status.php');
        mysqli_close($conectar);
        exit;
      }
    }else{
      echo "Não Há Equipamento!";  
    }
  }else{
    echo "Não Há Contrato!";
  }
?>
