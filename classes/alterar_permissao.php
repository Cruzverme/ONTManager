<?php
  include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
  session_start();
  $usuario_logado =  $_SESSION["id_usuario"];
  
  
  if (!mysqli_connect_errno())
  {
    $usuario = filter_input(INPUT_POST,'usuario');

    $botao = filter_input(INPUT_POST,'botao_validador');
    if($botao == 'remover')
    {
      
      $sql_remove = "DELETE FROM usuarios WHERE usuario = '$usuario'";
      $execute_remove = mysqli_query($conectar,$sql_remove);
      if($execute_remove)
      {
        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                        VALUES ('Usuario $usuario Removido Pelo Usuario de Codigo $usuario_logado','$usuario_logado')";
        $executa_log = mysqli_query($conectar,$sql_insert_log);

        echo $_SESSION['menssagem'] = "Usuario Excluido!";
        header('Location: ../users/alteracao_usuario.php');
        mysqli_close($conectar);
        exit;
      }else{
        $erro = mysqli_error($conectar);
        echo $_SESSION['menssagem'] = "Houve erro ao Remover o Usuario: $erro";
        header('Location: ../users/alteracao_usuario.php');
        mysqli_close($conectar);
        exit;
      }
    }else
    {
      $senha = filter_input(INPUT_POST,'senha');
      $nome = filter_input(INPUT_POST,'nome');
        
      //variaveis de permissao 
      $cadastrarONU = filter_input(INPUT_POST,'personalizada1') ?? 0;
      $modificarONU = filter_input(INPUT_POST,"personalizada2") ?? 0;
      $deletarONU = filter_input(INPUT_POST,"personalizada3") ?? 0;
      $cadastrarCTO = filter_input(INPUT_POST,"personalizada4") ?? 0;
      $desabilitarHabilitarONU = filter_input(INPUT_POST,"personalizada5") ?? 0;
      $cadastrarEquipamento = filter_input(INPUT_POST,"personalizada6") ?? 0;
      $cadastrarOLT = filter_input(INPUT_POST,"personalizada7") ?? 0;
      $cadastrarVelocidade = filter_input(INPUT_POST,"personalizada8") ?? 0;
      $cadastrarUsuarios = filter_input(INPUT_POST,"personalizada9") ?? 0;
      $alterarMacONT = filter_input(INPUT_POST,"personalizada10") ?? 0;
      $consulta_onu = filter_input(INPUT_POST,"personalizada11") ?? 0;
      $consulta_cto = filter_input(INPUT_POST,"personalizada12") ?? 0;
      $remover_cto = filter_input(INPUT_POST,"personalizada13") ?? 0;
      $remover_olt = filter_input(INPUT_POST,"personalizada14") ?? 0;
      $alterar_usuario = filter_input(INPUT_POST,"personalizada15") ?? 0;
      $consulta_relatorio_sinal = filter_input(INPUT_POST,"personalizada16") ?? 0;
      $transferir_celula = filter_input(INPUT_POST,"personalizada17") ?? 0;
      //fim variaveis de permissao
        
      ########## permissoes personalizadas ########
      $cadastrarONU == 1? $permitir_cadastrar_ONU=1 : $permitir_cadastrar_ONU=1 ;
      
      $modificarONU == 2? $permitir_alterarONU=1 : $permitir_alterarONU=0;
      
      $deletarONU == 3? $permitir_removerONU=1 : $permitir_removerONU=0;
      
      $cadastrarCTO == 4? $permitir_cadastrarCTO=1 : $permitir_cadastrarCTO=0;

      $desabilitarHabilitarONU == 5? $permitir_desabilitarHabilitar=1 : $permitir_desabilitarHabilitar=0;
      
      $cadastrarEquipamento == 6 ? $permitir_cadastrarEquipamento=1 : $permitir_cadastrarEquipamento=0;
    
      $cadastrarOLT == 7? $permitir_cadastrarOLT=1 : $permitir_cadastrarOLT=0;
    
      $cadastrarVelocidade == 8? $permitir_cadastrarVelocidade=1 : $permitir_cadastrarVelocidade=0;
    
      $cadastrarUsuarios == 9? $permitir_cadastrarUsuarios=1 : $permitir_cadastrarUsuarios=0;

      $alterarMacONT == 10? $permitir_alterar_MAC=1 : $permitir_alterar_MAC=0;
      
      $consulta_onu == 11? $permitir_consulta_onu=1 : $permitir_consulta_onu=0;
      
      $consulta_cto == 12? $permitir_consulta_cto=1 : $permitir_consulta_cto=0;
      
      $remover_cto == 13? $permitir_removerCTO=1 : $permitir_removerCTO=0;
      
      $remover_olt == 14? $permitir_removerOLT=1 : $permitir_removerOLT=0;
      
      $alterar_usuario == 15? $permitir_listar_usuario = 1 : $permitir_listar_usuario = 0;
      
      $consulta_relatorio_sinal == 16? $permitir_relatorio_sinal = 1 : $permitir_relatorio_sinal = 0;
      
      $transferir_celula == 17? $permitir_transferencia_celula = 1 : $permitir_transferencia_celula = 0;
      #######  FIM PERMISSOES PERSONALIZADAS ########

      if($senha == null)
      {
        $sql_altera_usuario = "UPDATE usuarios SET nome='$nome' WHERE usuario='$usuario'";
      }else{
        $senhaCript = md5($senha);
        $sql_altera_usuario = "UPDATE usuarios SET nome='$nome', senha='$senhaCript' WHERE usuario='$usuario'";
      }
      $cadastrar = mysqli_query($conectar,$sql_altera_usuario);
      if($cadastrar)
      {
        $sql_get_userID = ("SELECT usuario_id FROM usuarios WHERE usuario = '$usuario' " );
        $getUserID = mysqli_query($conectar,$sql_get_userID);
        $dados = @mysqli_fetch_array($getUserID);
        $userID = $dados['usuario_id']; //pega o usuario_id

        #### SE TIVER NOVAS OPÇOES DEVERA INCLUIR NO BANCO E ACRESCENTAR A COLUNA AQUI ####
        $sql_cadastrar_permissao = " UPDATE usuario_permissao SET cadastrar_onu = $permitir_cadastrar_ONU, 
            deletar_onu = $permitir_removerONU, modificar_onu = $permitir_alterarONU, 
            desativar_ativar_onu = $permitir_desabilitarHabilitar, cadastrar_cto = $permitir_cadastrarCTO, 
            cadastrar_olt = $permitir_cadastrarOLT, cadastrar_velocidade = $permitir_cadastrarVelocidade, 
            cadastrar_usuario = $permitir_cadastrarUsuarios, cadastrar_equipamento = $permitir_cadastrarEquipamento,
            alterar_mac_ont = $permitir_alterar_MAC, consulta_ont = $permitir_consulta_onu, 
            consulta_cto = $permitir_consulta_cto, remover_cto = $permitir_removerCTO, remover_olt = $permitir_removerOLT,
            alterar_usuario = $permitir_listar_usuario, relatorio_sinal = $permitir_relatorio_sinal, 
            transferir_celula = $permitir_transferencia_celula
            WHERE usuario = $userID ";

        $permissoes = mysqli_query($conectar,$sql_cadastrar_permissao);
      
        if($permissoes)
        {
          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
              VALUES ('Usuario $usuario Alterado Pelo Usuario de Codigo $usuario_logado','$usuario_logado')";                    
          $executa_log = mysqli_query($conectar,$sql_insert_log);
          echo $_SESSION['menssagem'] = "Informações Alteradas!";
          header('Location: ../users/alteracao_usuario.php');
          mysqli_close($conectar);
          exit;
        }else{
          $erro = mysqli_error($conectar);
          $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
              VALUES ('Usuario $usuario Alterado Sem Permissões Pelo Usuario de Codigo $usuario_logado' erro: $erro,'$usuario_logado')";                    
          $executa_log = mysqli_query($conectar,$sql_insert_log);
          
          echo $_SESSION['menssagem'] = "Informações Alteradas, porem sem alterar permissão! $erro";
          header('Location: ../users/alteracao_usuario.php');
          mysqli_close($conectar);
          exit;
        }
      }else{
        $erro = mysqli_error($conectar);
        echo $_SESSION['menssagem'] = "Informações Não Alteradas! $erro";
        header('Location: ../users/alteracao_usuario.php');
        mysqli_close($conectar);
        exit;
      }//fim cadastrar
    }//fim verifica botao
  }else{
    echo $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
    header('Location: ../users/alteracao_usuario.php');
    mysqli_close($conectar);
    exit;
  }

?>