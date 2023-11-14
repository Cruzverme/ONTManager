<?php 

  include_once "/var/www/html/ontManager/db/db_config_mysql.php";
  include_once "/var/www/html/ontManager/db/db_config_radius.php";
  include_once "/var/www/html/ontManager/u2000/tl1_sender.php";

  $contrato = filter_input(INPUT_POST,"contrato");
  $serial = filter_input(INPUT_POST,"serial");
  $msg_retorno = "";

  if($contrato && $serial)
  {
    ###### PEGA INFORMAÇÔES PARA REMOÇÃO
    $select_ont_info = "SELECT onu.ontID,onu.service_port_iptv,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip,onu.mac FROM ont onu 
    INNER JOIN ctos ct ON ct.serial='$serial' AND ct.caixa_atendimento= onu.cto 
    INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
    WHERE onu.serial='$serial' AND onu.contrato='$contrato'";
    
    $sql_ont_info_execute = mysqli_query($conectar,$select_ont_info);

    while($onu_info = mysqli_fetch_array($sql_ont_info_execute, MYSQLI_BOTH))
    {
      $infoONTID = $onu_info['ontID'];
      list($frame,$slot,$pon) = explode('-',$onu_info['frame_slot_pon']);
      $infoPonID = $onu_info['pon_id_fk'];
      $infoDev = $onu_info['deviceName'];
      $ip = $onu_info['olt_ip'];
      $servicePortIptv = $onu_info['service_port_iptv'];
      $mac = $onu_info['mac'];
    }
    ##### REMOVE DO u2000
    $deletar_2000 = deletar_onu_2000($infoDev,$frame,$slot,$pon,$infoONTID,$ip,$servicePortIptv);
    
    $tira_ponto_virgula = explode(";",$deletar_2000);
    $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
    $remove_desc = explode("ENDESC=",$check_sucesso[1]);
    $errorCode = trim($remove_desc[0]);
    if($errorCode != "0")
    {
      $trato = tratar_errors($errorCode);
      echo "Houve erro ao remover no u2000: $trato";
    }else
    {
      #### REMOVE NO BANCO LOCAL
      $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
      $deletar_onu = mysqli_query($conectar,$sql_apagar_onu);

      if($deletar_onu)
      {
        if($total = mysqli_affected_rows($conectar))   // retorna quantas rows foram afetadas           
        {
          #### DISPONIBILIZA O IP PARA USO
          $sql_atualizar_disponibilidade_ip = ("UPDATE ips_valido SET utilizado=false WHERE (mac_serial='$serial' 
          || mac_serial = '$mac')");
          mysqli_query($conectar,$sql_atualizar_disponibilidade_ip);

          // disponibiliza a porta para seleção na CTO
          $sql_disponibiliza_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0 WHERE serial = '$serial'";
          $executa_query = mysqli_query($conectar,$sql_disponibiliza_porta);

          ######## REMOVENDO NO RADIUS ##########
          $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial@vertv%' ";
          $executa_query_banda= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

          $deletar_onu_radius = " DELETE FROM radcheck WHERE username like '%$serial@vertv%' ";
          $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
          ######## FIM REMOVENDO NO RADIUS #########

          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
            VALUES ('$serial Removido Pelo Usuario de Codigo $usuario','$usuario')";
          $executa_log = mysqli_query($conectar,$sql_insert_log);

          $removeListaCancelado = "DELETE FROM canceled_costumer WHERE contrato = $contrato";
          $executa_removeListaCancelado = mysqli_query($conectar,$removeListaCancelado);
          
          echo "$total ONU Removida!";
        }else{
          echo "ONU Não Removida Localmente!";
        }
      }### FIM DELETA BANCO LOCAL
    }### FIM TL1
  }else{
    echo "Informação Faltando Contrato: $contrato, Serial: $serial.";
  }
?>