<?php
include_once "../db/db_config_mysql.php";
include_once "../db/db_config_radius.php";
include_once "../u2000/tl1_sender.php";
// Inicia sessões 
session_start();

if (!mysqli_connect_errno())
{
  $pacote = filter_input(INPUT_POST,'pacote');

  $_POST["optionsRadios"] == "VAS_IPTV-VoIP"? $pacote = "none" : $pacote ;
  $_POST["optionsRadios"] == "VAS_IPTV"? $pacote = "none" : $pacote ;

  if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) && isset($_POST["caixa_atendimento_select"])
    && isset($_POST["porta_atendimento"]) && isset($_POST["frame"]) && isset($_POST["slot"]) && $pacote &&
     isset($_POST["pon"]) && isset($_POST["deviceName"])  )
  {
    $cto = $_POST["caixa_atendimento_select"];
    $frame = $_POST["frame"];
    $slot = $_POST["slot"];
    $pon = $_POST["pon"];
    $usuario = $_SESSION["id_usuario"];
    $contrato = $_POST["contrato"];
    $nome = $_POST["nome"];
    $serial = strtoupper($_POST["serial"]);
    $equipment = $_POST['equipamentos'];
    $telNumber = $_POST["numeroTel"];
    $telPass = $_POST["passwordTel"];
    $telNumber2 = $_POST["numeroTelNovo2"];
    $telPass2 = $_POST["passwordTelNovo2"];
    $vasProfile = $_POST["optionsRadios"];
    $porta_atendimento = $_POST["porta_atendimento"];
    $deviceName = $_POST["deviceName"];
    
    $mac = filter_input(INPUT_POST,'mac');
    $ip_fixo = filter_input(INPUT_POST,'ipFixo');
    $modo_bridge = filter_input(INPUT_POST,'modo_bridge');
    
    if($modo_bridge != 'mac_externo')
      $mac = $serial;
    
    
    if($ip_fixo != 'NULL' AND $modo_bridge != 'mac_externo')
      $vasProfile = "$vasProfile-CORP-IP";
    
    if($modo_bridge == 'mac_externo')
      $vasProfile = "$vasProfile-CORP-IP-Bridge";

    $ip_olt = NULL;
    $nomeCompleto = str_replace(" ","_",$nome);
    
    if(($pacote == '' && $vasProfile != 'VAS_IPTV') || ($pacote == '' && $vasProfile != 'VAS_IPTV-VoIP'))
    {
      echo $_SESSION['menssagem'] = "Velocidade Não Existe no Cplus";
      header('Location: ../ont_classes/ont_register.php');
      mysqli_close($conectar_radius);
      mysqli_close($conectar);
      exit;
    }
        
    if(empty($telNumber) && empty($telPass) )
    {
      $telNumber = 0;
      $telPass = 0;
    }

    if( empty($telNumber2) && empty($telPass2) ) {
      $telNumber2 = 0;
      $telPass2 = 0;
    }else{
      $vasProfile = str_replace("VoIP","twoVoIP",$vasProfile);
    }
    
     $sql_verifica_limite = "SELECT limite_equipamentos FROM ont WHERE contrato='$contrato'";
     $sql_limite_result = mysqli_query($conectar,$sql_verifica_limite);
     
     $sql_verifica_limite_ont = "SELECT serial,contrato FROM ont WHERE  serial = '$serial' LIMIT 1"; //verifica se ja existe o mac
     $executa_verifica_limite_ont = mysqli_query($conectar,$sql_verifica_limite_ont);
     //var_dump($executa_verifica_limite_ont);
     if(mysqli_num_rows($executa_verifica_limite_ont) > 0) //se o resultado do limite for 1 ele cai aqui
     {
        $limiteONT = mysqli_fetch_array($executa_verifica_limite_ont, MYSQLI_BOTH);
        echo $_SESSION['menssagem'] = "MAC Já Cadastrado no contrato $limiteONT[contrato]";
        header('Location: ../ont_classes/ont_register.php');
        mysqli_close($conectar_radius);
        mysqli_close($conectar);
        exit;
     }
     $limite_registro = "";
     while ($limite = mysqli_fetch_array($sql_limite_result, MYSQLI_BOTH)) 
     {
       $limite_registro = $limite['limite_equipamentos'];
     }
     
     if ($limite_registro < 1 AND $limite_registro != null) 
     {
       echo $_SESSION['menssagem'] = "Favor, entrar em contato com o TI, para solicitar aumento de registro de equipamentos";
       header('Location: ../ont_classes/ont_register.php');
       mysqli_close($conectar_radius);
       mysqli_close($conectar);
       exit;
     }

     if($vasProfile == "VAS_IPTV" || $vasProfile == "VAS_IPTV-VoIP")
     {
      $pacote = NULL;
     }
      
      $sql_registra_onu = ("INSERT INTO ont (contrato, serial, cto, tel_number, tel_user, tel_password, tel_number2, tel_user2, tel_password2, perfil, pacote, usuario_id,equipamento,porta)
                              VALUES ('$contrato','$serial','$cto','$telNumber','$telNumber','$telPass', '$telNumber2','$telNumber2','$telPass2','$vasProfile','$pacote','$usuario','$equipment','$porta_atendimento')" );

      $cadastrar = mysqli_query($conectar,$sql_registra_onu);
      if ($cadastrar )               
      {
        if($vasProfile != "VAS_IPTV")
        {
          if($ip_fixo != 'NULL')
          {
            $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
            VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'User-Name', ':=', '2503/$slot/$pon/$serial@vertv-corp-ip' )";

            $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value)
            VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'User-Password', ':=', 'vlan' )";

            $insere_ont_radius_profile_ip_fixo = "INSERT INTO radreply( username, attribute, op, value)
            VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'Framed-IP-Address',':=','$ip_fixo')";

            $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
            VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";

            if($modo_bridge == 'mac_externo')
            {
              $insere_ont_radius_mac = "INSERT INTO radcheck(username,attribute,op,value) 
                values('2503/$slot/$pon/$serial@vertv-corp-ip','Huawei-User-Mac','=','$mac')";

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
            $executa_query_profile_ip_fixo = true;
          }

          

          $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
          $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);
          $executa_query_qos_profile= mysqli_query($conectar_radius,$insere_ont_radius_qos_profile);
        }else{
          $executa_query_username=true;
          $executa_query_password=true;
          $executa_query_qos_profile=true;
          $executa_query_profile_ip_fixo=true;
        }
          $sql_atualiza_limite = "UPDATE ont SET limite_equipamentos=0 WHERE contrato = $contrato";
          $diminui_limite = mysqli_query($conectar,$sql_atualiza_limite);

          if ($executa_query_qos_profile && $executa_query_password && $executa_query_username
           && $executa_query_profile_ip_fixo && $diminui_limite) 
          {  #####TL1 INICIO#####
          
          #### SELECT OLT IP ####
          $sql_pega_olt_ip = "SELECT olt_ip FROM pon WHERE deviceName='$deviceName'";
          $executa_pega_olt_ip = mysqli_query($conectar,$sql_pega_olt_ip);
          while ($ip = mysqli_fetch_array($executa_pega_olt_ip, MYSQLI_BOTH))
          {
            $ip_olt = $ip['olt_ip'];
          }
         
          
          ##SO VERIFICAR PORTA DO SPLITTER E ALTERAR O ONME DA VARIAVEL
        //echo "<br>$deviceName,$frame,$slot,$pon,$contrato,$cto,$porta_atendimento,$serial,$equipment,$vasProfile<br><br>";
          $ontID = cadastrar_ont($deviceName,$frame,$slot,$pon,
          $contrato,$nomeCompleto,$cto,$porta_atendimento,$serial,$equipment,$vasProfile);
          $onuID = NULL; //zera ONUID para evitar problema de cash.
          sleep(1);
          $tira_ponto_virgula = explode(";",$ontID);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0")
          {
            #realizado para debugar ###
              $sql_insert_log_status = "INSERT INTO log_estado (contrato,user_id,estado) VALUES ('$contrato',$usuario,'ONT NAO CADASTRADA COM ID $ontID!')";
              $executa_log_estado = mysqli_query($conectar,$sql_insert_log_status);

            $trato = tratar_errors($errorCode);
            echo $_SESSION['menssagem'] = "Houve erro ao inserir no u2000: $trato";

              //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
            mysqli_query($conectar,$sql_apagar_onu);

            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial@vertv' 
              AND attribute='Huawei-Qos-Profile-Name' ";
            $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

            $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial@vertv' ";
            $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES ('ERRO NO U2000 AO GERAR ONTID $trato 
              informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass',$usuario)";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            header('Location: ../ont_classes/ont_register.php');
            mysqli_close($conectar_radius);
            mysqli_close($conectar);
            exit;
          }else{
            $remove_barras_para_pegar_id = explode("---------------------------",$tira_ponto_virgula[1]);
            $filtra_espaco = explode("\r\n",$remove_barras_para_pegar_id[1]);
            $pega_id = explode("	",$filtra_espaco[2]);//posicao 4 será sempre o ONTID
            $onuID=trim($pega_id[4]);
            
              #realizado para debugar ###
            $sql_insert_log_status = "INSERT INTO log_estado (contrato,user_id,estado) VALUES ('$contrato','$usuario','INSERIDO A ONT COM ID $onuID')";
            $executa_log_estado = mysqli_query($conectar,$sql_insert_log_status);

            $insere_ont_id = "UPDATE ont SET ontID='$onuID' WHERE serial = '$serial'";
            $executa_insere_ont_id = mysqli_query($conectar,$insere_ont_id);
            ##### IPTV SERVICE PORT ######
            if($vasProfile == "VAS_IPTV" || $vasProfile== "VAS_Internet-VoIP-IPTV" || $vasProfile == "VAS_Internet-IPTV" || $vasProfile == 'VAS_IPTV-VoIP'
              || $vasProfile== "VAS_Internet-VoIP-IPTV-REAL" || $vasProfile == "VAS_Internet-IPTV-REAL" || $vasProfile == "VAS_Internet-IPTV-CORP-IP-Bridge"
              || $vasProfile == "VAS_Internet-twoVoIP-IPTV" || $vasProfile == "VAS_Internet-twoVoIP-IPTV-REAL") ####SERVICE 
            {
              $servicePortIPTV = get_service_port_iptv($deviceName,$frame,$slot,$pon,$onuID,$contrato);

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
                  MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                  Senha Telefone: $telPass',$usuario)";
                $executa_log = mysqli_query($conectar,$sql_insert_log);
                
                echo $_SESSION['menssagem'] = "Houve erro Inserir a Service Port de IPTV: $trato";

                //se der erro ele irá apagar o registro salvo na tabela local ont
                $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
                mysqli_query($conectar,$sql_apagar_onu);
                
                if($vasProfile != "VAS_IPTV")//se for apenas iptv nao apagara o radius, pois nao existe
                {
                  $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial@vertv' 
                    AND attribute='Huawei-Qos-Profile-Name' ";
                  $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

                  $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial@vertv' ";
                  $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
                }
                
                deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID,$ip_olt,$servicePortIPTV);
                  
                header('Location: ../ont_classes/ont_register.php');
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
                $btv_olt = insere_btv_iptv($deviceName,$frame,$slot,$pon,$onuID);
                $tira_ponto_virgula = explode(";",$btv_olt);
                $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
                $remove_desc = explode("ENDESC=",$check_sucesso[1]);
                $errorCode = trim($remove_desc[0]);

                if($errorCode != "0") //se der erro na btv iptv
                {
                  $trato = tratar_errors($errorCode);
                  echo $_SESSION['menssagem'] = "Houve erro no BTV: $trato";

                  //se der erro ele irá apagar o registro salvo na tabela local ont
                  $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
                  mysqli_query($conectar,$sql_apagar_onu);
                  
                  if($vasProfile != "VAS_IPTV" || $vasProfile != 'VAS_IPTV-VoIP' || $vasProfile != 'VAS_IPTV-twoVoIP')//se for apenas iptv nao apagara o radius, pois nao existe
                  {
                    $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial@vertv' 
                      AND attribute='Huawei-Qos-Profile-Name' ";
                    $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

                    $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial@vertv' ";
                    $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
                  }
                  
                  deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID,$ip_olt,$servicePortIPTV);
                    
                  $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                    VALUES ('Ocorreu um erro ao criar o btv!
                    informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                    Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                    MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                    Senha Telefone: $telPass','$usuario')";
                  $executa_log = mysqli_query($conectar,$sql_insert_log);
                  header('Location: ../ont_classes/ont_register.php');
                  mysqli_close($conectar_radius);
                  mysqli_close($conectar);
                  exit;
 
                }
                ### FIM BTV ###
                if($vasProfile == "VAS_IPTV")
                {
                  echo $_SESSION['menssagem'] = "Cadastrado";

                  $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                    VALUES ('$serial Cadastrado com o serviço $vasProfile 
                    informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                    Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                    MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                    Senha Telefone: $telPass',$usuario)";
                  $executa_log = mysqli_query($conectar,$sql_insert_log);
                  
                  //Atualizar Porta CTO
                  $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 1, serial = '$serial'
                    WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
                  $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
                  //Fim Atualizar Porta CTO

                  header("Location: ../ont_classes/ont_register.php");
                  mysqli_close($conectar_radius);
                  mysqli_close($conectar);
                  exit;
                }
              }//fim service port iptv
              ##### IPTV SERVICE PORT ######
            }
            
            ###INICIO TELEFONIA TL1###
            if($vasProfile == "VAS_Internet-VoIP" || $vasProfile == "VAS_Internet-VoIP-IPTV" || $vasProfile == 'VAS_IPTV-VoIP' 
              || $vasProfile == "VAS_Internet-VoIP-REAL" || $vasProfile == "VAS_Internet-VoIP-IPTV-REAL" 
              || $vasProfile == "VAS_Internet-twoVoIP-IPTV" || $vasProfile == "VAS_Internet-twoVoIP-IPTV-REAL" || $vasProfile == "VAS_Internet-twoVoIP-REAL"
              || $vasProfile == "VAS_IPTV-twoVoIP" ) //ATIVAR TELEFONIA
            {
              //echo "\n <br><br> DEV: $deviceName | $frame | $slot | $pon | $onuID | $telNumber | $telPass | $telNumber <br><br> \n";
              if( $telNumber2 == 0 && $telPass2 == 0)
                $telefone_on = ativa_telefonia($deviceName,$frame,$slot,$pon,$onuID,$telNumber,$telPass,$telNumber);
              else
                $telefone_on = ativa_telefonia($deviceName,$frame,$slot,$pon,$onuID,$telNumber,$telPass,$telNumber,$telNumber2,$telPass2,$telNumber2);
              echo $telefone_on;
              $tira_ponto_virgula = explode(";",$telefone_on);
              $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
              $remove_desc = explode("ENDESC=",$check_sucesso[1]);
              $errorCode = trim($remove_desc[0]);
              if($errorCode != "0") //se der erro na ativacao da telefonia
              {
                $trato = tratar_errors($errorCode);

                echo $_SESSION['menssagem'] = "Houve erro ao inserir no u2000: $trato";
                $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
                mysqli_query($conectar,$sql_apagar_onu);

                $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                  VALUES ('Erro ao ativar Serviço de Telefonia $trato 
                  informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                  Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                  MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                  Senha Telefone: $telPass','$usuario')";
                $executa_log = mysqli_query($conectar,$sql_insert_log);

                header('Location: ../ont_classes/ont_register.php');
                mysqli_close($conectar_radius);
                mysqli_close($conectar);
                exit;
              }else{
                  ## INICIO SERVICE PORT TELEFONE ##
                $servicePortTelefone = get_service_port_telefone($deviceName,$frame,$slot,$pon,$onuID,$contrato);

                $tira_ponto_virgula = explode(";",$servicePortTelefone);
                $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
                //print_r($check_sucesso);echo "<br><br>";
                $remove_desc = explode("ENDESC=",$check_sucesso[1]);
                //print_r($remove_desc);
                $errorCode = trim($remove_desc[0]);
                if($errorCode != "0") //se der erro na service port telefone
                {
                  $trato = tratar_errors($errorCode);
                  echo $_SESSION['menssagem'] = "Houve erro Inserir a Service Port Telefonia: $trato";
    
                  //se der erro ele irá apagar o registro salvo na tabela local ont
                  $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
                  mysqli_query($conectar,$sql_apagar_onu);
  
                  $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial@vertv' 
                    AND attribute='Huawei-Qos-Profile-Name' ";
                  $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);
  
                  $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial@vertv' ";
                  $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
                
                  deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID,$ip_olt,$servicePortIPTV);

                  $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
                    VALUES ('Erro ao Registrar o ServicePort de Telefone $trato' 
                    informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                    Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                    MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                    Senha Telefone: $telPass,'$usuario')";
                  $executa_log = mysqli_query($conectar,$sql_insert_log);
                  
                  header('Location: ../ont_classes/ont_register.php');
                  mysqli_close($conectar_radius);
                  mysqli_close($conectar);
                  exit;
                }else{
                  $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
                  $pegar_servicePortTel_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
                  
                  $pega_id = explode("	",$pegar_servicePortTel_ID[2]);//posicao 4 será sempre o ONTID
                  
                  $servicePortTelefoneID= $pega_id[0] - 1; 
                  
                  $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
                    VALUES ('ServicePort Telefone Cadastrada 
                    informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                    Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                    MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                    Senha Telefone: $telPass','$usuario')";
                  $executa_log = mysqli_query($conectar,$sql_insert_log);

                  $insere_service_telefone = "UPDATE ont SET service_port_telefone='$servicePortTelefoneID' WHERE serial = '$serial'";
                  $executa_insere_service_telefone = mysqli_query($conectar,$insere_service_telefone);
                  
                }//fim service port telefonia
                if( $vasProfile == 'VAS_IPTV-VoIP' || $vasProfile == 'VAS_IPTV-twoVoIP') // se for apenas IPTV e VOIP termina aqui
                {
                  echo $_SESSION['menssagem'] = "Cadastrado IPTV e Telefone";

                  $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                    VALUES ('$serial Cadastrado com o serviço $vasProfile 
                    informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                    Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                    MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                    Senha Telefone: $telPass',$usuario)";
                  $executa_log = mysqli_query($conectar,$sql_insert_log);
                  
                  //Atualizar Porta CTO
                  $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 1, serial = '$serial'
                    WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
                  $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
                  //Fim Atualizar Porta CTO

                  header("Location: ../ont_classes/ont_register.php");
                  mysqli_close($conectar_radius);
                  mysqli_close($conectar);
                  exit;
                }
              }
            } //FIM ATIVA TELEFONIA  
            ########FIM TL1########

            
            
            $servicePortInternet = get_service_port_internet($deviceName,$frame,$slot,$pon,$onuID,$contrato,$vasProfile,$modo_bridge);

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

              $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial@vertv' 
                AND attribute='Huawei-Qos-Profile-Name' ";
              $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

              $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial@vertv' ";
              $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
              
              deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID,$ip_olt,$servicePortIPTV);

              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                VALUES ('Erro ao Registrar o ServicePort de Internet $trato
                informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass','$usuario')";
              $executa_log = mysqli_query($conectar,$sql_insert_log);
              
              header('Location: ../ont_classes/ont_register.php');
              mysqli_close($conectar_radius);
              mysqli_close($conectar);
              exit;
            }else{
              $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
              $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
              //print_r($pegar_servicePorta_ID);
              $pega_id = explode("	",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
              
              $servicePortInternetID= $pega_id[0] - 1; 
              
              $insere_service_internet = "UPDATE ont SET service_port_internet=$servicePortInternetID WHERE serial = '$serial'";
              $executa_insere_service_internet = mysqli_query($conectar,$insere_service_internet);
              echo $_SESSION['menssagem'] = "Cadastrado";

              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                VALUES ('$serial Cadastrado com o serviço $vasProfile 
                informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass','$usuario')";
              $executa_log = mysqli_query($conectar,$sql_insert_log);

              //Atualizar Porta CTO
              $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 1, serial = '$serial'
                WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
              $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
              
              

              if(($ip_fixo != "NULL") && ($mac != "NULL"))
              {
                $sql_atualiza_ip_fixo = "UPDATE ont SET mac='$mac',ip='$ip_fixo' WHERE serial='$serial'";
                $executa_atualiza_ip_fixo = mysqli_query($conectar,$sql_atualiza_ip_fixo);

                $sql_atualiza_utilizado_ip = "UPDATE ips_valido SET utilizado=true,utilizado_por='$contrato',mac_serial='$mac'
                 WHERE numero_ip ='$ip_fixo'";
                $executa_atualiza_utitlizado_ip = mysqli_query($conectar,$sql_atualiza_utilizado_ip);
              }
              
              header('Location: ../ont_classes/ont_register.php');
              // fim Atualizar Porta CTO
              mysqli_close($conectar_radius);
              mysqli_close($conectar);
              exit;
            }//fim service port internet
          }
        }else{
          $erro = mysqli_error($conectar_radius);
          echo $_SESSION['menssagem'] = "Houve erro ao inserir no Radius SQL: $erro";

            //se der erro ele irá apagar o registro salvo na tabela local ont
          $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
          mysqli_query($conectar,$sql_apagar_onu); 

          header('Location: ../ont_classes/ont_register.php');
          mysqli_close($conectar_radius);
          mysqli_close($conectar);
          exit;
        }
      }else{
        $erro = mysqli_error($conectar);
        echo $_SESSION['menssagem'] = "Houve erro na execuão da query SQL: $erro";
        header('Location: ../ont_classes/ont_register.php');
        mysqli_close($conectar_radius);
        mysqli_close($conectar);
        exit;
      }
   }
   else
   {
       echo $_SESSION['menssagem'] = "Campos Faltando!";
       header('Location: ../ont_classes/ont_register.php');
       mysqli_close($conectar_radius);
       mysqli_close($conectar);
       exit;
   }
}else{
  echo $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
  header('Location: ../ont_classes/ont_register.php');
  mysqli_close($conectar_radius);
  mysqli_close($conectar);
  exit;
}
?>
