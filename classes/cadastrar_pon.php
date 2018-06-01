<?php
  include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
  session_start();

  $nomeDispositivo = filter_input(INPUT_POST,'nomeDev');
  $frame = filter_input(INPUT_POST, 'frame');
  $slot = filter_input(INPUT_POST, 'slot');
  $porta = filter_input(INPUT_POST,'porta');
  $ipOLT = filter_input(INPUT_POST,'ipOLT');

  if($frame || $frame == 0 && $slot && $porta && $ipOLT )
  {
    if(!mysqli_connect_errno())
    {
      $sql_insere_pon = ("INSERT INTO pon(deviceName,frame,slot,porta,deviceName,olt_ip) 
        VALUES('$nomeDispositivo','$frame','$slot',$porta,'$ipOLT')");
      $checar_insert = mysqli_query($conectar,$sql_insere_pon);

      if($checar_insert)
      { 
        echo  $_SESSION['menssagem'] = "PON Registrada!";
        header('Location: ../cto_classes/pon_create.php');
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
    header('Location: ../cto_classes/pon_create.php');
    mysqli_close($conectar);
    exit;
  }

  /*
  SQL PARA SALVAR NO RADIUS
  INSERT INTO radcheck( username, attribute, op, value) VALUES ( 'vlan2500/slot13/porta0/485754439C96D58B@vertv', 
  'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' ); qual olt

  INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

  INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
  */
?>