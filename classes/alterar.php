<?php
include_once "../db/db_config_mysql.php";
include_once "../db/db_config_radius.php";
include_once "../u2000/tl1_sender.php";
include_once "funcoes.php";
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
    $vasProfile = $_POST["optionsRadios"];
    $modo_bridge = filter_input(INPUT_POST,'modo_bridge');
    $ip_fixo = filter_input(INPUT_POST,'ipFixo');
    $mac = filter_input(INPUT_POST,'mac');
    $porta_atendimento = null;

    if($modo_bridge != 'mac_externo')
      $mac = $serial;

    //pega o Alias do assinante
    $json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_pacote_ftth_cplus.php?contra=$contrato");
    $json_str = json_decode($json_file, true);
    $itens = $json_str['velocidade'];
    $nome = $json_str['nome'];
    $nomeCompleto = str_replace(" ","_",$nome[0]);
    //fim alias

    if(empty($telNumber) && empty($telPass) )
    {
      $telNumber = 0;
      $telPass = 0;
    }

     $select_ont_info = "SELECT onu.ontID,onu.cto,onu.porta,onu.mac,onu.ip,onu.perfil,onu.service_port_iptv,onu.service_port_internet,onu.service_port_telefone,onu.equipamento,onu.pacote,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip FROM ont onu 
      INNER JOIN ctos ct ON ct.serial='$serial' AND ct.caixa_atendimento= onu.cto 
      INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
      WHERE onu.serial='$serial' AND onu.contrato='$contrato'";
    
    $sql_ont_info_execute = mysqli_query($conectar,$select_ont_info);

    while($onu_info = mysqli_fetch_array($sql_ont_info_execute, MYSQLI_BOTH))
    {
      $ontIDOld = $onu_info['ontID'];
      list($frame,$slot,$pon) = explode('-',$onu_info['frame_slot_pon']);
      $infoPonID = $onu_info['pon_id_fk'];
      $device = $onu_info['deviceName'];
      $ip = $onu_info['olt_ip'];
      $servicePortIptv = $onu_info['service_port_iptv'];
      $servicePortNet = $onu_info['service_port_internet'];
      $servicePortTel = $onu_info['service_port_telefone'];
      $cto = $onu_info['cto'];
      $porta_atendimento = $onu_info['porta'];
      $pacote = $onu_info['pacote'];
      $equipment = $onu_info['equipamento'];
      $vasProfileOld = $onu_info['perfil'];
    
      $mac_atual = $onu_info['mac'];
      $ip_fixo_atual = $onu_info['ip'];
    
    }

    if($mac != $mac_atual)
      $mac_novo = $mac;
    else
      $mac_novo = $mac_atual;

    if($ip_fixo != $ip_fixo_atual && ($vasProfile != "VAS_Internet-CORP-IP" || $vasProfile != "VAS_Internet-CORP-IP-Bridge"))
      $ip_novo = $ip_fixo;
    else
      $ip_novo = $ip_fixo_atual;

    $deletar_2000 = deletar_onu_2000($device,$frame,$slot,$pon,$ontIDOld,$ip,$servicePortIptv);
    $tira_ponto_virgula = explode(";",$deletar_2000);
    $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
    $remove_desc = explode("ENDESC=",$check_sucesso[1]);
    $errorCode = trim($remove_desc[0]);
    
    if($errorCode != "0" && $errorCode != "1615331086") //se der erro ao deletar a ONT
    {
      $trato = tratar_errors($errorCode);

      $_SESSION['menssagem'] = "Não foi possível deletar a ONT! $errorCode $trato";
      header('Location: ../ont_classes/ont_change.php');
      mysqli_close($conectar_radius);
      mysqli_close($conectar);
      exit;
    }else{
      
      ######### Cadastro a OLT Novamente ##############
      $ontID = cadastrar_ont($device,$frame,$slot,$pon,$contrato,$nomeCompleto,$cto,$porta_atendimento,$serial,$equipment,$vasProfile);
      $onuID = NULL; //zera ONUID para evitar problema de cash.
      
      $tira_ponto_virgula = explode(";",$ontID);
      $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
      $remove_desc = explode("ENDESC=",$check_sucesso[1]);
      $errorCode = trim($remove_desc[0]);
      if($errorCode != "0") // se der erro ao recadastrar a ONT
      {
        $trato = tratar_errors($errorCode);
      //salva em LOG
        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
            VALUES (ERRO NO U2000 AO ALTERAR A ONTID $trato 
            informações relatadas: 
                OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, 
                Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Novo Perfil: $vasProfile, 
                Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass,$usuario)";
        
        $executa_log = mysqli_query($conectar,$sql_insert_log);
        
      //remove radius
        $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial@vertv%' ";
        $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

        $deletar_onu_radius = " DELETE FROM radcheck WHERE username like '%$serial@vertv%' ";
        $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
      // retorna as conf antigas
        deu_ruim_callback($device,$frame,$slot,$pon,$contrato,$nomeCompleto,$cto,$porta_atendimento,$serial,$equipment,$vasProfileOld,
          $telNumber,$telPass,$pacote);
      
        $_SESSION['menssagem'] = "Não Consegui Recadastrar a ONT! $errorCode $trato";
        header('Location: ../ont_classes/ont_change.php');
        mysqli_close($conectar_radius);
        mysqli_close($conectar);
        exit;

      }else{ //Se Ele Cadastrar a ONT
        $remove_barras_para_pegar_id = explode("---------------------------",$tira_ponto_virgula[1]);
        $filtra_espaco = explode("\r\n",$remove_barras_para_pegar_id[1]);
        $pega_id = preg_split('/\s+/',$filtra_espaco[2]);
        $onuID=trim($pega_id[4]);
        

        $insere_ont_id = "UPDATE ont SET ontID='$onuID', perfil='$vasProfile',
                            service_port_internet=NULL,service_port_telefone=NULL,
                            service_port_iptv=NULL,mac=NULL,ip=NULL
                          WHERE serial = '$serial'";
        
        #### ATUALIZA IP VALIDO ####
        
        $sql_atualiza_utilizado_ip = "UPDATE ips_valido SET utilizado=false,utilizado_por='$contrato',mac_serial='$mac_atual'
          WHERE numero_ip ='$ip_fixo_atual' && mac_serial = '$mac_atual'";
        $executa_atualiza_utitlizado_ip = mysqli_query($conectar,$sql_atualiza_utilizado_ip);

        $executa_insere_ont_id = mysqli_query($conectar,$insere_ont_id);
        ######### Fim Cadastro de OLT #############

        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('ONT criada no u2000',$usuario)";
        mysqli_query($conectar,$sql_insert_log);

        ######### APAGA O RADIUS e ONT PARA DPS CRIAR NOVAMENTE #############
        
        $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial@vertv%' ";
        $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

        $deletar_onu_radius = " DELETE FROM radcheck WHERE username like '%$serial@vertv%' ";
        $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
        
        ########### FIM APAGA RADIUS e ONT##############

        $atualiza_infosONT = "UPDATE ont SET perfil='$vasProfile' WHERE serial = '$serial'";
        $executa_atualiza_infosONT = mysqli_query($conectar,$atualiza_infosONT);

      ############ SE INTERNET #################

        if($vasProfile == "VAS_Internet" || $vasProfile == "VAS_Internet-VoIP" || $vasProfile == "VAS_Internet-IPTV" 
            || $vasProfile == "VAS_Internet-CORP-IP" || $vasProfile == "VAS_Internet-CORP-IP-Bridge" ) // se somente internet
        {
          ############ INSERE RADIUS ############
          
          if($vasProfile == "VAS_Internet-CORP-IP" || $vasProfile == "VAS_Internet-CORP-IP-Bridge")
          {

            $atualiza_mac_ip_ont = "UPDATE ont SET mac='$mac_novo',ip='$ip_novo' WHERE serial = '$serial'";
            
            $executa_atualiza_mac_ip_ont = mysqli_query($conectar,$atualiza_mac_ip_ont);

            $sql_atualiza_utilizado_ip = "UPDATE ips_valido SET utilizado=true,utilizado_por='$contrato',mac_serial='$mac_novo'
              WHERE numero_ip ='$ip_novo'";
            $executa_atualiza_utitlizado_ip = mysqli_query($conectar,$sql_atualiza_utilizado_ip);

            $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
            VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'User-Name', ':=', '2503/$slot/$pon/$serial@vertv-corp-ip' )";

            $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value)
            VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'User-Password', ':=', 'vlan' )";

            $insere_ont_radius_profile_ip_fixo = "INSERT INTO radreply( username, attribute, op, value)
            VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'Framed-IP-Address',':=','$ip_novo')";

            $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
            VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";

            if($vasProfile == "VAS_Internet-CORP-IP-Bridge")
            {
              $insere_ont_radius_mac = "INSERT INTO radcheck(username,attribute,op,value)
                values('2503/$slot/$pon/$serial@vertv-corp-ip','Huawei-User-Mac','=','$mac_novo')";

              $executa_query_ont_radius_mac = mysqli_query($conectar_radius,$insere_ont_radius_mac);
            }

            $executa_query_profile_ip_fixo = mysqli_query($conectar_radius,$insere_ont_radius_profile_ip_fixo);
          }else{
            $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
                  VALUES ( '2500/$slot/$pon/$serial@vertv', 'User-Name', ':=', '2500/$slot/$pon/$serial@vertv' )";

            $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value) 
                  VALUES ( '2500/$slot/$pon/$serial@vertv', 'User-Password', ':=', 'vlan' )";

            $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
                  VALUES ( '2500/$slot/$pon/$serial@vertv', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";

          }
          $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
          $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);
          $executa_query_qos_profile= mysqli_query($conectar_radius,$insere_ont_radius_qos_profile);
          
          ########## FIM INSERE RADIUS ##############
            
          ##### CRIA SERVIEC PORT INTERNET #####

          $servicePortInternet = get_service_port_internet($device,$frame,$slot,$pon,$onuID,$contrato,$vasProfile,$modo_bridge);
          
          $tira_ponto_virgula = explode(";",$servicePortInternet);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0"){ //se der erro ao pegar service port

            //salva em LOG
            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
            VALUES (ERRO NO U2000 AO ALTERAR A ONTID $trato 
            informações relatadas: 
                OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, 
                Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Novo Perfil: $vasProfile, 
                Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass,$usuario)";
        
            $executa_log = mysqli_query($conectar,$sql_insert_log);
        
            echo $_SESSION['menssagem'] = "Houve erro Inserir a Service Port de Internet: $errorCode $trato";
            header('Location: ../ont_classes/ont_change.php');
            mysqli_close($conectar_radius);
            mysqli_close($conectar);
            exit;
          }else{ // se nao der erro
            
            $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
            $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
            
            $pega_id = explode("  ",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
            
            $servicePortInternetID= $pega_id[0] - 1; 
            
            $insere_service_internet = "UPDATE ont SET service_port_internet='$servicePortInternetID', mac = $mac_novo,ip=$ip_novo 
              WHERE serial = '$serial'";
            $executa_insere_service_internet = mysqli_query($conectar,$insere_service_internet);
            
            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('Service Port Internet Criada $servicePortInternetID',$usuario)";
            mysqli_query($conectar,$sql_insert_log);
            
            if($vasProfile == "VAS_Internet" || $vasProfile == "VAS_Internet-CORP-IP" || $vasProfile == "VAS_Internet-CORP-IP-Bridge")
            {
              $_SESSION['menssagem'] = "Plano Alterado! Em caso de alteração de Velocidade: Consulte o Equipamento e Reinicie Para efetivar a mudança";
              header('Location: ../ont_classes/ont_change.php');
              mysqli_close($conectar_radius);
              mysqli_close($conectar);
              exit;
              
            }
          }
        }
        
        ######### SE VOIP #########
        if($vasProfile == "VAS_Internet-VoIP" || $vasProfile == "VAS_Internet-VoIP-IPTV" || $vasProfile == "VAS_Internet-VoIP-IPTV" )
        {
          
          ########## ATIVA TL1 ############
          $telefone_on = ativa_telefonia($device,$frame,$slot,$pon,$onuID,$telNumber,$telPass,$telNumber);
          $tira_ponto_virgula = explode(";",$telefone_on);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          
          if($errorCode != "0") // se der erro na ativacao da telefonia
          {
              $trato = tratar_errors($errorCode);

              //salva em LOG
              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES (ERRO NO U2000 AO ALTERAR A ONTID $trato 
              informações relatadas Ativar Telefonia: 
                  OLT: $device, PON: $pon, Frame: $frame,
                  Porta de Atendimento: $porta_atendimento, 
                  Slot: $slot, CTO: $cto Contrato: $contrato,
                  MAC: $serial, Novo Perfil: $vasProfile, 
                  Internet: $pacote, Telefone: $telNumber,
                  Senha Telefone: $telPass,$usuario)";
          
              $executa_log = mysqli_query($conectar,$sql_insert_log);

              $_SESSION['menssagem'] = "Não foi possível Ativar a Telefonia! $errorCode $trato";
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

                //salva em LOG
                $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
                VALUES (ERRO NO U2000 AO ALTERAR A ONTID $trato 
                informações relatadas SP Telefonia: 
                    OLT: $device, PON: $pon, Frame: $frame,
                    Porta de Atendimento: $porta_atendimento, 
                    Slot: $slot, CTO: $cto Contrato: $contrato,
                    MAC: $serial, Novo Perfil: $vasProfile, 
                    Internet: $pacote, Telefone: $telNumber,
                    Senha Telefone: $telPass,$usuario)";
            
                $executa_log = mysqli_query($conectar,$sql_insert_log);

                $_SESSION['menssagem'] = "Houve erro Inserir a Service Port Telefonia: $trato";
                header('Location: ../ont_classes/ont_change.php');
                mysqli_close($conectar_radius);
                mysqli_close($conectar);
                exit;

              }else{
                
                $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
                $pegar_servicePortTel_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
                
                $pega_id = explode("  ",$pegar_servicePortTel_ID[2]);//posicao 4 será sempre o ONTID
                
                $servicePortTelefoneID= $pega_id[0] - 1;
                
                $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('Service Port Telefonia Criada: $servicePortTelefoneID',$usuario)";
                mysqli_query($conectar,$sql_insert_log);
                
                $insere_service_telefone = "UPDATE ont SET service_port_telefone='$servicePortTelefoneID',tel_user='$telNumber',tel_number='$telNumber',tel_password='$telPass'
                WHERE serial = '$serial'";
                $executa_insere_service_telefone = mysqli_query($conectar,$insere_service_telefone);
                
                if($vasProfile == "VAS_Internet-VoIP")
                {
                  $_SESSION['menssagem'] = "Plano Alterado! Em caso de alteração de Velocidade: Consulte o Equipamento e Reinicie Para efetivar a mudança";    
                  header('Location: ../ont_classes/ont_change.php');
                  mysqli_close($conectar_radius);
                  mysqli_close($conectar);
                  exit;
                }

              }
          }
        }

        #################### SE FOR IPTV #################################  
        if($vasProfile == "VAS_IPTV" || $vasProfile == "VAS_Internet-IPTV" || $vasProfile == "VAS_Internet-VoIP-IPTV")
        {
          
          $servicePortIPTV = get_service_port_iptv($device,$frame,$slot,$pon,$onuID,$contrato);
          
          
          $tira_ponto_virgula = explode(";",$servicePortIPTV);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0") //se der erro na service port iptv
          {
              $trato = tratar_errors($errorCode);

              //salva em LOG
              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES (ERRO NO U2000 AO ALTERAR A ONTID $trato 
              informações relatadas SP Telefonia: 
                  OLT: $device, PON: $pon, Frame: $frame,
                  Porta de Atendimento: $porta_atendimento, 
                  Slot: $slot, CTO: $cto Contrato: $contrato,
                  MAC: $serial, Novo Perfil: $vasProfile, 
                  Internet: $pacote, Telefone: $telNumber,
                  Senha Telefone: $telPass,$usuario)";
          
              $executa_log = mysqli_query($conectar,$sql_insert_log);

              $_SESSION['menssagem'] = "Houve erro Inserir a Service Port IPTV: $trato";
              header('Location: ../ont_classes/ont_change.php');
              mysqli_close($conectar_radius);
              mysqli_close($conectar);
              exit;

          }else{
            $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
            $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
            
            $pega_id = explode("  ",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
            
            $servicePortIptvID= $pega_id[0] - 1;
            
            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('Service Port de IPTV Criada: $servicePortIptvID',$usuario)";
            mysqli_query($conectar,$sql_insert_log);
            
            $insere_service_iptv = "UPDATE ont SET service_port_iptv='$servicePortIptvID' WHERE serial = '$serial'";
            $executa_insere_service_iptv = mysqli_query($conectar,$insere_service_iptv);
            
            ### BTV ###
            $btv_olt = insere_btv_iptv($ip,"$servicePortIptvID");
            
            if($btv_olt != 'valido' )
            {
              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('Erro ao Inserir o BTV - Service Port: $servicePortIptvID',$usuario)";
              mysqli_query($conectar,$sql_insert_log);

              $trato = tratar_errors($errorCode);

              $_SESSION['menssagem'] = "Não foi possível Inserir no BTV a ONT! $errorCode $trato";
      
              //salva em LOG
              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES (ERRO NO U2000 AO ALTERAR A ONTID $trato 
              informações relatadas BTV IpTV: 
                  OLT: $device, PON: $pon, Frame: $frame,
                  Porta de Atendimento: $porta_atendimento, 
                  Slot: $slot, CTO: $cto Contrato: $contrato,
                  MAC: $serial, Novo Perfil: $vasProfile, 
                  Internet: $pacote, Telefone: $telNumber,
                  Senha Telefone: $telPass,$usuario)";
          
              $executa_log = mysqli_query($conectar,$sql_insert_log);
              
              header('Location: ../ont_classes/ont_change.php');
              mysqli_close($conectar_radius);
              mysqli_close($conectar);
              exit;
            }else{
              
              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('Service Port - $servicePortIptvID - Adicionado na BTV da OLT de $ip',$usuario)";
              mysqli_query($conectar,$sql_insert_log);
              
              if($vasProfile == "VAS_IPTV")
              {
                $_SESSION['menssagem'] = "IpTV Cadastrada!";
                header('Location: ../ont_classes/ont_change.php');
                mysqli_close($conectar_radius);
                mysqli_close($conectar);
                exit;
              }else{
                //se der tudo ok ira aparecer a msg!
                $_SESSION['menssagem'] = "Plano Alterado! Em caso de alteração de Velocidade: Consulte o Equipamento e Reinicie Para efetivar a mudança";
                header('Location: ../ont_classes/ont_change.php');
                mysqli_close($conectar_radius);
                mysqli_close($conectar);
                exit;
              }
            }
          }
        }
        
      }//fim cadastrar
    }// fim deletar
    
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
INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/$slot/$slot/485754439C96D58B@vertv', 'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' );

INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
*/
?>
  