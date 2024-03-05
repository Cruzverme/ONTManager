<?php
  include_once "../db/db_config_mysql.php";
  include_once "../db/db_config_radius.php";
  include_once "../u2000/tl1_sender.php";
  // Inicia sessões 
  session_start();

  $usuario = $_SESSION["id_usuario"];
  $contrato = filter_input(INPUT_POST,"contrato");
  $serial = filter_input(INPUT_POST,"serial");

  $array_processos_historico = [];

  $select_ont_info = "SELECT onu.ontID,onu.cto,onu.porta,onu.service_port_iptv,ct.frame_slot_pon,ct.pon_id_fk,
  p.deviceName,p.olt_ip,onu.mac,onu.ip FROM ont onu 
  INNER JOIN ctos ct ON ct.serial='$serial' AND ct.caixa_atendimento= onu.cto 
  INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
  WHERE onu.serial='$serial' AND onu.contrato='$contrato'";
  
  $sql_ont_info_execute = mysqli_query($conectar,$select_ont_info);
  $onu_info = mysqli_fetch_assoc($sql_ont_info_execute);
  
  $infoONTID = $onu_info['ontID'];
  list($frame,$slot,$pon) = explode('-',$onu_info['frame_slot_pon']);
  $infoPonID = $onu_info['pon_id_fk'];
  $infoDev = $onu_info['deviceName'];
  $ip = $onu_info['olt_ip'];
  $servicePortIptv = $onu_info['service_port_iptv'];
  $mac = $onu_info['mac'];
  $ip_fixo = $onu_info['ip'];
  $cto = $onu_info['cto'];
  $porta_atendimento = $onu_info['porta'];

  ####### INICIO DO u2000 REMOVENDO
  $deletar_2000 = deletar_onu_2000($infoDev,$frame,$slot,$pon,$infoONTID,$ip,$servicePortIptv);

  if($deletar_2000 != 0)
  {
    echo "<p style='text-align:center;'>Houve erro ao inserir ao restaurar a ONT! CODE: $deletar_2000</p>";
    mysqli_close($conectar_radius);
    mysqli_close($conectar);
    exit;
  }else{
    $tira_ponto_virgula = explode(";",$deletar_2000);
    $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
    $remove_desc = explode("ENDESC=",$check_sucesso[1]);
    $errorCode = trim($remove_desc[0]);

    if($errorCode != "0")
    {
      $trato = tratar_errors($errorCode);
      echo "<p style='text-align:center;'>Houve erro ao remover no u2000: $trato</p>" ;
      mysqli_close($conectar_radius);
      mysqli_close($conectar);
      exit;
    }else{
    #### REMOVE u2000
      //remove banda e IP Fixo
      $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial%'";
      $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

      if($executa_query)
      {
        array_push($array_processos_historico,"Removido Banda no u2000");
        if($ip_fixo != NULL) array_push($array_processos_historico,"Removido IP Fixo no u2000");
      }
      else
      {
        array_push($array_processos_historico,"<span style='color:red'>Ocorreu um Erro ao remover a banda u2000</span>");
      }

      //remove autenticação ont e MAC 
      $deletar_onu_radius = " DELETE FROM radcheck WHERE username like '%$serial%' ";
      $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
      if($executa_query_radius) 
        array_push($array_processos_historico,"Removido Autenticação da ONT no u2000");
      else
        array_push($array_processos_historico,"<span style='color:red'>Ocorreu um Erro ao remover a autenticação no u2000</span>");
      

    ####disponibiliza a porta para seleção
      $sql_disponibiliza_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0 WHERE serial = '$serial'";
      $executa_query_portaCTO = mysqli_query($conectar,$sql_disponibiliza_porta);

      if($executa_query_portaCTO)
        array_push($array_processos_historico,"Porta $porta_atendimento da CTO $cto está liberada!");
      else
        array_push($array_processos_historico,"<span style='color:red'>Porta $porta_atendimento da CTO $cto não está liberada!</span>");

    #### REMOVE BANCO LOCAL
      $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
      $deletar_onu = mysqli_query($conectar,$sql_apagar_onu);

      if($deletar_onu)
      {
        /// se tiver IP fixo será removido
        if($mac != NULL and $ip_fixo != NULL)
        {
          $sql_atualizar_disponibilidade_ip = ("UPDATE ips_valido SET utilizado=false,mac_serial = NULL WHERE (mac_serial='$serial' 
                        || mac_serial = '$mac')");
          mysqli_query($conectar,$sql_atualizar_disponibilidade_ip);

          array_push($array_processos_historico,"IP $ip_fixo está livre para utilização!");
        }

        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
          VALUES ('$serial Removido Pelo Usuario de Codigo $usuario','$usuario')";
        $executa_log = mysqli_query($conectar,$sql_insert_log);

        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES ('ONT REMOVIDA 
              informações relatadas: OLT: $infoDev, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial', $usuario)";
        $executa_log = mysqli_query($conectar,$sql_insert_log);

        array_push($array_processos_historico,"<span style='color: green'>ONT $serial do Contrato $contrato foi removida</span>");
        array_push($array_processos_historico,"<input type='hidden' name='removido' value='deletada'/>");
        mysqli_close($conectar);
      }else{
        array_push($array_processos_historico,"<span style='color: red'> ONU Não Removida! </span>");
        mysqli_close($conectar);
      }
    }
    ##### FECHA AS CONEXOES COM OS BANCOS #####
    mysqli_close($conectar_radius);
    mysqli_close($conectar);
    
    echo "<p style='font-weight:bold;text-align:center'>TIMELINE</p>";
  
    foreach($array_processos_historico as $historia)
    {
      echo "<div style='text-align:center'>$historia</div>";
    }
  }




?>