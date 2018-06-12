<?php 
  include_once "../db/db_config_mysql.php";

  $cto = filter_input(INPUT_POST,'cto');
  $usuario = filter_input(INPUT_SESSION,'id_usuario');
  if($cto || $cto == 0)
  {
    if(!mysqli_connect_errno())
    {
      $sql_remove = "DELETE FROM ctos WHERE caixa_atendimento = '$cto'";
      $execute_remove = mysqli_query($conectar,$sql_remove);
      if($execute_remove)
      {
        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
            VALUES ('$cto Removido Pelo Usuario de Codigo $usuario','$usuario')";                    
        $executa_log = mysqli_query($conectar,$sql_insert_log);
        
        echo $_SESSION['menssagem'] = "CTO Removida!";
        header('Location: ../cto_classes/remover_cto.php');
        mysqli_close($conectar);
        exit;
      }else{
        echo $_SESSION['menssagem'] = "Houve erro ao deletar a CTO: $erro";
        header('Location: ../cto_classes/remover_cto.php');
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
    $erro = mysqli_error($conectar);
    echo $_SESSION['menssagem'] = "CTO Inexistente! $erro";
    header('Location: ../cto_classes/remover_cto.php');
    mysqli_close($conectar);
    exit;
  }

    



?>