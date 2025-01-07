<?php
  include_once "../db/db_config_mysql.php";
  include_once "../db/db_config_radius.php";
  include_once "../u2000/tl1_sender.php";
  // Inicia sessões
  session_start();

  $pacote = filter_input(INPUT_POST,'pacote');

  #### SE O USUARIO NAO PRECISAR DE INTERNET, PACOTE É VAZIO
  $_POST["optionsRadios"] == "VAS_IPTV-VoIP"? $pacote = "none" : $pacote ;
  $_POST["optionsRadios"] == "VAS_IPTV"? $pacote = "none" : $pacote ;

  $cto = filter_input(INPUT_POST,"caixa_atendimento_select");
  $frame = filter_input(INPUT_POST,"frame");
  $slot = filter_input(INPUT_POST,"slot");
  $pon = filter_input(INPUT_POST,"pon");
  $usuario = $_SESSION["id_usuario"];
  $contrato = filter_input(INPUT_POST,"contrato");
  $nome = filter_input(INPUT_POST,"nome");
  $serial = strtoupper(filter_input(INPUT_POST,"serial"));
  $modelo_ont = filter_input(INPUT_POST,'equipamentos');
  $telNumber = filter_input(INPUT_POST,"numeroTel");
  $telPass = filter_input(INPUT_POST,"passwordTel");
  $telNumber2 = filter_input(INPUT_POST,"numeroTelNovo2");
  $telPass2 = filter_input(INPUT_POST,"passwordTelNovo2");
  $vasProfile = filter_input(INPUT_POST,"optionsRadios");
  $porta_atendimento = filter_input(INPUT_POST,"porta_atendimento");
  $deviceName = filter_input(INPUT_POST,"deviceName");

  #### CASOS ESPECIAIS DE IP FIXO CORPORATIVO
  $mac = filter_input(INPUT_POST,'mac');
  $ip_fixo = filter_input(INPUT_POST,'ipFixo');
  $modo_bridge = filter_input(INPUT_POST,'modo_bridge');

  if($ip_fixo != NULL)
  {
    $executeIpFixoCheck = mysqli_query($conectar,"SELECT utilizado_por,utilizado FROM ips_valido WHERE numero_ip = $ip_fixo");
    $ipChecado = mysqli_fetch_assoc($executeIpFixoCheck);

    if($ipChecado['utilizado'])
    {
      echo "<p style='color: blue;text-align:center;'>IP Já cadastrado no contrato $ipChecado[utilizado_por]</p>";
      mysqli_close($conectar_radius);
      mysqli_close($conectar);
      exit;
    }
  }

  if($modo_bridge != 'mac_externo')
    $mac = $serial;

  if($ip_fixo != NULL AND $modo_bridge != 'mac_externo')
    $vasProfile = "$vasProfile-CORP-IP";

  if($modo_bridge == 'mac_externo')
    $vasProfile == "VAS_Internet-VoIP-IPTV"? $vasProfile = "$vasProfile-CORP-IP-B" : $vasProfile = "$vasProfile-CORP-IP-Bridge";


  $nomeCompleto = str_replace(" ","_",$nome);

  $verifica_contrato_existente = "";

  ####### SE MAC ESTIVER VAZIO ELE IRA ALERTAR
  if( ($serial == NULL || strlen($serial) < 16) && $vasProfile != 'conversorHFC')
  {
    $s = strlen($serial);
    echo "<p style='color: blue;text-align:center;'>Digite o PON Number $serial (MAC da Ont) ou $vasProfile Revise o MAC</p>";
    mysqli_close($conectar_radius);
    mysqli_close($conectar);
    exit;
  }

  ###### VERIFICA SE ESCOLHERAM O PACOTE DO ASSINANTE SE ELE TIVER INTERNET
  if( (($pacote == '' && $vasProfile != 'VAS_IPTV') || ($pacote == '' && $vasProfile != 'VAS_IPTV-VoIP')) && $vasProfile != 'conversorHFC')
  {
    echo "<p style='color: blue;text-align:center;'>Plano de Internet Inexistente no Contrato do Control Plus ou Velocidade não selecionada</p>";
    mysqli_close($conectar_radius);
    mysqli_close($conectar);
    exit;
  }

  #### DEFINE VALOR DEFAULT DE NUMERO TELEFONE 1
  if(empty($telNumber) && empty($telPass) )
  {
    $telNumber = 0;
    $telPass = 0;
  }

  #### DEFINE VALOR DEFAULT DE NUMERO TELEFONE 2
  if( empty($telNumber2) && empty($telPass2) ) {
    $telNumber2 = 0;
    $telPass2 = 0;
  }else{
    $vasProfile = str_replace("VoIP","twoVoIP",$vasProfile); //SUBSTITUI VOIP POR TWOVOIP CASO TENHA 2 NUMEROS
  }

  ### VERIFICA O LIMITE DO ASSINANTE
  $sql_verifica_limite = "SELECT limite_equipamentos FROM ont WHERE contrato='$contrato'";
  $execute_sql_limite_result = mysqli_query($conectar,$sql_verifica_limite);
  $limite_registro = mysqli_fetch_assoc($execute_sql_limite_result);
  $limite_registro = $limite_registro['limite_equipamentos'] ?? null;

  if ($limite_registro != null AND $limite_registro < "1" )
  {
    echo "<p style='color: blue;text-align:center;'> MAC já cadastrado no contrato. </p>";
    mysqli_close($conectar_radius);
    mysqli_close($conectar);
    exit;
  }

  #### VERIFICA SE A ONT JA SE ENCONTRA EM ALGUM ASSINANTE
  $sql_verifica_ont_cadastrado = "SELECT contrato FROM ont WHERE serial = '$serial' LIMIT 1"; //verifica se ja existe o mac
  $executa_verifica_ont_cadastrada = mysqli_query($conectar,$sql_verifica_ont_cadastrado);
  $mac_existente = mysqli_fetch_assoc($executa_verifica_ont_cadastrada);

  if($mac_existente)
  {
    echo "<p style='color: blue; text-align:center;'>MAC Já Cadastrado no contrato $mac_existente[contrato]</p>";
    mysqli_close($conectar_radius);
    mysqli_close($conectar);
    exit;
  }


  #### SELECT OLT IP ####
  $sql_pega_olt_ip = "SELECT olt_ip FROM pon WHERE deviceName='$deviceName'";
  $executa_pega_olt_ip = mysqli_query($conectar,$sql_pega_olt_ip);
  $ip = mysqli_fetch_assoc($executa_pega_olt_ip);
  $ip_olt = $ip['olt_ip'];

  if($ip_olt == NULL)
  {
    echo "<p style='color:blue;text-align:center;'>Não Consegui pegar o IP da OLT</p>";
    exit;
  }

  ######### VERIFICA O SERVICO QUE SERA ATIVO
  $array_profiles_internet =[
                              "VAS_Internet","VAS_Internet-VoIP","VAS_Internet-IPTV","VAS_Internet-VoIP-IPTV",
                              "VAS_Internet-twoVoIP-IPTV","VAS_Internet-twoVoIP"
                            ];

  $array_profiles_internet_ip = [
                                  "VAS_Internet-CORP-IP","VAS_Internet-VoIP-IPTV-CORP-IP",
                                  "VAS_Internet-VoIP-CORP-IP",
                                  "VAS_Internet-CORP-IP-Bridge","VAS_Internet-IPTV-CORP-IP-Bridge",
                                  "VAS_Internet-VoIP-IPTV-CORP-IP-B","VAS_Internet-VoIP-CORP-IP-Bridge"
                                ];

  $array_profiles_telefonia = [
                                "VAS_Internet-VoIP","VAS_IPTV-VoIP","VAS_Internet-VoIP-IPTV",
                                "VAS_Internet-twoVoIP-IPTV","VAS_Internet-twoVoIP",
                                "VAS_Internet-VoIP-CORP-IP","VAS_Internet-VoIP-IPTV-CORP-IP",
                                "VAS_Internet-VoIP-IPTV-CORP-IP-B","VAS_Internet-VoIP-CORP-IP-Bridge"
                              ];

  $array_profiles_iptv =[
                          "VAS_IPTV","VAS_IPTV-VoIP","VAS_Internet-IPTV","VAS_Internet-VoIP-IPTV",
                          "VAS_Internet-VoIP-IPTV-CORP-IP",
                          "VAS_Internet-VoIP-IPTV-CORP-IP-B",
                          "VAS_Internet-IPTV-CORP-IP-Bridge"
                        ];

  $internet = false;
  $internet_ip = false;
  $telefone = false;
  $iptv = false;

  if(in_array($vasProfile,$array_profiles_internet))
    $internet = true;
  if (in_array($vasProfile,$array_profiles_internet_ip))
    $internet_ip = true;
  if (in_array($vasProfile,$array_profiles_telefonia))
  {
    if($telPass == NULL || $telPass == NULL) //verifica se telefone nao está null
    {
      echo "<p><center style='color:blue;text-align:center;'> Telefone não pode ser em branco neste plano </center></p>";
      exit;
    }
    $telefone = true;
  }

  if (in_array($vasProfile,$array_profiles_iptv))
    $iptv = true;
  if(!$internet AND !$internet_ip AND !$telefone AND !$iptv AND $vasProfile != 'conversorHFC')
  {
    echo "<p style=color:blue;text-align:center;>VAS Profile - <strong>$vasProfile</strong> - Não cadastrado!</p>";
    exit;
  }

  ### VARIAVEL SE ACERTOU TUDO ###
  $ativado = "sucesso";

  ####### CADASTRA A ONT NO U2000 ############

  $array_processos_historico = [];

  ### CADASTRAR CLIENTE NO BANCO SOMENTE CONVERSOR ###
  if($vasProfile == 'conversorHFC'){
    array_push($array_processos_historico,"<p style='font-weight:bold;'>### CONVERSOR HFC ###</p>");

    $serial = "CONVERSOR";

    ### CRIA NO BANCO LOCAL
    $sql_registra_onu = ("INSERT INTO ont (contrato, serial, cto, tel_number, tel_user, tel_password, tel_number2, tel_user2, tel_password2, perfil, pacote, usuario_id,equipamento,porta)
                              VALUES ('$contrato','$serial','$cto','$telNumber','$telNumber','$telPass', '$telNumber2','$telNumber2','$telPass2','$vasProfile','$pacote','$usuario','$modelo_ont','$porta_atendimento')" );
    $cadastrar = mysqli_query($conectar,$sql_registra_onu);

    array_push($array_processos_historico,"ONT cadastrada no banco local");

    //Atualizar Porta CTO
    $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 1, serial = '$serial'
    WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
    $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
    array_push($array_processos_historico,"Reservada Porta $porta_atendimento da CTO $cto");

    $sql_atualiza_limite = "UPDATE ont SET limite_equipamentos=0 WHERE contrato = $contrato";
    $diminui_limite = mysqli_query($conectar,$sql_atualiza_limite);
  }else{
    $ontID = cadastrar_ont($deviceName,$frame,$slot,$pon,$contrato,$nomeCompleto,$cto,$porta_atendimento,$serial,$modelo_ont,$vasProfile);

    $onuID = NULL; //zera ONUID para evitar problema de cash.

    sleep(4); //dorme para processar

    $tira_ponto_virgula = explode(";",$ontID);
    $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
    $remove_desc = explode("ENDESC=",$check_sucesso[1]);
    $errorCode = trim($remove_desc[0]);
    if($errorCode != "0" && ($errorCode != " " OR $errorCode != "" OR $errorCode != NULL))
    {
      $ativado = "Ocorreu Error"; //variavel de sucesso para o JS
      $trato = tratar_errors($errorCode);

      array_push($array_processos_historico,"<p style='color:red'>!!!! Houve erro ao inserir a ONT no u2000: <strong>$trato</strong> !!!!</p>");

      /// salva no log
      $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO GERAR ONTID $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass','$usuario', '$serial', '$cto', '$contrato')";
  
      $executa_log = mysqli_query($conectar,$sql_insert_log);

      deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID,$ip_olt,NULL);

      array_push($array_processos_historico,"Removido u2000");
    }else{
      array_push($array_processos_historico,"<p style='color:green'>ONT Adicionada ao U2000!</p>");

      ########## PEGANDO ID DA ONT PARA SALVAR ############
      $remove_barras_para_pegar_id = explode("---------------------------",$tira_ponto_virgula[1]);
      $filtra_espaco = explode("\r\n",$remove_barras_para_pegar_id[1]);
      $pega_id = explode("	",$filtra_espaco[2]);//posicao 4 será sempre o ONTID
      $onuID=trim($pega_id[4]);

      ### CRIA NO BANCO LOCAL
      $sql_registra_onu = ("INSERT INTO ont (contrato, serial, cto, tel_number, tel_user, tel_password, tel_number2, tel_user2, tel_password2, perfil, pacote, usuario_id,equipamento,porta)
                                VALUES ('$contrato','$serial','$cto','$telNumber','$telNumber','$telPass', '$telNumber2','$telNumber2','$telPass2','$vasProfile','$pacote','$usuario','$modelo_ont','$porta_atendimento')" );
      $cadastrar = mysqli_query($conectar,$sql_registra_onu);

      array_push($array_processos_historico,"ONT cadastrada no banco local");

      //insere o id no banco local
      $insere_ont_id = "UPDATE ont SET ontID='$onuID' WHERE serial = '$serial'";
      $executa_insere_ont_id = mysqli_query($conectar,$insere_ont_id);

      array_push($array_processos_historico,"Inserido ID da ONT!");

      //Atualizar Porta CTO
      $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 1, serial = '$serial'
      WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
      $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
      array_push($array_processos_historico,"Reservada Porta $porta_atendimento da CTO $cto");

      $sql_atualiza_limite = "UPDATE ont SET limite_equipamentos=0 WHERE contrato = $contrato";
      $diminui_limite = mysqli_query($conectar,$sql_atualiza_limite);

    ##################################### I N T E R N E T ##############################################

      if($internet)
      {
        array_push($array_processos_historico,"<p style='font-weight:bold;'>### INTERNET ###</p>");

        $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
            VALUES ( '2500/$slot/$pon/$serial@vertv', 'User-Name', ':=', '2500/$slot/$pon/$serial@vertv' )";

        $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value)
            VALUES ( '2500/$slot/$pon/$serial@vertv', 'Cleartext-Password', ':=', 'vlan' )";

        $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
            VALUES ( '2500/$slot/$pon/$serial@vertv', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";

        $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
        $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);
        $executa_query_qos_profile= mysqli_query($conectar_radius,$insere_ont_radius_qos_profile);

        if($executa_query_qos_profile && $executa_query_password && $executa_query_username)
          array_push($array_processos_historico,"Radius: Banda Ativada e DHCP Configurado");
        else
          array_push($array_processos_historico,"Radius: Erro ao Ativar Banda e Configurar DHCP");

        array_push($array_processos_historico,"Ativando Internet");
      }
      if($internet_ip)
      {
        array_push($array_processos_historico,"<p style='font-weight:bold;'>### INTERNET ###</p>");

        $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
          VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'User-Name', ':=', '2503/$slot/$pon/$serial@vertv-corp-ip' )";

        $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value)
          VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'Cleartext-Password', ':=', 'vlan' )";

        $insere_ont_radius_profile_ip_fixo = "INSERT INTO radreply( username, attribute, op, value)
          VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'Framed-IP-Address',':=','$ip_fixo')";

        $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
          VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";

        //exibe que não esta no CCGNAT
        $cgnat_sql = "UPDATE ont SET cgnat = false WHERE serial = '$serial'";

        if($modo_bridge == 'mac_externo')
        {
          $insere_ont_radius_mac = "INSERT INTO radcheck(username,attribute,op,value) 
            values('2503/$slot/$pon/$serial@vertv-corp-ip','Huawei-User-Mac','=','$mac')";
          $executa_query_ont_radius_mac = mysqli_query($conectar_radius,$insere_ont_radius_mac);
        }

        $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
        $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);
        $executa_query_qos_profile= mysqli_query($conectar_radius,$insere_ont_radius_qos_profile);
        $executa_query_profile_ip_fixo = mysqli_query($conectar_radius,$insere_ont_radius_profile_ip_fixo);
        $executa_cgnat_sql = mysqli_query($conectar,$cgnat_sql); //cgnat ativa devido a ser ip fixo publico

        if($executa_query_qos_profile && $executa_query_password && $executa_query_username && $executa_query_profile_ip_fixo)
        {
          array_push($array_processos_historico,"Radius: Banda Ativada e IP Fixo Configurado");

          $sql_atualiza_ip_fixo = "UPDATE ont SET mac='$mac',ip='$ip_fixo' WHERE serial='$serial'";
          $executa_atualiza_ip_fixo = mysqli_query($conectar,$sql_atualiza_ip_fixo);

          $sql_atualiza_utilizado_ip = "UPDATE ips_valido SET utilizado=true,utilizado_por='$contrato',mac_serial='$mac'
            WHERE numero_ip ='$ip_fixo'";
          $executa_atualiza_utitlizado_ip = mysqli_query($conectar,$sql_atualiza_utilizado_ip);
        }else{
          array_push($array_processos_historico,"Radius: Erro ao Ativar Banda e Configurar IP Fixo");
        }

        array_push($array_processos_historico,"Ativando Internet com IP");

      }
      if($internet_ip || $internet) // cria o service port de internet no u2000
      {
        $servicePortInternet = get_service_port_internet($deviceName,$frame,$slot,$pon,$onuID,$contrato,$vasProfile,$modo_bridge);
        $tira_ponto_virgula = explode(";",$servicePortInternet);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        if($errorCode != "0") //se der erro na service port internet
        {
          $ativado = "Ocorreu Error"; //variavel de sucesso para o JS
          $trato = tratar_errors($errorCode);

          array_push($array_processos_historico,"Erro ao criar o service port de Internet: $trato");

          /// salva no log
          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO GERAR CRIAR SERVICE PORT INTERNET $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";

          $executa_log = mysqli_query($conectar,$sql_insert_log);

          //se der erro ele irá apagar o registro salvo na tabela local ont
          $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
          mysqli_query($conectar,$sql_apagar_onu);

          array_push($array_processos_historico,"<p>Removido do banco local</p>");

          //Dessassocia IP
          $sql_remove_utilizado_antigo = "UPDATE ips_valido SET utilizado=false,utilizado_por=NULL,mac_serial=NULL
          WHERE numero_ip ='$ip_fixo'";
          $executa_atualiza_utitlizado_ip = mysqli_query($conectar,$sql_remove_utilizado_antigo);

          //Desativa Porta CTO
          $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial'
          WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
          $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
          array_push($array_processos_historico,"Desassociada Porta $porta_atendimento da CTO $cto");

          $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial%'";
          mysqli_query($conectar_radius,$deletar_onu_radius_banda);

          $deletar_onu_radius = "DELETE FROM radcheck WHERE username like '%$serial%'";
          mysqli_query($conectar_radius,$deletar_onu_radius);

          array_push($array_processos_historico,"Removido do Radius");

          deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID,$ip_olt,NULL);

          array_push($array_processos_historico,"Removido u2000");

        }else{
          $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
          $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
          $pega_id = explode("	",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID

          $servicePortInternetID= $pega_id[0] - 1;

          array_push($array_processos_historico,"Service Port de Internet Criada: $servicePortInternetID!");

          $insere_service_internet = "UPDATE ont SET service_port_internet=$servicePortInternetID WHERE serial = '$serial'";
          $executa_insere_service_internet = mysqli_query($conectar,$insere_service_internet);

          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES ('ServicePort Internet Cadastrada 
              informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario')";
          $executa_log = mysqli_query($conectar,$sql_insert_log);

          array_push($array_processos_historico,"<p style='color:green'>Internet Ativada!</p>");
        }
      }

      ##################################### T E L E F O N I A ##############################################

      if($telefone)
      {
        array_push($array_processos_historico,"<p style='font-weight: bold'>### TELEFONE ###</p>");

        #### ATIVA A POTS DO TELEFONE #####
        if( $telNumber2 == 0 && $telPass2 == 0)
          $telefone_on = ativa_telefonia($deviceName,$frame,$slot,$pon,$onuID,$telNumber,$telPass,$telNumber);
        else
          $telefone_on = ativa_telefonia($deviceName,$frame,$slot,$pon,$onuID,$telNumber,$telPass,$telNumber,$telNumber2,$telPass2,$telNumber2);

        $tira_ponto_virgula = explode(";",$telefone_on);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        if($errorCode != "0") //se der erro na ativacao da telefonia
        {
          $ativado = "Ocorreu Error"; //variavel de sucesso para o JS
          $trato = tratar_errors($errorCode);

          array_push($array_processos_historico,"<p style='color:red'>Houve erro ao ativar os numeros na ONT: $trato</p>");

          /// salva no log
          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 ATIVAR POTS DE TELEFONIA $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";

          $executa_log = mysqli_query($conectar,$sql_insert_log);

          //se der erro ele irá apagar o registro salvo na tabela local ont
          $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
          mysqli_query($conectar,$sql_apagar_onu);
          array_push($array_processos_historico,"<p>Removido do Banco Local</p>");

          //Dessassocia IP
          $sql_remove_utilizado_antigo = "UPDATE ips_valido SET utilizado=false,utilizado_por=NULL,mac_serial=NULL
          WHERE numero_ip ='$ip_fixo'";
          $executa_atualiza_utitlizado_ip = mysqli_query($conectar,$sql_remove_utilizado_antigo);

          //Desativa Porta CTO
          $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial'
          WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
          $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
          array_push($array_processos_historico,"Desassociada Porta $porta_atendimento da CTO $cto");

          $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial%'";
          mysqli_query($conectar_radius,$deletar_onu_radius_banda);

          $deletar_onu_radius = "DELETE FROM radcheck WHERE username like '%$serial%'";
          mysqli_query($conectar_radius,$deletar_onu_radius);
          array_push($array_processos_historico,"Removido do Radius");

          deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
          array_push($array_processos_historico,"Removido do u2000");

        }else{
          array_push($array_processos_historico,"Número(s) Ativado(s)");
          ## INICIO SERVICE PORT TELEFONE ##
          $servicePortTelefone = get_service_port_telefone($deviceName,$frame,$slot,$pon,$onuID,$contrato);

          $tira_ponto_virgula = explode(";",$servicePortTelefone);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0") //se der erro na service port telefone
          {
            $ativado = "Ocorreu Error"; //variavel de sucesso para o JS
            $trato = tratar_errors($errorCode);

            array_push($array_process_result,"<p style='color:red'>Houve erro ao criar a Service Port de Telefonia: <strong>$trato</strong></p>");

            /// salva no log
            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO GERAR CRIAR SERVICE PORT TELEFONIA $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";

            $executa_log = mysqli_query($conectar,$sql_insert_log);

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
            mysqli_query($conectar,$sql_apagar_onu);
            array_push($array_process_result,"Removido do Banco Local");

            //Dessassocia IP
            $sql_remove_utilizado_antigo = "UPDATE ips_valido SET utilizado=false,utilizado_por=NULL,mac_serial=NULL
            WHERE numero_ip ='$ip_fixo'";
            $executa_atualiza_utitlizado_ip = mysqli_query($conectar,$sql_remove_utilizado_antigo);

            //Desativa Porta CTO
            $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial'
            WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
            $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
            array_push($array_processos_historico,"Desassociada Porta $porta_atendimento da CTO $cto");

            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial%' ";
            $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

            $deletar_onu_radius = " DELETE FROM radcheck WHERE username like '%$serial%' ";
            $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
            array_push($array_process_result,"Removido do Radius");

            deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
            array_push($array_process_result,"Removido u2000");
          }else
          {
            $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
            $pegar_servicePortTel_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);

            $pega_id = explode("	",$pegar_servicePortTel_ID[2]);//posicao 4 será sempre o ONTID

            $servicePortTelefoneID= $pega_id[0] - 1;
            array_push($array_processos_historico,"Service Port Telefonia Criado: $servicePortTelefoneID");

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES ('ServicePort Telefone Cadastrada 
              informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass','$usuario')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            $insere_service_telefone = "UPDATE ont SET service_port_telefone='$servicePortTelefoneID' WHERE serial = '$serial'";
            $executa_insere_service_telefone = mysqli_query($conectar,$insere_service_telefone);
            array_push($array_processos_historico,"Atualizado Service Port na ONT");

            array_push($array_processos_historico,"<p style='color: green'>Telefone Ativado! </p>");
          }
        }

      }

      ##################################### I P T V ##############################################

      if($iptv)
      {
        array_push($array_processos_historico,"<p style='font-weight:bold'> ###I P T V### </p>");
        ####### ATIVA SERVICE PORT IPTV ########
        $servicePortIPTV = get_service_port_iptv($deviceName,$frame,$slot,$pon,$onuID,$contrato);

        $tira_ponto_virgula = explode(";",$servicePortIPTV);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        if($errorCode != "0") //se der erro na service port iptv
        {
          $ativado = "Ocorreu Error"; //variavel de sucesso para o JS
          $trato = tratar_errors($errorCode);

          array_push($array_processos_historico,"<p style='color:red'>Houve erro Inserir a Service Port de IPTV: <strong>$trato</strong></p>");

          /// salva no log
          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO GERAR CRIAR SERVICE PORT IPTV $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";

          $executa_log = mysqli_query($conectar,$sql_insert_log);

          //se der erro ele irá apagar o registro salvo na tabela local ont
          $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );

          //remove do radius
          mysqli_query($conectar,$sql_apagar_onu);
          array_push($array_processos_historico,"<p>Removido do Banco Local</p>");

          //Dessassocia IP
          $sql_remove_utilizado_antigo = "UPDATE ips_valido SET utilizado=false,utilizado_por=NULL,mac_serial=NULL
          WHERE numero_ip ='$ip_fixo'";
          $executa_atualiza_utitlizado_ip = mysqli_query($conectar,$sql_remove_utilizado_antigo);

          //Desativa Porta CTO
          $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial'
          WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
          $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
          array_push($array_processos_historico,"Desassociada Porta $porta_atendimento da CTO $cto");

          $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial%'";
          mysqli_query($conectar_radius,$deletar_onu_radius_banda);

          $deletar_onu_radius = "DELETE FROM radcheck WHERE username like '%$serial%'";
          mysqli_query($conectar_radius,$deletar_onu_radius);

          array_push($array_processos_historico,"Removido do Radius");

          //remove do u2000
          deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID,$ip_olt,$servicePortIPTV);
          array_push($array_processos_historico,"Removido do u2000");

        }else{
          $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
          $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);

          $pega_id = explode("	",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID

          $servicePortIptvID= $pega_id[0] - 1;
          array_push($array_processos_historico,"Service Port IPTV Criado: $servicePortIptvID");

          $insere_service_iptv = "UPDATE ont SET service_port_iptv='$servicePortIptvID' WHERE serial = '$serial'";
          $executa_insere_service_iptv = mysqli_query($conectar,$insere_service_iptv);
          array_push($array_processos_historico,"Atualizado Service Port na ONT");

          ### BTV ###
          $btv_olt = insere_btv_iptv($deviceName,$frame,$slot,$pon,$onuID);
          $tira_ponto_virgula = explode(";",$btv_olt);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);

          if($errorCode != "0") //se der erro na btv iptv
          {
            $ativado = "Ocorreu Error"; //variavel de sucesso para o JS
            $trato = tratar_errors($errorCode);

            array_push($array_processos_historico,"Houve erro ao criar o BTV: $trato");

            /// salva no log
            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO GERAR CRIAR BTV $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
                Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";

            $executa_log = mysqli_query($conectar,$sql_insert_log);

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
            mysqli_query($conectar,$sql_apagar_onu);
            array_push($array_processos_historico,"<p>Removido do Banco Local</p>");

            //Dessassocia IP
            $sql_remove_utilizado_antigo = "UPDATE ips_valido SET utilizado=false,utilizado_por=NULL,mac_serial=NULL
            WHERE numero_ip ='$ip_fixo'";
            $executa_atualiza_utitlizado_ip = mysqli_query($conectar,$sql_remove_utilizado_antigo);

            //Desativa Porta CTO
            $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial'
            WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
            $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
            array_push($array_processos_historico,"Desassociada Porta $porta_atendimento da CTO $cto");

            //remove do radius
            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial%'";
            mysqli_query($conectar_radius,$deletar_onu_radius_banda);

            $deletar_onu_radius = "DELETE FROM radcheck WHERE username like '%$serial%'";
            mysqli_query($conectar_radius,$deletar_onu_radius);

            array_push($array_processos_historico,"Removido do Radius");

            deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID,$ip_olt,$servicePortIPTV);
            array_push($array_processos_historico,"Removido do u2000");

          }else{
            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES ('BTV Criado e SP IPTV Cadastrada 
              informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass','$usuario')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            array_push($array_processos_historico,"<p>BTV Criado na OLT</p>");
            array_push($array_processos_historico,"<p style='color:green;';>IPTV Ativada</p>");
          }
        }
      }
    }
  }

  array_push($array_processos_historico,$ativado);
  echo "<p style='font-weight:bold;text-align:center'>TIMELINE</p>";

  $hasError = 0;

  foreach($array_processos_historico as $historia)
  {
    if (stripos($historia, 'erro') !== false) {
      $hasError+=1;
    }

    echo "<div style='text-align:center'>$historia</div>";
  }

  if (!$hasError) {
    $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
              VALUES ('Cadastro da ONT concluido com sucesso 
              informações relatadas: OLT: $deviceName, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";
    $executa_log = mysqli_query($conectar,$sql_insert_log);
  }

  ##### FECHA AS CONEXOES COM OS BANCOS #####
  mysqli_close($conectar_radius);
  mysqli_close($conectar);
?>
