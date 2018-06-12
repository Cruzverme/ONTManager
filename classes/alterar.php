<?php
include_once "../db/db_config_mysql.php";
include_once "../db/db_config_radius.php";
include_once "../u2000/tl1_sender.php";
// Inicia sessões 
session_start();

if (!mysqli_connect_errno())
{
  if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) && isset($_POST["pacote"]) )
  {
    $usuario = $_SESSION["id_usuario"];
    $contrato = $_POST["contrato"];
    $serial = $_POST["serial"];
    $pacote = $_POST["pacote"];
    $telNumber = $_POST["numeroTelNovo"];
    $telPass = $_POST["passwordTelNovo"];
    $vasProfileNovo = $_POST["optionsRadios"];
    $porta_atendimento = null;

    if(empty($telNumber) && empty($telPass) )
     {
        $telNumber = 0;
        $telPass = 0;
     }

    $select_ont_info = "SELECT onu.ontID,onu.cto,onu.porta, onu.perfil,onu.service_port_iptv,onu.service_port_internet,onu.equipamento,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip FROM ont onu 
      INNER JOIN ctos ct ON ct.serial='$serial' AND ct.caixa_atendimento= onu.cto 
      INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
      WHERE onu.serial='$serial' AND onu.contrato='$contrato'";
    
    $sql_ont_info_execute = mysqli_query($conectar,$select_ont_info);

    while($onu_info = mysqli_fetch_array($sql_ont_info_execute, MYSQLI_BOTH))
    {
      $infoONTID = $onu_info['ontID'];
      list($frame,$slot,$pon) = explode('-',$onu_info['frame_slot_pon']);
      $infoPonID = $onu_info['pon_id_fk'];
      $device = $onu_info['deviceName'];
      $ip = $onu_info['olt_ip'];
      $servicePortIptv = $onu_info['service_port_iptv'];
      $servicePortNet = $onu_info['service_port_internet'];
      $cto = $onu_info['cto'];
      $porta_atendimento = $onu_info['porta'];
      $equipment = $onu_info['equipamento'];
      $vasProfile = $onu_info['perfil'];
    }
    ############## INICIO DELETAR ##################
    $deletar_2000 = deletar_onu_2000($device,$frame,$slot,$pon,$infoONTID,$ip,$servicePortIptv);
    $tira_ponto_virgula = explode(";",$deletar_2000);
    $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
    $remove_desc = explode("ENDESC=",$check_sucesso[1]);
    $errorCode = trim($remove_desc[0]);
    if($errorCode != "0") //se der erro ao deletar a ONT
    {
      $trato = tratar_errors($errorCode);

      $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
          VALUES (ERRO NO U2000 AO DELETAR A ONTID $trato 
          informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
          Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
          MAC: $serial, Novo Perfil: $vasProfileNovo, Internet: $pacote, Telefone: $telNumber,
          Senha Telefone: $telPass',$usuario)";
      $executa_log = mysqli_query($conectar,$sql_insert_log);

      echo $_SESSION['menssagem'] = "Houve erro ao remover no u2000: $trato";
      header('Location: ../ont_classes/ont_change.php');
      mysqli_close($conectar_radius);
      mysqli_close($conectar);
      exit;
    }else
    {
      if($vasProfileNovo != "VAS_IPTV" && $servicePortNet == NULL)
      {
        $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value) 
            VALUES ( '2500/13/0/$serial@vertv', 'User-Name', ':=', '2500/13/0/$serial@vertv' )";

        $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value) 
                VALUES ( '2500/13/0/$serial@vertv', 'User-Password', ':=', 'vlan' )";

        $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
                VALUES ( '2500/13/0/$serial@vertv', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";

        $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
        $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);
        $executa_query_qos_profile= mysqli_query($conectar_radius,$insere_ont_radius_qos_profile);
      }else{
        $atualiza_qos_radius = "UPDATE radreply SET value='$pacote' WHERE username='2500/13/0/$serial@vertv' 
           AND attribute='Huawei-Qos-Profile-Name' ";
        $executa_query= mysqli_query($conectar_radius,$atualiza_qos_radius);
        $update_velocidade = "UPDATE ont SET pacote='$pacote' WHERE serial = '$serial'";
        $executa_update_velocidade = mysqli_query($conectar,$update_velocidade);
      }
      
      $ontID = cadastrar_ont($device,$frame,$slot,$pon,$contrato,$cto,$porta_atendimento,$serial,$equipment,$vasProfileNovo);
      $onuID = NULL; //zera ONUID para evitar problema de cash.
      $tira_ponto_virgula = explode(";",$ontID);
      $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
      $remove_desc = explode("ENDESC=",$check_sucesso[1]);
      $errorCode = trim($remove_desc[0]);
      if($errorCode != "0") // se der erro ao recadastrar a ONT
      {
        $trato = tratar_errors($errorCode);

        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
          VALUES (ERRO NO U2000 AO ALTERAR A ONTID $trato 
          informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
          Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
          MAC: $serial, Novo Perfil: $vasProfileNovo, Internet: $pacote, Telefone: $telNumber,
          Senha Telefone: $telPass',$usuario)";
        $executa_log = mysqli_query($conectar,$sql_insert_log);

        echo $_SESSION['menssagem'] = "Houve erro ao inserir no u2000: $trato";

          //se der erro ele irá apagar o registro salvo na tabela local ont
        $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
        mysqli_query($conectar,$sql_apagar_onu);

        $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
          AND attribute='Huawei-Qos-Profile-Name' ";
        $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

        $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
        $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);

        header('Location: ../ont_classes/ont_change.php');
        mysqli_close($conectar_radius);
        mysqli_close($conectar);
        exit;
      }else{
        $remove_barras_para_pegar_id = explode("---------------------------",$tira_ponto_virgula[1]);
        $filtra_espaco = explode("\r\n",$remove_barras_para_pegar_id[1]);
        $pega_id = explode("	",$filtra_espaco[2]);//posicao 4 será sempre o ONTID
        $onuID=trim($pega_id[4]);
        
        $insere_ont_id = "UPDATE ont SET ontID='$onuID', perfil='$vasProfileNovo',service_port_internet='',service_port_telefone='',service_port_iptv='' WHERE serial = '$serial'";
        $executa_insere_ont_id = mysqli_query($conectar,$insere_ont_id);
      ##### IPTV SERVICE PORT ######
        if($vasProfileNovo == "VAS_IPTV" || $vasProfileNovo== "VAS_Internet-VoIP-IPTV" || $vasProfileNovo == "VAS_Internet-IPTV") ####SERVICE 
        {
          $servicePortIPTV = get_service_port_iptv($device,$frame,$slot,$pon,$onuID,$contrato);

          $tira_ponto_virgula = explode(";",$servicePortIPTV);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0") //se der erro na service port iptv
          {
            $trato = tratar_errors($errorCode);

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
              VALUES ('ERRO NO U2000 AO GERAR SERVICE PORT IPTV $trato 
              informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Novo Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass',$usuario)";
            $executa_log = mysqli_query($conectar,$sql_insert_log);
  
            echo $_SESSION['menssagem'] = "Houve erro Inserir a Service Port de IPTV: $trato";

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
            mysqli_query($conectar,$sql_apagar_onu);
            
            if($vasProfileNovo != "VAS_IPTV")//se for apenas iptv nao apagara o radius, pois nao existe
            {
              $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
                AND attribute='Huawei-Qos-Profile-Name' ";
              $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

              $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
              $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
            }
            
            deletar_onu_2000($device,$frame,$slot,$pon,$onuID);
              
            header('Location: ../ont_classes/ont_change.php');
            mysqli_close($conectar_radius);
            mysqli_close($conectar);
            exit;

          }else{
            $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
            $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
            
            $pega_id = explode("	",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
            
            $servicePortIptvID= $pega_id[0] - 1;
            
            $insere_service_iptv = "UPDATE ont SET service_port_iptv='$servicePortIptvID' WHERE serial = '$serial'";
            $executa_insere_service_iptv = mysqli_query($conectar,$insere_service_iptv);
            
            ### BTV ###
            $btv_olt = insere_btv_iptv($ip,"$servicePortIptvID");
            var_dump($btv_olt);
            if($btv_olt != 'valido' )
            {
              echo $_SESSION['menssagem'] = "Houve erro no BTV: $btv_olt";

              //se der erro ele irá apagar o registro salvo na tabela local ont
              $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
              mysqli_query($conectar,$sql_apagar_onu);
              
              if($vasProfileNovo != "VAS_IPTV")//se for apenas iptv nao apagara o radius, pois nao existe
              {
                $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
                  AND attribute='Huawei-Qos-Profile-Name' ";
                $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

                $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
                $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
              }
              
              deletar_onu_2000($device,$frame,$slot,$pon,$onuID);
              
              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                    VALUES ('Ocorreu um erro ao criar novamente o btv!
                    informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                    Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                    MAC: $serial, Novo Perfil: $vasProfileNovo, Internet: $pacote, Telefone: $telNumber,
                    Senha Telefone: $telPass','$usuario')";
              $executa_log = mysqli_query($conectar,$sql_insert_log);

              header('Location: ../ont_classes/ont_change.php');
              mysqli_close($conectar_radius);
              mysqli_close($conectar);
              exit;

            }
            ### FIM BTV ###
            if($vasProfileNovo == "VAS_IPTV")
            {
              echo $_SESSION['menssagem'] = "Plano Alterado!";

              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                VALUES ('$serial Alterado com o serviço $vasProfile 
                informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Novo Perfil: $vasProfileNovo, Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass',$usuario)";
              $executa_log = mysqli_query($conectar,$sql_insert_log);

              header('Location: ../ont_classes/ont_change.php');
              mysqli_close($conectar_radius);
              mysqli_close($conectar);
              exit;
            }
          }//fim service port iptv
          ##### IPTV SERVICE PORT ######
        } ##FIM IPTV
        
        ###INICIO TELEFONIA TL1###
        if($vasProfileNovo == "VAS_Internet-VoIP" || $vasProfileNovo == "VAS_Internet-VoIP-IPTV") //ATIVAR TELEFONIA
        {
          //echo "\n <br><br> DEV: $device | $frame | $slot | $pon | $onuID | $telNumber | $telPass | $telNumber <br><br> \n";
          $telefone_on = ativa_telefonia($device,$frame,$slot,$pon,$onuID,$telNumber,$telPass,$telNumber);

          //echo "<br> TELON: $telefone_on<br>"; var_dump($telefone_on); echo "<br><br>";

          $tira_ponto_virgula = explode(";",$telefone_on);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0") // se der erro na ativacao da telefonia
          {
            $trato = tratar_errors($errorCode);

            echo $_SESSION['menssagem'] = "Houve erro ao inserir no u2000: $trato";
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
            mysqli_query($conectar,$sql_apagar_onu);

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES ('Erro ao ativar Serviço de Telefonia $trato 
              informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Novo Perfil: $vasProfileNovo, Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass','$usuario')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            header('Location: ../ont_classes/ont_change.php');
            mysqli_close($conectar_radius);
            mysqli_close($conectar);
            exit;
          }else{
              ## INICIO SERVICE PORT TELEFONE ##
            $servicePortTelefone = get_service_port_telefone($device,$frame,$slot,$pon,$onuID,$contrato);

            $tira_ponto_virgula = explode(";",$servicePortTelefone);
            $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
            $remove_desc = explode("ENDESC=",$check_sucesso[1]);
            $errorCode = trim($remove_desc[0]);
            if($errorCode != "0") //se der erro na service port telefone
            {
              $trato = tratar_errors($errorCode);

              echo $_SESSION['menssagem'] = "Houve erro Inserir a Service Port Telefonia: $trato";

                //se der erro ele irá apagar o registro salvo na tabela local ont
              $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
              mysqli_query($conectar,$sql_apagar_onu);

              $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
                AND attribute='Huawei-Qos-Profile-Name' ";
              $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

              $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
              $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
            
              deletar_onu_2000($device,$frame,$slot,$pon,$onuID);
              
              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
                VALUES ('Erro ao Alterar o ServicePort de Telefone $trato' 
                informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Novo Perfil: $vasProfileNovo, Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass,'$usuario')";
              $executa_log = mysqli_query($conectar,$sql_insert_log);

              header('Location: ../ont_classes/ont_change.php');
              mysqli_close($conectar_radius);
              mysqli_close($conectar);
              exit;
            }else{
              $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
              $pegar_servicePortTel_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
              
              $pega_id = explode("	",$pegar_servicePortTel_ID[2]);//posicao 4 será sempre o ONTID
              
              $servicePortTelefoneID= $pega_id[0] - 1; 
              
              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
                VALUES ('ServicePort Telefone Alterado 
                informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Novo Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass','$usuario')";
              $executa_log = mysqli_query($conectar,$sql_insert_log);

              $insere_service_telefone = "UPDATE ont SET service_port_telefone='$servicePortTelefoneID',tel_user=$telNumber,tel_number=$telNumber,tel_password=$telPass
               WHERE serial = '$serial'";
              $executa_insere_service_telefone = mysqli_query($conectar,$insere_service_telefone);
              
            }//fim service port telefonia
          }
        } //FIM ATIVA TELEFONIA  
        ########FIM TL1########
        
        $servicePortInternet = get_service_port_internet($device,$frame,$slot,$pon,$onuID,$contrato);

        $tira_ponto_virgula = explode(";",$servicePortInternet);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        if($errorCode != "0") //se der erro na service port internet
        {
          $trato = tratar_errors($errorCode);

          echo $_SESSION['menssagem'] = "Houve erro Inserir a Service Port de Internet: $trato";

          //se der erro ele irá apagar o registro salvo na tabela local ont
          $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
          mysqli_query($conectar,$sql_apagar_onu);

          $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
            AND attribute='Huawei-Qos-Profile-Name' ";
          $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

          $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
          $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
          
          deletar_onu_2000($device,$frame,$slot,$pon,$onuID);

          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
            VALUES ('Erro ao Alterar a ServicePort de Internet $trato
            informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
            Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
            MAC: $serial, Novo Perfil: $vasProfileNovo, Internet: $pacote, Telefone: $telNumber,
            Senha Telefone: $telPass','$usuario')";
          $executa_log = mysqli_query($conectar,$sql_insert_log);
          
          header('Location: ../ont_classes/ont_change.php');
          mysqli_close($conectar_radius);
          mysqli_close($conectar);
          exit;
        }else{
          $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
          $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
          //print_r($pegar_servicePorta_ID);
          $pega_id = explode("	",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
          
          $servicePortInternetID= $pega_id[0] - 1; 
          
          $insere_service_internet = "UPDATE ont SET service_port_internet='$servicePortInternetID' WHERE serial = '$serial'";
          $executa_insere_service_internet = mysqli_query($conectar,$insere_service_internet);
          
            echo $_SESSION['menssagem'] = "Plano Alterado!";

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
              VALUES ('$serial Alterado com o serviço $vasProfile 
              informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Novo Perfil: $vasProfileNovo, Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass','$usuario')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);
            header('Location: ../ont_classes/ont_change.php');  
            mysqli_close($conectar_radius);
            mysqli_close($conectar);
            exit;
        }//fim service port internet
      }
    }
    ################# FIM DELETAR ######################
  }else{
    echo $_SESSION['menssagem'] = "Campos Faltando!";
    header('Location: ../ont_classes/ont_change.php');
    mysqli_close($conectar_radius);
    mysqli_close($conectar);
    exit;
  }
}else{
  echo $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
  header('Location: ../ont_classes/ont_change.php');
  mysqli_close($conectar);
  exit;
}


/*
SQL PARA SALVAR NO RADIUS
INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' );

INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
*/
?>
