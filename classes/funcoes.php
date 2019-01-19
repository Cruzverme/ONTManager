<?php 

  include_once "../u2000/tl1_sender.php";
  

  function checar_contrato($contrato)
  {
    $json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_contrato_status_ftth_cplus.php?contra=$contrato");
    $json_str = json_decode($json_file, true);
    
    $contratoInterno = $json_str['contrato'];
    
    if(empty($contratoInterno))
    {
      //caso contrato esteja com ponto cancelado porem [e uma reconexao]
      $json_file_segunda = file_get_contents("http://192.168.80.5/sisspc/demos/get_contrato_status_ftth_reconexao.php?contra=$contrato");
      $json_str_segunda = json_decode($json_file_segunda,true);

      $contratoInterno2 = $json_str_segunda['contrato'];
      if(empty($contratoInterno2))
      {
        return $contrato; //null
      }else{
        return 'ok';
      }
    }else{
      return 'ok';
    }
  }

  function reiniciaONT($deviceName,$framePar,$slotPar,$ponPar,$ontIDPar)
  {
    $reseta_equipamento = reseta_ont($deviceName,$framePar,$slotPar,$ponPar,$ontIDPar);

    $tira_ponto_virgula = explode(";",$reseta_equipamento);
    $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
    $remove_desc = explode("ENDESC=",$check_sucesso[1]);
    $errorCode = trim($remove_desc[0]);
    
    return $errorCode;
  }

  function deu_ruim_callback($device,$frame,$slot,$pon,$contrato,$nomeCompleto,$cto,$porta_atendimento,$serial,$equipment,$vasProfile,$telNumber,$telPass,$pacote)
  {
    include "../db/db_config_mysql.php";
    include "../db/db_config_radius.php";

    echo "<br>DEU RUIM MANOOWW!! TO CONSERTADNO!</br>";
    $ontID = cadastrar_ont($device,$frame,$slot,$pon,$contrato,$nomeCompleto,$cto,$porta_atendimento,$serial,$equipment,$vasProfile);
    $onuID = NULL; //zera ONUID para evitar problema de cash.
    
    $tira_ponto_virgula = explode(";",$ontID);
    $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
    $remove_desc = explode("ENDESC=",$check_sucesso[1]);
    $errorCode = trim($remove_desc[0]);
    if($errorCode != "0") // se der erro ao recadastrar a ONT
    {
      echo "Erro ao ";
    }else{ //Se Ele Cadastrar a ONT
      $remove_barras_para_pegar_id = explode("---------------------------",$tira_ponto_virgula[1]);
      $filtra_espaco = explode("\r\n",$remove_barras_para_pegar_id[1]);
      $pega_id = preg_split('/\s+/',$filtra_espaco[2]);
      $onuID=trim($pega_id[4]);
      echo "ONU ID: $onuID"; 
      $insere_ont_id = "UPDATE ont SET ontID='$onuID', perfil='$vasProfile',service_port_internet=NULL,service_port_telefone=NULL,service_port_iptv=NULL WHERE serial = '$serial'";
      $executa_insere_ont_id = mysqli_query($conectar,$insere_ont_id);
      ######### Fim Cadastro de OLT #############

      ######### APAGA O RADIUS e ONT PARA DPS CRIAR NOVAMENTE #############
      
      $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial@vertv'
                                AND attribute='Huawei-Qos-Profile-Name' ";
      $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

      $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial@vertv' ";
      $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
      
      ########### FIM APAGA RADIUS e ONT##############

      $atualiza_infosONT = "UPDATE ont SET perfil='$vasProfile' WHERE serial = '$serial'";
      $executa_atualiza_infosONT = mysqli_query($conectar,$atualiza_infosONT);

    ############ SE INTERNET #################

      if($vasProfile == "VAS_Internet" || $vasProfile == "VAS_Internet-VoIP" || $vasProfile == "VAS_Internet-IPTV" 
          || $vasProfile == "VAS_Internet-VoIP-IPTV") // se somente internet
      {
        ############ INSERE RADIUS ############
        echo "<br>ID: $onuID </br>";
        $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
              VALUES ( '2500/$slot/$pon/$serial@vertv', 'User-Name', ':=', '2500/$slot/$pon/$serial@vertv' )";

        $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value) 
              VALUES ( '2500/$slot/$pon/$serial@vertv', 'User-Password', ':=', 'vlan' )";

        $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
              VALUES ( '2500/$slot/$pon/$serial@vertv', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";

        $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
        $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);
        $executa_query_qos_profile= mysqli_query($conectar_radius,$insere_ont_radius_qos_profile);
        echo "<br>Atualizei no RAdius<br>"; 
        ########## FIM INSERE RADIUS ##############
          
        ##### CRIA SERVIEC PORT INTERNET #####
        echo "DEVICE: $device, FRAME:  $frame, SLOT: $slot, PON: $pon, ONUID: $onuID, CONTRA: $contrato";
        $servicePortInternet = get_service_port_internet($device,$frame,$slot,$pon,$onuID,$contrato,$vasProfile,$modo = null);
        var_dump($servicePortInternet);
        $tira_ponto_virgula = explode(";",$servicePortInternet);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        if($errorCode != "0"){ //se der erro ao pegar service port
          echo $_SESSION['menssagem'] = "Houve erro Inserir a Service Port de Internet: $errorCode $trato";
        }else{ // se nao der erro
          echo "<br>Nao deu erro na serv port Internet</br>";
          $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
          $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
          
          $pega_id = explode("  ",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
          
          $servicePortInternetID= $pega_id[0] - 1; 
          
          $insere_service_internet = "UPDATE ont SET service_port_internet='$servicePortInternetID' WHERE serial = '$serial'";
          $executa_insere_service_internet = mysqli_query($conectar,$insere_service_internet);
          
          var_dump($executa_insere_service_internet);
          echo "<br>Tenho uma ServicePort Internet e 
            Estou saindo de $vasProfile e irei evoluir paraaaaa $vasProfile mon!";
          echo "<br>criei SP INTERNET: $servicePortInternetID e ID: $onuID";
          echo "<br>Atualizei No BD";
          echo "<br>JOGUEI NO RADIUS";
          echo "<br>VOU FUNFAR<br>";
          return $_SESSION['menssagem'] = "Plano RETORNADO! Em caso de alteração de Velocidade: Consulte o Equipamento e Reinicie Para efetivar a mudança";            
          
        }
      }
      echo "<br><br> TELEFONE </br></br>";
      ######### SE VOIP #########
      if($vasProfile == "VAS_Internet-VoIP" || $vasProfile == "VAS_Internet-VoIP-IPTV" || $vasProfile == "VAS_Internet-VoIP-IPTV" )
      {
        echo "<br>Telfone Estou saindo de $vasProfile e irei evoluir paraaaaa $vasProfile mon!<br>";
        
        ########## ATIVA TL1 ############
        $telefone_on = ativa_telefonia($device,$frame,$slot,$pon,$onuID,$telNumber,$telPass,$telNumber);
        $tira_ponto_virgula = explode(";",$telefone_on);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        
        if($errorCode != "0") // se der erro na ativacao da telefonia
        {
            $trato = tratar_errors($errorCode);
            $_SESSION['menssagem'] = "Houve erro ao inserir no u2000: $trato";
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

              $_SESSION['menssagem'] = "Houve erro Inserir a Service Port Telefonia: $trato";
            }else{
              
              $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
              $pegar_servicePortTel_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
              
              $pega_id = explode("  ",$pegar_servicePortTel_ID[2]);//posicao 4 será sempre o ONTID
              
              $servicePortTelefoneID= $pega_id[0] - 1;
              echo "<br>criei SP VOIP $servicePortTelefoneID";
              echo "<br>Linha $telNumber com Senha $telPass</br>";
              $insere_service_telefone = "UPDATE ont SET service_port_telefone='$servicePortTelefoneID',tel_user='$telNumber',tel_number='$telNumber',tel_password='$telPass'
               WHERE serial = '$serial'";
              $executa_insere_service_telefone = mysqli_query($conectar,$insere_service_telefone);
              var_dump($insere_service_telefone);
              var_dump($executa_insere_service_telefone);
              echo "<br>VOU FUNFAR<br>";
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
        }else{
          $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
          $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
          
          $pega_id = explode("  ",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
          
          $servicePortIptvID= $pega_id[0] - 1;
          echo "<br>criei a SP IPTV $servicePortIptvID";
          
          $insere_service_iptv = "UPDATE ont SET service_port_iptv='$servicePortIptvID' WHERE serial = '$serial'";
          $executa_insere_service_iptv = mysqli_query($conectar,$insere_service_iptv);
          echo "<br>Atualizei No BD";
          
          ### BTV ###
          $btv_olt = insere_btv_iptv($ip,"$servicePortIptvID");
          var_dump($btv_olt);
          if($btv_olt != 'valido' )
          {
            echo "NAO Registra BTV";
          }else{
            echo "<br>VOU FUNFAR";  
          }
        }
      }
      
    }//fim cadastrar
  }// fim deletar
  
  function get_nome_alias_cplus($contrato)
  {
    //pega o Alias do assinante
    $json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_pacote_ftth_cplus.php?contra=$contrato");
    $json_str = json_decode($json_file, true);
    $itens = $json_str['velocidade'];
    $nome = $json_str['nome'];
    $nomeCompleto = str_replace(" ","_",$nome[0]);
    return $nomeCompleto;
    //fim alias
  }

?>