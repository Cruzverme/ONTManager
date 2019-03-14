<?php
  
  include_once "../db/db_config_mysql.php";
  //iniciando sessao para enviar as msgs
  session_start();
  
  $usuario = $_SESSION["id_usuario"];
  
  $tipoCTO = filter_input(INPUT_POST,'tipoCTO');
  $ctoSelect = filter_input(INPUT_POST,'ctoSelect');
  $quantidadePortaAtendimento = filter_input(INPUT_POST,'porta');
  $maxCTO = filter_input(INPUT_POST,'nCtos');

  $olt_frame_slot_pon = filter_input(INPUT_POST,'pon');
  list($oltID,$frame,$slot,$pon) = explode('-',$olt_frame_slot_pon);
  
  $celula = null;
  
  if((substr($ctoSelect,-1)) == "B")
  {
    $ctoSelectExplode = explode('.',$ctoSelect);
    $celula = $ctoSelectExplode[0];
    $tipo = 'associada';
  }else{
    $ctoSelectExplode = explode('.',$ctoSelect);
    $celula = $ctoSelectExplode[0];  
    $tipo = null;
  }
  
  $disponibilizar = filter_input(INPUT_POST,'cto_disponivel');
  
  if($tipoCTO AND $ctoSelect AND $quantidadePortaAtendimento AND $olt_frame_slot_pon)
  {
    if (!mysqli_connect_errno())
    {
      if($disponibilizar != 1) $disponibilizar = 0; 
      
      $tipo == 'associada'? 
        $query_select_checar_cto_existente = "SELECT DISTINCT caixa_atendimento FROM ctos WHERE caixa_atendimento LIKE '$celula.%B' "
        :
        $query_select_checar_cto_existente = "SELECT DISTINCT caixa_atendimento FROM ctos WHERE caixa_atendimento LIKE '$celula.%' and caixa_atendimento NOT LIKE '$celula.%B' ";
      
      $execute_select_checar_cto_existente = mysqli_query($conectar,$query_select_checar_cto_existente);
      $linhas_retornadas = mysqli_num_rows($execute_select_checar_cto_existente);
      $ctos_cadastradas = array();

      //echo $linhas_retornadas;
      $quantidadeMaximaExpandida = $linhas_retornadas + $maxCTO;
       echo "Tipo: $tipoCTO, Dispon: $disponibilizar, Celula: $celula, MaxCTO: $maxCTO, oltID: $oltID, Frame: $frame
       SLOT: $slot, PON: $pon ";
      
      for ($quantidadeExpandida=$linhas_retornadas+1; $quantidadeExpandida <= $quantidadeMaximaExpandida ; $quantidadeExpandida++) {
        $tipo == 'associada'? $cto = $celula.'.'.$quantidadeExpandida.'B' : $cto = "$celula.$quantidadeExpandida";
        
      //  echo "</br>$cto </br>";
        
        for($portas = 1; $portas <= $quantidadePortaAtendimento; $portas++){
          //echo "$portas </br>";
          $sql_insere_caixa = ("INSERT INTO ctos(caixa_atendimento,porta_atendimento,frame_slot_pon,disponivel,pon_id_fk,tipoCTO)
            VALUES('$cto',$portas,'$frame-$slot-$pon',$disponibilizar,'$oltID','$tipoCTO')");
          $checar_insert = mysqli_query($conectar,$sql_insere_caixa);
          $checar_insert = true;
        }
        array_push($ctos_cadastradas,$cto);
      }
      
      $ctos_incluidas = implode(" ",$ctos_cadastradas); //somente para mostrar na msg de retorno as CTOs cadastradas
      
      if($checar_insert)
      {
        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
          VALUES ('CTO $cto_incluidas Expandida Pelo Usuario de Codigo $usuario','$usuario')";
        $executa_log = mysqli_query($conectar,$sql_insert_log);

        echo  $_SESSION['menssagem'] = "Caixa de Atendimento Registrada! CTOs Cadastradas: $ctos_incluidas ";
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