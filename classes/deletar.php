<?php
  include_once "../db/db_config_mysql.php";
  include_once "../db/db_config_radius.php";
  // Inicia sessões 
  session_start();

  if (!mysqli_connect_errno())
  {
    if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) )
    {
      $usuario = $_SESSION["id_usuario"];
      $contrato = $_POST["contrato"];
      $serial = $_POST["serial"];

      
      $sql_disponibiliza_porta = "UPDATE ctos SET porta_atendimento_disponivel = 1 WHERE serial = '$serial'";
      $executa_query = mysqli_query($conectar,$sql_disponibiliza_porta);

      if ($executa_query) 
      {
        $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='2500/13/0/$serial@vertv' 
          AND attribute='Huawei-Qos-Profile-Name' ";
        $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

        $deletar_onu_radius = " DELETE FROM radcheck WHERE username='2500/13/0/$serial@vertv' ";
        $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);

        if ($executa_query && $deletar_onu_radius) 
        {
          $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial'" );

          $deletar_onu = mysqli_query($conectar,$sql_apagar_onu);
          if($deletar_onu)
          {
              if ( $total = mysqli_affected_rows($conectar))    //retorna quantas rows foram afetadas           
              {
                  $_SESSION['menssagem'] = "$total ONU Removida!";
                  header('Location: ../ont_classes/ont_delete.php');
                  mysqli_close($conectar);
                  exit;
              }else{
                  $_SESSION['menssagem'] = "ONU Não Removida!";
                  header('Location: ../ont_classes/ont_delete.php');
                  mysqli_close($conectar);
                  exit;
              }
          }else{
              $erro = mysqli_error($conectar);
              $_SESSION['menssagem'] = "Houve erro ao deletar SQL: $erro";
              header('Location: ../ont_classes/ont_delete.php');
              mysqli_close($conectar);
              exit;
          }
        }else{
          $erro = mysqli_error($conectar);
          $_SESSION['menssagem'] = "Houve erro ao deletar SQL Radius: $erro";
          header('Location: ../ont_classes/ont_delete.php');
          mysqli_close($conectar);
          exit;
        }
      }else{
        $erro = mysqli_error($conectar);
        $_SESSION['menssagem'] = "Houve erro ao deletar SQL: $erro";
        header('Location: ../ont_classes/ont_delete.php');
        mysqli_close($conectar);
        exit;
      }
    }
    else
    {
      $_SESSION['menssagem'] = "Campos Faltando!";
      header('Location: ../ont_classes/ont_delete.php');
      mysqli_close($conectar);
      exit;
    }
  }else{
    $erro = mysqli_error($conectar);
    $_SESSION['menssagem'] = "Não Consegui Contato com Servidor! $erro";
    header('Location: ../ont_classes/ont_delete.php');
    mysqli_close($conectar);
    exit;
  }
  /* close connection */

  
/*
SQL PARA SALVAR NO RADIUS
INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' );

INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
*/
?>