<?php

  include_once "../db/db_config_mysql.php";
  session_start();

  $ipInicial = filter_input(INPUT_POST,'ip_inicial',FILTER_VALIDATE_IP);
  $ipFinal = filter_input(INPUT_POST,'ip_final',FILTER_VALIDATE_IP);
  
  if( $ipInicial && $ipFinal)
  {

    $inicial = explode('.',$ipInicial);
    $final = explode('.',$ipFinal);

    #LAST IP DONT MUST BE SMALLER THAN INITIAL IP
    $inicial[3] > $final[3]? die("Ip Final Não Pode Ser Menor que a Inicial") : '';

    if(($inicial[0] == $final[0]) && ($inicial[1] == $final[1]) && ($inicial[2] == $final[2]) )
    {
      $ip = $inicial[3];
      $naoInseridos = array();
      $total = 0;
      while($inicial[3] <= $final[3])
      {
        $ipReal = "$inicial[0].$inicial[1].$inicial[2].$ip";

        $sqlInsertIp = "INSERT INTO ips_valido SET numero_ip = '$ipReal'";
        $execute = mysqli_query($conectar,$sqlInsertIp);
        
        if(mysqli_errno($conectar) == 1062)
        {
          array_push($naoInseridos,$ipReal);
        }
        $ip = $inicial[3]+=1;
        $total+=1;
      }
      $totalNaoInseridos = count($naoInseridos);
      
      $totalNaoInseridos > 0? $msg = "$totalNaoInseridos de $total IPs não foram inseridos devido já estarem cadastrados!" : $msg = "$ipInicial até $ipFinal foram cadastrados!";
      
      echo $msg;
    }  
    else
      echo "A Classe de IP Está diferente, favor corrigir!";
  }else{
    die('Está faltando definir o range de IP');
  }