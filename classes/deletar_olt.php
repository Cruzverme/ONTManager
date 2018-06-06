<?php 
  include_once "../db/db_config_mysql.php";

  $oltName = filter_input(INPUT_POST,'pon');

  if($oltName || $oltName == 0)
  {
    if(!mysqli_connect_errno())
    {
      $sql_remove = "DELETE FROM pon WHERE deviceName = '$oltName'";
      $execute_remove = mysqli_query($conectar,$sql_remove);
      if($execute_remove)
      {
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