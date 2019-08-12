<?php 
  
  include_once "../db/db_config_mysql.php";
  include_once "../db/db_config_radius.php";
  include_once "../u2000/tl1_sender.php";
  // Inicia sessões 
  session_start();

  $nome = filter_input(INPUT_POST,'nome');
  $vasProfile = filter_input(INPUT_POST,'vasProfile');
  $serial_number = filter_input(INPUT_POST,'serial');
  $pacote_internet = filter_input(INPUT_POST,'pacote_internet');
  $modelo_ont = filter_input(INPUT_POST,'modelo_ont');
  $sip_number = filter_input(INPUT_POST,'sip_number');
  $sip_password = filter_input(INPUT_POST,'sip_password');
  $usuario = $_SESSION["id_usuario"];

  $porta_selecionado = filter_input(INPUT_POST,'porta_atendimento');
  $frame = filter_input(INPUT_POST,'frame');
  $slot = filter_input(INPUT_POST,'slot');
  $pon = filter_input(INPUT_POST,'pon');
  $cto = filter_input(INPUT_POST,'cto');
  $device = filter_input(INPUT_POST,'device');
  $contrato = filter_input(INPUT_POST,'contrato');
  $designacao = filter_input(INPUT_POST,'designacao');
  $vlan_associada = filter_input(INPUT_POST,'vlan_number');

  $internet = filter_input(INPUT_POST,"internet_check");
  $lanToLan = filter_input(INPUT_POST,"vlan_check");
  $iptv = filter_input(INPUT_POST,"iptv");
  $voip = filter_input(INPUT_POST,"voip");
  $modo_bridge = filter_input(INPUT_POST,'modo_bridge');

  ## ALIAS DO ASSINANTE PARA U2000
  $nomeAlias = str_replace(" ","_",$nome);

  ## CODIGO TAVA AKI
  $array_process_result = [];

############ CHECA O LIMITE DE ONT NO CLIENTE #############

  $sql_verifica_limite = "SELECT limite_equipamentos FROM ont WHERE contrato='$contrato'";
  $sql_limite_result = mysqli_query($conectar,$sql_verifica_limite);

  $limite_registro = "";
  
  while ($limite = mysqli_fetch_array($sql_limite_result, MYSQLI_BOTH)) 
  {
    $limite_registro = $limite['limite_equipamentos'];
  }

  if ($limite_registro < 1 AND $limite_registro != null) 
  {
    array_push($array_process_result,"Favor, entrar em contato com o TI, para solicitar aumento de registro de equipamentos");

    ##### FECHA AS CONEXOES COM OS BANCOS #####
    mysqli_close($conectar_radius);
    mysqli_close($conectar);
    
    echo "Ocorreu um Erro \r";
    foreach($array_process_result as $result)
    {
      echo "$result \r";
    }
    exit;
  }

############### VERIFICA O MAC SE JA FOI CADASTRADO ################
  $sql_verifica_limite_ont = "SELECT serial,contrato FROM ont WHERE  serial = '$serial_number' LIMIT 1"; //verifica se ja existe o mac
  $executa_verifica_limite_ont = mysqli_query($conectar,$sql_verifica_limite_ont);
  
  if(mysqli_num_rows($executa_verifica_limite_ont) > 0) //se o resultado do limite for 1 ele cai aqui
  {
    $limiteONT = mysqli_fetch_array($executa_verifica_limite_ont, MYSQLI_BOTH);
    array_push($array_process_result,"MAC Já Cadastrado no contrato $limiteONT[contrato]");

    ##### FECHA AS CONEXOES COM OS BANCOS #####
    mysqli_close($conectar_radius);
    mysqli_close($conectar);
    
    echo "Ocorreu um erro \r";
    foreach($array_process_result as $result)
    {
      echo "$result \r";
    }
    exit;
  }

  ########## VERIFICA SE O CHECKBOX DOS SERVIÇOS FOI MARCADO  ################
  if(($internet != "Internet" && $lanToLan != "l2l" && $iptv != "IPTV" && $voip != "Telefone") )
  {
    array_push($array_process_result,"Nenhum Serviço Selecionado");
  }else{
################### CADASTRA A ONT NO BANCO LOCAL ######################
    $sql_registra_onu = ("INSERT INTO ont (contrato, serial, cto, tel_number, tel_user, tel_password, perfil, pacote, usuario_id,equipamento,porta)
      VALUES ('$contrato','$serial_number','$cto','$sip_number','$sip_number','$sip_password','$vasProfile','$pacote_internet','$usuario','$modelo_ont','$porta_selecionado')" );

    $cadastrar = mysqli_query($conectar,$sql_registra_onu);

    if($cadastrar)
    {
      array_push($array_process_result,"Cadastrado no Banco Local");

      $sql_atualiza_limite = "UPDATE ont SET limite_equipamentos=0 WHERE contrato = $contrato";
      $diminui_limite = mysqli_query($conectar,$sql_atualiza_limite);

      ####### CADASTRA A ONT NO U2000 ############
      $ontID = cadastrar_ont($device,$frame,$slot,$pon,$contrato,$nomeAlias,$cto,$porta_selecionado,$serial_number,$modelo_ont,$vasProfile,null,$designacao);
      
      $onuID = NULL; //zera ONUID para evitar problema de cash.
      
      sleep(1); //dorme para processar

      $tira_ponto_virgula = explode(";",$ontID);
      $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
      $remove_desc = explode("ENDESC=",$check_sucesso[1]);
      $errorCode = trim($remove_desc[0]);
      if($errorCode != "0")
      {
        $trato = tratar_errors($errorCode);
        array_push($array_process_result,"!!!! Houve erro ao inserir a ONT no u2000: $trato !!!!");

        //se der erro ele irá apagar o registro salvo na tabela local ont
        $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
        mysqli_query($conectar,$sql_apagar_onu);

        array_push($array_process_result,"Removido do Banco Local!");
      }else{
        array_push($array_process_result,"ONT Adicionada ao U2000!");

      ########## PEGANDO ID DA ONT PARA SALVAR ############
        $remove_barras_para_pegar_id = explode("---------------------------",$tira_ponto_virgula[1]);
        $filtra_espaco = explode("\r\n",$remove_barras_para_pegar_id[1]);
        $pega_id = explode("	",$filtra_espaco[2]);//posicao 4 será sempre o ONTID
        $onuID=trim($pega_id[4]);

        $insere_ont_id = "UPDATE ont SET ontID='$onuID' WHERE serial = '$serial_number'";
        $executa_insere_ont_id = mysqli_query($conectar,$insere_ont_id);

        array_push($array_process_result,"Inserido ID da ONT!");

      #### SELECT OLT IP ####
        $sql_pega_olt_ip = "SELECT olt_ip FROM pon WHERE deviceName='$device'";
        $executa_pega_olt_ip = mysqli_query($conectar,$sql_pega_olt_ip);
        while ($ip = mysqli_fetch_array($executa_pega_olt_ip, MYSQLI_BOTH))
        {
          $ip_olt = $ip['olt_ip'];
        }

    ############ INICIO DA ATIVACAO DOS SERVIÇOS #######
    
    ##################################### L A N t o L A N ##############################################
    
        if($lanToLan == "l2l")
        {
          array_push($array_process_result,"### LAN TO LAN ###");

        ######## ADICIONA A ONT NO RADIUS PARA PEGAR BANDA E IP ########
          $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
              VALUES ( '2500/$slot/$pon/$serial_number@vertv', 'User-Name', ':=', '2500/$slot/$pon/$serial_number@vertv' )";

          $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value)
              VALUES ( '2500/$slot/$pon/$serial_number@vertv', 'User-Password', ':=', 'vlan' )";

          $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
          $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);
          
          if($executa_query_username && $executa_query_password)
          {
            array_push($array_process_result,"1-ONT inserida no Radius IP Gerencia!");

          ###### CRIA SERVICE PORT VLAN ######
            $servicePortl2l = get_service_port_l2l($device,$frame,$slot,$pon,$onuID,$contrato,$vlan_associada);
            $tira_ponto_virgula = explode(";",$servicePortl2l);
            $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
            $remove_desc = explode("ENDESC=",$check_sucesso[1]);
            $errorCode = trim($remove_desc[0]);
            if($errorCode != "0") //se der erro na service port internet
            {
              $trato = tratar_errors($errorCode);
              
              array_push($array_process_result,"Erro ao criar o service porta de Lan to Lan: $trato");

              //se der erro ele irá apagar o registro salvo na tabela local ont
              $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
              mysqli_query($conectar,$sql_apagar_onu);

              array_push($array_process_result,"Removido do banco local");

              $deletar_onu_radius_banda = "DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial_number@vertv' 
                AND attribute='Huawei-Qos-Profile-Name' ";
              mysqli_query($conectar_radius,$deletar_onu_radius_banda);

              array_push($array_process_result,"Removido do radius");
              
              deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
              array_push($array_process_result,"Removido do u2000");

            }else{
              $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
              $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
              $pega_id = explode("	",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
              
              $servicePortl2lID= $pega_id[0] - 1;

              array_push($array_process_result,"2-Service Port de Clear Channel Criada $servicePortl2lID!");

              $insere_service_l2l = "UPDATE ont SET service_port_l2l=$servicePortl2lID WHERE serial = '$serial_number'";
              $executa_insere_service_l2l = mysqli_query($conectar,$insere_service_l2l);

            ############## CRIA O SERVICE PORT DE GERENCIA ####################
              $servicePortInternet = get_service_port_internet($device,$frame,$slot,$pon,$onuID,$contrato,$vasProfile,$modo_bridge);
              $tira_ponto_virgula = explode(";",$servicePortInternet);
              $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
              $remove_desc = explode("ENDESC=",$check_sucesso[1]);
              $errorCode = trim($remove_desc[0]);
              if($errorCode != "0") //se der erro na service port internet
              {
                $trato = tratar_errors($errorCode);

                array_push($array_process_result,"Erro ao criar o service port de Gerencia/Internet: $trato");

                //se der erro ele irá apagar o registro salvo na tabela local ont
                $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
                mysqli_query($conectar,$sql_apagar_onu);

                array_push($array_process_result,"Removido do banco local");

                $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial_number@vertv' 
                  AND attribute='Huawei-Qos-Profile-Name' ";
                mysqli_query($conectar_radius,$deletar_onu_radius_banda);

                array_push($array_process_result,"Removido do Radius");

                deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,$servicePortIPTV);

                array_push($array_process_result,"Removido do u2000");
              }else{
                $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
                $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
                $pega_id = explode("	",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
                
                $servicePortInternetID= $pega_id[0] - 1;
                
                array_push($array_process_result,"3-Service Port de Internet/Gerencia Criada $servicePortInternetID!");

                $insere_service_internet = "UPDATE ont SET service_port_internet=$servicePortInternetID WHERE serial = '$serial_number'";
                $executa_insere_service_internet = mysqli_query($conectar,$insere_service_internet);

                array_push($array_process_result,"4-Lan to Lan Cadastrada!");
              }
            }
          }
          else
          {
            array_push($array_process_result,"Ocorreu um erro ao Inserir no Radius!");

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
            mysqli_query($conectar,$sql_apagar_onu);
            array_push($array_process_result,"Removido do Banco Local");

            $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial_number@vertv'";
            $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
            array_push($array_process_result,"Removido do Radius");

            deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
            array_push($array_process_result,"Removido do do u2000");
          }
        }

  ##################################### I N T E R N E T ##############################################

        if($internet == "Internet"){          
          array_push($array_process_result,"### INTERNET ###");

          ##### SELECIONA O SERVICE PORT PARA VERIFICAR SE TEM A VLAN DE INTERNET CADASTRADA #########
          $select_servicePortInternet = "SELECT service_port_internet FROM ont WHERE contrato=$contrato";
          $executa_select_servicePortInternet = mysqli_query($conectar,$select_servicePortInternet);
          $row = mysqli_fetch_assoc($executa_select_servicePortInternet);

          if($row['service_port_internet'] != NULL)
          {
            ############### INSERE A BANDA NO RADIUS ################
            $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
            VALUES ( '2500/$slot/$pon/$serial_number@vertv', 'Huawei-Qos-Profile-Name', ':=', '$pacote_internet' )";
        
            $executa_query_qos_profile= mysqli_query($conectar_radius,$insere_ont_radius_qos_profile);
            $executa_query_profile_ip_fixo = true;

            if($executa_query_username && $executa_query_profile_ip_fixo) 
            {
              array_push($array_process_result,"1-ONT inserida Banda no Radius!");

              array_push($array_process_result,"2-Service Port de Internet Criada $servicePortInternetID!");

              $insere_service_internet = "UPDATE ont SET service_port_internet=$servicePortInternetID WHERE serial = '$serial_number'";
              $executa_insere_service_internet = mysqli_query($conectar,$insere_service_internet);

              array_push($array_process_result,"3-Internet Cadastrada!");
            }else{
              array_push($array_process_result,"Ocorreu um erro ao Inserir no Radius!");

              //se der erro ele irá apagar o registro salvo na tabela local ont
              $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
              mysqli_query($conectar,$sql_apagar_onu);
              array_push($array_process_result,"Removido do Banco Local");

              $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial_number@vertv' 
                AND attribute='Huawei-Qos-Profile-Name' ";
              $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);
              array_push($array_process_result,"Removido do Radius");

              deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
              array_push($array_process_result,"Removido do do u2000");
            }            
          }else{
            array_push($array_process_result,"ERRO: Não encontrei o Service Port ID Criado! $row[service_port_internet]");
            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
            mysqli_query($conectar,$sql_apagar_onu);
            array_push($array_process_result,"Removido do Banco Local");

            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial_number@vertv' 
              AND attribute='Huawei-Qos-Profile-Name' ";
            mysqli_query($conectar_radius,$deletar_onu_radius_banda);
            array_push($array_process_result,"Removido do Radius");

            deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
            array_push($array_process_result,"Removido do do u2000");
          }
        }
  
    ######################################### I P T V ##################################################

        if($iptv == "IPTV") {
          array_push($array_process_result,"###I P T V###");

          ####### ATIVA SERVICE PORT IPTV ########
          $servicePortIPTV = get_service_port_iptv($device,$frame,$slot,$pon,$onuID,$contrato);

          $tira_ponto_virgula = explode(";",$servicePortIPTV);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0") //se der erro na service port iptv
          {
            $trato = tratar_errors($errorCode);

            array_push($array_process_result,"Houve erro Inserir a Service Port de IPTV: $trato");

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
            mysqli_query($conectar,$sql_apagar_onu);
            array_push($array_process_result,"Removido do Banco Local");
            
            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial_number@vertv' 
              AND attribute='Huawei-Qos-Profile-Name' ";
            $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

            $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial_number@vertv' ";
            $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
            array_push($array_process_result,"Removido do Radius");

            deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,$servicePortIPTV);
            array_push($array_process_result,"Removido do do u2000");

          }else{
            $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
            $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
            
            $pega_id = explode("	",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
            
            $servicePortIptvID= $pega_id[0] - 1;
            array_push($array_process_result,"Service Port IPTV Criado: $servicePortIptvID");
            
            $insere_service_iptv = "UPDATE ont SET service_port_iptv='$servicePortIptvID' WHERE serial = '$serial_number'";
            $executa_insere_service_iptv = mysqli_query($conectar,$insere_service_iptv);
            array_push($array_process_result,"Atualizado Service Port na ONT");
            
            ### BTV ###
            $btv_olt = insere_btv_iptv($device,$frame,$slot,$pon,$onuID);
            $tira_ponto_virgula = explode(";",$btv_olt);
            $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
            $remove_desc = explode("ENDESC=",$check_sucesso[1]);
            $errorCode = trim($remove_desc[0]);

            if($errorCode != "0") //se der erro na btv iptv
            {
              $trato = tratar_errors($errorCode);

              array_push($array_process_result,"Houve erro ao criar o BTV: $trato");

              //se der erro ele irá apagar o registro salvo na tabela local ont
              $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
              mysqli_query($conectar,$sql_apagar_onu);
              array_push($array_process_result,"Removido do Banco Local");
              
              $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial_number@vertv' 
                AND attribute='Huawei-Qos-Profile-Name' ";
              $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

              $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial_number@vertv' ";
              $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
              array_push($array_process_result,"Removido do Radius");

              deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,$servicePortIPTV);
              array_push($array_process_result,"Removido do do u2000");

            }else{
              array_push($array_process_result,"BTV Criado na OLT");
              array_push($array_process_result,"IPTV Ativada!");
            }
          }
        }

    ##################################### T E L E F O N E ##############################################    

        if($voip == "Telefone") {
          array_push($array_process_result,"###TELEFONE###");
          
          #### ATIVA A POTS DO TELEFONE #####
          $telefone_on = ativa_telefonia($device,$frame,$slot,$pon,$onuID,$sip_number,$sip_password,$sip_number);
          $tira_ponto_virgula = explode(";",$telefone_on);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0") //se der erro na ativacao da telefonia
          {
            $trato = tratar_errors($errorCode);

            array_push($array_process_result,"Houve erro ao ativar os numeros na ONT: $trato");

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
            mysqli_query($conectar,$sql_apagar_onu);
            array_push($array_process_result,"Removido do Banco Local");
            
            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial_number@vertv' 
              AND attribute='Huawei-Qos-Profile-Name' ";
            $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

            $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial_number@vertv' ";
            $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
            array_push($array_process_result,"Removido do Radius");

            deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
            array_push($array_process_result,"Removido do do u2000");

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

              array_push($array_process_result,"Houve erro ao criar a Service Port de Telefonia: $trato");

              //se der erro ele irá apagar o registro salvo na tabela local ont
              $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
              mysqli_query($conectar,$sql_apagar_onu);
              array_push($array_process_result,"Removido do Banco Local");
              
              $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/$slot/$pon/$serial_number@vertv' 
                AND attribute='Huawei-Qos-Profile-Name' ";
              $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

              $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/$slot/$pon/$serial_number@vertv' ";
              $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
              array_push($array_process_result,"Removido do Radius");

              deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
              array_push($array_process_result,"Removido do do u2000");
            }else{
              $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
              $pegar_servicePortTel_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
              
              $pega_id = explode("	",$pegar_servicePortTel_ID[2]);//posicao 4 será sempre o ONTID
              
              $servicePortTelefoneID= $pega_id[0] - 1; 
              array_push($array_process_result,"Service Port Telefonia Criado: $servicePortTelefoneID");

              $insere_service_telefone = "UPDATE ont SET service_port_telefone='$servicePortTelefoneID' WHERE serial = '$serial_number'";
              $executa_insere_service_telefone = mysqli_query($conectar,$insere_service_telefone);
              array_push($array_process_result,"Atualizado Service Port na ONT");
            }

          }
        }
      }
    }else{
      echo "OCORREU UM PROBLEMA AO CADASTRAR A ONT NO BANCO LOCAL";
    }
  }  
  
  ## CODIGO TERMINAVA AKI
  
  ##### FECHA AS CONEXOES COM OS BANCOS #####
  mysqli_close($conectar_radius);
  mysqli_close($conectar);
  
  echo "SERVIÇOS ATIVADOS \r";
  foreach($array_process_result as $result)
  {
    echo "$result \r";
  }
  
?>