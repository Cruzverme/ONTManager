<?php 
  include_once "../db/db_config_mysql.php";
  include_once "../db/db_config_radius.php";
  include_once "../u2000/tl1_sender.php";
  include_once "funcoes.php";
  // Inicia sessões 
  session_start();

  $usuario = $_SESSION["id_usuario"];
  $contrato = filter_input(INPUT_POST,"contrato");
  $serial = filter_input(INPUT_POST,"serial");
  $pacote = filter_input(INPUT_POST,"pacote");
  $equipamento = filter_input(INPUT_POST,'equipamento');
  $telNumber = filter_input(INPUT_POST,"numeroTel");
  $telPass = filter_input(INPUT_POST,"passwordTel");
  $telNumber2 = filter_input(INPUT_POST,"numeroTelNovo2");
  $telPass2 = filter_input(INPUT_POST,"passwordTelNovo2");
  $vasProfile = filter_input(INPUT_POST,"vasProfile");
  $modo_bridge = filter_input(INPUT_POST,'modo_bridge');
  $ip_fixo = filter_input(INPUT_POST,'ipFixo');
  $mac = filter_input(INPUT_POST,'mac');
  $cgnat_status = filter_input(INPUT_POST,'cgnat');$cgnat_status == null? $cgnat_status = false : $cgnat_status;
  
    ######### VERIFICA O SERVICO QUE SERA ATIVO
  $array_profiles_internet =[
                              "VAS_Internet",
                              "VAS_Internet-VoIP",
                              "VAS_Internet-IPTV",
                              "VAS_Internet-VoIP-IPTV",
                              "VAS_Internet-twoVoIP-IPTV",
                              "VAS_Internet-twoVoIP",
                              "VAS_Internet-REAL",
                              "VAS_Internet-IPTV-REAL",
                              "VAS_Internet-VoIP-REAL",
                              "VAS_Internet-VoIP-IPTV-REAL"
  ];

  $array_profiles_internet_ip = [
                              "VAS_Internet-CORP-IP",
                              "VAS_Internet-IPTV-CORP-IP",
                              "VAS_Internet-VoIP-IPTV-CORP-IP",
                              "VAS_Internet-VoIP-CORP-IP",
                              "VAS_Internet-CORP-IP-Bridge",
                              "VAS_Internet-IPTV-CORP-IP-Bridge",
                              "VAS_Internet-VoIP-IPTV-CORP-IP-B",
                              "VAS_Internet-VoIP-CORP-IP-Bridge"
        ];

  $array_profiles_telefonia = [
                              "VAS_Internet-VoIP",
                              "VAS_IPTV-VoIP",
                              "VAS_Internet-VoIP-IPTV",
                              "VAS_Internet-twoVoIP-IPTV",
                              "VAS_Internet-twoVoIP",
                              "VAS_Internet-VoIP-REAL",
                              "VAS_Internet-VoIP-IPTV-REAL",
                              "VAS_Internet-VoIP-CORP-IP",
                              "VAS_Internet-VoIP-IPTV-CORP-IP",
                              "VAS_Internet-VoIP-IPTV-CORP-IP-B",
                              "VAS_Internet-VoIP-CORP-IP-Bridge"
      ];

  $array_profiles_iptv =[
                    "VAS_IPTV",
                    "VAS_IPTV-VoIP",
                    "VAS_Internet-IPTV",
                    "VAS_Internet-VoIP-IPTV",
                    "VAS_Internet-IPTV-REAL",
                    "VAS_Internet-VoIP-IPTV-REAL",
                    "VAS_Internet-VoIP-IPTV-CORP-IP",
                    "VAS_Internet-IPTV-CORP-IP",
                    "VAS_Internet-VoIP-IPTV-CORP-IP-B",
                    "VAS_Internet-IPTV-CORP-IP-Bridge"
  ];

  $internet = false;
  $internet_ip = false;
  $telefone = false;
  $iptv = false;

  //define variáveis de controle de serviço
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
  if(!$internet AND !$internet_ip AND !$telefone AND !$iptv)
  {
    echo "<p style=color:blue;text-align:center;>VAS Profile - <strong>$vasProfile</strong> - Não cadastrado!</p>";
    exit;
  }

  ### VAS PROFILE BRIDGE EM TRIPLE PLAY 
  $vasProfile == "VAS_Internet-VoIP-IPTV-CORP-IP-B"? $vasProfile = "VAS_Internet-VoIP-IPTV-CORP-IP-B" : $vasProfile;
  
  ### VARIAVEL SE ACERTOU TUDO ###
  $ativado = "recadastrado";

  $cgnat_status == false? $tipoNAT = 0 : $tipoNAT = 1; // se for cgnat o tipo é 0 ele tem IP REAL, caso contrario tipo = 1 CGNAT

  //pega o Alias do assinante
  $json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_pacote_ftth_cplus.php?contra=$contrato");
  $json_str = json_decode($json_file, true);
  $itens = $json_str['velocidade'];
  $nome = $json_str['nome'];
  $nomeCompleto = str_replace(" ","_",$nome[0]);

######## DEFINE TELEFONIA #########
  if(empty($telNumber) && empty($telPass)  )
  {
    $telNumber = 0;
    $telPass = 0;
  }

  if( empty($telNumber2) && empty($telPass2) ) {
    $telNumber2 = 0;
    $telPass2 = 0;
  }else{
    // define o vas profile para 2 Sips
    $vasArray = explode('-',$vasProfile);
    if(!in_array('twoVoIP',$vasArray))
      $vasProfile = str_replace("VoIP","twoVoIP",$vasProfile);
  }


#### DADOS ATUAIS DO BANCO ####
  $select_ont_info = "SELECT onu.ontID,onu.cto,onu.porta,onu.mac,onu.ip,onu.perfil,onu.service_port_iptv,onu.service_port_internet,onu.service_port_telefone,onu.equipamento,onu.pacote,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip FROM ont onu 
      INNER JOIN ctos ct ON ct.serial='$serial' AND ct.caixa_atendimento= onu.cto 
      INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
      WHERE onu.serial='$serial' AND onu.contrato='$contrato'";
  
  $sql_ont_info_execute = mysqli_query($conectar,$select_ont_info);

  while($info_ont_atual = mysqli_fetch_array($sql_ont_info_execute, MYSQLI_ASSOC))
  {
    $ontIDOld = $info_ont_atual['ontID'];
    list($frame,$slot,$pon) = explode('-',$info_ont_atual['frame_slot_pon']);
    $infoPonID = $info_ont_atual['pon_id_fk'];
    $device = $info_ont_atual['deviceName'];
    $ip_olt = $info_ont_atual['olt_ip'];
    $servicePortIptv = $info_ont_atual['service_port_iptv'];
    $servicePortNet = $info_ont_atual['service_port_internet'];
    $servicePortTel = $info_ont_atual['service_port_telefone'];
    $cto = $info_ont_atual['cto'];
    $porta_atendimento = $info_ont_atual['porta'];
    $pacoteAtual = $info_ont_atual['pacote'];
    $equipment = $info_ont_atual['equipamento'];
    $vasProfileOld = $info_ont_atual['perfil'];
  
    $mac_atual = $info_ont_atual['mac'];
    $ip_fixo_atual = $info_ont_atual['ip'];
  
  }
  


#### REMOVE ONT PARA CADASTRAR NOVAMENTE ####
  $deletar_2000 = deletar_onu_2000($device,$frame,$slot,$pon,$ontIDOld,$ip_olt,$servicePortIptv);
  $tira_ponto_virgula = explode(";",$deletar_2000);
  $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
  $remove_desc = explode("ENDESC=",$check_sucesso[1]);
  $errorCode = trim($remove_desc[0]);

  if($errorCode != "0" && $errorCode != "1615331086") //se der erro ao deletar a ONT
  {
    $trato = tratar_errors($errorCode);

    echo "<p style='text-align:center;'>Houve erro ao remover no u2000: $errorCode - $trato</p>" ;

    //salva em LOG
    $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
          VALUES ('ERRO NO U2000 AO remover a ONT atual $trato 
          informações antes da alteração: 
              OLT: $device, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, 
              Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $mac_atual, Perfil: $vasProfileOld, 
              Internet: $pacoteAtual, Telefone: $telNumber,
              Senha Telefone: $telPass, Ip Fixo: $ip_fixo_atual',$usuario, $serial, $cto, $contrato)";

    $executa_log = mysqli_query($conectar,$sql_insert_log);

    mysqli_close($conectar_radius);
    mysqli_close($conectar);
    exit;
  }else{

    //Array que contem o historico de processos
    $array_processos_historico = [];

######### Cadastro a OLT Novamente ##############
    $ontID = cadastrar_ont($device,$frame,$slot,$pon,$contrato,$nomeCompleto,$cto,$porta_atendimento,$serial,$equipment,$vasProfile,$tipoNAT);
    $onuID = NULL; //zera ONUID para evitar problema de cash.

    $tira_ponto_virgula = explode(";",$ontID);
    $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
    $remove_desc = explode("ENDESC=",$check_sucesso[1]);
    $errorCode = trim($remove_desc[0]);
    if($errorCode != "0") // se der erro ao recadastrar a ONT
    {
      $trato = tratar_errors($errorCode);
      
      array_push($array_processos_historico,"<p style='color:red'>!!!! Houve erro ao inserir a ONT no u2000 $vasProfile: <strong>$trato</strong> !!!!</p>");
      
      //salva em LOG
      $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
          VALUES ('ERRO NO U2000 AO Recadastrar a ONT ONTID Não Criada $trato 
          informações relatadas: 
              OLT: $device, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, 
              Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Novo Perfil: $vasProfile, 
              Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";
      
      $executa_log = mysqli_query($conectar,$sql_insert_log);

      deletar_onu_2000($device,$frame,$slot,$pon,$ontIDOld,$ip_olt,$servicePortIptv);
      array_push($array_processos_historico,"Removido u2000");
    }else{
      array_push($array_processos_historico,"<span style='color:blue'>Realizando Recadastramento</span>");
      array_push($array_processos_historico,"<span style='color:green'>ONT Adicionada ao U2000!</span>");

      $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
          VALUES ('Iniciou o recadastramento 
          informações relatadas:
              OLT: $device, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, 
              Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Novo Perfil: $vasProfile, 
              Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";

      $executa_log = mysqli_query($conectar,$sql_insert_log);

########## PEGANDO ID DA ONT PARA SALVAR ############
      $remove_barras_para_pegar_id = explode("---------------------------",$tira_ponto_virgula[1]);
      $filtra_espaco = explode("\r\n",$remove_barras_para_pegar_id[1]);
      $pega_id = explode("	",$filtra_espaco[2]);//posicao 4 será sempre o ONTID
      $onuID=trim($pega_id[4]);

### Atualiza NO BANCO LOCAL e zera os services Port
      $insere_ont_id = "UPDATE ont SET ontID='$onuID', perfil='$vasProfile', equipamento='$equipamento',
                            service_port_internet=NULL,service_port_telefone=NULL,
                            service_port_iptv=NULL,mac=NULL,ip=NULL
                        WHERE serial = '$serial'";
      $executa_insere_ont_id = mysqli_query($conectar,$insere_ont_id);
      
      //Atualiza pacote de internet
      $atualiza_banda_local = "UPDATE ont SET pacote='$pacote' WHERE serial = '$serial'";
      $executa_atualiza_banda_local = mysqli_query($conectar,$atualiza_banda_local);
      
      $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('ONT criada no u2000',$usuario)";
      mysqli_query($conectar,$sql_insert_log);

######### APAGA O RADIUS e ONT PARA DPS CRIAR NOVAMENTE #############
        
      $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial@vertv%' ";
      $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

      $deletar_onu_radius = " DELETE FROM radcheck WHERE username like '%$serial@vertv%' ";
      $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
      
      ########### FIM APAGA RADIUS e ONT ##############

      //Dessassocia IP
      $sql_remove_utilizado_antigo = "UPDATE ips_valido SET utilizado=false,utilizado_por=NULL,mac_serial=NULL
        WHERE numero_ip ='$ip_fixo_atual'";
      $executa_atualiza_utitlizado_ip = mysqli_query($conectar,$sql_remove_utilizado_antigo);


      ##################################### I N T E R N E T ##############################################
      if($internet)
      {
        array_push($array_processos_historico,"<hr><span style='font-weight:bold;'>### INTERNET ###</span>");

        if($cgnat_status != 'ip_real_ativo') //se nao precisar de IP REAL entrar no CGNAT
        {
          $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
            VALUES ( '2500/$slot/$pon/$serial@vertv', 'User-Name', ':=', '2500/$slot/$pon/$serial@vertv' )";

          $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value)
              VALUES ( '2500/$slot/$pon/$serial@vertv', 'Cleartext-Password', ':=', 'vlan' )";

          $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
              VALUES ( '2500/$slot/$pon/$serial@vertv', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";

          //exibe que esta no CCGNAT
          $cgnat_sql = "UPDATE ont SET cgnat = true WHERE serial = '$serial'";
        }else{
          $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
                  VALUES ( '2504/$slot/$pon/$serial@vertv-real', 'User-Name', ':=', '2504/$slot/$pon/$serial@vertv-real' )";

          $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value) 
                VALUES ( '2504/$slot/$pon/$serial@vertv-real', 'Cleartext-Password', ':=', 'vlan' )";

          $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
                VALUES ( '2504/$slot/$pon/$serial@vertv-real', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";

          //exibe que nao esta no CCGNAT
          $cgnat_sql = "UPDATE ont SET cgnat = false WHERE serial = '$serial'";
          
        }

        $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
        $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);
        $executa_query_qos_profile= mysqli_query($conectar_radius,$insere_ont_radius_qos_profile);
        $executa_cgnat_sql = mysqli_query($conectar,$cgnat_sql);

        if($executa_query_qos_profile && $executa_query_password && $executa_query_username)
          array_push($array_processos_historico,"Radius: Banda Ativada e DHCP Configurado");  
        else
          array_push($array_processos_historico,"<span style='color:red'>Radius: Erro ao Ativar Banda e Configurar DHCP</span>");

        array_push($array_processos_historico,"Reativando Internet");

      }
      if($internet_ip)
      {
        array_push($array_processos_historico,"<hr><span style='font-weight:bold;'>### INTERNET ###</span>");
        
        // verifica se houve mudança de MAC
        if($mac != $mac_atual)
          $mac_novo = $mac;
        else
          $mac_novo = $mac_atual;

        // verifica se houve mudança de IP
        if($ip_fixo != $ip_fixo_atual)
          $ip_fixo_novo = $ip_fixo;
        else
          $ip_fixo_novo = $ip_fixo_atual;

        $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
          VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'User-Name', ':=', '2503/$slot/$pon/$serial@vertv-corp-ip' )";

        $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value)
          VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'Cleartext-Password', ':=', 'vlan' )";

        $insere_ont_radius_profile_ip_fixo = "INSERT INTO radreply( username, attribute, op, value)
          VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'Framed-IP-Address',':=','$ip_fixo_novo')";

        $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
          VALUES ( '2503/$slot/$pon/$serial@vertv-corp-ip', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";

        if($modo_bridge == 'mac_externo')
        {
          $insere_ont_radius_mac = "INSERT INTO radcheck(username,attribute,op,value) 
            values('2503/$slot/$pon/$serial@vertv-corp-ip','Huawei-User-Mac','=','$mac_novo')";
          $executa_query_ont_radius_mac = mysqli_query($conectar_radius,$insere_ont_radius_mac);
        }      
                                                                                    
        $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
        $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);
        $executa_query_qos_profile= mysqli_query($conectar_radius,$insere_ont_radius_qos_profile);
        $executa_query_profile_ip_fixo = mysqli_query($conectar_radius,$insere_ont_radius_profile_ip_fixo);

        if($executa_query_qos_profile && $executa_query_password && $executa_query_username && $executa_query_profile_ip_fixo)
        {
          array_push($array_processos_historico,"Radius: Banda Ativada e IP Fixo Configurado");
          
          //atualiza o mac e ip da ONT no BD local
          $sql_atualiza_ip_fixo = "UPDATE ont SET mac='$mac_novo',ip='$ip_fixo_novo' WHERE serial='$serial'";
          $executa_atualiza_ip_fixo = mysqli_query($conectar,$sql_atualiza_ip_fixo);

          array_push($array_processos_historico,"Mac: $mac_novo e IP $ip_fixo_novo associados ao contrato $contrato!");

          // atualiza Ip Novo
          $sql_atualiza_utilizado_ip = "UPDATE ips_valido SET utilizado=true,utilizado_por='$contrato',mac_serial='$mac_novo'
            WHERE numero_ip ='$ip_fixo_novo'";
          $executa_atualiza_utitlizado_ip = mysqli_query($conectar,$sql_atualiza_utilizado_ip);
          
          array_push($array_processos_historico,"Radius: Ip Fixo Reservado");

          //exibe que nao esta no CCGNAT
          $cgnat_sql = "UPDATE ont SET cgnat = false WHERE serial = '$serial'";
          $executa_cgnat_sql = mysqli_query($conectar,$cgnat_sql);
        }else{
          array_push($array_processos_historico,"<span style='color:red'>Radius: Erro ao Ativar Banda e Configurar IP Fixo</span>");
        }
  
        array_push($array_processos_historico,"Reativando Internet com IP");
      }
      if($internet_ip || $internet) // cria o service port de internet no u2000
      {
        $servicePortInternet = get_service_port_internet($device,$frame,$slot,$pon,$onuID,$contrato,$vasProfile,$modo_bridge,$tipoNAT);
        $tira_ponto_virgula = explode(";",$servicePortInternet);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        if($errorCode != "0") //se der erro na service port internet
        {
          $ativado = "Ocorreu Error"; //variavel de sucesso para o JS
          $trato = tratar_errors($errorCode);
  
          array_push($array_processos_historico,"Erro ao criar o service port de Internet: $trato");

          //salva em LOG
          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
          VALUES ('ERRO NO U2000 AO Recadastrar Service Port Internet Não Criada $trato 
          informações relatadas: 
              OLT: $device, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, 
              Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Novo Perfil: $vasProfile, 
              Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";

          $executa_log = mysqli_query($conectar,$sql_insert_log);
  
          //se der erro ele irá apagar o registro salvo na tabela local ont
          $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
          mysqli_query($conectar,$sql_apagar_onu);
  
          array_push($array_processos_historico,"<p>Removido do banco local</p>");

          //Atualizar Porta CTO
          $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial'
          WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
          $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
          array_push($array_processos_historico,"Disponibilizando Porta $porta_atendimento da CTO $cto");
  
          $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial%'";
          mysqli_query($conectar_radius,$deletar_onu_radius_banda);
  
          $deletar_onu_radius = "DELETE FROM radcheck WHERE username like '%$serial%'";
          mysqli_query($conectar_radius,$deletar_onu_radius);
  
          array_push($array_processos_historico,"Removido do Radius");
  
          deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
  
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
              VALUES ('ServicePort Internet Recadastrada 
              informações relatadas: OLT: $device, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario')";
          $executa_log = mysqli_query($conectar,$sql_insert_log);

          array_push($array_processos_historico,"<span style='color:green'>Internet Reativada!</span>");
        }
      }
    }
    
    ##################################### T E L E F O N I A ##############################################
    if($telefone)
    {
      array_push($array_processos_historico,"<hr><span style='font-weight: bold'>### TELEFONE ###</span>");
      
      #### ATIVA A POTS DO TELEFONE #####
      if( $telNumber2 == 0 && $telPass2 == 0)
        $telefone_on = ativa_telefonia($device,$frame,$slot,$pon,$onuID,$telNumber,$telPass,$telNumber);
      else
        $telefone_on = ativa_telefonia($device,$frame,$slot,$pon,$onuID,$telNumber,$telPass,$telNumber,$telNumber2,$telPass2,$telNumber2);

      $tira_ponto_virgula = explode(";",$telefone_on);
      $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
      $remove_desc = explode("ENDESC=",$check_sucesso[1]);
      $errorCode = trim($remove_desc[0]);
      
      if($errorCode != "0") //se der erro na ativacao da telefonia
      {
        $ativado = "Ocorreu Error"; //variavel de sucesso para o JS
        $trato = tratar_errors($errorCode);

        array_push($array_processos_historico,"<p style='color:red'>Houve erro ao ativar os numeros na ONT: $trato</p>");

        //se der erro ele irá apagar o registro salvo na tabela local ont
        $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
        mysqli_query($conectar,$sql_apagar_onu);
        array_push($array_processos_historico,"<p>Removido do Banco Local</p>");

        //Atualizar Porta CTO
        $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial'
        WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
        $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
        array_push($array_processos_historico,"Disponibilizando Porta $porta_atendimento da CTO $cto");
        
        $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial%'";
        mysqli_query($conectar_radius,$deletar_onu_radius_banda);

        $deletar_onu_radius = "DELETE FROM radcheck WHERE username like '%$serial%'";
        mysqli_query($conectar_radius,$deletar_onu_radius);
        array_push($array_processos_historico,"Removido do Radius");

        deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
        array_push($array_processos_historico,"Removido do u2000");

        //salva em LOG
        $sql_insert_log = "INSERT INTO log(registro,codigo_usuario, mac, cto, contrato)
        VALUES ('ERRO NO U2000 AO ATIVAR O SIP - $trato 
        informações relatadas Ativar Telefonia: 
            OLT: $device, PON: $pon, Frame: $frame,
            Porta de Atendimento: $porta_atendimento, 
            Slot: $slot, CTO: $cto Contrato: $contrato,
            MAC: $serial, Novo Perfil: $vasProfile, 
            Internet: $pacote, Telefone: $telNumber,
            Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";
    
        $executa_log = mysqli_query($conectar,$sql_insert_log);
      }else{
        array_push($array_processos_historico,"Número(s) Ativado(s)");
        ## INICIO SERVICE PORT TELEFONE ##
        $servicePortTelefone = get_service_port_telefone($device,$frame,$slot,$pon,$onuID,$contrato);

        $tira_ponto_virgula = explode(";",$servicePortTelefone);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        if($errorCode != "0") //se der erro na service port telefone
        {
          $ativado = "Ocorreu Error"; //variavel de sucesso para o JS
          $trato = tratar_errors($errorCode);

          array_push($array_process_result,"<span style='color:red'>Houve erro ao criar a Service Port de Telefonia: <strong>$trato</strong></span>");

          //se der erro ele irá apagar o registro salvo na tabela local ont
          $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
          mysqli_query($conectar,$sql_apagar_onu);
          array_push($array_process_result,"Removido do Banco Local");
          
          $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial%' ";
          $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

          $deletar_onu_radius = " DELETE FROM radcheck WHERE username like '%$serial%' ";
          $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
          array_push($array_process_result,"Removido do Radius");

          deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
          array_push($array_process_result,"Removido u2000");

          //salva em LOG
          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
          VALUES ('ERRO NO U2000 AO GERAR SERVICE PORT TELEFONIA $trato 
          informações relatadas SP Telefonia: 
              OLT: $device, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, 
              Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Novo Perfil: $vasProfile, 
              Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";
      
          $executa_log = mysqli_query($conectar,$sql_insert_log);

        }else{
          $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
          $pegar_servicePortTel_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
          
          $pega_id = explode("	",$pegar_servicePortTel_ID[2]);//posicao 4 será sempre o ONTID
          
          $servicePortTelefoneID= $pega_id[0] - 1; 
          array_push($array_processos_historico,"Service Port Telefonia Criado: $servicePortTelefoneID");

          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
            VALUES ('ServicePort Telefone Cadastrada 
            informações relatadas: OLT: $device, PON: $pon, Frame: $frame,
            Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
            MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
            Senha Telefone: $telPass','$usuario')";
          $executa_log = mysqli_query($conectar,$sql_insert_log);

          $insere_service_telefone = "UPDATE ont SET service_port_telefone='$servicePortTelefoneID', tel_user='$telNumber',
            tel_number='$telNumber',tel_password='$telPass',
            tel_user2='$telNumber2' ,tel_number2='$telNumber2',tel_password2='$telPass2'
          WHERE serial = '$serial'";
          $executa_insere_service_telefone = mysqli_query($conectar,$insere_service_telefone);
          array_push($array_processos_historico,"Atualizado Service Port na ONT");

          array_push($array_processos_historico,"<span style='color: green'>Telefone Reativado! </span>");
        }
      }
    }

    ##################################### I P T V ##############################################
    if($iptv)
    {
      array_push($array_processos_historico,"<hr><span style='font-weight:bold'> ###I P T V### </span>");
      ####### ATIVA SERVICE PORT IPTV ########
      $servicePortIPTV = get_service_port_iptv($device,$frame,$slot,$pon,$onuID,$contrato);

      $tira_ponto_virgula = explode(";",$servicePortIPTV);
      $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
      $remove_desc = explode("ENDESC=",$check_sucesso[1]);
      $errorCode = trim($remove_desc[0]);
      
      if($errorCode != "0") //se der erro na service port iptv
      {
        $ativado = "Ocorreu Error"; //variavel de sucesso para o JS
        $trato = tratar_errors($errorCode);

        array_push($array_processos_historico,"<span style='color:red'>Houve erro Inserir a Service Port de IPTV: <strong>$trato</strong></span>");

        //salva em LOG
        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
          VALUES ('ERRO NO U2000 AO Recadastrar Service Port IPTV Não Criada $trato 
          informações relatadas: 
              OLT: $device, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, 
              Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Novo Perfil: $vasProfile, 
              Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";

        $executa_log = mysqli_query($conectar,$sql_insert_log);

        //se der erro ele irá apagar o registro salvo na tabela local ont
        $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
        
        //remove do radius
        mysqli_query($conectar,$sql_apagar_onu);
        array_push($array_processos_historico,"Removido do Banco Local");

        //Atualizar Porta CTO
        $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial'
        WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
        $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
        array_push($array_processos_historico,"Disponibilizando Porta $porta_atendimento da CTO $cto");
        
        $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial%'";
        mysqli_query($conectar_radius,$deletar_onu_radius_banda);

        $deletar_onu_radius = "DELETE FROM radcheck WHERE username like '%$serial%'";
        mysqli_query($conectar_radius,$deletar_onu_radius);

        array_push($array_processos_historico,"Removido do Radius");

        //remove do u2000
        deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,$servicePortIPTV);
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

        $btv_olt = insere_btv_iptv($device,$frame,$slot,$pon,$onuID);
        $tira_ponto_virgula = explode(";",$btv_olt);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);

        if($errorCode != "0") //se der erro na btv iptv
        {
          $ativado = "Ocorreu Error"; //variavel de sucesso para o JS
          $trato = tratar_errors($errorCode);

          array_push($array_processos_historico,"Houve erro ao criar o BTV: $trato");

          //salva em LOG
          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
          VALUES ('ERRO NO U2000 AO Recadastrar BTV Não Criada $trato 
          informações relatadas: 
              OLT: $device, PON: $pon, Frame: $frame,
              Porta de Atendimento: $porta_atendimento, 
              Slot: $slot, CTO: $cto Contrato: $contrato,
              MAC: $serial, Novo Perfil: $vasProfile, 
              Internet: $pacote, Telefone: $telNumber,
              Senha Telefone: $telPass, Ip Fixo: $ip_fixo','$usuario', '$serial', '$cto', '$contrato')";

          $executa_log = mysqli_query($conectar,$sql_insert_log);

          //se der erro ele irá apagar o registro salvo na tabela local ont
          $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
          mysqli_query($conectar,$sql_apagar_onu);
          array_push($array_processos_historico,"<p>Removido do Banco Local</p>");

          //Atualizar Porta CTO
          $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial'
          WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
          $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
          array_push($array_processos_historico,"Disponibilizando Porta $porta_atendimento da CTO $cto");

          //remove do radius
          $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial%'";
          mysqli_query($conectar_radius,$deletar_onu_radius_banda);

          $deletar_onu_radius = "DELETE FROM radcheck WHERE username like '%$serial%'";
          mysqli_query($conectar_radius,$deletar_onu_radius);

          array_push($array_processos_historico,"Removido do Radius");

          deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,$servicePortIPTV);
          array_push($array_processos_historico,"Removido do u2000");
        }else{
          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
            VALUES ('BTV Criado e SP IPTV Cadastrada 
            informações relatadas: OLT: $device, PON: $pon, Frame: $frame,
            Porta de Atendimento: $porta_atendimento, Slot: $slot, CTO: $cto Contrato: $contrato,
            MAC: $serial, Perfil: $vasProfile, Internet: $pacote, Telefone: $telNumber,
            Senha Telefone: $telPass','$usuario')";
          $executa_log = mysqli_query($conectar,$sql_insert_log);

          array_push($array_processos_historico,"<p>BTV Criado na OLT</p>");
          array_push($array_processos_historico,"<p style='color:green;';>IPTV Reativada</p>");
        }
      }
    }
  }
  
  ##### FECHA AS CONEXOES COM OS BANCOS #####
  mysqli_close($conectar_radius);
  mysqli_close($conectar);
  array_push($array_processos_historico,$ativado);
  echo "<p style='font-weight:bold;text-align:center'>TIMELINE</p>";

  foreach($array_processos_historico as $historia)
  {
    echo "<div style='text-align:center'>$historia</div>";
  }

?>
