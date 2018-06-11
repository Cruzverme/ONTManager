<?php
  include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
  session_start();
  $usuario_logado =  $_SESSION["id_usuario"];
  if (!mysqli_connect_errno())
  {
    if( isset($_POST["usuario"]) && isset($_POST["password"]) && isset($_POST["nome_usuario"]) 
      && !empty($_POST["usuario"]) && !empty($_POST["password"]) && !empty($_POST["nome_usuario"]) )
    {
        $usuario = $_POST["usuario"];
        $senha = md5($_POST["password"]);
        $nome = $_POST["nome_usuario"];
        $nivel_usuario = $_POST["nivel"] ?? 0;
        
        //variaveis de permissao 
        $cadastrarONU = $_POST["personalizada1"] ?? 0;
        $modificarONU = $_POST["personalizada2"] ?? 0;
        $deletarONU = $_POST["personalizada3"] ?? 0;
        $cadastrarCTO = $_POST["personalizada4"] ?? 0;
        $desabilitarHabilitarONU = $_POST["personalizada5"] ?? 0;
        $cadastrarEquipamento = $_POST["personalizada6"] ?? 0;
        $cadastrarOLT = $_POST["personalizada7"] ?? 0;
        $cadastrarVelocidade = $_POST["personalizada8"] ?? 0;
        $cadastrarUsuarios = $_POST["personalizada9"] ?? 0;
        $alterarMacONT = $_POST["personalizada10"] ?? 0;
        $consulta_onu = $_POST["personalizada11"] ?? 0;
        $consulta_cto = $_POST["personalizada12"] ?? 0;
        $remover_cto = $_POST["personalizada13"] ?? 0;
        $remover_olt = $_POST["personalizada14"] ?? 0;
        $alterar_usuario = $_POST["personalizada15"] ?? 0;
        //fim variaveis de permissao
        
        //permissoes personalizadas
        if($cadastrarONU == 1 || $nivel_usuario == 1 )
        {
          $permitir_cadastrar_ONU=1;
        }else{
          $permitir_cadastrar_ONU=0;
        }

        if($modificarONU == 2 || $nivel_usuario == 1)
        {
          $permitir_alterarONU=1; 
        }else{
          $permitir_alterarONU=0;
        }

        if($deletarONU == 3 || $nivel_usuario == 1)
        {
          $permitir_removerONU=1;
        }else{
          $permitir_removerONU=0;
        }
        
        if($cadastrarCTO == 4 || $nivel_usuario == 1)
        {
          $permitir_cadastrarCTO=1;
        }else{
          $permitir_cadastrarCTO=0;
        }
        
        if($desabilitarHabilitarONU == 5 || $nivel_usuario == 1)
        {
          $permitir_desabilitarHabilitar=1;
        }else{
          $permitir_desabilitarHabilitar=0;
        }
        
        if($cadastrarEquipamento == 6 || $nivel_usuario == 1)
        {
          $permitir_cadastrarEquipamento=1;
        }else{
          $permitir_cadastrarEquipamento=0;
        }
        
        if($cadastrarOLT == 7 || $nivel_usuario == 1)
        {
          $permitir_cadastrarOLT=1;
        }else{
          $permitir_cadastrarOLT=0;
        }
        
        if($cadastrarVelocidade == 8 || $nivel_usuario == 1)
        {
          $permitir_cadastrarVelocidade=1;
        }else{
          $permitir_cadastrarVelocidade=0;
        
        }
        
        if($cadastrarUsuarios == 9 || $nivel_usuario == 1)
        {
          $permitir_cadastrarUsuarios=1;
        }else{
          $permitir_cadastrarVelocidade=0;
        }

        if($alterarMacONT == 10 || $nivel_usuario == 1 )
        {
          $permitir_alterar_MAC=1;
        }else{
          $permitir_alterar_MAC=0;
        }
        if($consulta_onu == 11 || $nivel_usuario == 1 )
        {
          $permitir_consulta_onu=1;
        }else{
          $permitir_consulta_onu=0;
        }
        if($consulta_cto == 12 || $nivel_usuario == 1 )
        {
          $permitir_consulta_cto=1;
        }else{
          $permitir_consulta_cto=0;
        }
        if($remover_cto == 13 || $nivel_usuario == 1 )
        {
          $permitir_removerCTO=1;
        }else{
          $permitir_removerCTO=0;
        }
        if($remover_olt == 14 || $nivel_usuario == 1 )
        {
          $permitir_removerOLT=1;
        }else{
          $permitir_removerOLT=0;
        }
        if($alterar_usuario == 15 || $nivel_usuario == 1)
        {
          $permitir_listar_usuario = 1;
        }else{
          $permitir_listar_usuario = 0;
        }
        

        // FIM PERMISSOES PERSONALIZADAS

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
            $sql_registra_usuario = "INSERT INTO usuarios (usuario,senha,nome) 
                                  VALUES ('$usuario','$senha','$nome')";
            $cadastrar = mysqli_query($conectar,$sql_registra_usuario);
            if($cadastrar)
            {
              $sql_get_userID = ("SELECT usuario_id FROM usuarios WHERE usuario = '$usuario' " );
              $getUserID = mysqli_query($conectar,$sql_get_userID);
              $dados = @mysqli_fetch_array($getUserID); 
              $userID = $dados['usuario_id']; //pega o usuario_id

              $sql_cadastrar_permissao = "INSERT INTO usuario_permissao (usuario, cadastrar_onu, deletar_onu, modificar_onu,
                  desativar_ativar_onu, cadastrar_cto, cadastrar_olt, cadastrar_velocidade, cadastrar_usuario, cadastrar_equipamento,
                  alterar_mac_ont, consulta_ont, consulta_cto, remover_cto, remover_olt,
                  alterar_usuario)
                  VALUES ($userID,$permitir_cadastrar_ONU,$permitir_removerONU,$permitir_alterarONU,$permitir_desabilitarHabilitar,
                  $permitir_cadastrarCTO,$permitir_cadastrarOLT,$permitir_cadastrarVelocidade, $permitir_cadastrarUsuarios, $permitir_cadastrarEquipamento,
                  $permitir_alterar_MAC,$permitir_consulta_onu,$permitir_consulta_onu, $permitir_removerCTO, $permitir_removerOLT,
                  $permitir_listar_usuario)";

              $permissoes = mysqli_query($conectar,$sql_cadastrar_permissao);
              
              if($permissoes)
              {  
                $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                  VALUES ('Usuario $usuario Cadastrado Pelo Usuario de Codigo $usuario_logado','$usuario_logado')";                    
                $executa_log = mysqli_query($conectar,$sql_insert_log);
                
                $_SESSION['menssagem'] = "Usuario Cadastrado!";
                header('Location: ../users/usuario_new.php');
                mysqli_close($conectar);
                exit;
              }else{
                $erro = mysqli_error($conectar);

                $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                  VALUES ('Usuario $usuario Alterado Sem Permissões Pelo Usuario de Codigo $usuario_logado' erro: $erro,'$usuario_logado')";                    
                $executa_log = mysqli_query($conectar,$sql_insert_log);

                $_SESSION['menssagem'] = "Usuario Cadastrado, porem sem permissão! $erro";  
                header('Location: ../users/usuario_new.php');
                mysqli_close($conectar);
                exit;
              }
            }else{
              $erro = mysqli_error($conectar);
              $_SESSION['menssagem'] = "Usuario Não Cadastrado! $erro";  
              header('Location: ../users/usuario_new.php');
              mysqli_close($conectar);
              exit;
            }//fim cadastrar
          }//fim cadastro
        } //FIm checa repetido
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