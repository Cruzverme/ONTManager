<?php 

  include_once "../db/db_config_mysql.php";
  include_once "../db/db_config_radius.php";
  include_once "../u2000/tl1_sender.php";

  $motivo = filter_input(INPUT_POST,"motivo");
  $contrato = filter_input(INPUT_POST,"contrato");
  $serial = filter_input(INPUT_POST,"serial");

  if($motivo && $contrato && $serial)
  {
    $selectInfos = "SELECT onu.ontID,onu.service_port_iptv,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip 
          FROM ont onu INNER JOIN ctos ct ON ct.serial='$serial' AND ct.caixa_atendimento= onu.cto 
          INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
          WHERE onu.serial='$serial' AND onu.contrato='$contrato'";
    
    $execute_selectInfos = mysqli_query($conectar,$selectInfos);
    $onu_info = mysqli_fetch_assoc($execute_selectInfos);

    $infoONTID = $onu_info['ontID'];
    list($frame,$slot,$pon) = explode('-',$onu_info['frame_slot_pon']);
    $infoPonID = $onu_info['pon_id_fk'];
    $infoDev = $onu_info['deviceName'];
    $ip = $onu_info['olt_ip'];
    $servicePortIptv = $onu_info['service_port_iptv'];
    
    if($motivo == 1)
    {
      $tl1_metodo =  ativa_inadimplente($infoDev,$frame,$slot,$pon,$infoONTID);
      $status = 2;
      $mensagem = "Cliente reativado";
    }else{
      $tl1_metodo = desabilita_inadimplente($infoDev,$frame,$slot,$pon,$infoONTID);
      $status = 1;
      $mensagem = "Cliente desativado";
    }

    $tira_ponto_virgula = explode(";",$tl1_metodo);
    $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
    $remove_desc = explode("ENDESC=",$check_sucesso[1]);
    $errorCode = trim($remove_desc[0]);
    
    if($errorCode != "0")
    {
      $trato = tratar_errors($errorCode);
      echo $mensagem = "Houve erro ao alterar status no u2000: $trato";
      mysqli_close($conectar_radius);
      mysqli_close($conectar);      
    }else{
      $sql_altera_status_ont = "UPDATE ont SET status=$status WHERE serial='$serial'";
      $result_status = mysqli_query($conectar,$sql_altera_status_ont);

      $sql_altera_status_blocked = "UPDATE blocked_costumer SET inadimplente=$status WHERE serial='$serial'";
      $result_blocked = mysqli_query($conectar,$sql_altera_status_blocked);
      
      echo "$mensagem";
      mysqli_free_result($result_blocked);
      mysqli_free_result($result_status);
      
      mysqli_close($conectar_radius);
      mysqli_close($conectar);
    }
    
  }else{
    echo "Informação Faltando Id Motivo: $motivo, Contrato: $contrato, Serial: $serial.";
  }
?>