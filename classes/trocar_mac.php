<?php
  include_once "../db/db_config_mysql.php";
  include_once "../db/db_config_radius.php";
  include_once "../u2000/tl1_sender.php";

  //iniciando sessao para enviar as msgs
  session_start();

  
  $serial = filter_input(INPUT_POST, 'serial');
  $novoSerial = filter_input(INPUT_POST,'novoSerial');
  $contrato = filter_input(INPUT_POST, 'contrato');
  
  if($novoSerial && $serial && $contrato)
  {
    if (!mysqli_connect_errno())
    {
      $selectInfos = "SELECT onu.ontID,onu.service_port_iptv,onu.service_port_internet,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip 
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
        $servicePortInter = $onu_info['service_port_internet'];
      }

      if($execute_selectInfos)
      {
        if($servicePortInter != null)
        {
          $atualiza_qos_radius = "UPDATE radreply SET username='2500/13/0/$novoSerial@vertv' WHERE username='2500/13/0/$serial@vertv' 
            AND attribute='Huawei-Qos-Profile-Name' ";
          
          $atualiza_radius_username = "UPDATE radcheck SET username='2500/13/0/$novoSerial@vertv', value='2500/13/0/$novoSerial@vertv' 
            WHERE username='2500/13/0/$serial@vertv' AND attribute='User-Name'";

          $atualiza_radius_password = "UPDATE radcheck SET username='2500/13/0/$novoSerial@vertv' 
          WHERE username='2500/13/0/$serial@vertv' AND attribute='User-Password'";
          
          $update_serial = "UPDATE ont SET serial='$novoSerial' WHERE serial='$serial'";

          $update_serial_cto = "UPDATE ctos SET serial='$novoSerial' WHERE serial='$serial' "; 

          $executa_query_radius_username= mysqli_query($conectar_radius,$atualiza_radius_username);//atualiza radiu username
          $executa_query_radius_password= mysqli_query($conectar_radius,$atualiza_radius_password);//atualiza radius password
          $executa_query_radius_qos= mysqli_query($conectar_radius,$atualiza_qos_radius);//atualiza qos radius
        
          $executa_update_serial = mysqli_query($conectar,$update_serial);//atualiza serial banco local
          $executa_update_serial_cto = mysqli_query($conectar,$update_serial_cto);// atualiza serial da cto no banco local
        }else{
          $executa_query_radius_username= 1;
          $executa_query_radius_password= 1;
          $executa_query_radius_qos= 1;
          $executa_update_serial = 1;
          $executa_update_serial_cto = 1;
        }
        if($executa_query_radius_password && $executa_query_radius_qos && $executa_query_radius_username 
          && $executa_update_serial && $executa_update_serial_cto)
        {
          $tl1_alterar_mac = modificar_pon_ont($infoDev,$frame,$slot,$pon,$infoONTID,strtoupper($novoSerial));
          if($tl1_alterar_mac != 0 )//verificar se resetou a padrao de fabrica
          {
            if($servicePortInter != null ) // VOLTA PARA O SERIAL ANTERIOR SE DER RUIM
            {
              $atualiza_qos_radius = "UPDATE radreply SET username='2500/13/0/$serial@vertv' WHERE username='2500/13/0/$novoSerial@vertv' 
              AND attribute='Huawei-Qos-Profile-Name' ";
            
              $atualiza_radius_username = "UPDATE radcheck SET username='2500/13/0/$serial@vertv', value='2500/13/0/$serial@vertv'
                WHERE username='2500/13/0/$novoSerial@vertv' AND attribute='User-Name'";

              $atualiza_radius_password = "UPDATE radcheck SET username='2500/13/0/$serial@vertv'
                WHERE username='2500/13/0/$novoSerial@vertv' AND attribute='User-Password'";
              
              $update_serial = "UPDATE ont SET serial='$serial' WHERE serial = '$novoSerial'";

              $update_serial_cto = "UPDATE ctos SET serial='$serial' WHERE serial='$novoSerial' "; 

              $executa_query_radius_username= mysqli_query($conectar_radius,$atualiza_radius_username);//atualiza radiu username
              $executa_query_radius_password= mysqli_query($conectar_radius,$atualiza_radius_password);//atualiza radius password
              $executa_query_radius_qos= mysqli_query($conectar_radius,$atualiza_qos_radius);//atualiza qos radius
            
              $executa_update_serial = mysqli_query($conectar,$update_serial);//atualiza serial banco local
              $executa_update_serial_cto = mysqli_query($conectar,$update_serial_cto);// atualiza serial da cto no banco local
            }
            $trato = tratar_errors($tl1_alterar_mac);
            echo $_SESSION['menssagem'] = "Houve erro ao restaurar a ONT!: $trato";
            header('Location: ../ont_classes/alterar_mac_ont.php');
            mysqli_close($conectar_radius);
            mysqli_close($conectar);
            exit;
          }else{
            $tira_ponto_virgula = explode(";",$tl1_alterar_mac);
            $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
            $remove_desc = explode("ENDESC=",$check_sucesso[1]);
            $errorCode = trim($remove_desc[0]);
            if($errorCode != "0")
            {
              if($servicePortInter != null ) // VOLTA PARA O SERIAL ANTERIOR SE DER RUIM
              {
                $atualiza_qos_radius = "UPDATE radreply SET username='2500/13/0/$serial@vertv' WHERE username='2500/13/0/$novoSerial@vertv' 
                AND attribute='Huawei-Qos-Profile-Name' ";
              
                $atualiza_radius_username = "UPDATE radcheck SET username='2500/13/0/$serial@vertv', value='2500/13/0/$serial@vertv'
                  WHERE username='2500/13/0/$novoSerial@vertv' AND attribute='User-Name'";

                $atualiza_radius_password = "UPDATE radcheck SET username='2500/13/0/$serial@vertv'
                  WHERE username='2500/13/0/$novoSerial@vertv' AND attribute='User-Password'";
                
                $update_serial = "UPDATE ont SET serial='$serial' WHERE serial = '$novoSerial'";

                $update_serial_cto = "UPDATE ctos SET serial='$serial' WHERE serial='$novoSerial' "; 

                $executa_query_radius_username= mysqli_query($conectar_radius,$atualiza_radius_username);//atualiza radiu username
                $executa_query_radius_password= mysqli_query($conectar_radius,$atualiza_radius_password);//atualiza radius password
                $executa_query_radius_qos= mysqli_query($conectar_radius,$atualiza_qos_radius);//atualiza qos radius
              
                $executa_update_serial = mysqli_query($conectar,$update_serial);//atualiza serial banco local
                $executa_update_serial_cto = mysqli_query($conectar,$update_serial_cto);// atualiza serial da cto no banco local
              }
              $trato = tratar_errors($errorCode);
              echo $_SESSION['menssagem'] = "Houve erro ao alterar no u2000: $trato";
              header('Location: ../ont_classes/alterar_mac_ont.php');
              mysqli_close($conectar_radius);
              mysqli_close($conectar);
              exit;
            }else{
              echo $_SESSION['menssagem'] = "MAC Alterado";
              header('Location: ../ont_classes/alterar_mac_ont.php');
              mysqli_close($conectar);
              exit;
            }
          }
        }else{
          $erro = mysqli_error($conectar);
          echo $_SESSION['menssagem'] = "Ocorreu Erro no Radius! SQL: $erro";
          if($servicePortInter != null) // VOLTA PARA O SERIAL ANTERIOR SE DER RUIM
          {
            $atualiza_qos_radius = "UPDATE radreply SET username='2500/13/0/$serial@vertv' WHERE username='2500/13/0/$novoSerial@vertv' 
              AND attribute='Huawei-Qos-Profile-Name' ";
          
            $atualiza_radius_username = "UPDATE radcheck SET username='2500/13/0/$serial@vertv', value='2500/13/0/$serial@vertv'
              WHERE username='2500/13/0/$novoSerial@vertv' AND attribute='User-Name' )";

            $atualiza_radius_password = "UPDATE radcheck SET username='2500/13/0/$serial@vertv'
              WHERE username='2500/13/0/$novoSerial@vertv' AND attribute='User-Password' )";
            
            $update_serial = "UPDATE ont SET serial='$serial' WHERE serial = '$novoSerial'";

            $update_serial_cto = "UPDATE ctos SET serial='$serial' WHERE serial='$novoSerial' "; 

            @$executa_query_radius_username= mysqli_query($conectar_radius,$atualiza_radius_username);//atualiza radiu username
            @$executa_query_radius_password= mysqli_query($conectar_radius,$atualiza_radius_password);//atualiza radius password
            @$executa_query_radius_qos= mysqli_query($conectar_radius,$atualiza_qos_radius);//atualiza qos radius
          
            @$executa_update_serial = mysqli_query($conectar,$update_serial);//atualiza serial banco local
            @$executa_update_serial_cto = mysqli_query($conectar,$update_serial_cto);// atualiza serial da cto no banco local
          }
          header('Location: ../ont_classes/alterar_mac_ont.php');
          mysqli_close($conectar);
          exit;
        }
      }else{
        $erro = mysqli_error($conectar);
        echo $_SESSION['menssagem'] = "Não encontrei informações! SQL: $erro";
        header('Location: ../ont_classes/alterar_mac_ont.php');
        mysqli_close($conectar);
        exit;
      }
    }else{
      echo $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
      header('Location: ../index.php');
      mysqli_close($conectar);
      exit;
    }
  }else{
    echo $_SESSION['menssagem'] = "Campos Faltando!";
    header('Location: ../ont_classes/alterar_mac_ont.php');
    mysqli_close($conectar);
    exit;
  }
?>