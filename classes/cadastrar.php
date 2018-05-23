<?php
include_once "../db/db_config_mysql.php";
include_once "../db/db_config_radius.php";
include_once "../u2000/tl1_sender.php";
// Inicia sessões 
session_start();

if (!mysqli_connect_errno())
{
  if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) && isset($_POST["caixa_atendimento_select"])
    && isset($_POST["pacote"]) )
  {
    list($pon_id,$cto) = explode("-",$_POST["caixa_atendimento_select"]);
    $usuario = $_SESSION["id_usuario"];
    $contrato = $_POST["contrato"];
    $serial = strtoupper($_POST["serial"]);
    $equipment = $_POST['equipamentos'];
    $pacote = $_POST["pacote"];
    $telNumber = $_POST["numeroTel"];
    $telPass = $_POST["passwordTel"];
    $telUser = $_POST["telUser"];
    $vasProfile = $_POST["optionsRadios"];
    $porta_atendimento = '5';

     if(empty($telNumber) && empty($telPass) && empty($telUser) )
     {
         $telNumber = 0;
         $telPass = 0;
         $telUser = 0;
     }
    
     $sql_verifica_limite = "SELECT limite_equipamentos FROM ont WHERE contrato='$contrato'";
     $sql_limite_result = mysqli_query($conectar,$sql_verifica_limite);

     while ($limite = mysqli_fetch_array($sql_limite_result, MYSQLI_BOTH)) 
     {
       $limite_registro = $limite['limite_equipamentos'];
     }

     $sql_verifica_limite = "SELECT limite_equipamentos FROM ont WHERE contrato='$contrato'";

     if ($limite_registro < 1 AND $limite_registro != null) 
     {
       $_SESSION['menssagem'] = "Favor, entrar em contato com o TI, para solicitar aumento de registro de equipamentos";
       header('Location: ../ont_classes/ont_register.php');
       mysqli_close($conectar_radius);
       mysqli_close($conectar);
       exit;
     }

      $sql_registra_onu = ("INSERT INTO ont (contrato, serial, cto, tel_number, tel_user, tel_password, pacote, usuario_id) 
                              VALUES ('$contrato','$serial','$cto','$telNumber','$telUser','$telPass','$pacote','$usuario')" );
    
      $cadastrar = mysqli_query($conectar,$sql_registra_onu);
      if ($cadastrar )               
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

          $sql_atualiza_limite = "UPDATE ont SET limite_equipamentos=0 WHERE contrato = $contrato";
          $diminui_limite = mysqli_query($conectar,$sql_atualiza_limite);

          if ($executa_query_qos_profile && $executa_query_password && $executa_query_username && $diminui_limite) 
          {  #####TL1 INICIO#####
              //SELECIONA O FRAME SLOT e PON PARA ENVIAR VIA TL1
          $select_frame_slot_pon = "SELECT distinct frame_slot_pon FROM ctos WHERE caixa_atendimento = '$cto'";
          $executa_select_frame_slot_pon= mysqli_query($conectar,$select_frame_slot_pon);
          
          //seleciona o nome do disponsitivo no BD
          $deviceName = null;
          $select_deviceName = "SELECT deviceName FROM pon WHERE pon_id = $pon_id";
          $executa_select_deviceName = mysqli_query($conectar,$select_deviceName);
          while ($pon = mysqli_fetch_array($executa_select_deviceName, MYSQLI_BOTH))
          {
            $deviceName = $pon['deviceName'];
          }

          // seleciona o frame,slot e pon no banco
          $frame = 0;
          $slot = 0;
          $pon = 0;
          
          while ($fram_slot_pon = mysqli_fetch_array($executa_select_frame_slot_pon, MYSQLI_BOTH))
          {
            $frame_slot_pon = $fram_slot_pon['frame_slot_pon'];
            list($frame,$slot,$pon) = explode("-", $frame_slot_pon);
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

            header('Location: ../ont_classes/ont_register.php');
            mysqli_close($conectar_radius);
            mysqli_close($conectar);
            exit;
          }else{
            $remove_barras_para_pegar_id = explode("---------------------------",$tira_ponto_virgula[1]);
            $filtra_espaco = explode("\r\n",$remove_barras_para_pegar_id[1]); 
            $pega_id = explode("	",$filtra_espaco[2]);//posicao 4 será sempre o ONTID
            $onuID = $pega_id[4];
            echo "<br>IDOnu: $onuID";

            $insere_ont_id = "UPDATE ont SET ontID='$onuID' WHERE serial = '$serial'";
            $executa_insere_ont_id = mysqli_query($conectar,$insere_ont_id);
            ########FIM TL1########
             $_SESSION['menssagem'] = "Selecione a Porta de Atendimento!";
             $caixa_atendimento = $_GET['caixa_atendimento_select'] = $cto;
             header("Location: ../ont_classes/_ont_register_porta_disponivel.php?caixa_atendimento_select=$caixa_atendimento&serial=$serial");
             mysqli_close($conectar_radius);
             mysqli_close($conectar);
             exit;
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
