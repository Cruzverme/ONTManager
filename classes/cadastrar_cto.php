<?php
  include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
  session_start();

  if (!mysqli_connect_errno())
  {
    if( isset($_POST["cto"]) && !empty($_POST["cto"]) && isset($_POST["porta"]) && !empty($_POST["porta"]) )
    {
        $cto = $_POST["cto"];
        $porta_atendimento = $_POST["porta"];
        
        for($portas = 1; $portas <= $porta_atendimento; $portas++)
        {
          $sql_insere_caixa = ("INSERT INTO ctos(caixa_atendimento,porta_atendimento) VALUES('$cto',$portas)");
          $checar_insert = mysqli_query($conectar,$sql_insere_caixa);
        }

        if($checar_insert)
        { 
             echo  $_SESSION['menssagem'] = "Caixa Registrada!";
              header('Location: ../cto_classes/cto_create.php');
              mysqli_close($conectar);
              exit;
        }
    }else{
      echo $_SESSION['menssagem'] = "Campos Faltando!";
      header('Location: ../cto_classes/cto_create.php');
      mysqli_close($conectar);
      exit;
    }
  }else{
    echo $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
    header('Location: ../index.php');
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