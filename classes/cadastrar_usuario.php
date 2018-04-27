<?php
  include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
  session_start();

  if (!mysqli_connect_errno())
  {
    if( isset($_POST["usuario"]) && isset($_POST["password"]) && isset($_POST["nome_usuario"]) 
      && !empty($_POST["usuario"]) && !empty($_POST["password"]) && !empty($_POST["nome_usuario"]) )
    {
        $usuario = $_POST["usuario"];
        $senha = md5($_POST["password"]);
        $nome = $_POST["nome_usuario"];
        $nivel_usuario = $_POST["nivel"];

        if ($nivel_usuario != 1) {
          $nivel_usuario = 0;
        }

        $sql_usuario_repetido = ("SELECT usuario FROM usuarios WHERE usuario = '$usuario'");
        $checar_repetido = mysqli_query($conectar,$sql_usuario_repetido);
        
        if($checar_repetido)
        { 
          if ($repetido = mysqli_num_rows($checar_repetido) != 0) 
          {
            $_SESSION['menssagem'] ="Usuario ja existe, favor tentar outro!";
            header('Location: ../users/usuario_new.php');
            mysqli_close($conectar);
            exit;
          }else
          {
            $sql_registra_usuario = "INSERT INTO usuarios (usuario,senha,nome,nivel_user) 
                                  VALUES ('$usuario','$senha','$nome',$nivel_usuario)";

            $cadastrar = mysqli_query($conectar,$sql_registra_usuario);
            if($cadastrar)
            {
              $_SESSION['menssagem'] = "Usuario Cadastrado!";
              header('Location: ../users/usuario_new.php');
              mysqli_close($conectar);
              exit;
            }else{
              $erro = mysqli_error($conectar);
              $_SESSION['menssagem'] = "Usuario Não Cadastrado! $erro";  
              header('Location: ../users/usuario_new.php');
              mysqli_close($conectar);
              exit;
            }
          }
        }
    }else{
      $_SESSION['menssagem'] = "Campos Faltando!";
      header('Location: ../users/usuario_new.php');
      mysqli_close($conectar);
      exit;
    }
  }else{
    $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
    header('Location: ../users/usuario_new.php');
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