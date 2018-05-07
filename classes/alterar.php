<?php
include_once "../db/db_config_mysql.php";
include_once "../db/db_config_radius.php";
// Inicia sessões 
session_start();

if (!mysqli_connect_errno())
{
  if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) && isset($_POST["pacote"]) )
  {
    $usuario = $_SESSION["id_usuario"];
    $contrato = $_POST["contrato"];
    $serial = $_POST["serial"];
    $pacote = $_POST["pacote"];

    $sql_muda_plano_onu = ("UPDATE ont SET pacote = '$pacote' WHERE contrato = '$contrato' AND serial = '$serial'" );

    $novo_pacote = mysqli_query($conectar,$sql_muda_plano_onu);
    if ( $novo_pacote )
    {
      $atualiza_qos_radius = "UPDATE radreply SET value='$pacote' WHERE username='2500/13/0/$serial@vertv' 
          AND attribute='Huawei-Qos-Profile-Name' ";
      $executa_query= mysqli_query($conectar_radius,$atualiza_qos_radius);
      
      if ($executa_query) 
      {
        $_SESSION['menssagem'] = "Velocidade Alterada!";
        header('Location: ../ont_classes/ont_change.php');
        mysqli_close($conectar_radius);
        mysqli_close($conectar);
        exit;  
      }else{
        $erro = mysqli_error($conectar_radius);
        $_SESSION['menssagem'] = "Ocorreu um erro ao alterar no radius SQL: $erro";
        header('Location: ../ont_classes/ont_change.php');
        mysqli_close($conectar_radius);
        mysqli_close($conectar);
        exit;
      }
    }else{
      $erro = mysqli_error($conectar);
      $_SESSION['menssagem'] = "Velocidade Não Alterada! SQL: $erro";
      header('Location: ../ont_classes/ont_change.php');
      mysqli_close($conectar_radius);
      mysqli_close($conectar);
      exit;
    }
  }else{
    $_SESSION['menssagem'] = "Campos Faltando!";
    header('Location: ../ont_classes/ont_change.php');
    mysqli_close($conectar_radius);
    mysqli_close($conectar);
    exit;
  }
}else{
  $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
  header('Location: ../ont_classes/ont_change.php');
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