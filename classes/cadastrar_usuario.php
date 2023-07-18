<?php
include_once "../db/db_config_mysql.php";
session_start();
$usuario_logado =  $_SESSION["id_usuario"];

function redirectWithError($message)
{
    $_SESSION['menssagem'] = $message;
    header('Location: ../users/usuario_new.php');
    exit;
}

function checkPermission($userPermission, $permissionCode, $levelUser)
{
    return ($userPermission == $permissionCode || $levelUser == 1) ? 1 : 0;
}

if (!mysqli_connect_errno()) {
    if (
        isset($_POST["usuario"], $_POST["password"], $_POST["nome_usuario"]) &&
        !empty($_POST["usuario"]) && !empty($_POST["password"]) && !empty($_POST["nome_usuario"])
    ) {
        // Sanitize and validate input
        $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
        $senha = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $nome = filter_input(INPUT_POST, 'nome_usuario', FILTER_SANITIZE_STRING);
        $nivel_usuario = intval($_POST["nivel"] ?? 0);

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
        $consulta_relatorio_sinal = $_POST["personalizada16"] ?? 0;
        $transferir_celula = $_POST["personalizada17"] ?? 0;
        $cadastrar_corporativo = $_POST["personalizada18"] ?? 0;
        $cadastrar_ip = $_POST["personalizada19"] ?? 0;
        $consulta_log = $_POST["personalizada20"] ?? 0;

        //fim variaveis de permissao

        //permissoes personalizadas
        $permitir_cadastrar_ONU = checkPermission($cadastrarONU, 1, $nivel_usuario);
        $permitir_alterarONU = checkPermission($modificarONU, 2, $nivel_usuario);
        $permitir_removerONU = checkPermission($deletarONU, 3, $nivel_usuario);
        $permitir_cadastrarCTO = checkPermission($cadastrarCTO, 4, $nivel_usuario);
        $permitir_desabilitarHabilitar = checkPermission($desabilitarHabilitarONU, 5, $nivel_usuario);
        $permitir_cadastrarEquipamento = checkPermission($cadastrarEquipamento, 6, $nivel_usuario);
        $permitir_cadastrarOLT = checkPermission($cadastrarOLT, 7, $nivel_usuario);
        $permitir_cadastrarVelocidade = checkPermission($cadastrarVelocidade, 8, $nivel_usuario);
        $permitir_cadastrarUsuarios = checkPermission($cadastrarUsuarios, 9, $nivel_usuario);
        $permitir_alterar_MAC = checkPermission($alterarMacONT, 10, $nivel_usuario);
        $permitir_consulta_onu = checkPermission($consulta_onu, 11, $nivel_usuario);
        $permitir_consulta_cto = checkPermission($consulta_cto, 12, $nivel_usuario);
        $permitir_removerCTO = checkPermission($remover_cto, 13, $nivel_usuario);
        $permitir_removerOLT = checkPermission($remover_olt, 14, $nivel_usuario);
        $permitir_listar_usuario = checkPermission($alterar_usuario, 15, $nivel_usuario);
        $permitir_relatorio_sinal = checkPermission($consulta_relatorio_sinal, 16, $nivel_usuario);
        $permitir_transferencia_celula = checkPermission($transferir_celula, 17, $nivel_usuario);
        $permitir_cadastro_corporativo = checkPermission($cadastrar_corporativo, 18, $nivel_usuario);
        $permitir_cadastro_ip = checkPermission($cadastrar_ip, 19, $nivel_usuario);
        $permitir_consultaLog = checkPermission($consulta_log, 20, $nivel_usuario);

        // FIM PERMISSOES PERSONALIZADAS

        $sql_usuario_repetido = ("SELECT usuario FROM usuarios WHERE usuario = '$usuario'");
        $checar_repetido = mysqli_query($conectar,$sql_usuario_repetido);

        if ($checar_repetido) {
            if (mysqli_num_rows($checar_repetido) != 0) {
                redirectWithError("Usuário já existe, favor tentar outro!");
            } else {
                $sql_registra_usuario = "INSERT INTO usuarios (usuario,senha,nome) 
                                  VALUES ('$usuario','$senha','$nome')";
                $cadastrar = mysqli_query($conectar,$sql_registra_usuario);
                if ($cadastrar) {
                    $sql_get_userID = ("SELECT usuario_id FROM usuarios WHERE usuario = '$usuario' " );
                    $getUserID = mysqli_query($conectar,$sql_get_userID);
                    $dados = @mysqli_fetch_array($getUserID);
                    $userID = $dados['usuario_id'];

                    $sql_cadastrar_permissao = "INSERT INTO usuario_permissao (usuario, cadastrar_onu, cadastrar_onu_corp, deletar_onu, modificar_onu,
                      desativar_ativar_onu, cadastrar_cto, cadastrar_olt, cadastrar_velocidade, cadastrar_usuario, cadastrar_equipamento,
                      alterar_mac_ont, consulta_ont, consulta_cto, consulta_log, remover_cto, remover_olt,
                      alterar_usuario, relatorio_sinal, transferir_celula, cadastrar_ip)
                      VALUES ($userID,$permitir_cadastrar_ONU, $permitir_cadastro_corporativo, $permitir_removerONU,$permitir_alterarONU,$permitir_desabilitarHabilitar,
                      $permitir_cadastrarCTO,$permitir_cadastrarOLT,$permitir_cadastrarVelocidade, $permitir_cadastrarUsuarios, $permitir_cadastrarEquipamento,
                      $permitir_alterar_MAC,$permitir_consulta_onu,$permitir_consulta_cto, $permitir_consultaLog, $permitir_removerCTO, $permitir_removerOLT,
                      $permitir_listar_usuario, $permitir_relatorio_sinal, $permitir_transferencia_celula, $permitir_cadastro_ip)";

                    $permissoes = mysqli_query($conectar,$sql_cadastrar_permissao);

                    if ($permissoes) {
                        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
                            VALUES ('Usuario $usuario Cadastrado Pelo Usuario de Codigo $usuario_logado','$usuario_logado')";
                        $executa_log = mysqli_query($conectar,$sql_insert_log);

                        $_SESSION['menssagem'] = "Usuario Cadastrado!";
                        header('Location: ../users/usuario_new.php');
                        mysqli_close($conectar);
                        exit;
                    } else {
                        $erro = mysqli_error($conectar);
                        $sql_insert_log = "INSERT INTO log (registro, codigo_usuario) 
                            VALUES ('Usuario $usuario Alterado Sem Permissões Pelo Usuario de Codigo $usuario_logado' erro: $erro,'$usuario_logado')";
                        $executa_log = mysqli_query($conectar, $sql_insert_log);
                        redirectWithError("Usuário cadastrado, porém sem permissão! $erro");
                    }
                } else {
                    $erro = mysqli_error($conectar);
                    redirectWithError("Usuário Não Cadastrado! $erro");
                }
            }
        } else {
            redirectWithError("Não Consegui Contato com Servidor!");
        }
    } else {
        redirectWithError("Campos Faltando!");
    }
} else {
    redirectWithError("Erro de conexão com o banco de dados.");
}

/*
  SQL PARA SALVAR NO RADIUS
  INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' );

  INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

  INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
  */
?>