<?php
include_once "../db/db_config_mysql.php";
include_once "../db/db_config_radius.php";
include_once "../u2000/tl1_sender.php";
// Inicia sessões 
session_start();

if (!mysqli_connect_errno())
{
  if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) && isset($_POST["caixa_atendimento_select"])
    && isset($_POST["pacote"]) && isset($_POST["porta_atendimento"]) && isset($_POST["frame"]) && isset($_POST["slot"]) &&
     isset($_POST["pon"]) && isset($_POST["deviceName"])  )
  {
    $cto = $_POST["caixa_atendimento_select"];
    $frame = $_POST["frame"];
    $slot = $_POST["slot"];
    $pon = $_POST["pon"];
    $usuario = $_SESSION["id_usuario"];
    $contrato = $_POST["contrato"];
    $serial = strtoupper($_POST["serial"]);
    $equipment = $_POST['equipamentos'];
    $pacote = $_POST["pacote"];
    $telNumber = $_POST["numeroTel"];
    $telPass = $_POST["passwordTel"];
    $vasProfile = $_POST["optionsRadios"];
    $porta_atendimento = $_POST["porta_atendimento"];
    $deviceName = $_POST["deviceName"];
    $ip_olt = NULL;

     if(empty($telNumber) && empty($telPass) )
     {
        $telNumber = 0;
        $telPass = 0;
     }
    
     $sql_verifica_limite = "SELECT limite_equipamentos FROM ont WHERE contrato='$contrato'";
     $sql_limite_result = mysqli_query($conectar,$sql_verifica_limite);
     
     $sql_verifica_limite_ont = "SELECT serial,contrato FROM ont WHERE  serial = '$serial' LIMIT 1"; //verifica se ja existe o mac
     $executa_verifica_limite_ont = mysqli_query($conectar,$sql_verifica_limite_ont);
     var_dump($executa_verifica_limite_ont);
     if(mysqli_num_rows($executa_verifica_limite_ont) > 0) //se o resultado do limite for 1 ele cai aqui
     {
        $limiteONT = mysqli_fetch_array($executa_verifica_limite_ont, MYSQLI_BOTH);
        $_SESSION['menssagem'] = "MAC Já Cadastrado no contrato $limiteONT[contrato]";
        header('Location: ../ont_classes/ont_register.php');
        mysqli_close($conectar_radius);
        mysqli_close($conectar);
        exit;
     }
     while ($limite = mysqli_fetch_array($sql_limite_result, MYSQLI_BOTH)) 
     {
       $limite_registro = $limite['limite_equipamentos'];
     }

     $sql_verifica_limite = "SELECT limite_equipamentos FROM ont WHERE contrato='$contrato'";
     $limite_registro = "";
     if ($limite_registro < 1 AND $limite_registro != null) 
     {
       $_SESSION['menssagem'] = "Favor, entrar em contato com o TI, para solicitar aumento de registro de equipamentos";
       header('Location: ../ont_classes/ont_register.php');
       mysqli_close($conectar_radius);
       mysqli_close($conectar);
       exit;
     }

     if($vasProfile == "VAS_IPTV")
     {
      $pacote = NULL;
     }
      
      $sql_registra_onu = ("INSERT INTO ont (contrato, serial, cto, tel_number, tel_user, tel_password, perfil, pacote, usuario_id,equipamento,porta) 
                              VALUES ('$contrato','$serial','$cto','$telNumber','$telNumber','$telPass','$vasProfile','$pacote','$usuario','$equipment','$porta_atendimento')" );
    
      $cadastrar = mysqli_query($conectar,$sql_registra_onu);
      if ($cadastrar )               
      {
        if($vasProfile != "VAS_IPTV")
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
          $executa_query_username=true;
          $executa_query_password=true;
          $executa_query_qos_profile=true;
        }
          $sql_atualiza_limite = "UPDATE ont SET limite_equipamentos=0 WHERE contrato = $contrato";
          $diminui_limite = mysqli_query($conectar,$sql_atualiza_limite);

          if ($executa_query_qos_profile && $executa_query_password && $executa_query_username && $diminui_limite) 
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
           $contrato,$cto,$porta_atendimento,$serial,$equipment,$vasProfile);
          $onuID = NULL; //zera ONUID para evitar problema de cash.
          $tira_ponto_virgula = explode(";",$ontID);
          $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
          $remove_desc = explode("ENDESC=",$check_sucesso[1]);
          $errorCode = trim($remove_desc[0]);
          if($errorCode != "0")
          {
            $_SESSION['menssagem'] = "Houve erro ao inserir no u2000 SQL: $errorCode";

              //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
            mysqli_query($conectar,$sql_apagar_onu);

            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
              AND attribute='Huawei-Qos-Profile-Name' ";
            $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

            $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
            $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);

            header('Location: ../ont_classes/ont_register.php');
            mysqli_close($conectar_radius);
            mysqli_close($conectar);
            exit;
          }else{
            $remove_barras_para_pegar_id = explode("---------------------------",$tira_ponto_virgula[1]);
            $filtra_espaco = explode("\r\n",$remove_barras_para_pegar_id[1]);
            $pega_id = explode("	",$filtra_espaco[2]);//posicao 4 será sempre o ONTID
            $onuID=trim($pega_id[4]);
            
            $insere_ont_id = "UPDATE ont SET ontID='$onuID' WHERE serial = '$serial'";
            $executa_insere_ont_id = mysqli_query($conectar,$insere_ont_id);
          ##### IPTV SERVICE PORT ######
            if($vasProfile == "VAS_IPTV" || $vasProfile== "VAS_Internet-VoIP-IPTV" || $vasProfile == "VAS_Internet-IPTV") ####SERVICE 
            {
              $servicePortIPTV = get_service_port_iptv($deviceName,$frame,$slot,$pon,$onuID,$contrato);

              $tira_ponto_virgula = explode(";",$servicePortIPTV);
              $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
              $remove_desc = explode("ENDESC=",$check_sucesso[1]);
              $errorCode = trim($remove_desc[0]);
              if($errorCode != "0") //se der erro na service port internet
              {
                $_SESSION['menssagem'] = "Houve erro Inserir a Service Port de IPTV: $errorCode";

                //se der erro ele irá apagar o registro salvo na tabela local ont
                $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
                mysqli_query($conectar,$sql_apagar_onu);
                
                if($vasProfile != "VAS_IPTV")//se for apenas iptv nao apagara o radius, pois nao existe
                {
                  $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
                    AND attribute='Huawei-Qos-Profile-Name' ";
                  $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

                  $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
                  $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
                }
                
                deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID);
                  
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
                $btv_olt = insere_btv_iptv("$ip_olt","$servicePortIptvID");

                if($btv_olt != 'valido' )
                {
                  $_SESSION['menssagem'] = "Houve erro no BTV: $btv_olt";

                  //se der erro ele irá apagar o registro salvo na tabela local ont
                  $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
                  mysqli_query($conectar,$sql_apagar_onu);
                  
                  if($vasProfile != "VAS_IPTV")//se for apenas iptv nao apagara o radius, pois nao existe
                  {
                    $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
                      AND attribute='Huawei-Qos-Profile-Name' ";
                    $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

                    $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
                    $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
                  }
                  
                  deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID);
                    
                  header('Location: ../ont_classes/ont_register.php');
                  mysqli_close($conectar_radius);
                  mysqli_close($conectar);
                  exit;
 
                }
                ### FIM BTV ###
                if($vasProfile == "VAS_IPTV")
                {
                  $_SESSION['menssagem'] = "Cadastrado";
                  //Atualizar Porta CTO
                  $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 1, serial = '$serial'
                    WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
                  $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
                  //Fim Atualizar Porta CTO

                  // header("Location: ../ont_classes/_ont_register_porta_disponivel.php?caixa_atendimento_select=$caixa_atendimento&serial=$serial");
                  mysqli_close($conectar_radius);
                  mysqli_close($conectar);
                  exit;
                }
              }//fim service port iptv
              ##### IPTV SERVICE PORT ######
            }
            
            ###INICIO TELEFONIA TL1###
            if($vasProfile == "VAS_Internet-VoIP" || $vasProfile == "VAS_Internet-VoIP-IPTV") //ATIVAR TELEFONIA
            {
              //echo "\n <br><br> DEV: $deviceName | $frame | $slot | $pon | $onuID | $telNumber | $telPass | $telNumber <br><br> \n";
              $telefone_on = ativa_telefonia($deviceName,$frame,$slot,$pon,$onuID,$telNumber,$telPass,$telNumber);

              //echo "<br> TELON: $telefone_on<br>"; var_dump($telefone_on); echo "<br><br>";

              $tira_ponto_virgula = explode(";",$telefone_on);
              $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
              $remove_desc = explode("ENDESC=",$check_sucesso[1]);
              $errorCode = trim($remove_desc[0]);
              if($errorCode != "0")
              {
                $_SESSION['menssagem'] = "Houve erro ao inserir no u2000 SQL: $errorCode";
                $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
                mysqli_query($conectar,$sql_apagar_onu);

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
                  $_SESSION['menssagem'] = "Houve erro Inserir a Service Port Telefonia: $errorCode";
    
                  //se der erro ele irá apagar o registro salvo na tabela local ont
                   $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
                   mysqli_query($conectar,$sql_apagar_onu);
    
                   $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
                     AND attribute='Huawei-Qos-Profile-Name' ";
                   $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);
    
                   $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
                   $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
                  
                   deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID);
                  
                  header('Location: ../ont_classes/ont_register.php');
                  mysqli_close($conectar_radius);
                  mysqli_close($conectar);
                  exit;
                }else{
                  $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
                  $pegar_servicePortTel_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);
                  
                  $pega_id = explode("	",$pegar_servicePortTel_ID[2]);//posicao 4 será sempre o ONTID
                  
                  $servicePortTelefoneID= $pega_id[0] - 1; 
                  
                  $insere_service_telefone = "UPDATE ont SET service_port_telefone='$servicePortTelefoneID' WHERE serial = '$serial'";
                  $executa_insere_service_telefone = mysqli_query($conectar,$insere_service_telefone);
                  
                }//fim service port telefonia
              }
            } //FIM ATIVA TELEFONIA  
            ########FIM TL1########
            
            $servicePortInternet = get_service_port_internet($deviceName,$frame,$slot,$pon,$onuID,$contrato);

            $tira_ponto_virgula = explode(";",$servicePortInternet);
            $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
            $remove_desc = explode("ENDESC=",$check_sucesso[1]);
            $errorCode = trim($remove_desc[0]);
            if($errorCode != "0") //se der erro na service port internet
            {
              $_SESSION['menssagem'] = "Houve erro Inserir a Service Port de Internet: $errorCode";

              //se der erro ele irá apagar o registro salvo na tabela local ont
              $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );
              mysqli_query($conectar,$sql_apagar_onu);

              $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
                AND attribute='Huawei-Qos-Profile-Name' ";
              $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

              $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
              $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
              
              deletar_onu_2000($deviceName,$frame,$slot,$pon,$onuID);
              
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
              
              $insere_service_internet = "UPDATE ont SET service_port_internet='$servicePortInternetID' WHERE serial = '$serial'";
              $executa_insere_service_internet = mysqli_query($conectar,$insere_service_internet);
              $_SESSION['menssagem'] = "Cadastrado";
              //Atualizar Porta CTO
              $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 1, serial = '$serial'
                WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_atendimento'";
              $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
              header('Location: ../ont_classes/ont_register.php');
              // fim Atualizar Porta CTO
              mysqli_close($conectar_radius);
              mysqli_close($conectar);
              exit;
            }//fim service port internet
          }
        }else{
          $erro = mysqli_error($conectar_radius);
          $_SESSION['menssagem'] = "Houve erro ao inserir no Radius SQL: $erro";

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
        $_SESSION['menssagem'] = "Houve erro na execuão da query SQL: $erro";
        header('Location: ../ont_classes/ont_register.php');
        mysqli_close($conectar_radius);
        mysqli_close($conectar);
        exit;
      }
   }
   else
   {
       $_SESSION['menssagem'] = "Campos Faltando!";
       header('Location: ../ont_classes/ont_register.php');
       mysqli_close($conectar_radius);
       mysqli_close($conectar);
       exit;
   }
}else{
  $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
  header('Location: ../ont_classes/ont_register.php');
  mysqli_close($conectar_radius);
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
