<?php
  include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
  session_start();

  if (!mysqli_connect_errno())
  {
    if( isset($_GET["porta_atendimento_selecionada"]) )
    {
      $porta_selecionada = $_GET["porta_atendimento_selecionada"];
      
      $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 1 WHERE porta_atendimento= $porta_selecionada";
      $executa_query = mysqli_query($conectar,$sql_insere_porta);
      
      if ($executa_query )               
      {
          $_SESSION['menssagem'] = "ONU Cadastrada!";
          header('Location: ../ont_classes/ont_register.php');
          mysqli_close($conectar);
          exit;
      }else{
          $erro = mysqli_error($conectar);
          $_SESSION['menssagem'] = "Houve erro na execuão da query SQL: $erro";
          header("Location: ../ont_classes/_ont_register_porta_disponivel.php?caixa_atendimento_selecionada=$caixa_atendimento");
          mysqli_close($conectar);
          exit;
      }
    }else{
      $_SESSION['menssagem'] = "Campos Faltando!";
      mysqli_close($conectar);
      header("Location: ../ont_classes/_ont_register_porta_disponivel.php?caixa_atendimento_selecionada=$caixa_atendimento");
      exit;
    }
  }else{
    $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
    header('Location: ../usuario_new.php');
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


