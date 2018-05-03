<?php
include_once "../db/db_config_mysql.php";
include_once "../db/db_config_radius.php";
// Inicia sessões 
session_start();

if (!mysqli_connect_errno())
{
  if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) && isset($_POST["caixa_atendimento_select"])
    && isset($_POST["pacote"]) )
  {
    $usuario = $_SESSION["id_usuario"];
    $contrato = $_POST["contrato"];
    $serial = strtoupper($_POST["serial"]);
    $cto = $_POST["caixa_atendimento_select"];
    $pacote = $_POST["pacote"];
    $telNumber = $_POST["numeroTel"];
    $telPass = $_POST["passwordTel"];
    $telUser = $_POST["telUser"];

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

    if ($limite_registro < 1) 
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

        $insere_ont_radius_qos_profile = "INSERT INTO radcheck( username, attribute, op, value) 
                VALUES ( '2500/13/0/$serial@vertv', 'Huawei-Qos-Profile-Name', ':=', '$pacote' )";

        $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
        $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);
        $executa_query_qos_profile= mysqli_query($conectar_radius,$insere_ont_radius_qos_profile);

        $sql_atualiza_limite = "UPDATE ont SET limite_equipamentos=0 WHERE contrato = $contrato";
        $diminui_limite = mysqli_query($conectar,$sql_atualiza_limite);

        if ($executa_query_qos_profile && $executa_query_password && $executa_query_username && $diminui_limite) 
        {
          $_SESSION['menssagem'] = "Selecione a Porta de Atendimento!";
          $caixa_atendimento = $_GET['caixa_atendimento_select'] = $cto;
          header("Location: ../ont_classes/_ont_register_porta_disponivel.php?caixa_atendimento_select=$caixa_atendimento&serial=$serial");
          mysqli_close($conectar_radius);
          mysqli_close($conectar);
          exit;
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