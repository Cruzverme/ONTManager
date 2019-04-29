<?php 
  include_once "../db/db_config_mysql.php";
  
  $checked = filter_input(INPUT_POST,'cto_disponibilidade');
  $cto = filter_input(INPUT_POST,'cto');

  $SQL = "UPDATE ctos SET disponivel = $checked WHERE caixa_atendimento = '$cto'";
  $executa_SQL = mysqli_query($conectar,$SQL);

  mysqli_affected_rows($conectar) > 0? $msg = 1 : $msg = 0;
  
  echo $msg;
?>