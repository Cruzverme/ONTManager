<?php 
  include_once "../db/db_config_mysql.php";

  $oltName = filter_input(INPUT_POST,'pon');
  $usuario = filter_input(INPUT_SESSION,'id_usuario');
  
  if($oltName || $oltName == 0)
  {
    if(!mysqli_connect_errno())
    {
      $sql_remove = "DELETE FROM pon WHERE deviceName = '$oltName'";
      $execute_remove = mysqli_query($conectar,$sql_remove);
      if($execute_remove)
      {
        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                        VALUES ('$oltName Removido Pelo Usuario de Codigo $usuario','$usuario')";                    
        $executa_log = mysqli_query($conectar,$sql_insert_log);

        echo $_SESSION['menssagem'] = "OLT Removida!";
        header('Location: ../cto_classes/remover_olt.php');
        mysqli_close($conectar);
        exit;
      }else{
        $erro = mysqli_error($conectar);
        echo $_SESSION['menssagem'] = "Houve erro ao deletar a OLT: $erro";
        header('Location: ../cto_classes/remover_olt.php');
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
    echo $_SESSION['menssagem'] = "OLT Inexistente! $erro";
    header('Location: ../cto_classes/remover_olt.php');
    mysqli_close($conectar);
    exit;
  }



?>