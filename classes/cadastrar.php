<?php
  include_once "../db/db_config_mysql.php";
  // Inicia sessões 
  session_start();

  if (!mysqli_connect_errno())
  {
      if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) && isset($_POST["caixa_atendimento_select"])
        && isset($_POST["pacote"]) )
      {
            $usuario = $_SESSION["id_usuario"];
            $contrato = $_POST["contrato"];
            $serial = $_POST["serial"];
            $cto = $_POST["caixa_atendimento_select"];
            //$porta = $_POST["porta"];
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
          
            $sql_registra_onu = ("INSERT INTO ont (contrato, serial, celula, tel_number, tel_user, tel_password, pacote, fk_usuario_id) 
                                    VALUES ('$contrato','$serial','$cto','$telNumber','$telUser','$telPass','$pacote','$usuario')" );

            $cadastrar = mysqli_query($conectar,$sql_registra_onu);
            if ($cadastrar )               
            {
                $_SESSION['menssagem'] = "Selecione a Porta de Atendimento!";
                $caixa_atendimento = $_GET['caixa_atendimento_select'] = $cto;
                header("Location: ../ont_classes/_ont_register_porta_disponivel.php?caixa_atendimento_select=$caixa_atendimento");
                //header('Location: ../ont_classes/ont_register.php');
                mysqli_close($conectar);
                exit;
            }else{
                $erro = mysqli_error($conectar);
                $_SESSION['menssagem'] = "Houve erro na execuão da query SQL: $erro";
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