<?php
  include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
  session_start();

  if (!mysqli_connect_errno())
  {
    if( isset($_POST["senha"]) && isset($_POST["nova_senha"]) && !empty($_POST["senha"]) && !empty($_POST["nova_senha"]) )
    {
      $senha = md5($_POST["senha"]);
      $nova_senha = md5($_POST["nova_senha"]);
      
      $user_id= $_SESSION["id_usuario"];

      
        $sql_usuario_senha = ("SELECT senha FROM usuarios WHERE senha = '$senha' AND usuario_id = '$user_id'");
        $checar_senha = mysqli_query($conectar,$sql_usuario_senha);
        
        /* numeric array */
        $row = mysqli_fetch_array($checar_senha, MYSQLI_NUM);
        if($row[0] == $senha)
        { 
          if ($senha_row = mysqli_num_rows($checar_repetido) != 0) 
          {
            $erro = mysqli_error($conectar);
            echo $_SESSION['menssagem'] = "Senha Incorreta! $erro"; 
            header('Location: ../users/usuario_edit.php');
            mysqli_close($conectar);
            exit;
          }else{
            $sql_registra_usuario = "UPDATE usuarios SET senha = '$nova_senha' 
                                  WHERE senha='$senha' AND usuario_id = '$user_id'" ;

            $cadastrar = mysqli_query($conectar,$sql_registra_usuario);
            if($cadastrar)
            {
              echo $_SESSION['menssagem'] = "Senha Alterada!";
              header('Location: ../index.php');
              mysqli_close($conectar);
              exit;
            }else{
              $erro = mysqli_error($conectar);
              echo $_SESSION['menssagem'] = "Senha Não Alterada! $erro";  
              header('Location: ../users/usuario_edit.php');
              mysqli_close($conectar);
              exit;
            }
          }
        }else{
          $erro = mysqli_error($conectar);
          echo $_SESSION['menssagem'] = "Senha Atual Não Confere! $erro";  
          header('Location: ../users/usuario_edit.php');
          mysqli_close($conectar);
          exit;
        }
    }else{
      echo $_SESSION['menssagem'] = "Campos Faltando!";
      header('Location: ../users/usuario_edit.php');
      mysqli_close($conectar);
      exit;
    }    
  }else{
    echo $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
    header('Location: ../users/usuario_edit.php');
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