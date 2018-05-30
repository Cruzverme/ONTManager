<?php
  include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
  session_start();

  $modelo = filter_input(INPUT_POST, 'modelo');
  
  if($modelo)
  {
    if(!mysqli_connect_errno())
    {
      $sql_insere_equipamento = ("INSERT INTO equipamentos(modelo) VALUES('$modelo')");
      $checar_equipamento = mysqli_query($conectar,$sql_insere_equipamento);

      if($checar_equipamento)
      { 
        echo  $_SESSION['menssagem'] = "Equipamento Registrado!";
        header('Location: ../equipamento/cadastro_equipamento.php');
        mysqli_close($conectar);
        exit;
      }
    }else{
      echo $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
      header('Location: ../index.php');
      mysqli_close($conectar);
      exit;
    }
  }else{

    echo $_SESSION['menssagem'] = "Campos Faltando!";
    header('Location: ../equipamento/cadastro_equipamento.php');
    mysqli_close($conectar);
    exit;
  }

?>