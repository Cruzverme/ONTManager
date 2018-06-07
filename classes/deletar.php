<?php
  include_once "../db/db_config_mysql.php";
  include_once "../db/db_config_radius.php";
  include_once "../u2000/tl1_sender.php";
  // Inicia sessões 
  session_start();

  if (!mysqli_connect_errno())
  {
     if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) )
     {
      $usuario = $_SESSION["id_usuario"];
      $contrato = $_POST["contrato"];
      $serial = $_POST["serial"];

      // disponibiliza a porta para seleção
       $sql_disponibiliza_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0 WHERE serial = '$serial'";
       $executa_query = mysqli_query($conectar,$sql_disponibiliza_porta);

      if ($executa_query)
      {
          $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
            AND attribute='Huawei-Qos-Profile-Name' ";
          $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

          $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
          $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);

          if ($executa_query && $deletar_onu_radius) 
          {
            ########INICIO TL1########
            $select_ont_info = "SELECT onu.ontID,onu.service_port_iptv,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip FROM ont onu 
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
            }

            //echo "<br>DEV: $infoDev | ONTID: $infoONTID | FN: $frame | SN: $slot | PN: $pon <br>";

            $deletar_2000 = deletar_onu_2000($infoDev,$frame,$slot,$pon,$infoONTID,$ip,$servicePortIptv);
            if($deletar_2000 != 0)
            {
              $_SESSION['menssagem'] = "Houve erro ao inserir ao restaurar a ONT! CODE: $deletar_2000";
              header('Location: ../ont_classes/ont_delete.php');
              mysqli_close($conectar_radius);
              mysqli_close($conectar);
              exit;
            }else
            {
              $tira_ponto_virgula = explode(";",$deletar_2000);
              $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
              $remove_desc = explode("ENDESC=",$check_sucesso[1]);
              $errorCode = trim($remove_desc[0]);
              if($errorCode != "0")
              {
                $trato = tratar_errors($errorCode);
                $_SESSION['menssagem'] = "Houve erro ao inserir no u2000: $trato";
                header('Location: ../ont_classes/ont_delete.php');
                mysqli_close($conectar_radius);
                mysqli_close($conectar);
                exit;
              }else
              {
                $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );

                $deletar_onu = mysqli_query($conectar,$sql_apagar_onu);
                if($deletar_onu)
                {
                  if ( $total = mysqli_affected_rows($conectar))   // retorna quantas rows foram afetadas           
                  {
                      $_SESSION['menssagem'] = "$total ONU Removida!";
                      header('Location: ../ont_classes/ont_delete.php');
                      mysqli_close($conectar);
                      exit;
                  }else{
                      $_SESSION['menssagem'] = "ONU Não Removida!";
                      header('Location: ../ont_classes/ont_delete.php');
                      mysqli_close($conectar);
                      exit;
                  }
                  ########FIM TL1########
                }else{
                  $erro = mysqli_error($conectar);
                  $_SESSION['menssagem'] = "Houve erro ao deletar SQL: $erro";
                  header('Location: ../ont_classes/ont_delete.php');
                  mysqli_close($conectar);
                  exit;
                }
              }//FIM TL1ELSE
            }
          }else{
            $erro = mysqli_error($conectar);
            $_SESSION['menssagem'] = "Houve erro ao deletar SQL Radius: $erro";
            header('Location: ../ont_classes/ont_delete.php');
            mysqli_close($conectar);
            exit;
          }
       }else{
         $erro = mysqli_error($conectar);
         $_SESSION['menssagem'] = "Houve erro ao deletar SQL: $erro";
         header('Location: ../ont_classes/ont_delete.php');
         mysqli_close($conectar);
         exit;
       }
    }
    else
    {
      $_SESSION['menssagem'] = "Campos Faltando!";
      header('Location: ../ont_classes/ont_delete.php');
      mysqli_close($conectar);
      exit;
    }
  }else{
    $erro = mysqli_error($conectar);
    $_SESSION['menssagem'] = "Não Consegui Contato com Servidor! $erro";
    header('Location: ../ont_classes/ont_delete.php');
    mysqli_close($conectar);
    exit;
  }
  /* close connection */

  
/*
SQL PARA SALVAR NO RADIUS
INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' );

INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
*/
?>
