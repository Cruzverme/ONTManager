<?php
  include_once "../db/db_config_mysql.php";
  include_once "../db/db_config_radius.php";
  include_once "../u2000/tl1_sender.php";

  //iniciando sessao para enviar as msgs
  session_start();

  $status = filter_input(INPUT_POST, 'status');
  $serial = filter_input(INPUT_POST, 'serial');
  $contrato = filter_input(INPUT_POST, 'contrato');
  
  if($status && $serial && $contrato)
  {
    if (!mysqli_connect_errno())
    {
      if($status == 1)//se estiver desativado irá ativar
      {
        $sql_ativa_ont = ("UPDATE ont SET status=2 WHERE serial='$serial'");
        $result = mysqli_query($conectar,$sql_ativa_ont);
        if($result)
        {
          $selectInfos = "SELECT onu.ontID,onu.service_port_iptv,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip 
          FROM ont onu INNER JOIN ctos ct ON ct.serial='$serial' AND ct.caixa_atendimento= onu.cto 
          INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
          WHERE onu.serial='$serial' AND onu.contrato='$contrato'";

          $execute_selectInfos = mysqli_query($conectar,$selectInfos);

          while($onu_info = mysqli_fetch_array($execute_selectInfos, MYSQLI_BOTH))
          {
            $infoONTID = $onu_info['ontID'];
            list($frame,$slot,$pon) = explode('-',$onu_info['frame_slot_pon']);
            $infoPonID = $onu_info['pon_id_fk'];
            $infoDev = $onu_info['deviceName'];
            $ip = $onu_info['olt_ip'];
            $servicePortIptv = $onu_info['service_port_iptv'];
          }
          $tl1_ativa =  ativa_inadimplente($infoDev,$frame,$slot,$pon,$infoONTID);
          $tira_ponto_virgula = explode(";",$tl1_ativa);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0")
          {
            $_SESSION['menssagem'] = "Houve erro ao inserir no u2000 SQL: $errorCode";
            header('Location: ../ont_classes/ont_delete.php');
            mysqli_close($conectar_radius);
            mysqli_close($conectar);
            exit;
          }else{
            $_SESSION['menssagem'] = "Cliente Ativado!";
            header('Location: ../ont_classes/ont_disable.php');
            mysqli_free_result($result);
            mysqli_close($conectar);
            exit;
          }
        }else{
          $erro = mysqli_error($conectar);
          $_SESSION['menssagem'] = "Cliente Não Ativado! SQL: $erro";
          header('Location: ../ont_classes/ont_disable.php');
          mysqli_close($conectar);
          exit;
        }
      }else{//aqui desativa, se tiver ativado
        $sql_desativa_ont = ("UPDATE ont SET status=1 WHERE serial='$serial'");
        $result = mysqli_query($conectar,$sql_desativa_ont);
        if($result)
        {
          $selectInfos = "SELECT onu.ontID,onu.service_port_iptv,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip 
          FROM ont onu INNER JOIN ctos ct ON ct.serial='$serial' AND ct.caixa_atendimento= onu.cto 
          INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
          WHERE onu.serial='$serial' AND onu.contrato='$contrato'";

          $execute_selectInfos = mysqli_query($conectar,$selectInfos);

          while($onu_info = mysqli_fetch_array($execute_selectInfos, MYSQLI_BOTH))
          {
            $infoONTID = $onu_info['ontID'];
            list($frame,$slot,$pon) = explode('-',$onu_info['frame_slot_pon']);
            $infoPonID = $onu_info['pon_id_fk'];
            $infoDev = $onu_info['deviceName'];
            $ip = $onu_info['olt_ip'];
            $servicePortIptv = $onu_info['service_port_iptv'];
          }
          $tl1_desabilita = desabilita_inadimplente($infoDev,$frame,$slot,$pon,$infoONTID);

          $tira_ponto_virgula = explode(";",$tl1_desabilita);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0")
          {
            $_SESSION['menssagem'] = "Houve erro ao inserir no u2000 SQL: $errorCode";
            header('Location: ../ont_classes/ont_delete.php');
            mysqli_close($conectar_radius);
            mysqli_close($conectar);
            exit;
          }else{
            $_SESSION['menssagem'] = "Cliente Desativado!";
            header('Location: ../ont_classes/ont_disable.php');
            mysqli_free_result($result);
            mysqli_close($conectar);
            exit;
          }
        }else{
          $erro = mysqli_error($conectar);
          $_SESSION['menssagem'] = "Cliente Não Desativado! SQL: $erro";
          header('Location: ../ont_classes/ont_disable.php');
          mysqli_close($conectar);
          exit;
        }
      }
    }else{
      $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
      header('Location: ../index.php');
      mysqli_close($conectar);
      exit;
    }
  }else{
    $_SESSION['menssagem'] = "Campos Faltando!";
    header('Location: ../ont_classes/ont_disable.php');
    mysqli_close($conectar);
    exit;
  }
  
  /*
  SQL PARA SALVAR NO RADIUS
  INSERT INTO radcheck( username, attribute, op, value) VALUES ( 'vlan2500/slot13/porta0/485754439C96D58B@vertv', 
  'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' ); qual olt

  INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

  INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
  */
?>