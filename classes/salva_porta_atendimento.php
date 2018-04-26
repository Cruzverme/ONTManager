<?php
  include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
  session_start();

  if (!mysqli_connect_errno())
  {
    if( isset($_GET["porta_atendimento_selecionada"]) && isset($_GET['serial']) && isset($_GET['caixa_atendimento'])
     && !empty($_GET['serial']) && !empty($_GET['porta_atendimento_selecionada']) && !empty($_GET['caixa_atendimento']) )
    {
      $porta_selecionada = $_GET["porta_atendimento_selecionada"];
      $serial = $_GET['serial'];
      $caixa = $_GET['caixa_atendimento'];
      
      $sql_atualiza_porta_ont = "UPDATE ont SET porta= $porta_selecionada 
        WHERE serial = '$serial' and cto = '$caixa'";

      $executa_porta_ont = mysqli_query($conectar,$sql_atualiza_porta_ont);

      if ($executa_porta_ont )               
      {
        
        $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 1, serial = '$serial'
          WHERE caixa_atendimento = '$caixa' AND porta_atendimento= $porta_selecionada";
        $executa_query = mysqli_query($conectar,$sql_insere_porta);

        if ($executa_query)
        {
          $_SESSION['menssagem'] = "ONU Cadastrada!";
          header('Location: ../ont_classes/ont_register.php');
          mysqli_close($conectar);
          exit;        
        }else{
          $erro = mysqli_error($conectar);
          $_SESSION['menssagem'] = "Houve um erro ao inserir a porta SQL: $erro";

          //se deu problema ele retornará o valor da porta na tabela local ont para 0
          $sql_atualiza_porta_ont = "UPDATE ont SET porta= NULL
            WHERE serial = '$serial' and cto = '$caixa'";
          mysqli_query($conectar,$sql_atualiza_porta_ont);

          header("Location: ../ont_classes/_ont_register_porta_disponivel.php?caixa_atendimento_select=$caixa&serial=$serial");
          mysqli_close($conectar);
          exit;
        } 
      }else{       
          $erro = mysqli_error($conectar);
          $_SESSION['menssagem'] = "Houve erro na execuão da query SQL: $erro";
          header("Location: ../ont_classes/_ont_register_porta_disponivel.php?caixa_atendimento_select=$caixa&serial=$serial");
          mysqli_close($conectar);
          exit;
      }
    }else{
      $_SESSION['menssagem'] = "Campos Faltando!";
      mysqli_close($conectar);
      header("Location: ../ont_classes/_ont_register_porta_disponivel.php?caixa_atendimento_select=$caixa&serial=$serial");
      exit;
    }
  }else{
    $erro = mysqli_error($conectar);
    $_SESSION['menssagem'] = "Não Consegui Contato com Servidor! $erro";
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


