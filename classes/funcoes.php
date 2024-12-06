<?php 

  include_once "/var/www/html/ontManager/u2000/tl1_sender.php";
  
  require '/var/www/html/ontManager/vendor/autoload.php'; //autoload do projeto

  use PhpOffice\PhpSpreadsheet\Spreadsheet; //classe responsável pela manipulação da planilha
  use PhpOffice\PhpSpreadsheet\Writer\Xlsx; //classe que salvará a planilha em .xlsx

  function checar_contrato($contrato)
  {
    $json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_contrato_status_ftth_cplus.php?contra=$contrato");
    
   // $json_teste = json_encode($json_file, true);
   // $json_str = json_decode($json_teste, true);

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
        return null; //null
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
      echo "Erro ao $errorCode";
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
              VALUES ( '2500/$slot/$pon/$serial@vertv', 'Cleartext-Password', ':=', 'vlan' )";

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
    //$json_teste = json_encode($json_file, true) ;
   // $json_str = json_decode($json_teste, true);   
    $json_str = json_decode($json_file, true);
    $itens = $json_str['velocidade'];
    $nome = $json_str['nome'];
    $nomeCompleto = str_replace(" ","_",$nome[0]);
    return $nomeCompleto;
    //fim alias
  }
  $testInv = 58004;
  function cadastrar_ont_func($device,$frame,$slot,$pon,$contrato,$nomeCompleto,$cto,$porta_atendimento,$serial,$equipment,$vasProfile,$telNumber,$telPass,$pacote,$usuario)
  {
    include "../db/db_config_mysql.php";
    include "../db/db_config_radius.php";

    $sql_registra_onu = ("INSERT INTO ont (contrato, serial, cto, tel_number, tel_user, tel_password, perfil, pacote, usuario_id,equipamento,porta)
                              VALUES ('$contrato','$serial','$cto','$telNumber','$telNumber','$telPass','$vasProfile','$pacote','$usuario','$equipment','$porta_atendimento')" );

    $cadastrar = mysqli_query($conectar,$sql_registra_onu);

    if($cadastrar)
    {
      echo "CADASTREI DISGRACAA NO BANCO ME ASSALTA!!!!!";
      echo "<br>DEU RUIM MANOOWW!! TO CONSERTADNO!</br>";
      $ontID = cadastrar_ont($device,$frame,$slot,$pon,$contrato,$nomeCompleto,$cto,$porta_atendimento,$serial,$equipment,$vasProfile);
      $onuID = NULL; //zera ONUID para evitar problema de cash.
      
      $tira_ponto_virgula = explode(";",$ontID);
      $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
      $remove_desc = explode("ENDESC=",$check_sucesso[1]);
      $errorCode = trim($remove_desc[0]);
      if($errorCode != "0") // se der erro ao recadastrar a ONT
      {
        echo "Erro ao $errorCode";
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
                VALUES ( '2500/$slot/$pon/$serial@vertv', 'Cleartext-Password', ':=', 'vlan' )";

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
  }

  function converteDataOracleMySQL($data)
  {
    list($dia,$mes,$ano) = explode('-',$data);
    switch($mes)
    {
      case 'JAN': $mes = '01';break;
      case 'FEB': $mes = '02';break;
      case 'MAR': $mes = '03';break;
      case 'APR': $mes = '04';break;
      case 'MAY': $mes = '05';break;
      case 'JUN': $mes = '06';break;
      case 'JUL': $mes = '07';break;
      case 'AUG': $mes = '08';break;
      case 'SEP': $mes = '09';break;
      case 'OCT': $mes = '10';break;
      case 'NOV': $mes = '11';break;
      case 'DEC': $mes = '12';break;
    }
    return "$dia/$mes/$ano";
  }

  function converteDataMySQLOracle($data)
  {
    list($dia,$mes,$ano) = explode('/',$data);
    switch($mes)
    {
      case '01': $mes = 'JAN';break;
      case '02': $mes = 'FEB';break;
      case '03': $mes = 'MAR';break;
      case '04': $mes = 'APR';break;
      case '05': $mes = 'MAY';break;
      case '06': $mes = 'JUN';break;
      case '07': $mes = 'JUL';break;
      case '08': $mes = 'AUG';break;
      case '09': $mes = 'SEP';break;
      case '10': $mes = 'OCT';break;
      case '11': $mes = 'NOV';break;
      case '12': $mes = 'DEC';break;
    }
    return "$dia-$mes-$ano";
  }

  function send_to_block_unblock($motivo,$contrato,$serial)
  {
    $cmd = "curl -F 'motivo=$motivo' -F 'contrato=$contrato' -F 'serial=$serial' http://localhost/ontManager/classes/gerencia_bloqueios.php";
    $result = shell_exec($cmd);

#    return $result;
    
    if($result)
      return 1;
    else
      return $result;
    
  }

  function send_to_cancel($contrato,$serial)
  {
    $cmd = "curl -F 'contrato=$contrato' -F 'serial=$serial' http://localhost/ontManager/classes/gerencia_cancelamento.php";
    $result = shell_exec($cmd);
   
    if($result)
      return 1;
    else
      return $result;
    
  }

  function send_email($assunto,$corpoEmail,$destinatario,$nomeDestinatario,$arquivo = NULL,$secondDestinatario = NULL)
  {
    include "/var/www/html/ontManager/auth/autenticacoes.php";
    
    // Inclui o arquivo class.phpmailer.php localizado na mesma pasta do arquivo php 
    include "/var/www/html/ontManager/lib/mailer/PHPMailerAutoload.php";

    // Inicia a classe PHPMailer 
    $mail = new PHPMailer(); 
    
    // Método de envio 
    $mail->IsSMTP(); 
    
    // Enviar por SMTP 
    $mail->Host = $host_email;

    // Você pode alterar este parametro para o endereço de SMTP do seu provedor 
    $mail->Port = $snmp_port; 
    
    // Usar autenticação SMTP (obrigatório) 
    $mail->SMTPAuth = true;

    // Usuário do servidor SMTP (endereço de email) 
    // obs: Use a mesma senha da sua conta de email 
    $mail->Username = $usuario_email; 
    $mail->Password = $senha_email; 

    // Configurações de compatibilidade para autenticação em TLS 
    $mail->SMTPOptions = array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true ) ); 

    // Você pode habilitar esta opção caso tenha problemas. Assim pode identificar mensagens de erro. 
    // $mail->SMTPDebug = 2; 
    
    // Define o remetente 
    // Seu e-mail 
    $mail->From = "bloqueadorcliente@vertv.com.br"; 
    
    // Seu nome 
    $mail->FromName = "VERTV"; 
    
    // Define o(s) destinatário(s) 
    $mail->AddAddress($destinatario, $nomeDestinatario);
    
    $secondDestinatario != NULL? $mail->AddAddress($secondDestinatario) : "";
    // Opcional: mais de um destinatário
    // $mail->AddAddress('fernando@email.com'); 
    
    // Opcionais: CC e BCC
    // $mail->AddCC('joana@provedor.com', 'Joana'); 
    // $mail->AddBCC('roberto@gmail.com', 'Roberto'); 
    
    // Definir se o e-mail é em formato HTML ou texto plano 
    // Formato HTML . Use "false" para enviar em formato texto simples ou "true" para HTML.
    $mail->IsHTML(true); 
    
    // Charset (opcional) 
    $mail->CharSet = 'UTF-8'; 
    
    // Assunto da mensagem 
    $mail->Subject = $assunto;
    
    // Corpo do email 
    $mail->Body = $corpoEmail;

    if($arquivo != NULL)
    {
      $data = date('dmY');
      
      if($nomeDestinatario == "Cancelamento")
      {
        file_put_contents("/var/www/html/ontManager/public/planilhaCancelados$data.xls", $corpoEmail);
        // Opcional: Anexos 
        $mail->AddAttachment("/var/www/html/ontManager/public/planilhaCancelados$data.xls", "documento_de_clientes__cancelados_fibra.xls");
      }else{
        create_xls();
        // Opcional: Anexos 
        $mail->AddAttachment("/var/www/html/ontManager/public/planilha$data.xlsx", "documento_de_clientes_fibra.xlsx");
      }
      
    }

    // Envia o e-mail 
    $enviado = $mail->Send(); 
    
    // Exibe uma mensagem de resultado 
    if ($enviado) 
      echo "Seu email foi enviado com sucesso!"; 
    else
      echo "Houve um erro enviando o email: ".$mail->ErrorInfo;
  }

  
  function create_xls()
  {
    include "/var/www/html/ontManager/db/db_config_mysql.php";

    $sql = "SELECT contrato,inadimplente,nome,dataVencimento,serial,criado_em FROM blocked_costumer_daily";
    $exec_sql = mysqli_query($conectar,$sql);

    $spreadsheet = new Spreadsheet(); //instanciando uma nova planilha
    $sheet = $spreadsheet->getActiveSheet(); //retornando a aba ativa
    $sheet->getStyle('A1:E1')->getFont()->setBold(true);
    $sheet->setTitle('Bloqueados'); //define titulo da aba
    
    #### Titulos da ABA Bloqueado
    $sheet->setCellValue('A1', 'Contrato'); //Definindo a célula A1
    $sheet->setCellValue('B1', 'Nome'); //Definindo a célula B1
    $sheet->setCellValue('C1', 'Data Bloqueio');
    $sheet->setCellValue('D1', 'Status');
    $sheet->setCellValue('E1', 'Serial');

    $total_linhas = mysqli_num_rows($exec_sql) + 1;
  
    ####### Body dos Bloqueados
    $celula = 2;
    
    while($rowBlocked = mysqli_fetch_array($exec_sql,MYSQLI_NUM))
    {
      while($celula <= $total_linhas)
      {
        $sheet->setCellValue("A$celula",$rowBlocked[0]);
        $sheet->setCellValue("B$celula",$rowBlocked[2]);
        $sheet->setCellValue("C$celula","$rowBlocked[5]");
        $sheet->setCellValue("D$celula",'Bloqueado');
        $sheet->setCellValue("E$celula","$rowBlocked[4]");
        break;
      }
      $celula+=1;
    }

    ########### ABA DESBLOQUEADOS #############
    $sql_unblock = "SELECT contrato,nome,status,serial,desbloqueado_em FROM unblocked_costumer";
    $exec_sql_unblock = mysqli_query($conectar,$sql_unblock);
    $total_linhas_unblock = mysqli_num_rows($exec_sql_unblock) + 1;

    $spreadsheet->createSheet();
    
    $spreadsheet->setActiveSheetIndex(1);
    $sheet2 = $spreadsheet->getActiveSheet();
    
    $sheet2->getStyle('A1:E1')->getFont()->setBold(true);
    $sheet2->setTitle('Desbloqueados'); //define titulo da aba

    #### Titulos da ABA Bloqueado
    $sheet2->setCellValue('A1', 'Contrato'); //Definindo a célula A1
    $sheet2->setCellValue('B1', 'Nome'); //Definindo a célula B1
    $sheet2->setCellValue('C1', 'Status');
    $sheet2->setCellValue('D1', 'Serial');
    $sheet2->setCellValue('E1', 'Data Desloqueio');

    ####### Body dos Desbloqueados
    $celula = 2;

    while($rowUnblocked = mysqli_fetch_array($exec_sql_unblock,MYSQLI_NUM))
    {
      while($celula <= $total_linhas_unblock)
      {
        $sheet2->setCellValue("A$celula",$rowUnblocked[0]);
        $sheet2->setCellValue("B$celula",$rowUnblocked[1]);
        $sheet2->setCellValue("C$celula","$rowUnblocked[2]");
        $sheet2->setCellValue("D$celula",'Desbloqueado');
        $sheet2->setCellValue("E$celula","$rowUnblocked[4]");
        break;
      }
      $celula+=1;
    }
    
    $spreadsheet->setActiveSheetIndex(0);
    $data = date('dmY');
    
    $writer = new Xlsx($spreadsheet); //Instanciando uma nova planilha
    $writer->save("/var/www/html/ontManager/public/planilha$data.xlsx"); //salvando a planilha na extensão definida
  }

  function getL2LInformationByName(string $l2lName)
  {
    include "../db/db_config_mysql.php";

    if ($l2lName) {
      $sqlLanLanInfo = "SELECT vas_profile, line_profile, service_profile, gem_ports FROM lan_lan WHERE name = '$l2lName'";

      $result = $conectar->query($sqlLanLanInfo);

      if ($result->num_rows > 0) {
        $rows = $result->fetch_all(MYSQLI_NUM);
        return $rows[0];
      }
    }
    return [];
  }

  function isContractBlockedToChanges(int $contract)
  {
      include "../db/db_config_mysql.php";

      $sqlIsBloqued = "SELECT contract FROM customer_change_blocked WHERE contract = $contract";
      $result = $conectar->query($sqlIsBloqued);

      if ($result->num_rows > 0) {
        return true;

      }
      return false;
  }

  function getAcronymMeaning($acronym) {
    $meanings = [
        'ONUDEL' => 'O ONT foi excluído.',
        'ONTDISCONNECT' => 'O ONT está desconectado.',
        'LOSI' => 'Possível problema de fibra.',
        'LOFI' => 'Perda de frames - Interrupção temporária.',
        'SFI' => 'Falha no sinal (sinal fraco ou comprometido).',
        'LOAI' => 'Falha no reconhecimento entre ONU e OLT.',
        'LOAMI' => 'Perda de mensagens de gerenciamento PLOAM.',
        'DACT FAILURE' => 'Falha na Desativação.',
        'DACT' => 'Desativada.',
        'RESET' => 'Reinicialização.',
        'RE-REGISTER' => 'Re-registro da ONT.',
        'POPUP FAILURE' => 'Falha de Ativação.',
        'AUTHOR FAILURE' => 'A autenticação da ONT falha.',
        'DYING-GASP' => 'Energia Elétrica.',
        'OFF-LINE FAILURE' => 'O ONT não consegue ficar offline.',
        'DEACTIVE BECAUSE OF RING' => 'Desativada por estar sendo afetada por Loop.'
    ];

    if (array_key_exists($acronym, $meanings)) {
      return $meanings[$acronym];
    }

    return $acronym;
  }
?>
