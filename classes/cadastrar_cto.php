<?php
  include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
  session_start();
  $usuario = filter_input(INPUT_SESSION,'id_ususario');
  
  if (!mysqli_connect_errno())
  {
    if( isset($_POST["cto"]) && !empty($_POST["cto"]) && isset($_POST["porta"]) && !empty($_POST["porta"])
        && isset($_POST["pon"]) )
    {
        list($pon_id,$frame,$slot,$porta) = explode("-",$_POST["pon"]);
        $cto = $_POST["cto"];
        $porta_atendimento = $_POST["porta"];
        $pon = "$frame-$slot-$porta";
        

        
         for($portas = 1; $portas <= $porta_atendimento; $portas++)
         {
           $sql_insere_caixa = ("INSERT INTO ctos(caixa_atendimento,porta_atendimento,frame_slot_pon,pon_id_fk) VALUES('$cto',$portas,'$pon','$pon_id')");
           $checar_insert = mysqli_query($conectar,$sql_insere_caixa);
         }

         if($checar_insert)
         {
            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES ('Equipamento $modelo Cadastrado Pelo Usuario de Codigo $usuario','$usuario')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            echo  $_SESSION['menssagem'] = "Caixa de Atendimento Registrada!";
            header('Location: ../cto_classes/cto_create.php');
            mysqli_close($conectar);
            exit;
         }else{
           echo $pon;
           $erro = mysqli_error($conectar);
           $_SESSION['menssagem'] = "CTO Não Cadastrada! SQL: $erro";
           header('Location: ../cto_classes/cto_create.php');
           mysqli_close('$conectar');
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
  INSERT INTO radcheck( username, attribute, op, value) VALUES ( 'vlan2500/slot13/porta0/485754439C96D58B@vertv', 
  'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' ); qual olt

  INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

  INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
  */
?>