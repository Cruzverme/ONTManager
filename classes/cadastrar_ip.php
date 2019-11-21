<?php

  include_once "../db/db_config_mysql.php";
  session_start();

  $ipInicial = filter_input(INPUT_POST,'ip_inicial',FILTER_VALIDATE_IP);
  $ipFinal = filter_input(INPUT_POST,'ip_final',FILTER_VALIDATE_IP);
  
  if( $ipInicial && $ipFinal)
  {
    #LAST IP DONT MUST BE SMALLER THAN INITIAL IP
    $ipFinal < $ipInicial? die('Ip Final Não Pode Ser Menor que a Inicial') : '';
    echo "VOU CADASTRAR SEU INUTIL!";
  }else{
    die('Está faltando definir o range de IP');
  }
    

