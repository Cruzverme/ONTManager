<html>
  <head>
    <script>
      $(document).on("load",{
        ajaxStart: function() { $body.addClass("loading"); }
      });
    </script>
    <link rel='stylesheet' type='text/css' href='../vendor/vertv/vertv.css' />
  </head>
  <body>
    <h1><center>INICIANDO PROCESSO</center></h1>
  <?php 
    set_time_limit(0);
    include_once "../db/db_config_mysql.php";
    include_once "../db/db_config_radius.php";
    include_once "../u2000/tl1_sender.php";
    include_once "funcoes.php";

    // Iniciamos o "contador"
    list($usec, $sec) = explode(' ', microtime());
    $script_start = (float) $sec + (float) $usec;

    $contra2 = $_POST['contrato'];

    foreach($contra2 as $contrato)
    {
      echo "<hr/>";
      echo "<div>";
      
      list($contrato,$serial, $status,$cto, $porta_atendimento, $usuario,$tel_number,$tel_user,$tel_pass,$vasProfile,
      $pacote, $limite,$equipment, $ontID, $servI, $servTV, $servTel,$device,$frame_slot_pon,$ip) = explode('.',$contrato);

      list($frame,$slot,$pon) = explode('-',$frame_slot_pon);

      echo "Contrato: $contrato,
            PON: $serial,
            Status: $status,
            CTO: $cto,
            Porta Atendimento: $porta_atendimento,
            UsuarioID: $usuario,
            Telefone: $tel_number,
            Telefone Senha: $tel_pass,
            VasProfile: $vasProfile,
            Pacote: $pacote,
            Modelo ONT: $equipment,
            ONT ID: $ontID,
            ServicePortInternet: $servI,
            ServicePortTV: $servTV,
            ServicePortTelefone: $servTel <br/>";

      $modo_bridge = NULL;
      $mac_novo = NULL;
      $ip_novo = NULL;

    
      
      ############################################

      $sql_insere_nat = "INSERT INTO `nat_em_processo`(`contrato`, `serial`, `status`, `cto`, `porta`,
                `usuario_id`, `tel_number`, `tel_user`, `tel_password`, 
                `perfil`, `pacote`, `limite_equipamentos`, `equipamento`,
                  `ontID`, `service_port_internet`, `service_port_iptv`, `service_port_telefone`)
                  VALUES('$contrato','$serial',$status,'$cto','$porta_atendimento',
                    $usuario,'$tel_number','$tel_user','$tel_pass',
                    '$vasProfile','$pacote', $limite,'$equipment',
                    '$ontID', '$servI','$servTV', '$servTel')";

      $executa_sql_insere_nat = mysqli_query($conectar,$sql_insere_nat);

      $tipoNAT = 1;

      #### IDENTIFICA O VAS PROFILE PARA SER ALTERADO PARA O NOVO do CGNAT ######
      switch ($vasProfile) {
        case 'VAS_Internet':
          $vasProfile = "VAS_Internet-CGNAT"; echo "Alterado para $vasProfile <br>";
          break;
        case 'VAS_Internet-IPTV':
          $vasProfile = "VAS_Internet-IPTV-CGNAT";echo "Alterado para $vasProfile <br>";
          break;
        case 'VAS_Internet-VoIP':
          $vasProfile = "VAS_Internet-VoIP-CGNAT";echo "Alterado para $vasProfile <br>";
          break;      
        case 'VAS_Internet-VoIP-IPTV':
          $vasProfile = "VAS_Internet-VoIP-IPTV-CGNAT";echo "Alterado para $vasProfile <br>";
          break;
        default:
          echo "O VasProfile é $vasProfile <br>";
          break;
      }

  ############################ INICIO DO PROCESSO DE MIGRAÇÃO ###############################

      //pega o Alias do assinante
      $json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_pacote_ftth_cplus.php?contra=$contrato");
      $json_str = json_decode($json_file, true);
      $itens = $json_str['velocidade'];
      $nome = $json_str['nome'];
      $nomeCompleto = str_replace(" ","_",$nome[0]);
      //fim alias
      sleep(3);

      $deletar_2000 = deletar_onu_2000($device,$frame,$slot,$pon,$ontID,$ip,$servTV);
      $tira_ponto_virgula = explode(";",$deletar_2000);
      $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
      $remove_desc = explode("ENDESC=",$check_sucesso[1]);
      $errorCode = trim($remove_desc[0]);
      
      if($errorCode != "0" && $errorCode != "1615331086") //se der erro ao deletar a ONT
      {
        
        $trato = tratar_errors($errorCode);

        $mensagem_erro = "Não foi possível deletar a ONT! $errorCode $trato";

        $sql_salva_erro = "UPDATE nat_em_processo SET erro_gerado = '$mensagem_erro' WHERE contrato = $contrato";
        $executa_salva_erro = mysqli_query($conectar,$sql_salva_erro);

        echo "OCORREU ERRO: FAVOR VERIFICAR $mensagem_erro<br>";

        continue;
      
      }else{  
        ######### Cadastro a OLT Novamente ##############
        echo "Recadastrando ONT: PON => $serial <br>";

        $ontID = cadastrar_ont($device,$frame,$slot,$pon,$contrato,$nomeCompleto,$cto,$porta_atendimento,
          $serial,$equipment,$vasProfile,$tipoNAT);
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
                  Internet: $pacote, Telefone: $tel_number,
                  Senha Telefone: $tel_pass,$usuario)";
          
          $executa_log = mysqli_query($conectar,$sql_insert_log);
          
        //remove radius
          $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username like '%$serial@vertv%' ";
          $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

          $deletar_onu_radius = " DELETE FROM radcheck WHERE username like '%$serial@vertv%' ";
          $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
        // retorna as conf antigas
          
          deu_ruim_callback($device,$frame,$slot,$pon,$contrato,$nomeCompleto,$cto,$porta_atendimento,$serial,$equipment,$vasProfile,
            $tel_number,$tel_pass,$pacote);
        
          $mensagem_erro =  "Não Consegui Recadastrar a ONT! $errorCode $trato";

          $sql_salva_erro = "UPDATE nat_em_processo SET erro_gerado = '$mensagem_erro' WHERE contrato = $contrato";
          $executa_salva_erro = mysqli_query($conectar,$sql_salva_erro);
          
          echo "OCORREU ERRO AO RECADASTRAR NO U2000: $mensagem_erro<br>";
          continue;

        }else{ //Se Ele Cadastrar a ONT
          $remove_barras_para_pegar_id = explode("---------------------------",$tira_ponto_virgula[1]);
          $filtra_espaco = explode("\r\n",$remove_barras_para_pegar_id[1]);
          $pega_id = preg_split('/\s+/',$filtra_espaco[2]);
          $onuID=trim($pega_id[4]);

          $insere_ont_id = "UPDATE ont SET ontID='$onuID', perfil='$vasProfile',
                              service_port_internet=NULL,service_port_telefone=NULL,
                              service_port_iptv=NULL,mac=NULL,ip=NULL
                            WHERE serial = '$serial'";
          
          echo "Cadastrei o Contrato $contrato  com PON $serial e de nome $nomeCompleto <br/>";
          ######### Fim Cadastro de OLT #############

          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('DNAT: ONT criada no u2000',$usuario)";
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

          if( $vasProfile == "VAS_Internet-CGNAT" || $vasProfile == "VAS_Internet-VoIP-CGNAT" 
          || $vasProfile == "VAS_Internet-IPTV-CGNAT" || $vasProfile == "VAS_Internet-VoIP-IPTV-CGNAT"   ) //se somente internet
          {
            ############ INSERE RADIUS ############
            $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
                  VALUES ( '2504/$slot/$pon/$serial@vertv-cgnat', 'User-Name', ':=', '2504/$slot/$pon/$serial@vertv-cgnat' )";

            $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value) 
                  VALUES ( '2504/$slot/$pon/$serial@vertv-cgnat', 'Cleartext-Password', ':=', 'vlan' )";

            $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value) 
                  VALUES ( '2504/$slot/$pon/$serial@vertv-cgnat', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";
          }
          $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
          $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);
          $executa_query_qos_profile= mysqli_query($conectar_radius,$insere_ont_radius_qos_profile);
          ########## FIM INSERE RADIUS ##############

          ##### CRIA SERVIEC PORT INTERNET #####

          if($executa_query_qos_profile)
          {
            echo "Radius Alterado para a VLAN 2504...Estou Quase Lá... <br>";
            $atualiza_banda_local = "UPDATE ont SET pacote='$pacote' WHERE serial = '$serial'";
            $executa_atualiza_banda_local = mysqli_query($conectar,$atualiza_banda_local);
          }

          $servicePortInternet = get_service_port_internet($device,$frame,$slot,$pon,$onuID,$contrato,$vasProfile,$modo_bridge,$tipoNAT);
          
          $tira_ponto_virgula = explode(";",$servicePortInternet);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0"){ //se der erro ao pegar service port
            
            $trato = tratar_errors($errorCode);
            //salva em LOG
            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
            VALUES (ERRO NO U2000 AO ALTERAR A ONTID $trato 
            informações relatadas: 
                OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_atendimento, 
                Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial, Novo Perfil: $vasProfile, 
                Internet: $pacote, Telefone: $tel_number,
                Senha Telefone: $tel_pass,$usuario)";
        
            $executa_log = mysqli_query($conectar,$sql_insert_log);
            
            $mensagem_erro = "Houve erro Inserir a Service Port de Internet: $errorCode $trato";

            $sql_salva_erro = "UPDATE nat_em_processo SET erro_gerado = '$mensagem_erro' WHERE contrato = $contrato";
            $executa_salva_erro = mysqli_query($conectar,$sql_salva_erro);
            
            echo "OCORREU ERRO A CRIAR A SERVICE PORT FAVOR VERIFICAR $mensagem_erro <br>";

            continue;
            
          }else{ // se nao der erro
            
            $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
            $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
            
            $pega_id = explode("  ",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
            
            $servicePortInternetID= $pega_id[0] - 1;
            
            $insere_service_internet = "UPDATE ont SET service_port_internet=$servicePortInternetID, mac = $mac_novo,ip=$ip_novo 
              WHERE serial = '$serial'";
            $executa_insere_service_internet = mysqli_query($conectar,$insere_service_internet);
            
            $deletar_em_progresso = "DELETE FROM nat_em_processo WHERE contrato = '$contrato'";
            $executa_remove_in_progress = mysqli_query($conectar,$deletar_em_progresso);
            echo "EU PASSEI NA PROVA DO DNAT, ESTAREI EM OUTRA TABELA <br>";

            $sql_executado = "INSERT INTO `nat_executado`(`contrato`, `serial`, `status`, `cto`, `porta`, `usuario_id`,
                                                        `tel_number`, `tel_user`, `tel_password`, `perfil`,
                                                        `pacote`, `limite_equipamentos`, `equipamento`, `ontID`, 
                                                        `service_port_internet`, `service_port_iptv`, `service_port_telefone`, `executado`) 
                              VALUES ('$contrato','$serial',$status,'$cto','$porta_atendimento', $usuario,
                                    '$tel_number','$tel_user','$tel_pass', '$vasProfile',
                                    '$pacote', $limite,'$equipment', '$onuID', 
                                    '$servI','$servTV', '$servTel', true)";
            
            $executa_sql_executado = mysqli_query($conectar,$sql_executado);

            echo "<br/>Estou na minha nova tabela, pois agora sou DNAT! <br/>";

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('Service Port Internet Criada $servicePortInternetID',$usuario)";
            mysqli_query($conectar,$sql_insert_log);
            
            if($vasProfile == "VAS_Internet-CGNAT")
            {
              echo "CONCLUI MINHA DNAT, PRÓXIMO! Plano Alterado! Em caso de alteração de Velocidade: Consulte o Equipamento e Reinicie Para efetivar a mudança <br>";
            }
          }
        }#FIM CADASTRA ONT INTERNET
        ######### SE VOIP #########
        ######### SE VOIP #########
        if($vasProfile == "VAS_Internet-VoIP" || $vasProfile == "VAS_Internet-VoIP-IPTV" || $vasProfile == "VAS_Internet-VoIP-IPTV-CGNAT" 
          || $vasProfile == "VAS_IPTV-VoIP" || $vasProfile == "VAS_Internet-VoIP-CGNAT")
        {
          
          ########## ATIVA TL1 ############
          $telefone_on = ativa_telefonia($device,$frame,$slot,$pon,$onuID,$tel_number,$tel_pass,$tel_number);
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
                  Internet: $pacote, Telefone: $tel_number,
                  Senha Telefone: $tel_pass,$usuario)";
          
              $executa_log = mysqli_query($conectar,$sql_insert_log);

              $mensagem_erro = "Não foi possível Ativar a Telefonia! $errorCode $trato";

              $sql_salva_erro = "UPDATE nat_em_processo SET erro_gerado = '$mensagem_erro' WHERE contrato = $contrato";
              $executa_salva_erro = mysqli_query($conectar,$sql_salva_erro);
              
              echo "OCORREU ERRO AO ATIVAR O TL1 FAVOR VERIFICAR $mensagem_erro<br>";
              continue;
              
          }else{
              echo "Ativei o TL1 e estou iniciando Service Port Telefone";
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
                    Internet: $pacote, Telefone: $tel_number,
                    Senha Telefone: $tel_pass,$usuario)";
            
                $executa_log = mysqli_query($conectar,$sql_insert_log);

                $mensagem_erro = "Houve erro Inserir a Service Port Telefonia: $trato";

                $sql_salva_erro = "UPDATE nat_em_processo SET erro_gerado = '$mensagem_erro' WHERE contrato = $contrato";
                $executa_salva_erro = mysqli_query($conectar,$sql_salva_erro);

                echo "OCORREU ERRO NO SERVICE PORTA TELEFONIA FAVOR VERIFICAR $mensagem_erro<br>";
                continue;
                

              }else{
                
                $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
                $pegar_servicePortTel_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
                
                $pega_id = explode("  ",$pegar_servicePortTel_ID[2]);//posicao 4 será sempre o ONTID
                
                $servicePortTelefoneID= $pega_id[0] - 1;
                
                $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('Service Port Telefonia Criada: $servicePortTelefoneID',$usuario)";
                mysqli_query($conectar,$sql_insert_log);
                
                $insere_service_telefone = "UPDATE ont SET service_port_telefone='$servicePortTelefoneID',tel_user='$tel_number',tel_number='$tel_number',tel_password='$tel_pass'
                WHERE serial = '$serial'";
                $executa_insere_service_telefone = mysqli_query($conectar,$insere_service_telefone);
                
                echo "Agora posso te ligar! Atende Humano!!<br>";
                
                if($vasProfile == "VAS_Internet-VoIP")
                {
                  "Plano Alterado! Em caso de alteração de Velocidade: Consulte o Equipamento e Reinicie Para efetivar a mudança";
                  echo "Agora Posso Navegador na internet e te ligar, vou te pertubar! PRÓXIMO!!<br>";
                }
              }
          }
        }

        #################### SE FOR IPTV #################################  
        if($vasProfile == "VAS_IPTV" || $vasProfile == "VAS_Internet-IPTV" || $vasProfile == "VAS_Internet-VoIP-IPTV" ||
        $vasProfile == "VAS_IPTV-VoIP" || $vasProfile == "VAS_Internet-IPTV-CGNAT" || $vasProfile == "VAS_Internet-VoIP-IPTV-CGNAT")
        {
          echo "Estou criando o service Port IPTV! <br>";
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
                Internet: $pacote, Telefone: $tel_number,
                Senha Telefone: $tel_pass,$usuario)";
        
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            $mensagem_erro = "Houve erro Inserir a Service Port IPTV: $trato";

            $sql_salva_erro = "UPDATE nat_em_processo SET erro_gerado = '$mensagem_erro' WHERE contrato = $contrato";
            $executa_salva_erro = mysqli_query($conectar,$sql_salva_erro);
            
            echo "OCORREU ERRO AO CRIAR O SERVICE PORT de IPTV FAVOR VERIFICAR $mensagem_erro<br>";

            continue;
          }else{
            $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
            $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
            
            $pega_id = explode("  ",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
            
            $servicePortIptvID= $pega_id[0] - 1;
            
            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('Service Port de IPTV Criada: $servicePortIptvID',$usuario)";
            mysqli_query($conectar,$sql_insert_log);
            
            $insere_service_iptv = "UPDATE ont SET service_port_iptv='$servicePortIptvID' WHERE serial = '$serial'";
            $executa_insere_service_iptv = mysqli_query($conectar,$insere_service_iptv);

            echo "Criei o service Port de IPTV, vamos continuar<br>";
            
            ### BTV ###
            $btv_olt = insere_btv_iptv($device,$frame,$slot,$pon,$onuID);
            $tira_ponto_virgula = explode(";",$btv_olt);
            $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
            $remove_desc = explode("ENDESC=",$check_sucesso[1]);
            $errorCode = trim($remove_desc[0]);

            if($errorCode != "0") //se der erro na btv iptv
            {
              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('Erro ao Inserir o BTV - Service Port: $servicePortIptvID',$usuario)";
              mysqli_query($conectar,$sql_insert_log);

              $trato = tratar_errors($errorCode);

              $mensagem_erro = "Não foi possível Inserir no BTV a ONT! $errorCode $trato";

              $sql_salva_erro = "UPDATE nat_em_processo SET erro_gerado = '$mensagem_erro' WHERE contrato = $contrato";
              $executa_salva_erro = mysqli_query($conectar,$sql_salva_erro);

              
              //salva em LOG
              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES (ERRO NO U2000 AO ALTERAR A ONTID $trato 
              informações relatadas BTV IpTV: 
                  OLT: $device, PON: $pon, Frame: $frame,
                  Porta de Atendimento: $porta_atendimento, 
                  Slot: $slot, CTO: $cto Contrato: $contrato,
                  MAC: $serial, Novo Perfil: $vasProfile, 
                  Internet: $pacote, Telefone: $tel_number,
                  Senha Telefone: $tel_pass,$usuario)";
          
              $executa_log = mysqli_query($conectar,$sql_insert_log);

              echo "OCORREU ERRO AO CRIAR O BTV FAVOR VERIFICAR $mensagem_erro<br>";
              continue;
              
            }else{

              $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES ('Service Port - $servicePortIptvID - Adicionado na BTV da OLT de $ip',$usuario)";
              mysqli_query($conectar,$sql_insert_log);
              
              //se der tudo ok ira aparecer a msg!
              $_SESSION['menssagem'] = "Plano Alterado! Em caso de alteração de Velocidade: Consulte o Equipamento e Reinicie Para efetivar a mudança";
              echo "Sei que hoje em dia estou morrendo, mas me assistam, agora você pode ver a TV<br>";
            }
          }
        }
      }
      
      echo "</div>";
    }

      // header('Location: ../ont_classes/troca_nat.php');
      // mysqli_close($conectar_radius);
      // mysqli_close($conectar);
      // exit;

  // Terminamos o "contador" e exibimos
    list($usec, $sec) = explode(' ', microtime());
    $script_end = (float) $sec + (float) $usec;
    $elapsed_time = round($script_end - $script_start, 5);

  // Exibimos uma mensagem
    echo "<hr>";
    echo '<br/><br/>Elapsed time: ', $elapsed_time, ' secs. Memory usage: ', round(((memory_get_peak_usage(true) / 1024) / 1024), 2), 'Mb <br/><br/>';
    echo "<h2><center>Como dizia meu amigo Gaguinho, Isso é tudo pessoal!</center></h2>";
  ?>
  </body>
  <a href="../ont_classes/troca_nat.php"><button type='button'>VOLTAR</button></a> 
</html>