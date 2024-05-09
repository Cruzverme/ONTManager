<?php
  
  include_once "../db/db_config_mysql.php";
  //iniciando sessao para enviar as msgs
  session_start();
  
  $usuario = $_SESSION["id_usuario"];
  
  $tipoCTO = filter_input(INPUT_POST,'tipoCTO');
  $ctoEspecifica = filter_input(INPUT_POST,'cto');
  $quantidadePortaAtendimento = filter_input(INPUT_POST,'portasAtendimento');
  $maxCTO = 8;

  $olt_frame_slot_pon = filter_input(INPUT_POST,'pon');
  list($oltID,$frame,$slot,$pon) = explode('-',$olt_frame_slot_pon);
  
  $disponibilizar = filter_input(INPUT_POST,'cto_disponivel');
  
  if($tipoCTO AND $ctoEspecifica AND $quantidadePortaAtendimento AND $olt_frame_slot_pon)
  {
    if (!mysqli_connect_errno())
    {      
      //VERIFICA SE CTO JA EXISTE
      $verificar_cto_existente = "SELECT DISTINCT caixa_atendimento FROM ctos WHERE caixa_atendimento='$ctoEspecifica'";
      $executa_verificar_cto_existente = mysqli_query($conectar,$verificar_cto_existente);
      $linhas_retornadas = mysqli_num_rows($executa_verificar_cto_existente);

      if( $linhas_retornadas != 1 )
      {
        
        
          $InternaCTO = $ctoEspecifica;
          echo "$InternaCTO<br>";
          for($portas = 1; $portas <= $quantidadePortaAtendimento; $portas++)
          {
            $sql_insere_caixa = ("INSERT INTO ctos(caixa_atendimento,porta_atendimento,frame_slot_pon,disponivel,pon_id_fk,tipoCTO)
              VALUES('$InternaCTO',$portas,'$frame-$slot-$pon',$disponibilizar,'$oltID','$tipoCTO')");
            echo "$sql_insere_caixa <br>";   
            $checar_insert = mysqli_query($conectar,$sql_insere_caixa);
            $checar_insert = true;
          }
        
      }
    
      if($checar_insert)
      {
        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
          VALUES ('CTO $ctoEspecifica Cadastrada Pelo Usuario de Codigo $usuario','$usuario')";
        $executa_log = mysqli_query($conectar,$sql_insert_log);

        echo  $_SESSION['menssagem'] = "Caixa de Atendimento Registrada! CTO Cadastrada: $ctoEspecifica ";
        header('Location: ../cto_classes/show_ctos.php');
        mysqli_close($conectar);
        exit;
      }else{
        $erro = mysqli_error($conectar);
        echo $_SESSION['menssagem'] = "CTO Não Cadastrada! SQL: $erro";
        header('Location: ../cto_classes/show_ctos.php');
        mysqli_close('$conectar');
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
    header('Location: ../cto_classes/show_ctos.php');
    mysqli_close($conectar);
    exit;
  }
?>
