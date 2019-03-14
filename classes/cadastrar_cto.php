<?php
  include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
  session_start();
  $usuario = $_SESSION["id_usuario"];
  $area = filter_input(INPUT_POST,'area');
  $celula = filter_input(INPUT_POST,'celula');
  $tipoCTO = filter_input(INPUT_POST,'tipoCTO');
  
  $tipoCTO != "especifica"? $maxCTO = filter_input(INPUT_POST,'nCtos') : $maxCTO = 1;
  
  $disponibilizar = filter_input(INPUT_POST,'cto_disponivel');
  if($disponibilizar != 1) $disponibilizar = 0; 

  if (!mysqli_connect_errno())
  {
    if( isset($_POST["porta"]) && !empty($_POST["porta"]) && isset($_POST["pon"]) )
    {
        list($pon_id,$frame,$slot,$porta) = explode("-",$_POST["pon"]);
        $porta_atendimento = $_POST["porta"];
        $pon = "$frame-$slot-$porta";
        
        $ctos_cadastradas = array();
        for($inicio = 1; $inicio <= $maxCTO; $inicio++)
        {
          $tipoCTO != "expansao"? $cto = $area."C".$celula.".".$inicio : $cto = filter_input(INPUT_POST,'cto') ;
          $tipoCTO == "associada"? $cto = $cto."B" : null ;
          
          //VERIFICA SE CTO JA EXISTE
          $verificar_cto_existente = "SELECT DISTINCT caixa_atendimento FROM ctos WHERE caixa_atendimento='$cto'";
          $executa_verificar_cto_existente = mysqli_query($conectar,$verificar_cto_existente);
          $linhas_retornadas = mysqli_num_rows($executa_verificar_cto_existente);

          if( $linhas_retornadas != 1 )
          {
            for($portas = 1; $portas <= $porta_atendimento; $portas++)
            {
              $sql_insere_caixa = ("INSERT INTO ctos(caixa_atendimento,porta_atendimento,frame_slot_pon,disponivel,pon_id_fk,tipoCTO) VALUES('$cto',$portas,'$pon',$disponibilizar,'$pon_id','$tipoCTO')");
              $checar_insert = mysqli_query($conectar,$sql_insere_caixa);
              $checar_insert = true;
            }
            array_push($ctos_cadastradas,$cto);
          }
        }
        
        $ctos_incluidas = implode(" ",$ctos_cadastradas); //somente para mostrar na msg de retorno as CTOs cadastradas

        if($checar_insert)
        {
            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
              VALUES ('CTO $cto_incluidas Cadastrada Pelo Usuario de Codigo $usuario','$usuario')";
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
      echo $_SESSION['menssagem'] = "Campos Faltando!";
      header('Location: ../cto_classes/show_ctos.php');
      mysqli_close($conectar);
      exit;
    }
  }else{
    echo $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
    header('Location: ../index.php');
    mysqli_close($conectar);
    exit;
  }


  /*
  SQL PARA SALVAR NO RADIUS
  INSERT INTO radcheck( username, attribute, op, value) VALUES ( 'vlan2500/slot13/porta0/485754439C96D58B@vertv', 
  'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' ); qual olt

  INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

  INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
  */
?>
