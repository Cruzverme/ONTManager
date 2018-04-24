<?php
  include_once "../db/db_config_mysql.php";
  // Inicia sessões 
  session_start();

  if (!mysqli_connect_errno())
  {
      if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) && isset($_POST["cto"])
      && isset($_POST["porta"]) && isset($_POST["pacote"]) )
      {
            $usuario = $_SESSION["id_usuario"];
            $contrato = $_POST["contrato"];
            $serial = $_POST["serial"];
            $cto = $_POST["cto"];
            $porta = $_POST["porta"];
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
          
            $sql_registra_onu = ("INSERT INTO ont (contrato, pon_mac, cto, tel_number, tel_user, tel_password, pacote, usuario_id, porta) 
                                    VALUES ('$contrato','$serial','$cto','$telNumber','$telUser','$telPass','$pacote','$usuario','$porta')" );

            $cadastrar = mysqli_query($conectar,$sql_registra_onu);
            if ($cadastrar )               
            {
                $_SESSION['menssagem'] = "ONU Cadastrada!";
                header('Location: ../ont_classes/ont_register.php');
                mysqli_close($conectar);
                exit;
            }else{
                $_SESSION['menssagem'] = "ONU Não Cadastrada! \n 'Houve erro na execuão da query SQL: '.mysqli_error($conectar)";
                header('Location: ../ont_classes/ont_register.php');
                mysqli_close($conectar);
                exit;
            }
      }
      else
      {
          $_SESSION['menssagem'] = "Campos Faltando!";
          header('Location: ../ont_classes/ont_register.php');
          mysqli_close($conectar);
          exit;
      }
  }else{
      $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
      header('Location: ../ont_classes/ont_register.php');
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