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
      $select_ont_infos = "SELECT onu.ontID,onu.cto,onu.perfil,onu.service_port_iptv,onu.service_port_internet,onu.equipamento,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip FROM ont onu 
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
      }
      // $device = "A1_VERTV-01";
      // $frame = "0";
      // $slot = "13";
      // $portaPon = "0";
      // $ontID=5;

      $status = get_status_ont($device,$frame,$slot,$pon,$ontID);
      $status_signal = get_signal_ont($device,$frame,$slot,$pon,$ontID);
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

      if($errorCode == "0" || $errorCode_sip == "0" || $errorCode_signal == "0")
      {
        
        $remove_barra = explode("-------------------------------------------------------------------------------------------------------------------",$remove_desc[1]);
        
        $remove_barra_signal = explode("-----------------------------------------------------------------------------------------",$remove_desc_signal[1]);

        $filtra_enter = explode(PHP_EOL,$remove_barra[1]);
        
        $filtra_enter_signal = explode(PHP_EOL,$remove_barra_signal[1]);
        
        $filtra_resultados = preg_split('/\s+/', $filtra_enter[2]);//explode('',$filtra_enter[2]);
        
        $filtra_resultados_signal = preg_split('/\s+/', $filtra_enter_signal[2]);//explode('',$filtra_enter[2]);
        
        echo "
        <div class='row'>
          <div class='col-lg-16'>
            <div class='table-responsive'>
              <table class='table'>
                <thead>
                  <tr>
                    <th>MAC</th>
                    <th>FRAME</th>
                    <th>SLOT</th>
                    <th>PON</th>
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
                if($filtra_resultados_signal[7] <= "-200")
                {
                  echo "<tr id=consulta_ont_positivo>";
                }elseif($filtra_resultados_signal[7] <= "-240")
                {
                  echo "<tr id=consulta_ont_neutro>";
                }else{
                  echo "<tr id=consulta_ont_negativo>";
                }
                  echo"  <td>$serial</td>
                    <td>$filtra_resultados[1]</td>
                    <td>$filtra_resultados[2]</td>
                    <td>$filtra_resultados[3]</td>
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
              </table>
              
              <center><button class='btn btn-secondary' onClick='location.reload();'><i class='fa fa-retweet fa-fw'></i></button></center>
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
